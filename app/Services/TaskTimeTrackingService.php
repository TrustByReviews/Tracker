<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskTimeLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TaskTimeTrackingService
{
    /**
     * Verificar límite de tareas simultáneas para un usuario
     */
    public function checkSimultaneousTasksLimit(User $user): bool
    {
        // Verificar si el usuario tiene permiso para tareas simultáneas sin límite
        if ($user->hasPermission('unlimited_simultaneous_tasks')) {
            return true; // Sin límite
        }
        
        // Contar tareas activas del usuario
        $activeTasksCount = Task::where('user_id', $user->id)
            ->where('is_working', true)
            ->where('status', 'in progress')
            ->count();
        
        // Límite de 3 tareas simultáneas
        return $activeTasksCount < 3;
    }
    
    /**
     * Obtener el número de tareas activas de un usuario
     */
    public function getActiveTasksCount(User $user): int
    {
        return Task::where('user_id', $user->id)
            ->where('is_working', true)
            ->where('status', 'in progress')
            ->count();
    }
    
    /**
     * Iniciar trabajo en una tarea
     */
    public function startWork(Task $task, User $user): bool
    {
        try {
            DB::beginTransaction();
            
            // Verificar que la tarea está asignada al usuario
            if ($task->user_id !== $user->id) {
                throw new \Exception('No tienes permisos para trabajar en esta tarea');
            }
            
            // Verificar que la tarea no está ya en progreso
            if ($task->is_working) {
                throw new \Exception('Ya estás trabajando en esta tarea');
            }
            
            // Verificar que la tarea no está completada
            if ($task->status === 'done') {
                throw new \Exception('No puedes trabajar en una tarea completada');
            }
            
            // Verificar límite de tareas simultáneas
            if (!$this->checkSimultaneousTasksLimit($user)) {
                $activeCount = $this->getActiveTasksCount($user);
                throw new \Exception("Ya tienes {$activeCount} tareas activas. El límite es 3 tareas simultáneas. Contacta a tu administrador si necesitas trabajar en más tareas.");
            }
            
            // Crear log de inicio
            $timeLog = TaskTimeLog::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'started_at' => now(),
                'action' => 'start',
                'notes' => 'Inicio de trabajo'
            ]);
            
            // Actualizar tarea
            $task->update([
                'status' => 'in progress',
                'work_started_at' => now(),
                'is_working' => true,
                'actual_start' => $task->actual_start ?? now()->format('Y-m-d'),
                'auto_paused' => false,
                'auto_paused_at' => null,
                'auto_pause_reason' => null,
                'alert_count' => 0,
                'last_alert_at' => null
            ]);
            
            Log::info('Trabajo iniciado en tarea', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'time_log_id' => $timeLog->id
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al iniciar trabajo en tarea', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'user_id' => $user->id
            ]);
            throw $e;
        }
    }
    
    /**
     * Pausar trabajo en una tarea
     */
    public function pauseWork(Task $task, User $user): bool
    {
        try {
            DB::beginTransaction();
            
            // Verificar que la tarea está asignada al usuario
            if ($task->user_id !== $user->id) {
                throw new \Exception('No tienes permisos para pausar esta tarea');
            }
            
            // Verificar que la tarea está en progreso
            if (!$task->is_working) {
                throw new \Exception('No hay trabajo activo en esta tarea');
            }
            
            // Obtener el log activo
            $activeLog = TaskTimeLog::where('task_id', $task->id)
                ->where('user_id', $user->id)
                ->whereNotNull('started_at')
                ->whereNull('paused_at')
                ->whereNull('finished_at')
                ->latest()
                ->first();
            
            if (!$activeLog) {
                throw new \Exception('No se encontró sesión activa de trabajo');
            }
            
            // Calcular tiempo transcurrido con mayor precisión
            $startTime = \Carbon\Carbon::parse($activeLog->started_at);
            $endTime = \Carbon\Carbon::now();
            
            // Calcular duración usando valor absoluto y redondear a entero para evitar problemas de zona horaria
            $duration = (int) round($endTime->diffInSeconds($startTime, true));
            
            // Log para debug si hay problemas de tiempo
            if ($endTime->lt($startTime)) {
                Log::warning('Tiempo de fin anterior al tiempo de inicio, usando valor absoluto', [
                    'task_id' => $task->id,
                    'started_at' => $activeLog->started_at,
                    'startTime' => $startTime->toDateTimeString(),
                    'endTime' => $endTime->toDateTimeString(),
                    'raw_difference' => $endTime->diffInSeconds($startTime, false),
                    'absolute_duration' => $duration
                ]);
            }
            
            // Log para debug
            Log::info('Calculando duración de pausa', [
                'task_id' => $task->id,
                'started_at' => $activeLog->started_at,
                'startTime' => $startTime->toDateTimeString(),
                'now' => $endTime->toDateTimeString(),
                'duration_seconds' => $duration,
                'total_before' => $task->total_time_seconds,
                'diff_in_seconds' => $endTime->diffInSeconds($startTime, false)
            ]);
            
            // Actualizar log
            $activeLog->update([
                'paused_at' => now(),
                'duration_seconds' => $duration,
                'action' => 'pause'
            ]);
            
            // Actualizar tarea
            $task->update([
                'is_working' => false,
                'total_time_seconds' => $task->total_time_seconds + $duration
            ]);
            
            Log::info('Trabajo pausado en tarea', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'duration_seconds' => $duration,
                'time_log_id' => $activeLog->id
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al pausar trabajo en tarea', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'user_id' => $user->id
            ]);
            throw $e;
        }
    }
    
    /**
     * Reanudar trabajo en una tarea
     */
    public function resumeWork(Task $task, User $user): bool
    {
        try {
            DB::beginTransaction();
            
            // Verificar que la tarea está asignada al usuario
            if ($task->user_id !== $user->id) {
                throw new \Exception('No tienes permisos para reanudar esta tarea');
            }
            
            // Verificar que la tarea no está ya en progreso
            if ($task->is_working) {
                throw new \Exception('Ya estás trabajando en esta tarea');
            }
            
            // Obtener el último log pausado
            $pausedLog = TaskTimeLog::where('task_id', $task->id)
                ->where('user_id', $user->id)
                ->whereNotNull('paused_at')
                ->whereNull('resumed_at')
                ->whereNull('finished_at')
                ->latest()
                ->first();
            
            if (!$pausedLog) {
                throw new \Exception('No se encontró sesión pausada de trabajo');
            }
            
            // Actualizar log pausado
            $pausedLog->update([
                'resumed_at' => now(),
                'action' => 'resume'
            ]);
            
            // Crear nuevo log de reanudación (este será el log activo)
            $newLog = TaskTimeLog::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'started_at' => now(),
                'action' => 'resume',
                'notes' => 'Reanudación de trabajo'
            ]);
            
            // Log para debug
            Log::info('Reanudando trabajo - nuevo log creado', [
                'task_id' => $task->id,
                'paused_log_id' => $pausedLog->id,
                'new_log_id' => $newLog->id,
                'new_log_started_at' => $newLog->started_at
            ]);
            
            // Actualizar tarea
            $task->update([
                'is_working' => true,
                'work_started_at' => now()
            ]);
            
            // El total_time_seconds ya está actualizado desde cuando se pausó
            // No necesitamos modificarlo aquí, solo mantener el valor acumulado
            
            Log::info('Trabajo reanudado en tarea', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'paused_log_id' => $pausedLog->id,
                'new_log_id' => $newLog->id
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al reanudar trabajo en tarea', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'user_id' => $user->id
            ]);
            throw $e;
        }
    }
    
    /**
     * Finalizar trabajo en una tarea
     */
    public function finishWork(Task $task, User $user): bool
    {
        try {
            DB::beginTransaction();
            
            // Verificar que la tarea está asignada al usuario
            if ($task->user_id !== $user->id) {
                throw new \Exception('No tienes permisos para finalizar esta tarea');
            }
            
            // Verificar que la tarea está en progreso
            if (!$task->is_working) {
                throw new \Exception('No hay trabajo activo en esta tarea');
            }
            
            // Obtener el log activo
            $activeLog = TaskTimeLog::where('task_id', $task->id)
                ->where('user_id', $user->id)
                ->whereNotNull('started_at')
                ->whereNull('paused_at')
                ->whereNull('finished_at')
                ->latest()
                ->first();
            
            if (!$activeLog) {
                throw new \Exception('No se encontró sesión activa de trabajo');
            }
            
            // Calcular tiempo transcurrido con mayor precisión
            $startTime = \Carbon\Carbon::parse($activeLog->started_at);
            $endTime = now();
            $duration = max(0, $endTime->diffInSeconds($startTime, false));
            
            // Actualizar log
            $activeLog->update([
                'finished_at' => now(),
                'duration_seconds' => $duration,
                'action' => 'finish'
            ]);
            
            // Actualizar tarea
            $task->update([
                'status' => 'done',
                'is_working' => false,
                'total_time_seconds' => $task->total_time_seconds + $duration,
                'actual_finish' => now()->format('Y-m-d'),
                'approval_status' => 'pending' // Pendiente de revisión por team leader
            ]);
            
            Log::info('Trabajo finalizado en tarea', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'duration_seconds' => $duration,
                'time_log_id' => $activeLog->id
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al finalizar trabajo en tarea', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'user_id' => $user->id
            ]);
            throw $e;
        }
    }
    
    /**
     * Obtener tiempo actual de trabajo en una tarea
     */
    public function getCurrentWorkTime(Task $task): int
    {
        if (!$task->is_working || !$task->work_started_at) {
            return $task->total_time_seconds;
        }
        
        $currentSessionTime = max(0, now()->diffInSeconds($task->work_started_at));
        return $task->total_time_seconds + $currentSessionTime;
    }
    
    /**
     * Obtener logs de tiempo de una tarea
     */
    public function getTaskTimeLogs(Task $task): \Illuminate\Database\Eloquent\Collection
    {
        return TaskTimeLog::where('task_id', $task->id)
            ->with('user')
            ->orderBy('started_at', 'desc')
            ->get();
    }
    
    /**
     * Obtener tareas en progreso de un usuario
     */
    public function getActiveTasksForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('user_id', $user->id)
            ->where('is_working', true)
            ->with(['sprint', 'project'])
            ->get();
    }
    
    /**
     * Obtener tareas pausadas de un usuario
     */
    public function getPausedTasksForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('user_id', $user->id)
            ->where('is_working', false)
            ->where('status', 'in progress')
            ->whereHas('timeLogs', function ($query) {
                $query->whereNotNull('paused_at')
                      ->whereNull('resumed_at');
            })
            ->with(['sprint', 'project'])
            ->get();
    }

    /**
     * Reanudar tarea auto-pausada
     */
    public function resumeAutoPausedTask(Task $task, User $user): bool
    {
        try {
            DB::beginTransaction();
            
            // Verificar que la tarea está asignada al usuario
            if ($task->user_id !== $user->id) {
                throw new \Exception('No tienes permisos para reanudar esta tarea');
            }
            
            // Verificar que la tarea está auto-pausada
            if (!$task->auto_paused) {
                throw new \Exception('Esta tarea no está auto-pausada');
            }
            
            // Crear nuevo log de reanudación
            $newLog = TaskTimeLog::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'started_at' => now(),
                'action' => 'resume_auto_paused',
                'notes' => 'Reanudación de tarea auto-pausada'
            ]);
            
            // Actualizar tarea
            $task->update([
                'is_working' => true,
                'work_started_at' => now(),
                'auto_paused' => false,
                'auto_paused_at' => null,
                'auto_pause_reason' => null,
                'alert_count' => 0,
                'last_alert_at' => null
            ]);
            
            Log::info('Tarea auto-pausada reanudada', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'new_log_id' => $newLog->id
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al reanudar tarea auto-pausada', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'user_id' => $user->id
            ]);
            throw $e;
        }
    }

    /**
     * Resetear contadores de alerta cuando se inicia trabajo manualmente
     */
    public function resetAlertCounters(Task $task): void
    {
        $task->update([
            'alert_count' => 0,
            'last_alert_at' => null
        ]);
        
        Log::info('Contadores de alerta reseteados', [
            'task_id' => $task->id,
            'task_name' => $task->name
        ]);
    }

    /**
     * Obtener tareas auto-pausadas de un usuario
     */
    public function getAutoPausedTasksForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('user_id', $user->id)
            ->where('auto_paused', true)
            ->where('status', 'in progress')
            ->with(['sprint', 'project'])
            ->get();
    }
} 
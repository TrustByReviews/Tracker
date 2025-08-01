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
                'actual_start' => $task->actual_start ?? now()->format('Y-m-d')
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
            
            // Calcular tiempo transcurrido
            $duration = max(0, now()->diffInSeconds($activeLog->started_at));
            
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
            
            // Crear nuevo log de reanudación
            $newLog = TaskTimeLog::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'started_at' => now(),
                'action' => 'resume',
                'notes' => 'Reanudación de trabajo'
            ]);
            
            // Actualizar tarea
            $task->update([
                'is_working' => true,
                'work_started_at' => now()
            ]);
            
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
            
            // Calcular tiempo transcurrido
            $duration = max(0, now()->diffInSeconds($activeLog->started_at));
            
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
} 
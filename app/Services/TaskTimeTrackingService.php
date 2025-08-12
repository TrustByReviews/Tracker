<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskTimeLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Task Time Tracking Service Class
 * 
 * This service handles all time tracking operations for tasks including start, pause,
 * resume, and finish work sessions. It manages the complete workflow of time tracking
 * with validation, logging, and automatic pause functionality.
 * 
 * Features:
 * - Start, pause, resume, and finish work sessions
 * - Automatic task pausing for inactivity
 * - Simultaneous task limit validation (max 3)
 * - Comprehensive time logging and tracking
 * - Alert system for long work sessions
 * - Auto-pause functionality for task management
 * 
 * @package App\Services
 * @author System
 * @version 1.0
 */
class TaskTimeTrackingService
{
    /**
     * Check if a user can work on additional tasks (simultaneous task limit)
     * 
     * Validates whether a user can start working on a new task based on
     * their current active task count and permissions.
     * 
     * @param User $user The user to check limits for
     * @return bool True if user can work on additional tasks, false otherwise
     */
    public function checkSimultaneousTasksLimit(User $user): bool
    {
        // Check if user has permission for unlimited simultaneous tasks
        if ($user->hasPermission('unlimited_simultaneous_tasks')) {
            return true; // No limit
        }
        
        // Count user's active tasks
        $activeTasksCount = Task::where('user_id', $user->id)
            ->where('is_working', true)
            ->where('status', 'in progress')
            ->count();
        
        // Limit of 3 simultaneous tasks
        return $activeTasksCount < 3;
    }
    
    /**
     * Get the number of active tasks for a user
     * 
     * Returns the count of tasks currently being worked on by a user.
     * 
     * @param User $user The user to get active task count for
     * @return int Number of active tasks
     */
    public function getActiveTasksCount(User $user): int
    {
        return Task::where('user_id', $user->id)
            ->where('is_working', true)
            ->where('status', 'in progress')
            ->count();
    }
    
    /**
     * Start working on a task
     * 
     * Initiates a work session on a specific task. This method includes
     * comprehensive validation and creates the necessary time logs.
     * 
     * @param Task $task The task to start working on
     * @param User $user The user starting the work
     * @return bool True if work was started successfully
     * @throws \Exception If validation fails
     */
    public function startWork(Task $task, User $user): bool
    {
        try {
            DB::beginTransaction();
            
            // Verify task is assigned to the user
            if ($task->user_id !== $user->id) {
                throw new \Exception('You do not have permission to work on this task');
            }
            
            // Verify task is not already in progress
            if ($task->is_working) {
                throw new \Exception('You are already working on this task');
            }
            
            // Verify task is not completed (unless it's rework)
            if ($task->status === 'done' && !$task->has_been_returned) {
                throw new \Exception('You cannot work on a completed task');
            }
            
            // Verify simultaneous task limit
            if (!$this->checkSimultaneousTasksLimit($user)) {
                $activeCount = $this->getActiveTasksCount($user);
                throw new \Exception("You already have {$activeCount} active tasks. The limit is 3 simultaneous tasks. Contact your administrator if you need to work on more tasks.");
            }
            
            // Determine if this is rework
            $isRework = $task->has_been_returned;
            $action = $isRework ? 'start_retwork' : 'start';
            $notes = $isRework ? 'Re-trabajo iniciado' : 'Work started';
            
            // Create start log
            $timeLog = TaskTimeLog::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'started_at' => now(),
                'action' => $action,
                'notes' => $notes
            ]);
            
            // Update task
            $updateData = [
                'status' => 'in progress',
                'work_started_at' => now(),
                'is_working' => true,
                'auto_paused' => false,
                'auto_paused_at' => null,
                'auto_pause_reason' => null,
                'alert_count' => 0,
                'last_alert_at' => null
            ];
            
            // If this is rework, set retwork_started_at
            if ($isRework) {
                $updateData['retwork_started_at'] = now();
            } else {
                $updateData['actual_start'] = $task->actual_start ?? now()->format('Y-m-d');
            }
            
            $task->update($updateData);
            
            Log::info('Work started on task', [
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
     * Pause work on a task
     * 
     * Temporarily stops work on a task while maintaining the session.
     * Calculates and logs the time spent before pausing.
     * 
     * @param Task $task The task to pause work on
     * @param User $user The user pausing the work
     * @return bool True if work was paused successfully
     * @throws \Exception If validation fails
     */
    public function pauseWork(Task $task, User $user): bool
    {
        try {
            DB::beginTransaction();
            
            // Verify task is assigned to the user
            if ($task->user_id !== $user->id) {
                throw new \Exception('You do not have permission to pause this task');
            }
            
            // Verify task is in progress
            if (!$task->is_working) {
                throw new \Exception('There is no active work on this task');
            }
            
            // Get the active log
            $activeLog = TaskTimeLog::where('task_id', $task->id)
                ->where('user_id', $user->id)
                ->whereNotNull('started_at')
                ->whereNull('paused_at')
                ->whereNull('finished_at')
                ->latest()
                ->first();
            
            if (!$activeLog) {
                throw new \Exception('No active work session found');
            }
            
            // Calculate elapsed time with higher precision
            $startTime = \Carbon\Carbon::parse($activeLog->started_at);
            $endTime = \Carbon\Carbon::now();
            
            // Calculate duration using absolute value and round to integer to avoid timezone issues
            $duration = (int) round($endTime->diffInSeconds($startTime, true));
            
            // Log for debug if there are time issues
            if ($endTime->lt($startTime)) {
                Log::warning('End time is before start time, using absolute value', [
                    'task_id' => $task->id,
                    'started_at' => $activeLog->started_at,
                    'startTime' => $startTime->toDateTimeString(),
                    'endTime' => $endTime->toDateTimeString(),
                    'raw_difference' => $endTime->diffInSeconds($startTime, false),
                    'absolute_duration' => $duration
                ]);
            }
            
            // Debug log
            Log::info('Calculating pause duration', [
                'task_id' => $task->id,
                'started_at' => $activeLog->started_at,
                'startTime' => $startTime->toDateTimeString(),
                'now' => $endTime->toDateTimeString(),
                'duration_seconds' => $duration,
                'total_before' => $task->total_time_seconds,
                'diff_in_seconds' => $endTime->diffInSeconds($startTime, false)
            ]);
            
            // Update log
            $activeLog->update([
                'paused_at' => now(),
                'duration_seconds' => $duration,
                'action' => 'pause'
            ]);
            
            // Update task
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
            
            // Determinar el siguiente paso en el flujo de re-trabajo
            $nextStep = $this->determineNextStepInRetworkFlow($task);
            
            // Determinar si es re-trabajo
            $isRework = $task->has_been_returned && $task->retwork_started_at;
            
            // Actualizar tarea
            $updateData = [
                'status' => $nextStep['status'],
                'is_working' => false,
                'total_time_seconds' => $task->total_time_seconds + $duration,
                'actual_finish' => now()->format('Y-m-d'),
                'approval_status' => $nextStep['approval_status'],
                'qa_status' => $nextStep['qa_status'] ?? $task->qa_status,
                'team_leader_requested_changes' => $nextStep['team_leader_requested_changes'] ?? false
            ];
            
            // Si es re-trabajo, actualizar tiempo de re-trabajo
            if ($isRework) {
                $retworkTime = $task->retwork_time_seconds + $duration;
                $updateData['retwork_time_seconds'] = $retworkTime;
                $updateData['retwork_started_at'] = null; // Reset rework start time
            }
            
            $task->update($updateData);
            
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

    /**
     * Determinar el siguiente paso en el flujo de re-trabajo
     */
    private function determineNextStepInRetworkFlow(Task $task): array
    {
        // Si la tarea fue rechazada por QA, va de vuelta a QA
        if ($task->qa_status === 'rejected') {
            return [
                'status' => 'done',
                'approval_status' => 'pending',
                'qa_status' => 'ready_for_test',
                'team_leader_requested_changes' => false
            ];
        }
        
        // Si la tarea tiene cambios solicitados por TL, va de vuelta a QA para re-testing
        if ($task->team_leader_requested_changes) {
            return [
                'status' => 'done',
                'approval_status' => 'pending',
                'qa_status' => 'ready_for_test', // QA debe re-testear los cambios
                'team_leader_requested_changes' => false
            ];
        }
        
        // Flujo normal: va a QA primero
        return [
            'status' => 'done',
            'approval_status' => 'pending',
            'qa_status' => 'ready_for_test',
            'team_leader_requested_changes' => false
        ];
    }
} 
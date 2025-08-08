<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TaskApprovalService
{
    public function __construct(private NotificationService $notificationService)
    {
    }
    /**
     * Obtener tareas pendientes de aprobación para un team leader
     */
    public function getPendingTasksForTeamLeader(User $teamLeader): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('approval_status', 'pending')
            ->whereHas('sprint.project.users', function ($query) use ($teamLeader) {
                $query->where('users.id', $teamLeader->id);
            })
            ->with(['user', 'sprint', 'project', 'assignedBy', 'timeLogs'])
            ->orderBy('actual_finish', 'desc')
            ->get();
    }

    /**
     * Obtener tareas pendientes de aprobación para todos los proyectos
     */
    public function getAllPendingTasks(): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('approval_status', 'pending')
            ->with(['user', 'sprint', 'project', 'assignedBy', 'timeLogs'])
            ->orderBy('actual_finish', 'desc')
            ->get();
    }

    /**
     * Aprobar una tarea
     */
    public function approveTask(Task $task, User $teamLeader, ?string $notes = null): bool
    {
        try {
            DB::beginTransaction();
            
            // Verificar que el team leader tiene permisos sobre el proyecto
            if (!$this->canTeamLeaderReviewProject($teamLeader, $task->project)) {
                throw new \Exception('Team leader no tiene permisos para revisar tareas en este proyecto');
            }
            
            // Verificar que la tarea está pendiente de aprobación
            if ($task->approval_status !== 'pending') {
                throw new \Exception('La tarea no está pendiente de aprobación');
            }
            
            // Aprobar la tarea
            $task->update([
                'approval_status' => 'approved',
                'reviewed_by' => $teamLeader->id,
                'reviewed_at' => now(),
                'rejection_reason' => null
            ]);

            // Marcar la tarea como lista para QA
            $task->markAsReadyForQa();
            
            // Enviar notificación a los QAs del proyecto
            $this->notificationService->notifyTaskReadyForQa($task->fresh());
            
            Log::info('Tarea aprobada por team leader y marcada para QA', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'developer_id' => $task->user_id,
                'developer_name' => $task->user->name,
                'team_leader_id' => $teamLeader->id,
                'team_leader_name' => $teamLeader->name,
                'notes' => $notes
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al aprobar tarea', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'team_leader_id' => $teamLeader->id
            ]);
            throw $e;
        }
    }

    /**
     * Rechazar una tarea
     */
    public function rejectTask(Task $task, User $teamLeader, string $rejectionReason): bool
    {
        try {
            DB::beginTransaction();
            
            // Verificar que el team leader tiene permisos sobre el proyecto
            if (!$this->canTeamLeaderReviewProject($teamLeader, $task->project)) {
                throw new \Exception('Team leader no tiene permisos para revisar tareas en este proyecto');
            }
            
            // Verificar que la tarea está pendiente de aprobación
            if ($task->approval_status !== 'pending') {
                throw new \Exception('La tarea no está pendiente de aprobación');
            }
            
            // Rechazar la tarea
            $task->update([
                'approval_status' => 'rejected',
                'reviewed_by' => $teamLeader->id,
                'reviewed_at' => now(),
                'rejection_reason' => $rejectionReason,
                'status' => 'in progress', // Volver a estado en progreso
                'is_working' => false // No está trabajando
            ]);
            
            Log::info('Tarea rechazada por team leader', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'developer_id' => $task->user_id,
                'developer_name' => $task->user->name,
                'team_leader_id' => $teamLeader->id,
                'team_leader_name' => $teamLeader->name,
                'rejection_reason' => $rejectionReason
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al rechazar tarea', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'team_leader_id' => $teamLeader->id
            ]);
            throw $e;
        }
    }

    /**
     * Obtener estadísticas de aprobación para un team leader
     */
    public function getApprovalStatsForTeamLeader(User $teamLeader): array
    {
        $pendingTasks = $this->getPendingTasksForTeamLeader($teamLeader);
        
        $approvedTasks = Task::where('approval_status', 'approved')
            ->where('reviewed_by', $teamLeader->id)
            ->whereHas('sprint.project.users', function ($query) use ($teamLeader) {
                $query->where('users.id', $teamLeader->id);
            })
            ->count();
            
        $rejectedTasks = Task::where('approval_status', 'rejected')
            ->where('reviewed_by', $teamLeader->id)
            ->whereHas('sprint.project.users', function ($query) use ($teamLeader) {
                $query->where('users.id', $teamLeader->id);
            })
            ->count();
        
        return [
            'pending' => $pendingTasks->count(),
            'approved' => $approvedTasks,
            'rejected' => $rejectedTasks,
            'total_reviewed' => $approvedTasks + $rejectedTasks
        ];
    }

    /**
     * Obtener tareas en progreso para un team leader
     */
    public function getInProgressTasksForTeamLeader(User $teamLeader): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('status', 'in progress')
            ->whereHas('sprint.project.users', function ($query) use ($teamLeader) {
                $query->where('users.id', $teamLeader->id);
            })
            ->with(['user', 'sprint', 'project', 'assignedBy'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Obtener desarrolladores y sus tareas activas para un team leader
     */
    public function getDevelopersWithActiveTasks(User $teamLeader): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'developer');
        })
        ->whereHas('projects', function ($query) use ($teamLeader) {
            $query->whereHas('users', function ($subQuery) use ($teamLeader) {
                $subQuery->where('users.id', $teamLeader->id);
            });
        })
        ->with(['tasks' => function ($query) {
            $query->whereIn('status', ['in progress', 'to do'])
                  ->with(['sprint', 'project']);
        }])
        ->orderBy('name')
        ->get();
    }

    /**
     * Obtener tareas completadas recientemente para un team leader
     */
    public function getRecentlyCompletedTasks(User $teamLeader, int $days = 7): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('status', 'done')
            ->where('actual_finish', '>=', now()->subDays($days))
            ->whereHas('sprint.project.users', function ($query) use ($teamLeader) {
                $query->where('users.id', $teamLeader->id);
            })
            ->with(['user', 'sprint', 'project', 'assignedBy', 'reviewedBy'])
            ->orderBy('actual_finish', 'desc')
            ->get();
    }

    /**
     * Verificar si un team leader puede revisar tareas en un proyecto
     */
    public function canTeamLeaderReviewProject(User $teamLeader, Project $project): bool
    {
        // Verificar que el usuario es team leader
        if (!$teamLeader->roles()->where('name', 'team_leader')->exists()) {
            return false;
        }
        
        // Verificar que el team leader está asignado al proyecto
        return $project->users()->where('users.id', $teamLeader->id)->exists();
    }

    /**
     * Obtener resumen de tiempo por desarrollador para un team leader
     */
    public function getDeveloperTimeSummary(User $teamLeader): array
    {
        $developers = $this->getDevelopersWithActiveTasks($teamLeader);
        $summary = [];
        
        foreach ($developers as $developer) {
            $totalTime = 0;
            $activeTasks = 0;
            $completedTasks = 0;
            
            foreach ($developer->tasks as $task) {
                if ($task->status === 'in progress') {
                    $activeTasks++;
                    $totalTime += $task->total_time_seconds;
                } elseif ($task->status === 'done') {
                    $completedTasks++;
                    $totalTime += $task->total_time_seconds;
                }
            }
            
            $summary[] = [
                'developer' => $developer,
                'total_time_seconds' => $totalTime,
                'formatted_time' => gmdate('H:i:s', $totalTime),
                'active_tasks' => $activeTasks,
                'completed_tasks' => $completedTasks
            ];
        }
        
        return $summary;
    }
} 
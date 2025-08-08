<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AdminDashboardService
{
    /**
     * Obtener estadísticas generales del sistema
     */
    public function getSystemStats(): array
    {
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'active')->count();
        $completedProjects = Project::where('status', 'completed')->count();
        
        $totalTasks = Task::count();
        $inProgressTasks = Task::where('status', 'in progress')->count();
        $completedTasks = Task::where('status', 'done')->count();
        $pendingApprovalTasks = Task::where('approval_status', 'pending')->count();
        
        $totalUsers = User::count();
        $developers = User::whereHas('roles', function ($query) {
            $query->where('name', 'developer');
        })->count();
        $teamLeaders = User::whereHas('roles', function ($query) {
            $query->where('name', 'team_leader');
        })->count();
        
        $totalSprints = Sprint::count();
        // Note: Sprints table doesn't have a status column, so we can't filter by status
        $activeSprints = $totalSprints; // For now, consider all sprints as active
        
        return [
            'projects' => [
                'total' => $totalProjects,
                'active' => $activeProjects,
                'completed' => $completedProjects,
                'completion_rate' => $totalProjects > 0 ? round(($completedProjects / $totalProjects) * 100, 1) : 0
            ],
            'tasks' => [
                'total' => $totalTasks,
                'in_progress' => $inProgressTasks,
                'completed' => $completedTasks,
                'pending_approval' => $pendingApprovalTasks,
                'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0
            ],
            'users' => [
                'total' => $totalUsers,
                'developers' => $developers,
                'team_leaders' => $teamLeaders,
                'admins' => $totalUsers - $developers - $teamLeaders
            ],
            'sprints' => [
                'total' => $totalSprints,
                'active' => $activeSprints
            ]
        ];
    }

    /**
     * Obtener tareas en curso con filtros avanzados
     */
    public function getInProgressTasksWithFilters(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Task::where('status', 'in progress')
            ->with(['user', 'sprint', 'project', 'assignedBy', 'timeLogs']);

        // Filtro por proyecto
        if (!empty($filters['project_id'])) {
            $query->whereHas('sprint.project', function ($subQuery) use ($filters) {
                $subQuery->where('id', $filters['project_id']);
            });
        }

        // Filtro por desarrollador
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filtro por prioridad
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        // Filtro por tiempo estimado vs tiempo real
        if (!empty($filters['time_comparison'])) {
            switch ($filters['time_comparison']) {
                case 'over_estimated':
                    $query->whereRaw('total_time_seconds > (estimated_hours * 3600)');
                    break;
                case 'under_estimated':
                    $query->whereRaw('total_time_seconds < (estimated_hours * 3600)');
                    break;
                case 'on_track':
                    $query->whereRaw('total_time_seconds BETWEEN (estimated_hours * 3600 * 0.8) AND (estimated_hours * 3600 * 1.2)');
                    break;
            }
        }

        // Filtro por fecha de inicio
        if (!empty($filters['start_date'])) {
            $query->where('actual_start', '>=', $filters['start_date']);
        }

        // Filtro por fecha de fin
        if (!empty($filters['end_date'])) {
            $query->where('actual_start', '<=', $filters['end_date']);
        }

        // Búsqueda por nombre de tarea
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDirection = $filters['order_direction'] ?? 'desc';
        $query->orderBy($orderBy, $orderDirection);

        return $query->get();
    }

    /**
     * Obtener métricas de rendimiento por desarrollador
     */
    public function getDeveloperPerformanceMetrics(): array
    {
        $developers = User::whereHas('roles', function ($query) {
            $query->where('name', 'developer');
        })->with(['tasks' => function ($query) {
            $query->whereIn('status', ['in progress', 'done']);
        }])->get();

        $metrics = [];
        
        foreach ($developers as $developer) {
            $totalTasks = $developer->tasks->count();
            $completedTasks = $developer->tasks->where('status', 'done')->count();
            $inProgressTasks = $developer->tasks->where('status', 'in progress')->count();
            
            $totalTimeSpent = $developer->tasks->sum('total_time_seconds');
            $totalEstimatedTime = $developer->tasks->sum('estimated_hours') * 3600;
            
            $efficiency = ($totalEstimatedTime > 0 && $totalTimeSpent > 0) ? round(($totalEstimatedTime / $totalTimeSpent) * 100, 1) : 0;
            $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
            
            $metrics[] = [
                'developer' => $developer,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'in_progress_tasks' => $inProgressTasks,
                'total_time_spent' => $totalTimeSpent,
                'formatted_time_spent' => gmdate('H:i:s', $totalTimeSpent),
                'total_estimated_time' => $totalEstimatedTime,
                'formatted_estimated_time' => gmdate('H:i:s', $totalEstimatedTime),
                'efficiency_percentage' => $efficiency,
                'completion_rate' => $completionRate,
                'average_task_time' => ($completedTasks > 0 && $totalTimeSpent > 0) ? round($totalTimeSpent / $completedTasks / 3600, 1) : 0
            ];
        }

        return $metrics;
    }

    /**
     * Obtener métricas de rendimiento por proyecto
     */
    public function getProjectPerformanceMetrics(): array
    {
        $projects = Project::with(['sprints.tasks.user', 'users'])->get();
        
        $metrics = [];
        
        foreach ($projects as $project) {
            $totalTasks = $project->sprints->sum(function ($sprint) {
                return $sprint->tasks->count();
            });
            
            $completedTasks = $project->sprints->sum(function ($sprint) {
                return $sprint->tasks->where('status', 'done')->count();
            });
            
            $inProgressTasks = $project->sprints->sum(function ($sprint) {
                return $sprint->tasks->where('status', 'in progress')->count();
            });
            
            $totalTimeSpent = $project->sprints->sum(function ($sprint) {
                return $sprint->tasks->sum('total_time_seconds');
            });
            
            $totalEstimatedTime = $project->sprints->sum(function ($sprint) {
                return $sprint->tasks->sum('estimated_hours') * 3600;
            });
            
            $teamMembers = $project->users->count();
            $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
            $efficiency = ($totalEstimatedTime > 0 && $totalTimeSpent > 0) ? round(($totalEstimatedTime / $totalTimeSpent) * 100, 1) : 0;
            
            $metrics[] = [
                'project' => $project,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'in_progress_tasks' => $inProgressTasks,
                'team_members' => $teamMembers,
                'total_time_spent' => $totalTimeSpent,
                'formatted_time_spent' => gmdate('H:i:s', $totalTimeSpent),
                'total_estimated_time' => $totalEstimatedTime,
                'formatted_estimated_time' => gmdate('H:i:s', $totalEstimatedTime),
                'completion_rate' => $completionRate,
                'efficiency_percentage' => $efficiency,
                'average_task_time' => ($completedTasks > 0 && $totalTimeSpent > 0) ? round($totalTimeSpent / $completedTasks / 3600, 1) : 0
            ];
        }

        return $metrics;
    }

    /**
     * Obtener tareas que requieren atención (sobre tiempo estimado)
     */
    public function getTasksRequiringAttention(): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('status', 'in progress')
            ->whereRaw('total_time_seconds > (estimated_hours * 3600 * 1.2)') // 20% más del tiempo estimado
            ->with(['user', 'sprint', 'project'])
            ->orderByRaw('(total_time_seconds - (estimated_hours * 3600)) DESC')
            ->get();
    }

    /**
     * Obtener tareas pendientes de aprobación
     */
    public function getPendingApprovalTasks(): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('approval_status', 'pending')
            ->with(['user', 'sprint', 'project', 'assignedBy'])
            ->orderBy('actual_finish', 'desc')
            ->get();
    }

    /**
     * Obtener reporte de tiempo por período
     */
    public function getTimeReportByPeriod(string $period = 'week'): array
    {
        $startDate = null;
        $endDate = now();
        
        switch ($period) {
            case 'week':
                $startDate = now()->subWeek();
                break;
            case 'month':
                $startDate = now()->subMonth();
                break;
            case 'quarter':
                $startDate = now()->subQuarter();
                break;
            case 'year':
                $startDate = now()->subYear();
                break;
            default:
                $startDate = now()->subWeek();
        }
        
        $tasks = Task::whereBetween('actual_start', [$startDate, $endDate])
            ->where('status', 'done')
            ->with(['user', 'project'])
            ->get();
        
        $totalTime = $tasks->sum('total_time_seconds');
        $totalEstimatedTime = $tasks->sum('estimated_hours') * 3600;
        
        $timeByProject = $tasks->groupBy('project.id')->map(function ($projectTasks) {
            return [
                'project' => $projectTasks->first()->project,
                'total_time' => $projectTasks->sum('total_time_seconds'),
                'formatted_time' => gmdate('H:i:s', $projectTasks->sum('total_time_seconds')),
                'task_count' => $projectTasks->count()
            ];
        });
        
        $timeByDeveloper = $tasks->groupBy('user.id')->map(function ($userTasks) {
            return [
                'developer' => $userTasks->first()->user,
                'total_time' => $userTasks->sum('total_time_seconds'),
                'formatted_time' => gmdate('H:i:s', $userTasks->sum('total_time_seconds')),
                'task_count' => $userTasks->count()
            ];
        });
        
        return [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_time' => $totalTime,
            'formatted_total_time' => gmdate('H:i:s', $totalTime),
            'total_estimated_time' => $totalEstimatedTime,
            'formatted_estimated_time' => gmdate('H:i:s', $totalEstimatedTime),
            'efficiency' => $totalEstimatedTime > 0 ? round(($totalEstimatedTime / $totalTime) * 100, 1) : 0,
            'time_by_project' => $timeByProject,
            'time_by_developer' => $timeByDeveloper,
            'task_count' => $tasks->count()
        ];
    }

    /**
     * Obtener proyectos activos con resumen
     */
    public function getActiveProjectsSummary(): \Illuminate\Database\Eloquent\Collection
    {
        return Project::where('status', 'active')
            ->with(['sprints.tasks' => function ($query) {
                $query->whereIn('status', ['in progress', 'to do']);
            }, 'users'])
            ->get()
            ->map(function ($project) {
                $project->in_progress_tasks_count = $project->sprints->sum(function ($sprint) {
                    return $sprint->tasks->where('status', 'in progress')->count();
                });
                $project->pending_tasks_count = $project->sprints->sum(function ($sprint) {
                    return $sprint->tasks->where('status', 'to do')->count();
                });
                $project->team_members_count = $project->users->count();
                return $project;
            });
    }

    /**
     * Obtener desarrolladores activos con resumen
     */
    public function getActiveDevelopersSummary(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'developer');
        })
        ->with(['tasks' => function ($query) {
            $query->whereIn('status', ['in progress', 'to do']);
        }])
        ->get()
        ->map(function ($developer) {
            $developer->active_tasks_count = $developer->tasks->where('status', 'in progress')->count();
            $developer->pending_tasks_count = $developer->tasks->where('status', 'to do')->count();
            $developer->total_time_today = $developer->tasks->where('status', 'in progress')->sum('total_time_seconds');
            $developer->formatted_time_today = gmdate('H:i:s', $developer->total_time_today);
            return $developer;
        });
    }
} 
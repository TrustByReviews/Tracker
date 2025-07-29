<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->roles()->where('value', 'admin')->exists();

        if ($isAdmin) {
            return $this->adminDashboard();
        } else {
            return $this->developerDashboard();
        }
    }

    private function developerDashboard()
    {
        $user = Auth::user();
        
        // Proyectos asignados al desarrollador
        $assignedProjects = $user->projects()
            ->with(['sprints.tasks' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->get();

        // Tareas del desarrollador
        $myTasks = $user->tasks()
            ->with(['sprint.project'])
            ->get();

        // Tareas en curso
        $tasksInProgress = $myTasks->where('status', 'in progress');
        
        // Tareas completadas
        $completedTasks = $myTasks->where('status', 'done');
        
        // Tareas con alta prioridad sin asignar
        $highPriorityUnassignedTasks = Task::whereNull('user_id')
            ->where('priority', 'high')
            ->with(['sprint.project'])
            ->get();

        // Calcular ganancias por tareas completadas
        $totalEarnings = $completedTasks->sum(function ($task) use ($user) {
            return ($task->actual_hours ?? 0) * $user->hour_value;
        });

        // Estadísticas generales
        $stats = [
            'total_projects' => $assignedProjects->count(),
            'tasks_in_progress' => $tasksInProgress->count(),
            'tasks_completed' => $completedTasks->count(),
            'total_earnings' => $totalEarnings,
            'high_priority_unassigned' => $highPriorityUnassignedTasks->count(),
        ];

        return Inertia::render('Dashboard', [
            'user' => $user,
            'assignedProjects' => $assignedProjects,
            'tasksInProgress' => $tasksInProgress,
            'completedTasks' => $completedTasks,
            'highPriorityUnassignedTasks' => $highPriorityUnassignedTasks,
            'stats' => $stats,
            'isAdmin' => false,
        ]);
    }

    private function adminDashboard()
    {
        // Todos los proyectos con estadísticas
        $projects = Project::with(['sprints.tasks', 'users'])
            ->get()
            ->map(function ($project) {
                $allTasks = $project->sprints->flatMap->tasks;
                
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'status' => $project->status,
                                    'total_tasks' => $allTasks->count(),
                'completed_tasks' => $allTasks->where('status', 'done')->count(),
                'in_progress_tasks' => $allTasks->where('status', 'in progress')->count(),
                'pending_tasks' => $allTasks->where('status', 'to do')->count(),
                'assigned_users' => $project->users->count(),
                'completion_percentage' => $allTasks->count() > 0 
                    ? round(($allTasks->where('status', 'done')->count() / $allTasks->count()) * 100, 2)
                    : 0,
                ];
            });

        // Rendimiento de desarrolladores
        $developers = User::with(['tasks', 'projects'])
            ->whereHas('roles', function ($query) {
                $query->where('value', '!=', 'admin');
            })
            ->get()
            ->map(function ($developer) {
                $completedTasks = $developer->tasks->where('status', 'done');
                $totalEarnings = $completedTasks->sum(function ($task) use ($developer) {
                    return ($task->actual_hours ?? 0) * $developer->hour_value;
                });

                return [
                    'id' => $developer->id,
                    'name' => $developer->name,
                    'email' => $developer->email,
                    'hour_value' => $developer->hour_value,
                    'total_tasks' => $developer->tasks->count(),
                    'completed_tasks' => $completedTasks->count(),
                    'in_progress_tasks' => $developer->tasks->where('status', 'in progress')->count(),
                    'total_earnings' => $totalEarnings,
                    'assigned_projects' => $developer->projects->count(),
                    'performance_score' => $developer->tasks->count() > 0 
                        ? round(($completedTasks->count() / $developer->tasks->count()) * 100, 2)
                        : 0,
                ];
            });

        // Estadísticas generales del sistema
        $systemStats = [
            'total_projects' => Project::count(),
            'total_users' => User::count(),
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::where('status', 'done')->count(),
            'in_progress_tasks' => Task::where('status', 'in progress')->count(),
            'pending_tasks' => Task::where('status', 'to do')->count(),
            'high_priority_tasks' => Task::where('priority', 'high')->count(),
            'unassigned_tasks' => Task::whereNull('user_id')->count(),
        ];

        return Inertia::render('Dashboard', [
            'user' => Auth::user(),
            'projects' => $projects,
            'developers' => $developers,
            'systemStats' => $systemStats,
            'isAdmin' => true,
        ]);
    }
} 
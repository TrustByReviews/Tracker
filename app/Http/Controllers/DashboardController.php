<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Bug;
use App\Services\AdminDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private AdminDashboardService $adminDashboardService
    ) {}

    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $isAdmin = $user->roles()->where('name', 'admin')->exists();

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

        // Bugs del desarrollador
        $myBugs = $user->bugs()
            ->with(['sprint.project'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Estadísticas de bugs
        $bugStats = [
            'total_bugs' => $user->bugs()->count(),
            'bugs_in_progress' => $user->bugs()->where('status', 'in progress')->count(),
            'bugs_resolved' => $user->bugs()->where('status', 'resolved')->count(),
            'high_priority_bugs' => $user->bugs()->whereIn('importance', ['high', 'critical'])->count(),
        ];

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
            'bugs' => $myBugs,
            'bugStats' => $bugStats,
            'isAdmin' => false,
        ]);
    }

    private function adminDashboard()
    {
        // Obtener estadísticas del sistema usando el servicio
        $systemStats = $this->adminDashboardService->getSystemStats();
        $tasksRequiringAttention = $this->adminDashboardService->getTasksRequiringAttention();
        $activeProjectsSummary = $this->adminDashboardService->getActiveProjectsSummary();
        $developerMetrics = $this->adminDashboardService->getDeveloperPerformanceMetrics();

        // Transformar systemStats para que coincida con lo que espera AdminStats
        $transformedStats = [
            'total_projects' => $systemStats['projects']['total'],
            'total_users' => $systemStats['users']['total'],
            'total_tasks' => $systemStats['tasks']['total'],
            'completed_tasks' => $systemStats['tasks']['completed'],
            'in_progress_tasks' => $systemStats['tasks']['in_progress'],
            'pending_tasks' => $systemStats['tasks']['total'] - $systemStats['tasks']['completed'] - $systemStats['tasks']['in_progress'],
            'high_priority_tasks' => \App\Models\Task::where('priority', 'high')->count(),
            'unassigned_tasks' => \App\Models\Task::whereNull('user_id')->count(),
        ];

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
                $query->where('name', '!=', 'admin');
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

        // Bugs recientes para el dashboard de admin
        $recentBugs = Bug::with(['user', 'project', 'sprint'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Estadísticas de bugs
        $bugStats = [
            'total_bugs' => Bug::count(),
            'bugs_new' => Bug::where('status', 'new')->count(),
            'bugs_in_progress' => Bug::where('status', 'in progress')->count(),
            'bugs_resolved' => Bug::where('status', 'resolved')->count(),
            'high_priority_bugs' => Bug::whereIn('importance', ['high', 'critical'])->count(),
        ];

        return Inertia::render('Dashboard', [
            'user' => Auth::user(),
            'projects' => $projects,
            'developers' => $developers,
            'systemStats' => $transformedStats,
            'tasksRequiringAttention' => $tasksRequiringAttention,
            'activeProjectsSummary' => $activeProjectsSummary,
            'developerMetrics' => $developerMetrics,
            'bugs' => $recentBugs,
            'bugStats' => $bugStats,
            'isAdmin' => true,
        ]);
    }
} 
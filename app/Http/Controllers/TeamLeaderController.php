<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\Bug;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Http\Request;

class TeamLeaderController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        // Obtener proyectos donde el TL está asignado
        $projects = Project::whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->with(['sprints', 'tasks', 'bugs'])->get();

        // Obtener sprints activos (basado en fechas)
        $activeSprints = Sprint::whereHas('project.users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->where('start_date', '<=', now())
          ->where('end_date', '>=', now())
          ->with(['project', 'tasks', 'bugs'])->get();

        // Obtener tareas pendientes de revisión
        $pendingTasks = Task::whereHas('project.users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->where('qa_status', 'approved')
          ->where('team_leader_final_approval', false)
          ->where('team_leader_requested_changes', false)
          ->with(['user', 'project', 'sprint', 'qaReviewedBy'])
          ->get();

        // Obtener bugs pendientes de revisión
        $pendingBugs = Bug::whereHas('project.users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->where('qa_status', 'approved')
          ->where('team_leader_final_approval', false)
          ->where('team_leader_requested_changes', false)
          ->with(['user', 'project', 'sprint', 'qaReviewedBy'])
          ->get();

        // Obtener tareas aprobadas por QA
        $qaApprovedTasks = Task::whereHas('project.users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->where('qa_status', 'approved')
          ->with(['user', 'project', 'sprint', 'qaReviewedBy'])
          ->get();

        // Obtener bugs aprobados por QA
        $qaApprovedBugs = Bug::whereHas('project.users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->where('qa_status', 'approved')
          ->with(['user', 'project', 'sprint', 'qaReviewedBy'])
          ->get();

        // Estadísticas
        $stats = [
            'activeProjects' => $projects->count(),
            'activeSprints' => $activeSprints->count(),
            'pendingTasks' => $pendingTasks->count(),
            'pendingBugs' => $pendingBugs->count(),
            'qaApprovedTasks' => $qaApprovedTasks->count(),
            'qaApprovedBugs' => $qaApprovedBugs->count(),
        ];

        return Inertia::render('TeamLeader/Dashboard', [
            'user' => $user,
            'projects' => $projects,
            'activeSprints' => $activeSprints,
            'pendingTasks' => $pendingTasks,
            'pendingBugs' => $pendingBugs,
            'qaApprovedTasks' => $qaApprovedTasks,
            'qaApprovedBugs' => $qaApprovedBugs,
            'stats' => $stats,
        ]);
    }

    public function projects()
    {
        $user = Auth::user();
        
        $projects = Project::whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->with(['sprints', 'tasks', 'bugs', 'users'])->get();

        return Inertia::render('TeamLeader/Projects', [
            'user' => $user,
            'projects' => $projects,
        ]);
    }

    public function sprints()
    {
        $user = Auth::user();
        
        // Obtener proyectos del TL
        $projects = Project::whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->get();

        // Obtener sprints de esos proyectos con sus relaciones
        $sprints = Sprint::whereIn('project_id', $projects->pluck('id'))
            ->with([
                'project',
                'tasks' => function ($query) {
                    $query->with(['user', 'qaReviewedBy']);
                },
                'bugs' => function ($query) {
                    $query->with(['user', 'qaReviewedBy']);
                }
            ])
            ->get();

        return Inertia::render('TeamLeader/Sprints', [
            'sprints' => $sprints,
            'projects' => $projects,
            'permissions' => 'team_leader'
        ]);
    }

    public function notifications()
    {
        $user = Auth::user();
        
        // Aquí puedes implementar la lógica para obtener notificaciones
        $notifications = []; // Placeholder

        return Inertia::render('TeamLeader/Notifications', [
            'user' => $user,
            'notifications' => $notifications,
        ]);
    }

    public function reports()
    {
        $user = Auth::user();
        
        // Aquí puedes implementar la lógica para reportes
        $reports = []; // Placeholder

        return Inertia::render('TeamLeader/Reports', [
            'user' => $user,
            'reports' => $reports,
        ]);
    }

    public function settings()
    {
        $user = Auth::user();
        
        return Inertia::render('TeamLeader/Settings', [
            'user' => $user,
        ]);
    }

    // API endpoints para el sidebar
    public function getStats()
    {
        $user = Auth::user();
        
        $activeProjects = Project::whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->count();

        $activeSprints = Sprint::whereHas('project.users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->where('start_date', '<=', now())
          ->where('end_date', '>=', now())
          ->count();

        $pendingTasks = Task::whereHas('project.users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->where('qa_status', 'approved')
          ->where('team_leader_final_approval', false)
          ->where('team_leader_requested_changes', false)
          ->count();

        $qaApprovedBugs = Bug::whereHas('project.users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->where('qa_status', 'approved')
          ->where('team_leader_final_approval', false)
          ->where('team_leader_requested_changes', false)
          ->count();

        $qaApprovedTasks = Task::whereHas('project.users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->where('qa_status', 'approved')->count();

        return response()->json([
            'stats' => [
                'activeProjects' => $activeProjects,
                'activeSprints' => $activeSprints,
                'pendingTasks' => $pendingTasks,
                'qaApprovedBugs' => $qaApprovedBugs,
                'qaApprovedTasks' => $qaApprovedTasks,
            ]
        ]);
    }

    public function getNotifications()
    {
        $user = Auth::user();
        
        $notifications = DB::table('notifications')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $unreadCount = DB::table('notifications')
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    public function markNotificationAsRead($notificationId)
    {
        $user = Auth::user();
        
        DB::table('notifications')
            ->where('id', $notificationId)
            ->where('user_id', $user->id)
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markAllNotificationsAsRead()
    {
        $user = Auth::user();
        
        DB::table('notifications')
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Mostrar tareas de un sprint específico
     */
    public function showSprint(Sprint $sprint)
    {
        $user = Auth::user();
        
        // Verificar que el TL pertenece al proyecto del sprint (los admins pueden ver todos)
        if (!$user->hasRole('admin') && !$sprint->project->users->contains($user->id)) {
            abort(403, 'No tienes acceso a este sprint');
        }

        // Cargar el sprint con todas sus relaciones necesarias
        $sprint->load([
            'project',
            'tasks.user',
            'tasks.qaReviewedBy',
            'bugs.user',
            'bugs.qaReviewedBy'
        ]);

        // Obtener desarrolladores disponibles del proyecto
        $developers = $sprint->project->users()
            ->whereHas('roles', function($query) {
                $query->where('name', 'developer');
            })->get();

        return Inertia::render('TeamLeader/SprintDetailsFixed', [
            'sprint' => $sprint,
            'project' => $sprint->project,
            'tasks' => $sprint->tasks,
            'bugs' => $sprint->bugs,
            'developers' => $developers,
            'permissions' => 'team_leader'
        ]);
    }

    public function sprintTasks(Sprint $sprint)
    {
        $user = Auth::user();
        
        // Verificar que el TL pertenece al proyecto del sprint (los admins pueden ver todos)
        if (!$user->hasRole('admin') && !$sprint->project->users->contains($user->id)) {
            abort(403, 'No tienes acceso a este sprint');
        }

        $tasks = $sprint->tasks()->with(['user', 'project', 'qaReviewedBy'])->get();

        return Inertia::render('Task/Index', [
            'user' => $user,
            'sprint' => $sprint->load(['project']),
            'tasks' => $tasks,
        ]);
    }

    /**
     * Mostrar bugs de un sprint específico
     */
    public function sprintBugs(Sprint $sprint)
    {
        $user = Auth::user();
        
        // Verificar que el TL pertenece al proyecto del sprint (los admins pueden ver todos)
        if (!$user->hasRole('admin') && !$sprint->project->users->contains($user->id)) {
            abort(403, 'No tienes acceso a este sprint');
        }

        $bugs = $sprint->bugs()->with(['user', 'project', 'qaReviewedBy'])->get();

        return Inertia::render('Bug/Index', [
            'user' => $user,
            'sprint' => $sprint->load(['project']),
            'bugs' => $bugs,
        ]);
    }
} 
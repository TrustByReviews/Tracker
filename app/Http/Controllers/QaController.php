<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Bug;
use App\Models\Project;
use App\Models\User;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class QaController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->middleware('role:qa');
    }

    /**
     * Dashboard principal del QA
     */
    public function dashboard(): Response
    {
        $user = Auth::user();
        
        // Obtener proyectos asignados al QA
        $projects = $user->projects()->with(['users', 'sprints.tasks'])->get();
        
        // Tareas listas para testing
        $tasksReadyForTesting = Task::whereHas('sprint.project', function ($query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })
        ->where('qa_status', 'ready_for_test')
        ->with(['sprint.project', 'user', 'sprint'])
        ->orderBy('priority', 'desc')
        ->orderBy('created_at', 'asc')
        ->get();

        // Bugs listos para testing
        $bugsReadyForTesting = Bug::whereHas('sprint.project', function ($query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })
        ->where('qa_status', 'ready_for_test')
        ->with(['sprint.project', 'user', 'sprint'])
        ->orderBy('importance', 'desc')
        ->orderBy('created_at', 'asc')
        ->get();

        // Tareas en testing por el QA actual
        $tasksInTesting = Task::where('qa_assigned_to', $user->id)
            ->where('qa_status', 'testing')
            ->with(['sprint.project', 'user', 'sprint'])
            ->get();

        // Bugs en testing por el QA actual
        $bugsInTesting = Bug::where('qa_assigned_to', $user->id)
            ->where('qa_status', 'testing')
            ->with(['sprint.project', 'user', 'sprint'])
            ->get();

        // Estadísticas
        $stats = [
            'total_projects' => $projects->count(),
            'tasks_ready_for_test' => $tasksReadyForTesting->count(),
            'bugs_ready_for_test' => $bugsReadyForTesting->count(),
            'tasks_in_testing' => $tasksInTesting->count(),
            'bugs_in_testing' => $bugsInTesting->count(),
        ];

        return Inertia::render('Qa/Dashboard', [
            'projects' => $projects,
            'tasksReadyForTesting' => $tasksReadyForTesting,
            'bugsReadyForTesting' => $bugsReadyForTesting,
            'tasksInTesting' => $tasksInTesting,
            'bugsInTesting' => $bugsInTesting,
            'stats' => $stats,
        ]);
    }

    /**
     * Vista Kanban para QA
     */
    public function kanban(): Response
    {
        $user = Auth::user();
        
        // Obtener todas las tareas y bugs del proyecto actual (si se especifica)
        $projectId = request('project_id');
        
        $query = Task::whereHas('project', function ($query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        });

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $tasks = $query->with(['project', 'user', 'sprint'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Bugs
        $bugQuery = Bug::whereHas('project', function ($query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        });

        if ($projectId) {
            $bugQuery->where('project_id', $projectId);
        }

        $bugs = $bugQuery->with(['project', 'user', 'sprint'])
            ->orderBy('importance', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Proyectos disponibles
        $projects = $user->projects()->get();

        return Inertia::render('Qa/Kanban', [
            'tasks' => $tasks,
            'bugs' => $bugs,
            'projects' => $projects,
            'currentProjectId' => $projectId,
        ]);
    }

    /**
     * Asignar tarea a QA
     */
    public function assignTask(Request $request, Task $task): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        // Verificar que el QA esté asignado al proyecto
        if (!$user->projects()->where('id', $task->project_id)->exists()) {
            return response()->json(['error' => 'You are not assigned to this project'], 403);
        }

        // Verificar que la tarea esté lista para testing
        if ($task->qa_status !== 'ready_for_test') {
            return response()->json(['error' => 'Task is not ready for testing'], 400);
        }

        $task->assignToQa($user);

        return response()->json([
            'message' => 'Task assigned successfully',
            'task' => $task->load(['project', 'user', 'sprint'])
        ]);
    }

    /**
     * Asignar bug a QA
     */
    public function assignBug(Request $request, Bug $bug): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        // Verificar que el QA esté asignado al proyecto
        if (!$user->projects()->where('id', $bug->project_id)->exists()) {
            return response()->json(['error' => 'You are not assigned to this project'], 403);
        }

        // Verificar que el bug esté lista para testing
        if ($bug->qa_status !== 'ready_for_test') {
            return response()->json(['error' => 'Bug is not ready for testing'], 400);
        }

        $bug->assignToQa($user);

        return response()->json([
            'message' => 'Bug assigned successfully',
            'bug' => $bug->load(['project', 'user', 'sprint'])
        ]);
    }

    /**
     * Aprobar tarea
     */
    public function approveTask(Request $request, Task $task): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        // Verificar que el QA esté asignado a la tarea
        if ($task->qa_assigned_to !== $user->id) {
            return response()->json(['error' => 'You are not assigned to this task'], 403);
        }

        // Verificar que la tarea esté en estado testing_finished
        if ($task->qa_status !== 'testing_finished') {
            return response()->json(['error' => 'Task must be in testing_finished status to be approved'], 400);
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $task->approveByQa($user, $request->notes);
        
        // Enviar notificaciones
        $this->notificationService->notifyTaskApprovedByQa($task, $user);

        return response()->json([
            'message' => 'Task approved successfully',
            'task' => $task->load(['project', 'user', 'sprint'])
        ]);
    }

    /**
     * Rechazar tarea
     */
    public function rejectTask(Request $request, Task $task): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        // Verificar que el QA esté asignado a la tarea
        if ($task->qa_assigned_to !== $user->id) {
            return response()->json(['error' => 'You are not assigned to this task'], 403);
        }

        // Verificar que la tarea esté en estado testing_finished
        if ($task->qa_status !== 'testing_finished') {
            return response()->json(['error' => 'Task must be in testing_finished status to be rejected'], 400);
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $task->rejectByQa($user, $request->reason);
        
        // Enviar notificaciones
        $this->notificationService->notifyTaskRejectedByQa($task, $user, $request->reason);

        return response()->json([
            'message' => 'Task rejected successfully',
            'task' => $task->load(['project', 'user', 'sprint'])
        ]);
    }

    /**
     * Aprobar bug
     */
    public function approveBug(Request $request, Bug $bug): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        // Verificar que el QA esté asignado al bug
        if ($bug->qa_assigned_to !== $user->id) {
            return response()->json(['error' => 'You are not assigned to this bug'], 403);
        }

        // Verificar que el bug esté en estado testing_finished
        if ($bug->qa_status !== 'testing_finished') {
            return response()->json(['error' => 'Bug must be in testing_finished status to be approved'], 400);
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $bug->approveByQa($user, $request->notes);
        
        // Enviar notificaciones
        $this->notificationService->notifyBugApprovedByQa($bug, $user);

        return response()->json([
            'message' => 'Bug approved successfully',
            'bug' => $bug->load(['project', 'user', 'sprint'])
        ]);
    }

    /**
     * Rechazar bug
     */
    public function rejectBug(Request $request, Bug $bug): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        // Verificar que el QA esté asignado al bug
        if ($bug->qa_assigned_to !== $user->id) {
            return response()->json(['error' => 'You are not assigned to this bug'], 403);
        }

        // Verificar que el bug esté en estado testing_finished
        if ($bug->qa_status !== 'testing_finished') {
            return response()->json(['error' => 'Bug must be in testing_finished status to be rejected'], 400);
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $bug->rejectByQa($user, $request->reason);
        
        // Enviar notificaciones
        $this->notificationService->notifyBugRejectedByQa($bug, $user, $request->reason);

        return response()->json([
            'message' => 'Bug rejected successfully',
            'bug' => $bug->load(['project', 'user', 'sprint'])
        ]);
    }

    /**
     * Ver detalles de tarea
     */
    public function showTask(Task $task): Response
    {
        $user = Auth::user();
        
        // Verificar que el QA esté asignado al proyecto
        if (!$user->projects()->where('id', $task->project_id)->exists()) {
            abort(403, 'You are not assigned to this project');
        }

        $task->load(['project', 'user', 'sprint', 'timeLogs', 'qaAssignedTo', 'qaReviewedBy']);

        return Inertia::render('Qa/TaskShow', [
            'task' => $task,
        ]);
    }

    /**
     * Ver detalles de bug
     */
    public function showBug(Bug $bug): Response
    {
        $user = Auth::user();
        
        // Verificar que el QA esté asignado al proyecto
        if (!$user->projects()->where('id', $bug->project_id)->exists()) {
            abort(403, 'You are not assigned to this project');
        }

        $bug->load(['project', 'user', 'sprint', 'timeLogs', 'qaAssignedTo', 'qaReviewedBy', 'comments']);

        return Inertia::render('Qa/BugShow', [
            'bug' => $bug,
        ]);
    }

    /**
     * Obtener notificaciones (API endpoint)
     */
    public function getNotifications(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        $notifications = \Illuminate\Support\Facades\DB::table('notifications')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    /**
     * Obtener notificaciones (vista)
     */
    public function notifications(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    /**
     * Marcar notificación como leída
     */
    public function markNotificationAsRead(Notification $notification): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        if ($notification->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'unread_count' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllNotificationsAsRead(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        $user->notifications()->update(['read_at' => now()]);
        
        return response()->json([
            'message' => 'Todas las notificaciones marcadas como leídas',
            'unread_count' => 0
        ]);
    }

    /**
     * Vista de tareas finalizadas para QA
     */
    public function finishedTasks(): Response
    {
        $user = Auth::user();
        
        // Tareas finalizadas por desarrolladores que necesitan aprobación de QA
        $finishedTasks = Task::whereHas('project', function ($query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })
        ->where('status', 'done')
        ->where('qa_status', 'ready_for_test')
        ->with(['project', 'user', 'sprint'])
        ->orderBy('priority', 'desc')
        ->orderBy('created_at', 'asc')
        ->get();

        return Inertia::render('Qa/FinishedTasks', [
            'finishedTasks' => $finishedTasks,
        ]);
    }

    /**
     * Vista unificada de items finalizados para QA
     */
    public function finishedItems(): Response
    {
        $user = Auth::user();
        
        // Tareas finalizadas por desarrolladores que necesitan aprobación de QA
        $finishedTasks = Task::whereHas('sprint.project', function ($query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })
        ->where('status', 'done')
        ->whereIn('qa_status', ['ready_for_test', 'testing', 'testing_paused', 'testing_finished', 'approved', 'rejected'])
        ->with(['sprint.project', 'user', 'sprint'])
        ->orderByRaw("CASE WHEN qa_status = 'ready_for_test' THEN 0 ELSE 1 END")
        ->orderBy('priority', 'desc')
        ->orderBy('created_at', 'asc')
        ->paginate(10);

        // Bugs finalizados por desarrolladores que necesitan aprobación de QA
        $finishedBugs = Bug::whereHas('sprint.project', function ($query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })
        ->where('status', 'resolved')
        ->whereIn('qa_status', ['ready_for_test', 'testing', 'testing_paused', 'testing_finished', 'approved', 'rejected'])
        ->with(['sprint.project', 'user', 'sprint'])
        ->orderBy('importance', 'desc')
        ->orderBy('created_at', 'asc')
        ->paginate(10);

        // Obtener proyectos y desarrolladores para los filtros
        $projects = $user->projects()->orderBy('name')->get();
        $developers = User::whereHas('roles', function ($query) {
            $query->where('value', 'developer');
        })->whereHas('projects', function ($query) use ($user) {
            $query->whereIn('projects.id', $user->projects->pluck('id'));
        })->orderBy('name')->get();

        return Inertia::render('Qa/FinishedItems', [
            'finishedTasks' => $finishedTasks,
            'finishedBugs' => $finishedBugs,
            'projects' => $projects,
            'developers' => $developers,
        ]);
    }

    /**
     * Iniciar testing de una tarea
     */
    public function startTestingTask(Request $request, Task $task): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        // Verificar que el QA esté asignado al proyecto
        if (!$task->sprint->project->users->contains($user->id)) {
            return response()->json(['error' => 'No tienes permisos para testear esta tarea'], 403);
        }

        // Verificar que el QA no tenga otra tarea en testing activo
        $activeTestingTask = Task::where('qa_assigned_to', $user->id)
            ->whereIn('qa_status', ['testing', 'testing_paused'])
            ->first();

        if ($activeTestingTask && $activeTestingTask->id !== $task->id) {
            return response()->json([
                'error' => 'Ya tienes una tarea en testing activo. Debes finalizar o pausar esa tarea antes de iniciar otra.',
                'activeTask' => $activeTestingTask->name
            ], 400);
        }

        $task->update([
            'qa_assigned_to' => $user->id,
            'qa_status' => 'testing',
            'qa_testing_started_at' => now(),
        ]);

        return response()->json([
            'message' => 'Testing iniciado',
            'task' => $task->load(['sprint.project', 'user', 'sprint'])
        ]);
    }

    /**
     * Pausar testing de una tarea
     */
    public function pauseTestingTask(Request $request, Task $task): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        if ($task->qa_assigned_to !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para pausar esta tarea'], 403);
        }

        $task->update([
            'qa_status' => 'testing_paused',
            'qa_testing_paused_at' => now(),
        ]);

        return response()->json([
            'message' => 'Testing pausado',
            'task' => $task->load(['project', 'user', 'sprint'])
        ]);
    }

    /**
     * Reanudar testing de una tarea
     */
    public function resumeTestingTask(Request $request, Task $task): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        if ($task->qa_assigned_to !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para reanudar esta tarea'], 403);
        }

        // Verificar que el QA no tenga otra tarea en testing activo (excluyendo la actual)
        $activeTestingTask = Task::where('qa_assigned_to', $user->id)
            ->whereIn('qa_status', ['testing', 'testing_paused'])
            ->where('id', '!=', $task->id)
            ->first();

        if ($activeTestingTask) {
            return response()->json([
                'error' => 'Ya tienes una tarea en testing activo. Debes finalizar o pausar esa tarea antes de reanudar otra.',
                'activeTask' => $activeTestingTask->name
            ], 400);
        }

        $task->update([
            'qa_status' => 'testing',
            'qa_testing_paused_at' => null,
        ]);

        return response()->json([
            'message' => 'Testing reanudado',
            'task' => $task->load(['project', 'user', 'sprint'])
        ]);
    }

    /**
     * Finalizar testing de una tarea
     */
    public function finishTestingTask(Request $request, Task $task): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        if ($task->qa_assigned_to !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para finalizar esta tarea'], 403);
        }

        $task->update([
            'qa_status' => 'testing_finished',
            'qa_testing_finished_at' => now(),
        ]);

        return response()->json([
            'message' => 'Testing finalizado. Ahora puedes aprobar o rechazar la tarea.',
            'task' => $task->load(['project', 'user', 'sprint'])
        ]);
    }

    /**
     * Iniciar testing de un bug
     */
    public function startTestingBug(Request $request, Bug $bug): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        // Verificar que el QA esté asignado al proyecto
        if (!$bug->sprint->project->users->contains($user->id)) {
            return response()->json(['error' => 'No tienes permisos para testear este bug'], 403);
        }

        // Verificar que el QA no tenga otro bug en testing activo
        $activeTestingBug = Bug::where('qa_assigned_to', $user->id)
            ->whereIn('qa_status', ['testing', 'testing_paused'])
            ->first();

        if ($activeTestingBug && $activeTestingBug->id !== $bug->id) {
            return response()->json([
                'error' => 'Ya tienes un bug en testing activo. Debes finalizar o pausar ese bug antes de iniciar otro.',
                'activeBug' => $activeTestingBug->title
            ], 400);
        }

        $bug->update([
            'qa_assigned_to' => $user->id,
            'qa_status' => 'testing',
            'qa_testing_started_at' => now(),
        ]);

        return response()->json([
            'message' => 'Testing iniciado',
            'bug' => $bug->load(['project', 'user', 'sprint'])
        ]);
    }

    /**
     * Pausar testing de un bug
     */
    public function pauseTestingBug(Request $request, Bug $bug): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        if ($bug->qa_assigned_to !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para pausar este bug'], 403);
        }

        $bug->update([
            'qa_status' => 'testing_paused',
            'qa_testing_paused_at' => now(),
        ]);

        return response()->json([
            'message' => 'Testing pausado',
            'bug' => $bug->load(['project', 'user', 'sprint'])
        ]);
    }

    /**
     * Reanudar testing de un bug
     */
    public function resumeTestingBug(Request $request, Bug $bug): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        if ($bug->qa_assigned_to !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para reanudar este bug'], 403);
        }

        // Verificar que el QA no tenga otro bug en testing activo (excluyendo el actual)
        $activeTestingBug = Bug::where('qa_assigned_to', $user->id)
            ->whereIn('qa_status', ['testing', 'testing_paused'])
            ->where('id', '!=', $bug->id)
            ->first();

        if ($activeTestingBug) {
            return response()->json([
                'error' => 'Ya tienes un bug en testing activo. Debes finalizar o pausar ese bug antes de reanudar otro.',
                'activeBug' => $activeTestingBug->title
            ], 400);
        }

        $bug->update([
            'qa_status' => 'testing',
            'qa_testing_paused_at' => null,
        ]);

        return response()->json([
            'message' => 'Testing reanudado',
            'bug' => $bug->load(['project', 'user', 'sprint'])
        ]);
    }

    /**
     * Finalizar testing de un bug
     */
    public function finishTestingBug(Request $request, Bug $bug): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        if ($bug->qa_assigned_to !== $user->id) {
            return response()->json(['error' => 'No tienes permisos para finalizar este bug'], 403);
        }

        $bug->update([
            'qa_status' => 'testing_finished',
            'qa_testing_finished_at' => now(),
        ]);

        return response()->json([
            'message' => 'Testing finalizado. Ahora puedes aprobar o rechazar el bug.',
            'bug' => $bug->load(['project', 'user', 'sprint'])
        ]);
    }
} 
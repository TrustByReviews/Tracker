<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\Sprint;
use App\Services\TaskAssignmentService;
use App\Services\TaskTimeTrackingService;
use App\Services\DeveloperActivityTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function __construct(
        private TaskAssignmentService $taskAssignmentService,
        private TaskTimeTrackingService $taskTimeTrackingService,
        private DeveloperActivityTrackingService $activityTrackingService
    ) {
    }

    public function index(Request $request): Response
    {
        $authUser = Auth::user();
        
        if (!$authUser) {
            return redirect()->route('login');
        }
        
        $role = $authUser->roles;
        $permissions = 'developer'; // Default permission

        if ($role && $role->count() > 0) {
            $permissions = $role->first()->name;
        }

        $tasksQuery = null;
        $projects = [];
        $sprints = [];

        if ($permissions === 'admin') {
            // Admin ve todo
            $tasksQuery = Task::with(['user', 'sprint', 'project']);
            $projects = Project::orderBy('name')->get();
            $sprints = Sprint::with('project')->orderBy('start_date', 'desc')->get();
        } elseif ($permissions === 'team_leader') {
            // Team leader ve tareas de sus proyectos
            $tasksQuery = Task::whereHas('sprint.project.users', function ($query) use ($authUser) {
                $query->where('users.id', $authUser->id);
            })->with(['user', 'sprint', 'project']);
            $projects = $authUser->projects()->orderBy('name')->get();
            $sprints = Sprint::whereHas('project.users', function ($query) use ($authUser) {
                $query->where('users.id', $authUser->id);
            })->with('project')->orderBy('start_date', 'desc')->get();
        } elseif ($permissions === 'qa') {
            // QA ve tareas de sus proyectos asignados
            $tasksQuery = Task::whereHas('project', function ($query) use ($authUser) {
                $query->whereHas('users', function ($q) use ($authUser) {
                    $q->where('users.id', $authUser->id);
                });
            })->with(['user', 'sprint', 'project']);
            $projects = $authUser->projects()->orderBy('name')->get();
            $sprints = Sprint::whereHas('project.users', function ($query) use ($authUser) {
                $query->where('users.id', $authUser->id);
            })->with('project')->orderBy('start_date', 'desc')->get();
        } elseif ($permissions === 'developer') {
            // Developer ve solo sus tareas asignadas
            $tasksQuery = Task::where('user_id', $authUser->id)
                ->with(['user', 'sprint', 'project', 'qaReviewedBy', 'teamLeaderReviewedBy']);
            $projects = $authUser->projects()->orderBy('name')->get();
            $sprints = Sprint::whereHas('project.users', function ($query) use ($authUser) {
                $query->where('users.id', $authUser->id);
            })->with('project')->orderBy('start_date', 'desc')->get();
        }

        // Aplicar filtros
        $filters = $request->only(['project_id', 'sprint_id', 'status', 'priority', 'assigned_user_id', 'sort_by', 'sort_order']);

        // Filtro por proyecto
        if (!empty($filters['project_id'])) {
            $tasksQuery->whereHas('sprint.project', function ($query) use ($filters) {
                $query->where('id', $filters['project_id']);
            });
        }

        // Filtro por sprint
        if (!empty($filters['sprint_id'])) {
            $tasksQuery->where('sprint_id', $filters['sprint_id']);
        }

        // Filtro por estado
        if (!empty($filters['status'])) {
            $tasksQuery->where('status', $filters['status']);
        }

        // Filtro por prioridad
        if (!empty($filters['priority'])) {
            $tasksQuery->where('priority', $filters['priority']);
        }

        // Filtro por usuario asignado
        if (!empty($filters['assigned_user_id'])) {
            if ($filters['assigned_user_id'] === 'unassigned') {
                $tasksQuery->whereNull('user_id');
            } else {
                $tasksQuery->where('user_id', $filters['assigned_user_id']);
            }
        }

        $tasks = $tasksQuery->get();

        // Aplicar ordenamiento personalizado
        if (!empty($filters['sort_by'])) {
            $tasks = $tasks->sortBy(function ($task) use ($filters) {
                switch ($filters['sort_by']) {
                    case 'priority':
                        $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
                        return $priorityOrder[$task->priority] ?? 0;
                    case 'status':
                        $statusOrder = ['to do' => 1, 'in progress' => 2, 'done' => 3];
                        return $statusOrder[$task->status] ?? 0;
                    case 'story_points':
                        return $task->story_points;
                    case 'estimated_hours':
                        return $task->estimated_hours;
                    case 'actual_hours':
                        return $task->actual_hours ?? 0;
                    case 'completion_percentage':
                        return $task->actual_hours && $task->estimated_hours 
                            ? ($task->actual_hours / $task->estimated_hours) * 100 
                            : 0;
                    case 'due_date':
                        return $task->estimated_finish ?? '9999-12-31';
                    case 'assigned_user':
                        return $task->user ? $task->user->name : '';
                    case 'project':
                        return $task->sprint && $task->sprint->project ? $task->sprint->project->name : '';
                    case 'sprint':
                        return $task->sprint ? $task->sprint->name : '';
                    case 'recent':
                        return $task->created_at;
                    default:
                        return $task->created_at;
                }
            });

            // Aplicar orden
            if ($filters['sort_order'] === 'desc') {
                $tasks = $tasks->reverse();
            }
        }

        // Agregar información de sesiones pausadas a las tareas
        $tasks->each(function ($task) {
            $task->has_paused_sessions = $task->hasPausedSessions();
        });

        // Obtener desarrolladores para el modal de creación de tareas
        $developers = User::with('roles')->whereHas('roles', function ($query) {
            $query->whereIn('name', ['developer', 'team_leader']);
        })->orderBy('name')->get();

        return Inertia::render('Task/Index', [
            'tasks' => $tasks,
            'permissions' => $permissions,
            'projects' => $projects,
            'sprints' => $sprints,
            'developers' => $developers,
            'filters' => $filters,
        ]);
    }

    public function show($id): Response
    {
        $authUser = Auth::user();
        
        if (!$authUser) {
            return redirect()->route('login');
        }
        
        $role = $authUser->roles;
        $permissions = 'developer'; // Default permission

        if ($role && $role->count() > 0) {
            $permissions = $role->first()->name;
        }

        $task = Task::with(['user', 'sprint', 'project'])->findOrFail($id);
        
        // Verificar permisos
        if ($permissions === 'developer') {
            // Verificar que el usuario tenga acceso a la tarea
            // El usuario puede ver la tarea si:
            // 1. Está asignado a la tarea específica, O
            // 2. Está asignado al proyecto de la tarea
            $isAssignedToTask = $task->user_id === $authUser->id;
            $isAssignedToProject = $task->sprint && $task->sprint->project && 
                                 $task->sprint->project->users()->where('users.id', $authUser->id)->exists();
            
            $hasAccess = $isAssignedToTask || $isAssignedToProject;
            
            if (!$hasAccess) {
                abort(403, 'Access denied');
            }
        }

        // Get developers for the project
        $developers = collect();
        if ($task->project) {
            $developers = $task->project->users()->orderBy('name')->get();
        }

        return Inertia::render('Task/Show', [
            'task' => $task,
            'permissions' => $permissions,
            'developers' => $developers,
        ]);
    }
    public function store(Request $request): RedirectResponse
    {
        try {
            Log::info('Task creation started', [
                'request_data' => $request->all()
            ]);

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'priority' => 'required|string|in:low,medium,high,critical',
                'category' => 'required|string|max:255',
                'story_points' => 'required|integer|min:1|max:255',
                'sprint_id' => 'required|string|exists:sprints,id',
                'project_id' => 'required|string|exists:projects,id',
                'estimated_hours' => 'required|integer|min:1|max:255',
                'assigned_user_id' => 'nullable|string|exists:users,id',
                // Campos de fechas eliminados por migración 2025_07_29_085000
            ]);

            Log::info('Task validation passed', [
                'validated_data' => $validatedData
            ]);

            // Normalizar prioridad/categoría y castear números para ajustarse al esquema actual
            $allowedPriorities = ['low', 'medium', 'high'];
            $normalizedPriority = $validatedData['priority'] === 'critical' ? 'high' : $validatedData['priority'];
            if (!in_array($normalizedPriority, $allowedPriorities, true)) {
                $normalizedPriority = 'medium';
            }

            $allowedCategories = ['frontend', 'backend', 'full stack', 'design', 'deployment', 'fixes'];
            $normalizedCategory = in_array($validatedData['category'], $allowedCategories, true)
                ? $validatedData['category']
                : 'full stack';

            $storyPoints = (int) $validatedData['story_points'];
            $estimatedHours = (int) $validatedData['estimated_hours'];

            $taskData = [
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'priority' => $normalizedPriority,
                'category' => $normalizedCategory,
                'story_points' => $storyPoints,
                'sprint_id' => $validatedData['sprint_id'],
                'estimated_hours' => $estimatedHours,
                'user_id' => $request->filled('assigned_user_id') ? $validatedData['assigned_user_id'] : null,
                'status' => 'to do',
                // La BD no permite 'pending' (ver migración 2025_08_06_*), usar 'ready_for_test'
                'qa_status' => 'ready_for_test',
            ];

            // Solo asignar project_id si existe la columna (compatibilidad con DB sin migración aplicada)
            if (Schema::hasColumn('tasks', 'project_id')) {
                $taskData['project_id'] = $validatedData['project_id'];
            }

            $task = Task::create($taskData);

            Log::info('Task created successfully', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'sprint_id' => $task->sprint_id,
                'assigned_user_id' => $task->user_id
            ]);

            return redirect()->route('sprints.show', [
                'project' => $validatedData['project_id'],
                'sprint' => $validatedData['sprint_id'],
            ])->with('success', 'Task created successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Task validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Task creation failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            $message = $e->getMessage();
            if ($e instanceof \Illuminate\Database\QueryException) {
                $message = $e->getMessage();
            }
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $message]);
        }
    }

    public function update(Request $request, $id): RedirectResponse
    {
        try {
            Log::info('Task update started', [
                'task_id' => $id,
                'request_data' => $request->all()
            ]);

            $validatedData = $request->validate([
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:255',
                'estimated_start' => 'nullable|date',
                'estimated_finish' => 'nullable|date',
                'status' => 'nullable|string|max:255',
                'priority' => 'nullable|string|max:255',
                'category' => 'nullable|string|max:255',
                'actual_hours' => 'nullable|integer|min:0|max:255',
                'story_points' => 'nullable|integer|min:1|max:255',
                'user_id' => 'nullable|string|exists:users,id',
                'estimated_hours' => 'nullable|integer|min:1|max:255',
                'actual_start' => 'nullable|date',
                'actual_finish' => 'nullable|date',
            ]);

            Log::info('Task update validation passed', [
                'validated_data' => $validatedData
            ]);

            $task = Task::findOrFail($id);

            // Handle status changes
            if (isset($validatedData['status']) && $validatedData['status'] === 'in progress' && $task->status !== 'in progress'){
                $task->actual_start = Carbon::now()->format('Y-m-d');
                Log::info('Task started', ['task_id' => $task->id, 'start_date' => $task->actual_start]);
            }

            if (isset($validatedData['status']) && $validatedData['status'] === 'done' && $task->status !== 'done'){
                $task->actual_finish = Carbon::now()->format('Y-m-d');
                Log::info('Task completed', ['task_id' => $task->id, 'finish_date' => $task->actual_finish]);
            }

            $task->update($validatedData);

            Log::info('Task updated successfully', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'new_status' => $task->status ?? 'unchanged',
                'assigned_user_id' => $task->user_id
            ]);

            return redirect()->back()->with('success', 'Task updated successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Task update validation failed', [
                'task_id' => $id,
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Task update failed', [
                'task_id' => $id,
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()->with('error', 'Failed to update task. Please try again.');
        }
    }

    /**
     * Asignar tarea a un desarrollador (por team leader)
     */
    public function assignTask(Request $request, $taskId): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'developer_id' => 'required|string|exists:users,id',
            ]);

            $task = Task::findOrFail($taskId);
            $developer = User::findOrFail($validatedData['developer_id']);
            $teamLeader = Auth::user();

            $this->taskAssignmentService->assignTaskByTeamLeader($task, $developer, $teamLeader);

            return response()->json([
                'success' => true,
                'message' => 'Tarea asignada correctamente',
                'task' => $task->fresh(['user', 'assignedBy'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al asignar tarea', [
                'error' => $e->getMessage(),
                'task_id' => $taskId,
                'developer_id' => $validatedData['developer_id'] ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Auto-asignar tarea (por desarrollador)
     */
    public function selfAssignTask(Request $request, $taskId): JsonResponse
    {
        try {
            $task = Task::findOrFail($taskId);
            $developer = Auth::user();

            $this->taskAssignmentService->selfAssignTask($task, $developer);

            return response()->json([
                'success' => true,
                'message' => 'Tarea auto-asignada correctamente',
                'task' => $task->fresh(['user', 'assignedBy'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al auto-asignar tarea', [
                'error' => $e->getMessage(),
                'task_id' => $taskId,
                'developer_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtener tareas disponibles para auto-asignación
     */
    public function getAvailableTasks(): JsonResponse
    {
        try {
            $developer = Auth::user();
            $availableTasks = $this->taskAssignmentService->getAvailableTasksForDeveloper($developer);

            return response()->json([
                'success' => true,
                'tasks' => $availableTasks
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener tareas disponibles', [
                'error' => $e->getMessage(),
                'developer_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tareas disponibles'
            ], 500);
        }
    }

    /**
     * Obtener tareas asignadas al desarrollador actual
     */
    public function getMyTasks(): JsonResponse
    {
        try {
            $developer = Auth::user();
            $assignedTasks = $this->taskAssignmentService->getAssignedTasksForDeveloper($developer);

            return response()->json([
                'success' => true,
                'tasks' => $assignedTasks
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener tareas asignadas', [
                'error' => $e->getMessage(),
                'developer_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tareas asignadas'
            ], 500);
        }
    }

    /**
     * Obtener desarrolladores disponibles para un proyecto
     */
    public function getAvailableDevelopers($projectId): JsonResponse
    {
        try {
            $project = Project::findOrFail($projectId);
            $developers = $this->taskAssignmentService->getAvailableDevelopersForProject($project);

            return response()->json([
                'success' => true,
                'developers' => $developers
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener desarrolladores disponibles', [
                'error' => $e->getMessage(),
                'project_id' => $projectId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener desarrolladores disponibles'
            ], 500);
        }
    }

    /**
     * Iniciar trabajo en una tarea
     */
    public function startWork(Request $request, $taskId): JsonResponse
    {
        try {
            $task = Task::findOrFail($taskId);
            $user = Auth::user();

            $this->taskTimeTrackingService->startWork($task, $user);

            // Log activity for tracking
            $this->activityTrackingService->logActivity($user, 'task_start', $task);

            return response()->json([
                'success' => true,
                'message' => 'Trabajo iniciado correctamente',
                'task' => $task->fresh(['user', 'sprint', 'project'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al iniciar trabajo', [
                'error' => $e->getMessage(),
                'task_id' => $taskId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Pausar trabajo en una tarea
     */
    public function pauseWork(Request $request, $taskId): JsonResponse
    {
        try {
            $task = Task::findOrFail($taskId);
            $user = Auth::user();

            $this->taskTimeTrackingService->pauseWork($task, $user);

            // Log activity for tracking
            $this->activityTrackingService->logActivity($user, 'task_pause', $task);

            return response()->json([
                'success' => true,
                'message' => 'Trabajo pausado correctamente',
                'task' => $task->fresh(['user', 'sprint', 'project'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al pausar trabajo', [
                'error' => $e->getMessage(),
                'task_id' => $taskId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reanudar trabajo en una tarea
     */
    public function resumeWork(Request $request, $taskId): JsonResponse
    {
        try {
            $task = Task::findOrFail($taskId);
            $user = Auth::user();

            $this->taskTimeTrackingService->resumeWork($task, $user);

            // Log activity for tracking
            $this->activityTrackingService->logActivity($user, 'task_resume', $task);

            return response()->json([
                'success' => true,
                'message' => 'Trabajo reanudado correctamente',
                'task' => $task->fresh(['user', 'sprint', 'project'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al reanudar trabajo', [
                'error' => $e->getMessage(),
                'task_id' => $taskId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Finalizar trabajo en una tarea
     */
    public function finishWork(Request $request, $taskId): JsonResponse
    {
        try {
            $task = Task::findOrFail($taskId);
            $user = Auth::user();

            $this->taskTimeTrackingService->finishWork($task, $user);

            // Log activity for tracking
            $this->activityTrackingService->logActivity($user, 'task_finish', $task);

            return response()->json([
                'success' => true,
                'message' => 'Trabajo finalizado correctamente. Pendiente de revisión por team leader.',
                'task' => $task->fresh(['user', 'sprint', 'project'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al finalizar trabajo', [
                'error' => $e->getMessage(),
                'task_id' => $taskId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtener tiempo actual de trabajo en una tarea
     */
    public function getCurrentWorkTime($taskId): JsonResponse
    {
        try {
            $task = Task::findOrFail($taskId);
            $currentTime = $this->taskTimeTrackingService->getCurrentWorkTime($task);

            return response()->json([
                'success' => true,
                'current_time_seconds' => $currentTime,
                'formatted_time' => gmdate('H:i:s', $currentTime)
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener tiempo de trabajo', [
                'error' => $e->getMessage(),
                'task_id' => $taskId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tiempo de trabajo'
            ], 500);
        }
    }

    /**
     * Obtener logs de tiempo de una tarea
     */
    public function getTaskTimeLogs($taskId): JsonResponse
    {
        try {
            $task = Task::findOrFail($taskId);
            $timeLogs = $this->taskTimeTrackingService->getTaskTimeLogs($task);

            return response()->json([
                'success' => true,
                'time_logs' => $timeLogs
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener logs de tiempo', [
                'error' => $e->getMessage(),
                'task_id' => $taskId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener logs de tiempo'
            ], 500);
        }
    }

    /**
     * Obtener tareas activas del usuario
     */
    public function getActiveTasks(): JsonResponse
    {
        try {
            $user = Auth::user();
            $activeTasks = $this->taskTimeTrackingService->getActiveTasksForUser($user);

            return response()->json([
                'success' => true,
                'active_tasks' => $activeTasks
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener tareas activas', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tareas activas'
            ], 500);
        }
    }

    /**
     * Obtener tareas pausadas del usuario
     */
    public function getPausedTasks(): JsonResponse
    {
        try {
            $user = Auth::user();
            $pausedTasks = $this->taskTimeTrackingService->getPausedTasksForUser($user);

            return response()->json([
                'success' => true,
                'paused_tasks' => $pausedTasks
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener tareas pausadas', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tareas pausadas'
            ], 500);
        }
    }

    /**
     * Reanudar tarea auto-pausada
     */
    public function resumeAutoPausedTask(Request $request, $taskId): JsonResponse
    {
        try {
            $task = Task::findOrFail($taskId);
            $user = Auth::user();

            $this->taskTimeTrackingService->resumeAutoPausedTask($task, $user);

            return response()->json([
                'success' => true,
                'message' => 'Tarea auto-pausada reanudada correctamente',
                'task' => $task->fresh(['user', 'sprint', 'project'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al reanudar tarea auto-pausada', [
                'error' => $e->getMessage(),
                'task_id' => $taskId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtener tareas auto-pausadas del usuario
     */
    public function getAutoPausedTasks(): JsonResponse
    {
        try {
            $user = Auth::user();
            $autoPausedTasks = $this->taskTimeTrackingService->getAutoPausedTasksForUser($user);

            return response()->json([
                'success' => true,
                'auto_paused_tasks' => $autoPausedTasks
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener tareas auto-pausadas', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tareas auto-pausadas'
            ], 500);
        }
    }

    /**
     * Buscar tareas por nombre, sprint o proyecto
     */
    public function search(Request $request): JsonResponse
    {
        $authUser = Auth::user();
        
        if (!$authUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $role = $authUser->roles;
        $permissions = 'developer'; // Default permission

        if ($role && $role->count() > 0) {
            $permissions = $role->first()->name;
        }

        $tasksQuery = null;

        if ($permissions === 'admin') {
            // Admin puede buscar en todas las tareas
            $tasksQuery = Task::with(['user', 'sprint', 'project']);
        } elseif ($permissions === 'team_leader') {
            // Team leader busca en tareas de sus proyectos
            $tasksQuery = Task::whereHas('sprint.project.users', function ($query) use ($authUser) {
                $query->where('users.id', $authUser->id);
            })->with(['user', 'sprint', 'project']);
        } elseif ($permissions === 'developer') {
            // Developer busca en sus tareas asignadas
            $tasksQuery = Task::where('user_id', $authUser->id)
                ->with(['user', 'sprint', 'project']);
        }

        $tasks = $tasksQuery->where(function ($q) use ($query) {
            $q->where('name', 'ilike', "%{$query}%")
              ->orWhere('description', 'ilike', "%{$query}%")
              ->orWhereHas('sprint', function ($sprintQuery) use ($query) {
                  $sprintQuery->where('name', 'ilike', "%{$query}%");
              });
        })
        ->orderBy('name')
        ->limit(10)
        ->get(['id', 'name', 'description', 'status', 'priority', 'sprint_id', 'project_id']);

        return response()->json($tasks);
    }
}

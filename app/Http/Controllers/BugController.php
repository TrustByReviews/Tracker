<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\User;
use App\Services\BugTimeTrackingService;
use App\Services\BugAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BugController extends Controller
{
    public function __construct(
        private BugTimeTrackingService $bugTimeTrackingService,
        private BugAssignmentService $bugAssignmentService
    ) {}

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

        $bugsQuery = null;
        $projects = [];
        $sprints = [];

        if ($permissions === 'admin') {
            // Admin ve todo
            $bugsQuery = Bug::with(['user', 'sprint', 'project', 'assignedBy', 'relatedTask']);
            $projects = Project::orderBy('name')->get();
            $sprints = Sprint::with('project')->orderBy('start_date', 'desc')->get();
        } elseif ($permissions === 'qa') {
            // QA ve bugs de sus proyectos asignados
            $bugsQuery = Bug::whereHas('sprint.project', function ($query) use ($authUser) {
                $query->whereHas('users', function ($q) use ($authUser) {
                    $q->where('users.id', $authUser->id);
                });
            })->with(['user', 'sprint', 'project', 'assignedBy', 'relatedTask']);
            $projects = $authUser->projects()->orderBy('name')->get();
            $sprints = Sprint::whereHas('project.users', function ($query) use ($authUser) {
                $query->where('users.id', $authUser->id);
            })->with('project')->orderBy('start_date', 'desc')->get();
        } elseif ($permissions === 'team_leader') {
            // Team leader ve bugs de sus proyectos
            $bugsQuery = Bug::whereHas('sprint.project.users', function ($query) use ($authUser) {
                $query->where('users.id', $authUser->id);
            })->with(['user', 'sprint', 'project', 'assignedBy', 'relatedTask']);
            $projects = $authUser->projects()->orderBy('name')->get();
            $sprints = Sprint::whereHas('project.users', function ($query) use ($authUser) {
                $query->where('users.id', $authUser->id);
            })->with('project')->orderBy('start_date', 'desc')->get();
        } elseif ($permissions === 'developer') {
            // Developer ve solo sus bugs asignados y disponibles
            $bugsQuery = Bug::where(function ($query) use ($authUser) {
                $query->where('user_id', $authUser->id)
                      ->orWhereNull('user_id');
            })->with(['user', 'sprint', 'project', 'assignedBy', 'relatedTask', 'qaReviewedBy', 'teamLeaderReviewedBy']);
            $projects = $authUser->projects()->orderBy('name')->get();
            $sprints = Sprint::whereHas('project.users', function ($query) use ($authUser) {
                $query->where('users.id', $authUser->id);
            })->with('project')->orderBy('start_date', 'desc')->get();
        }

        // Aplicar filtros
        $filters = $request->only(['project_id', 'sprint_id', 'status', 'importance', 'bug_type', 'assigned_user_id', 'sort_by', 'sort_order', 'search', 'qa_status', 'team_leader_status']);

        // Filtro por proyecto
        if (!empty($filters['project_id'])) {
            $bugsQuery->whereHas('sprint.project', function ($query) use ($filters) {
                $query->where('id', $filters['project_id']);
            });
        }

        // Filtro por sprint
        if (!empty($filters['sprint_id'])) {
            $bugsQuery->where('sprint_id', $filters['sprint_id']);
        }

        // Filtro por estado
        if (!empty($filters['status'])) {
            $bugsQuery->where('status', $filters['status']);
        }

        // Filtro por importancia
        if (!empty($filters['importance'])) {
            $bugsQuery->where('importance', $filters['importance']);
        }

        // Filtro por tipo de bug
        if (!empty($filters['bug_type'])) {
            $bugsQuery->where('bug_type', $filters['bug_type']);
        }

        // Filtro por usuario asignado
        if (!empty($filters['assigned_user_id'])) {
            if ($filters['assigned_user_id'] === 'unassigned') {
                $bugsQuery->whereNull('user_id');
            } else {
                $bugsQuery->where('user_id', $filters['assigned_user_id']);
            }
        }

        // Filtro de búsqueda
        if (!empty($filters['search'])) {
            $bugsQuery->where(function ($query) use ($filters) {
                $query->where('title', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Filtro por estado de QA
        if (!empty($filters['qa_status'])) {
            $bugsQuery->where('qa_status', $filters['qa_status']);
        }

        // Filtro por estado de Team Leader
        if (!empty($filters['team_leader_status'])) {
            switch ($filters['team_leader_status']) {
                case 'pending':
                    $bugsQuery->where('qa_status', 'approved')
                              ->where('team_leader_final_approval', false)
                              ->where('team_leader_requested_changes', false);
                    break;
                case 'approved':
                    $bugsQuery->where('team_leader_final_approval', true);
                    break;
                case 'changes_requested':
                    $bugsQuery->where('team_leader_requested_changes', true);
                    break;
            }
        }

        $bugs = $bugsQuery->get();

        // Aplicar ordenamiento personalizado
        if (!empty($filters['sort_by'])) {
            $bugs = $bugs->sortBy(function ($bug) use ($filters) {
                switch ($filters['sort_by']) {
                    case 'importance':
                        $importanceOrder = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
                        return $importanceOrder[$bug->importance] ?? 0;
                    case 'status':
                        $statusOrder = ['new' => 1, 'assigned' => 2, 'in progress' => 3, 'resolved' => 4, 'verified' => 5, 'closed' => 6, 'reopened' => 7];
                        return $statusOrder[$bug->status] ?? 0;
                    case 'priority_score':
                        return $bug->priority_score;
                    case 'estimated_hours':
                        return $bug->estimated_hours;
                    case 'actual_hours':
                        return $bug->actual_hours ?? 0;
                    case 'completion_percentage':
                        return $bug->actual_hours && $bug->estimated_hours 
                            ? ($bug->actual_hours / $bug->estimated_hours) * 100 
                            : 0;
                    case 'assigned_user':
                        return $bug->user ? $bug->user->name : '';
                    case 'project':
                        return $bug->sprint && $bug->sprint->project ? $bug->sprint->project->name : '';
                    case 'sprint':
                        return $bug->sprint ? $bug->sprint->name : '';
                    case 'recent':
                        return $bug->created_at;
                    default:
                        return $bug->created_at;
                }
            });

            // Aplicar orden
            if ($filters['sort_order'] === 'desc') {
                $bugs = $bugs->reverse();
            }
        }

        // Agregar información de sesiones pausadas a los bugs
        $bugs->each(function ($bug) {
            $bug->has_paused_sessions = $bug->hasPausedSessions();
        });

        // Obtener desarrolladores para el modal de creación de bugs
        $developers = User::with('roles')->whereHas('roles', function ($query) {
            $query->whereIn('name', ['developer', 'team_leader']);
        })->orderBy('name')->get();

        return Inertia::render('Bug/Index', [
            'bugs' => $bugs,
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

        $bug = Bug::with(['user', 'sprint', 'project', 'assignedBy', 'resolvedBy', 'verifiedBy', 'timeLogs', 'comments.user', 'relatedTask.sprint', 'relatedTask.user'])
            ->findOrFail($id);

        // Verificar permisos
        if ($permissions === 'developer' && $bug->user_id !== $authUser->id && $bug->user_id !== null) {
            abort(403, 'No tienes permisos para ver este bug.');
        }

        // Obtener usuarios del proyecto para asignación
        $projectUsers = collect();
        if ($bug->project) {
            $projectUsers = User::whereHas('projects', function ($query) use ($bug) {
                $query->where('project_id', $bug->project_id);
            })
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['developer', 'team_leader']);
            })
            ->select('id', 'name', 'email')
            ->get();
        }

        return Inertia::render('Bug/Show', [
            'bug' => $bug,
            'permissions' => $permissions,
            'projectUsers' => $projectUsers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'long_description' => 'nullable|string',
            'importance' => 'required|in:low,medium,high,critical',
            'bug_type' => 'required|in:frontend,backend,database,api,ui_ux,performance,security,other',
            'environment' => 'nullable|string',
            'browser_info' => 'nullable|string',
            'os_info' => 'nullable|string',
            'steps_to_reproduce' => 'nullable|string',
            'expected_behavior' => 'nullable|string',
            'actual_behavior' => 'nullable|string',
            'reproducibility' => 'required|in:always,sometimes,rarely,unable',
            'severity' => 'required|in:low,medium,high,critical',
            'sprint_id' => 'required|exists:sprints,id',
            'project_id' => 'required|exists:projects,id',
            'estimated_hours' => 'nullable|integer|min:0',
            'estimated_minutes' => 'nullable|integer|min:0|max:59',
            'tags' => 'nullable|string',
            'related_task_id' => 'nullable|exists:tasks,id',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max
        ]);

        // Procesar archivos adjuntos
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('bug-attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        // Calcular priority score
        $priorityScore = $this->calculatePriorityScore($validatedData['importance'], $validatedData['severity'], $validatedData['reproducibility']);

        $bug = Bug::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'long_description' => $validatedData['long_description'],
            'importance' => $validatedData['importance'],
            'bug_type' => $validatedData['bug_type'],
            'environment' => $validatedData['environment'],
            'browser_info' => $validatedData['browser_info'],
            'os_info' => $validatedData['os_info'],
            'steps_to_reproduce' => $validatedData['steps_to_reproduce'],
            'expected_behavior' => $validatedData['expected_behavior'],
            'actual_behavior' => $validatedData['actual_behavior'],
            'reproducibility' => $validatedData['reproducibility'],
            'severity' => $validatedData['severity'],
            'sprint_id' => $validatedData['sprint_id'],
            'project_id' => $validatedData['project_id'],
            'estimated_hours' => $validatedData['estimated_hours'] ?? 0,
            'estimated_minutes' => $validatedData['estimated_minutes'] ?? 0,
            'tags' => $validatedData['tags'],
            'related_task_id' => $validatedData['related_task_id'] ?? null,
            'attachments' => $attachments,
            'priority_score' => $priorityScore,
            'status' => Bug::STATUS_NEW,
            'qa_status' => 'testing',
        ]);

        return redirect()->route('bugs.show', $bug->id)
            ->with('success', 'Bug creado exitosamente.');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $bug = Bug::findOrFail($id);
        
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'long_description' => 'nullable|string',
            'importance' => 'required|in:low,medium,high,critical',
            'bug_type' => 'required|in:frontend,backend,database,api,ui_ux,performance,security,other',
            'environment' => 'nullable|string',
            'browser_info' => 'nullable|string',
            'os_info' => 'nullable|string',
            'steps_to_reproduce' => 'nullable|string',
            'expected_behavior' => 'nullable|string',
            'actual_behavior' => 'nullable|string',
            'reproducibility' => 'required|in:always,sometimes,rarely,unable',
            'severity' => 'required|in:low,medium,high,critical',
            'estimated_hours' => 'nullable|integer|min:0',
            'estimated_minutes' => 'nullable|integer|min:0|max:59',
            'tags' => 'nullable|string',
            'resolution_notes' => 'nullable|string',
        ]);

        // Calcular priority score
        $priorityScore = $this->calculatePriorityScore($validatedData['importance'], $validatedData['severity'], $validatedData['reproducibility']);

        $bug->update([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'long_description' => $validatedData['long_description'],
            'importance' => $validatedData['importance'],
            'bug_type' => $validatedData['bug_type'],
            'environment' => $validatedData['environment'],
            'browser_info' => $validatedData['browser_info'],
            'os_info' => $validatedData['os_info'],
            'steps_to_reproduce' => $validatedData['steps_to_reproduce'],
            'expected_behavior' => $validatedData['expected_behavior'],
            'actual_behavior' => $validatedData['actual_behavior'],
            'reproducibility' => $validatedData['reproducibility'],
            'severity' => $validatedData['severity'],
            'estimated_hours' => $validatedData['estimated_hours'] ?? 0,
            'estimated_minutes' => $validatedData['estimated_minutes'] ?? 0,
            'tags' => $validatedData['tags'],
            'resolution_notes' => $validatedData['resolution_notes'],
            'priority_score' => $priorityScore,
        ]);

        return redirect()->route('bugs.show', $bug->id)
            ->with('success', 'Bug actualizado exitosamente.');
    }

    public function destroy($id): JsonResponse
    {
        $bug = Bug::findOrFail($id);
        
        // Eliminar archivos adjuntos
        if ($bug->attachments) {
            foreach ($bug->attachments as $attachment) {
                Storage::disk('public')->delete($attachment['path']);
            }
        }
        
        $bug->delete();
        
        return response()->json(['message' => 'Bug eliminado exitosamente.']);
    }

    // Métodos para seguimiento de tiempo
    public function startWork(Request $request, Bug $bug)
    {
        try {
            $result = $this->bugTimeTrackingService->startWork($bug);
            
            // Para Inertia.js, devolver una respuesta que no cause advertencias
            if ($request->header('X-Inertia')) {
                return back()->with('success', $result['message']);
            }
            
            return response()->json($result);
        } catch (\Exception $e) {
            if ($request->header('X-Inertia')) {
                return back()->with('error', $e->getMessage());
            }
            
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function pauseWork(Request $request, Bug $bug)
    {
        try {
            $result = $this->bugTimeTrackingService->pauseWork($bug);
            
            // Para Inertia.js, devolver una respuesta que no cause advertencias
            if ($request->header('X-Inertia')) {
                return back()->with('success', $result['message']);
            }
            
            return response()->json($result);
        } catch (\Exception $e) {
            if ($request->header('X-Inertia')) {
                return back()->with('error', $e->getMessage());
            }
            
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function resumeWork(Request $request, Bug $bug)
    {
        try {
            $result = $this->bugTimeTrackingService->resumeWork($bug);
            
            // Para Inertia.js, devolver una respuesta que no cause advertencias
            if ($request->header('X-Inertia')) {
                return back()->with('success', $result['message']);
            }
            
            return response()->json($result);
        } catch (\Exception $e) {
            if ($request->header('X-Inertia')) {
                return back()->with('error', $e->getMessage());
            }
            
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function finishWork(Request $request, Bug $bug)
    {
        try {
            $result = $this->bugTimeTrackingService->finishWork($bug);
            
            // Para Inertia.js, devolver una respuesta que no cause advertencias
            if ($request->header('X-Inertia')) {
                return back()->with('success', $result['message']);
            }
            
            return response()->json($result);
        } catch (\Exception $e) {
            if ($request->header('X-Inertia')) {
                return back()->with('error', $e->getMessage());
            }
            
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // Métodos para asignación
    public function assignBug(Request $request, $bugId): RedirectResponse
    {
        try {
            $response = $this->bugAssignmentService->assignBug($bugId, $request->user_id, Auth::id());
            $responseData = json_decode($response->getContent(), true);
            
            if ($response->getStatusCode() === 200) {
                return redirect()->back()->with('success', $responseData['message']);
            } else {
                return redirect()->back()->with('error', $responseData['error']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al asignar bug: ' . $e->getMessage());
        }
    }

    public function selfAssignBug(Request $request, $bugId): RedirectResponse
    {
        try {
            $response = $this->bugAssignmentService->selfAssignBug($bugId, Auth::id());
            $responseData = json_decode($response->getContent(), true);
            
            if ($response->getStatusCode() === 200) {
                return redirect()->back()->with('success', $responseData['message']);
            } else {
                return redirect()->back()->with('error', $responseData['error']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al auto-asignar bug: ' . $e->getMessage());
        }
    }

    public function unassignBug(Request $request, $bugId): RedirectResponse
    {
        try {
            $response = $this->bugAssignmentService->unassignBug($bugId);
            $responseData = json_decode($response->getContent(), true);
            
            if ($response->getStatusCode() === 200) {
                return redirect()->back()->with('success', $responseData['message']);
            } else {
                return redirect()->back()->with('error', $responseData['error']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al desasignar bug: ' . $e->getMessage());
        }
    }

    // Métodos para resolución y verificación
    public function resolveBug(Request $request, $bugId): JsonResponse
    {
        $validatedData = $request->validate([
            'resolution_notes' => 'required|string',
        ]);

        $bug = Bug::findOrFail($bugId);
        
        $bug->update([
            'status' => Bug::STATUS_RESOLVED,
            'resolution_notes' => $validatedData['resolution_notes'],
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Bug marcado como resuelto.',
            'bug' => $bug->load(['resolvedBy']),
        ]);
    }

    public function verifyBug(Request $request, $bugId): JsonResponse
    {
        $bug = Bug::findOrFail($bugId);
        
        $bug->update([
            'status' => Bug::STATUS_VERIFIED,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return response()->json([
            'message' => 'Bug marcado como verificado.',
            'bug' => $bug->load(['verifiedBy']),
        ]);
    }

    public function closeBug(Request $request, $bugId): JsonResponse
    {
        $bug = Bug::findOrFail($bugId);
        
        $bug->update([
            'status' => Bug::STATUS_CLOSED,
        ]);

        return response()->json([
            'message' => 'Bug cerrado exitosamente.',
            'bug' => $bug,
        ]);
    }

    public function reopenBug(Request $request, $bugId): JsonResponse
    {
        $bug = Bug::findOrFail($bugId);
        
        $bug->update([
            'status' => Bug::STATUS_REOPENED,
        ]);

        return response()->json([
            'message' => 'Bug reabierto exitosamente.',
            'bug' => $bug,
        ]);
    }

    // Métodos para comentarios
    public function addComment(Request $request, $bugId): JsonResponse
    {
        $bug = Bug::findOrFail($bugId);
        
        $validatedData = $request->validate([
            'content' => 'required|string',
            'comment_type' => 'required|in:general,resolution,verification,reproduction,internal',
            'is_internal' => 'boolean',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('bug-comment-attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        $comment = $bug->comments()->create([
            'user_id' => Auth::id(),
            'content' => $validatedData['content'],
            'comment_type' => $validatedData['comment_type'],
            'is_internal' => $validatedData['is_internal'] ?? false,
            'attachments' => $attachments,
        ]);

        return response()->json([
            'message' => 'Comentario agregado exitosamente.',
            'comment' => $comment->load('user'),
        ]);
    }

    // Métodos para obtener datos
    public function getCurrentWorkTime($bugId): JsonResponse
    {
        return $this->bugTimeTrackingService->getCurrentWorkTime($bugId);
    }

    public function getBugTimeLogs($bugId): JsonResponse
    {
        return $this->bugTimeTrackingService->getBugTimeLogs($bugId);
    }

    public function getActiveBugs(): JsonResponse
    {
        $bugs = Bug::where('is_working', true)
            ->with(['user', 'sprint', 'project'])
            ->get();

        return response()->json(['bugs' => $bugs]);
    }

    public function getPausedBugs(): JsonResponse
    {
        $bugs = Bug::where('auto_paused', true)
            ->with(['user', 'sprint', 'project'])
            ->get();

        return response()->json(['bugs' => $bugs]);
    }

    /**
     * Calcular el score de prioridad basado en importancia, severidad y reproductibilidad
     */
    private function calculatePriorityScore(string $importance, string $severity, string $reproducibility): int
    {
        $importanceScore = match($importance) {
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 1,
        };

        $severityScore = match($severity) {
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 1,
        };

        $reproducibilityScore = match($reproducibility) {
            'always' => 4,
            'sometimes' => 3,
            'rarely' => 2,
            'unable' => 1,
            default => 2,
        };

        return ($importanceScore * 3) + ($severityScore * 2) + $reproducibilityScore;
    }
} 
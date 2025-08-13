<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Suggestion;
use App\Models\Project;
use Illuminate\Support\Facades\Validator;

class SuggestionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:client');
    }

    /**
     * Get all suggestions for the authenticated client.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();
        
        $suggestions = Suggestion::where('user_id', $user->id)
            ->with(['project', 'respondedBy', 'task', 'sprint'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($suggestion) {
                return [
                    'id' => $suggestion->id,
                    'title' => $suggestion->title,
                    'description' => $suggestion->description,
                    'status' => $suggestion->status,
                    'status_label' => $suggestion->status_label,
                    'status_color' => $suggestion->status_color,
                    'admin_response' => $suggestion->admin_response,
                    'responded_by' => $suggestion->respondedBy ? $suggestion->respondedBy->name : null,
                    'responded_at' => $suggestion->responded_at,
                    'project' => [
                        'id' => $suggestion->project->id,
                        'name' => $suggestion->project->name
                    ],
                    'task' => $suggestion->task ? [
                        'id' => $suggestion->task->id,
                        'title' => $suggestion->task->name
                    ] : null,
                    'sprint' => $suggestion->sprint ? [
                        'id' => $suggestion->sprint->id,
                        'name' => $suggestion->sprint->name
                    ] : null,
                    'related_entity_name' => $suggestion->related_entity_name,
                    'related_entity_type' => $suggestion->related_entity_type,
                    'created_at' => $suggestion->created_at,
                    'updated_at' => $suggestion->updated_at
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }

    /**
     * Store a new suggestion.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'sprint_id' => 'nullable|exists:sprints,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        // Verificar que el usuario tenga acceso al proyecto
        $project = $user->projects()->find($request->project_id);
        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este proyecto'
            ], 403);
        }

        // Verificar que la tarea pertenezca al proyecto (si se proporciona)
        if ($request->task_id) {
            $task = $project->tasks()->find($request->task_id);
            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'La tarea especificada no pertenece a este proyecto'
                ], 422);
            }
        }

        // Verificar que el sprint pertenezca al proyecto (si se proporciona)
        if ($request->sprint_id) {
            $sprint = $project->sprints()->find($request->sprint_id);
            if (!$sprint) {
                return response()->json([
                    'success' => false,
                    'message' => 'El sprint especificado no pertenece a este proyecto'
                ], 422);
            }
        }

        try {
            $suggestion = Suggestion::create([
                'user_id' => $user->id,
                'project_id' => $request->project_id,
                'task_id' => $request->task_id,
                'sprint_id' => $request->sprint_id,
                'title' => $request->title,
                'description' => $request->description,
                'status' => 'pending'
            ]);

            // Cargar relaciones para la respuesta
            $suggestion->load(['project', 'user', 'task', 'sprint']);

            return response()->json([
                'success' => true,
                'message' => 'Sugerencia creada exitosamente',
                'data' => [
                    'id' => $suggestion->id,
                    'title' => $suggestion->title,
                    'description' => $suggestion->description,
                    'status' => $suggestion->status,
                    'status_label' => $suggestion->status_label,
                    'project' => [
                        'id' => $suggestion->project->id,
                        'name' => $suggestion->project->name
                    ],
                    'task' => $suggestion->task ? [
                        'id' => $suggestion->task->id,
                        'title' => $suggestion->task->name
                    ] : null,
                    'sprint' => $suggestion->sprint ? [
                        'id' => $suggestion->sprint->id,
                        'name' => $suggestion->sprint->name
                    ] : null,
                    'related_entity_name' => $suggestion->related_entity_name,
                    'related_entity_type' => $suggestion->related_entity_type,
                    'created_at' => $suggestion->created_at
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la sugerencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific suggestion.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $suggestion = Suggestion::where('user_id', $user->id)
            ->with(['project', 'respondedBy', 'task', 'sprint'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $suggestion->id,
                'title' => $suggestion->title,
                'description' => $suggestion->description,
                'status' => $suggestion->status,
                'status_label' => $suggestion->status_label,
                'status_color' => $suggestion->status_color,
                'admin_response' => $suggestion->admin_response,
                'responded_by' => $suggestion->respondedBy ? $suggestion->respondedBy->name : null,
                'responded_at' => $suggestion->responded_at,
                'project' => [
                    'id' => $suggestion->project->id,
                    'name' => $suggestion->project->name
                ],
                'task' => $suggestion->task ? [
                    'id' => $suggestion->task->id,
                    'title' => $suggestion->task->name
                ] : null,
                'sprint' => $suggestion->sprint ? [
                    'id' => $suggestion->sprint->id,
                    'name' => $suggestion->sprint->name
                ] : null,
                'related_entity_name' => $suggestion->related_entity_name,
                'related_entity_type' => $suggestion->related_entity_type,
                'created_at' => $suggestion->created_at,
                'updated_at' => $suggestion->updated_at
            ]
        ]);
    }

    /**
     * Get suggestions statistics for the authenticated client.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        $user = Auth::user();
        
        $totalSuggestions = Suggestion::where('user_id', $user->id)->count();
        $pendingSuggestions = Suggestion::where('user_id', $user->id)->where('status', 'pending')->count();
        $reviewedSuggestions = Suggestion::where('user_id', $user->id)->where('status', 'reviewed')->count();
        $implementedSuggestions = Suggestion::where('user_id', $user->id)->where('status', 'implemented')->count();
        $rejectedSuggestions = Suggestion::where('user_id', $user->id)->where('status', 'rejected')->count();

        $statistics = [
            'total' => $totalSuggestions,
            'pending' => $pendingSuggestions,
            'reviewed' => $reviewedSuggestions,
            'implemented' => $implementedSuggestions,
            'rejected' => $rejectedSuggestions,
            'response_rate' => $totalSuggestions > 0 ? round((($totalSuggestions - $pendingSuggestions) / $totalSuggestions) * 100, 2) : 0
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * Get projects available for suggestions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableProjects()
    {
        $user = Auth::user();
        
        $projects = $user->projects()
            ->where('status', 'active')
            ->select('id', 'name', 'description')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    /**
     * Get tasks available for suggestions in a specific project.
     *
     * @param string $projectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableTasks($projectId)
    {
        $user = Auth::user();
        
        // Verificar que el usuario tenga acceso al proyecto
        $project = $user->projects()->find($projectId);
        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este proyecto'
            ], 403);
        }

        $tasks = $project->tasks()
            ->where('task_type', '!=', 'bug') // Excluir bugs
            ->select('id', 'name as title', 'description', 'status')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    /**
     * Get sprints available for suggestions in a specific project.
     *
     * @param string $projectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableSprints($projectId)
    {
        $user = Auth::user();
        
        // Verificar que el usuario tenga acceso al proyecto
        $project = $user->projects()->find($projectId);
        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este proyecto'
            ], 403);
        }

        $sprints = $project->sprints()
            ->select('id', 'name', 'description', 'start_date', 'end_date', 'sprint_type')
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sprints
        ]);
    }
}

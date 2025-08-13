<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Suggestion;
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
        $this->middleware('role:admin');
    }

    /**
     * Get all suggestions from clients with filters.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Suggestion::with(['user', 'project', 'task', 'sprint', 'respondedBy']);

        // Aplicar filtros
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('project_id') && $request->project_id !== 'all') {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('client_id') && $request->client_id !== 'all') {
            $query->where('user_id', $request->client_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        $suggestions = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15))
            ->through(function ($suggestion) {
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
                    'client' => [
                        'id' => $suggestion->user->id,
                        'name' => $suggestion->user->name,
                        'email' => $suggestion->user->email
                    ],
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
     * Get a specific suggestion with full details.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $suggestion = Suggestion::with(['user', 'project', 'task', 'sprint', 'respondedBy'])
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
                'client' => [
                    'id' => $suggestion->user->id,
                    'name' => $suggestion->user->name,
                    'email' => $suggestion->user->email
                ],
                'project' => [
                    'id' => $suggestion->project->id,
                    'name' => $suggestion->project->name,
                    'description' => $suggestion->project->description
                ],
                'task' => $suggestion->task ? [
                    'id' => $suggestion->task->id,
                    'title' => $suggestion->task->name,
                    'description' => $suggestion->task->description,
                    'status' => $suggestion->task->status
                ] : null,
                'sprint' => $suggestion->sprint ? [
                    'id' => $suggestion->sprint->id,
                    'name' => $suggestion->sprint->name,
                    'description' => $suggestion->sprint->description,
                    'start_date' => $suggestion->sprint->start_date,
                    'end_date' => $suggestion->sprint->end_date,
                    'sprint_type' => $suggestion->sprint->sprint_type
                ] : null,
                'related_entity_name' => $suggestion->related_entity_name,
                'related_entity_type' => $suggestion->related_entity_type,
                'created_at' => $suggestion->created_at,
                'updated_at' => $suggestion->updated_at
            ]
        ]);
    }

    /**
     * Respond to a suggestion and optionally change its status.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function respond(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_response' => 'required|string|max:1000',
            'status' => 'required|in:pending,reviewed,implemented,rejected,in_progress'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $suggestion = Suggestion::findOrFail($id);
        $admin = Auth::user();

        try {
            $suggestion->update([
                'admin_response' => $request->admin_response,
                'status' => $request->status,
                'responded_by' => $admin->id,
                'responded_at' => now()
            ]);

            // Cargar relaciones para la respuesta
            $suggestion->load(['user', 'project', 'task', 'sprint', 'respondedBy']);

            return response()->json([
                'success' => true,
                'message' => 'Respuesta enviada exitosamente',
                'data' => [
                    'id' => $suggestion->id,
                    'title' => $suggestion->title,
                    'status' => $suggestion->status,
                    'status_label' => $suggestion->status_label,
                    'admin_response' => $suggestion->admin_response,
                    'responded_by' => $suggestion->respondedBy ? $suggestion->respondedBy->name : null,
                    'responded_at' => $suggestion->responded_at,
                    'client' => [
                        'id' => $suggestion->user->id,
                        'name' => $suggestion->user->name,
                        'email' => $suggestion->user->email
                    ],
                    'project' => [
                        'id' => $suggestion->project->id,
                        'name' => $suggestion->project->name
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al responder a la sugerencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change the status of a suggestion.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,reviewed,implemented,rejected,in_progress'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Estado inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        $suggestion = Suggestion::findOrFail($id);

        try {
            $suggestion->update([
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente',
                'data' => [
                    'id' => $suggestion->id,
                    'status' => $suggestion->status,
                    'status_label' => $suggestion->status_label
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get suggestions statistics for admin dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        $totalSuggestions = Suggestion::count();
        $pendingSuggestions = Suggestion::where('status', 'pending')->count();
        $reviewedSuggestions = Suggestion::where('status', 'reviewed')->count();
        $implementedSuggestions = Suggestion::where('status', 'implemented')->count();
        $rejectedSuggestions = Suggestion::where('status', 'rejected')->count();
        $inProgressSuggestions = Suggestion::where('status', 'in_progress')->count();

        $statistics = [
            'total' => $totalSuggestions,
            'pending' => $pendingSuggestions,
            'reviewed' => $reviewedSuggestions,
            'implemented' => $implementedSuggestions,
            'rejected' => $rejectedSuggestions,
            'in_progress' => $inProgressSuggestions,
            'response_rate' => $totalSuggestions > 0 ? round((($totalSuggestions - $pendingSuggestions) / $totalSuggestions) * 100, 2) : 0,
            'implementation_rate' => $totalSuggestions > 0 ? round(($implementedSuggestions / $totalSuggestions) * 100, 2) : 0
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * Get filters data for suggestions (projects, clients, statuses).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilters()
    {
        // Obtener proyectos únicos que tienen sugerencias
        $projects = \App\Models\Project::whereHas('suggestions')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Obtener clientes únicos que han hecho sugerencias
        $clients = \App\Models\User::whereHas('suggestions')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'client');
            })
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        // Estados disponibles
        $statuses = [
            ['value' => 'pending', 'label' => 'Pendiente'],
            ['value' => 'reviewed', 'label' => 'Revisado'],
            ['value' => 'implemented', 'label' => 'Implementado'],
            ['value' => 'rejected', 'label' => 'Rechazado'],
            ['value' => 'in_progress', 'label' => 'En Progreso']
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'projects' => $projects,
                'clients' => $clients,
                'statuses' => $statuses
            ]
        ]);
    }
}

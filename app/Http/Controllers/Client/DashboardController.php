<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\Task;
use App\Models\Sprint;
use Carbon\Carbon;

class DashboardController extends Controller
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
     * Show the client dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();
        
        // Obtener proyectos asignados al cliente
        $projects = $user->projects()->with(['sprints', 'users.roles'])->get();
        
        $dashboardData = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ],
            'projects' => [],
            'total_projects' => $projects->count(),
            'average_progress' => 0,
            'completed_tasks' => 0,
            'pending_suggestions' => 0
        ];

        $totalTasks = 0;
        $completedTasks = 0;

        foreach ($projects as $project) {
            // Calcular progreso del proyecto
            $projectTasks = $project->tasks()->count();
            $projectCompletedTasks = $project->tasks()->where('status', 'done')->count();
            $projectProgress = $projectTasks > 0 ? ($projectCompletedTasks / $projectTasks) * 100 : 0;

            // Obtener sprint actual
            $currentSprint = $project->sprints()
                ->where('start_date', '<=', Carbon::now())
                ->where('end_date', '>=', Carbon::now())
                ->first();

            // Obtener equipo del proyecto (solo developers y QAs)
            $teamMembers = $project->users()
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', ['developer', 'qa']);
                })
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'client');
                })
                ->with('roles')
                ->get()
                ->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                        'roles' => $member->roles->pluck('name')->toArray()
                    ];
                });

            $projectData = [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'status' => $project->status,
                'progress_percentage' => round($projectProgress, 2),
                'total_tasks' => $projectTasks,
                'completed_tasks' => $projectCompletedTasks,
                'pending_tasks' => $projectTasks - $projectCompletedTasks,
                'in_progress_tasks' => $project->tasks()->where('status', 'in progress')->count(),
                'current_sprint' => $currentSprint ? [
                    'id' => $currentSprint->id,
                    'name' => $currentSprint->name,
                    'start_date' => $currentSprint->start_date,
                    'end_date' => $currentSprint->end_date,
                    'progress_percentage' => $this->calculateSprintProgress($currentSprint)
                ] : null,
                'team' => $teamMembers,
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at
            ];

            $dashboardData['projects'][] = $projectData;
            
            $totalTasks += $projectTasks;
            $completedTasks += $projectCompletedTasks;
        }

        // Calcular progreso general
        $dashboardData['completed_tasks'] = $completedTasks;
        $dashboardData['average_progress'] = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;
        
        // Obtener sugerencias pendientes del cliente
        $pendingSuggestions = \App\Models\Suggestion::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $dashboardData['pending_suggestions'] = $pendingSuggestions;

        return response()->json([
            'success' => true,
            'data' => $dashboardData
        ]);
    }

    /**
     * Get project details for a specific project.
     *
     * @param int $projectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectDetails($projectId)
    {
        try {
            $user = Auth::user();
            
            // Log para debugging
            \Log::info('getProjectDetails called - Auth::check(): ' . (Auth::check() ? 'true' : 'false'));
            if ($user) {
                \Log::info('getProjectDetails called by user: ' . $user->email . ' (ID: ' . $user->id . ') for project: ' . $projectId);
            } else {
                \Log::warning('getProjectDetails called but no user is authenticated');
            }
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }
            
            // Verificar que el usuario tenga acceso al proyecto
            $project = $user->projects()
                ->with(['sprints.tasks', 'users.roles'])
                ->findOrFail($projectId);

        // Calcular estadísticas detalladas del proyecto
        $totalTasks = $project->tasks()->count();
        $completedTasks = $project->tasks()->where('status', 'done')->count();
        $inProgressTasks = $project->tasks()->where('status', 'in progress')->count();
        $pendingTasks = $project->tasks()->where('status', 'pending')->count();

        // Obtener sprints con progreso
        $sprints = $project->sprints()->with('tasks')->get()->map(function ($sprint) {
            $sprintTasks = $sprint->tasks()->count();
            $sprintCompletedTasks = $sprint->tasks()->where('status', 'done')->count();
            
            return [
                'id' => $sprint->id,
                'name' => $sprint->name,
                'start_date' => $sprint->start_date,
                'end_date' => $sprint->end_date,
                'status' => $sprint->status,
                'total_tasks' => $sprintTasks,
                'completed_tasks' => $sprintCompletedTasks,
                'progress' => $sprintTasks > 0 ? round(($sprintCompletedTasks / $sprintTasks) * 100, 2) : 0,
                'is_current' => Carbon::now()->between($sprint->start_date, $sprint->end_date)
            ];
        });

        // Obtener tareas recientes (últimas 10)
        $recentTasks = $project->tasks()
            ->with(['user', 'sprint'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'assigned_to' => $task->user ? $task->user->name : 'Unassigned',
                    'sprint' => $task->sprint ? $task->sprint->name : 'No Sprint',
                    'updated_at' => $task->updated_at
                ];
            });

        // Obtener equipo del proyecto con estadísticas detalladas
        $teamMembers = $project->users()
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'client');
            })
            ->with(['roles', 'tasks' => function ($query) use ($project) {
                $query->where('project_id', $project->id);
            }])
            ->get()
            ->map(function ($member) use ($project) {
                // Obtener tareas del miembro para este proyecto específico
                $memberTasks = $member->tasks()->where('project_id', $project->id)->get();
                $completedTasks = $memberTasks->where('status', 'done')->count();
                $inProgressTasks = $memberTasks->where('status', 'in progress')->count();
                $pendingTasks = $memberTasks->where('status', 'pending')->count();
                
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'roles' => $member->roles->pluck('name')->toArray(),
                    'statistics' => [
                        'total_tasks' => $memberTasks->count(),
                        'completed_tasks' => $completedTasks,
                        'in_progress_tasks' => $inProgressTasks,
                        'pending_tasks' => $pendingTasks,
                        'completion_rate' => $memberTasks->count() > 0 ? round(($completedTasks / $memberTasks->count()) * 100, 2) : 0
                    ],
                    'recent_tasks' => $memberTasks->take(5)->map(function ($task) {
                        return [
                            'id' => $task->id,
                            'name' => $task->name,
                            'status' => $task->status,
                            'priority' => $task->priority,
                            'updated_at' => $task->updated_at
                        ];
                    })
                ];
            });

        // Estadísticas por rol
        $roleStatistics = [];
        $developers = $teamMembers->filter(function ($member) {
            return in_array('developer', $member['roles']);
        });
        $qas = $teamMembers->filter(function ($member) {
            return in_array('qa', $member['roles']);
        });
        $teamLeaders = $teamMembers->filter(function ($member) {
            return in_array('team_leader', $member['roles']);
        });

        if ($developers->count() > 0) {
            $roleStatistics['developers'] = [
                'count' => $developers->count(),
                'total_tasks' => $developers->sum('statistics.total_tasks'),
                'completed_tasks' => $developers->sum('statistics.completed_tasks'),
                'completion_rate' => $developers->avg('statistics.completion_rate')
            ];
        }

        if ($qas->count() > 0) {
            $roleStatistics['qa'] = [
                'count' => $qas->count(),
                'total_tasks' => $qas->sum('statistics.total_tasks'),
                'completed_tasks' => $qas->sum('statistics.completed_tasks'),
                'completion_rate' => $qas->avg('statistics.completion_rate')
            ];
        }

        if ($teamLeaders->count() > 0) {
            $roleStatistics['team_leaders'] = [
                'count' => $teamLeaders->count(),
                'total_tasks' => $teamLeaders->sum('statistics.total_tasks'),
                'completed_tasks' => $teamLeaders->sum('statistics.completed_tasks'),
                'completion_rate' => $teamLeaders->avg('statistics.completion_rate')
            ];
        }

        $projectDetails = [
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'status' => $project->status,
            'progress' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0,
            'statistics' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'in_progress_tasks' => $inProgressTasks,
                'pending_tasks' => $pendingTasks
            ],
            'sprints' => $sprints,
            'recent_tasks' => $recentTasks,
            'team_members' => $teamMembers,
            'role_statistics' => $roleStatistics,
            'created_at' => $project->created_at,
            'updated_at' => $project->updated_at
        ];

        return response()->json([
            'success' => true,
            'data' => $projectDetails
        ]);
        
        } catch (\Exception $e) {
            \Log::error('Error in getProjectDetails: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'project_id' => $projectId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sprints for the authenticated client.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSprints()
    {
        $user = Auth::user();
        
        $sprints = Sprint::whereHas('project.users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })
        ->with(['project', 'tasks' => function ($query) {
            $query->where('task_type', '!=', 'bug'); // Excluir bugs
        }])
        ->orderBy('start_date', 'desc')
        ->get()
        ->map(function ($sprint) {
            $totalTasks = $sprint->tasks()->where('task_type', '!=', 'bug')->count();
            $completedTasks = $sprint->tasks()->where('task_type', '!=', 'bug')->where('status', 'done')->count();
            $inProgressTasks = $sprint->tasks()->where('task_type', '!=', 'bug')->where('status', 'in progress')->count();
            $pendingTasks = $sprint->tasks()->where('task_type', '!=', 'bug')->where('status', 'pending')->count();
            
            return [
                'id' => $sprint->id,
                'name' => $sprint->name,
                'description' => $sprint->description,
                'start_date' => $sprint->start_date,
                'end_date' => $sprint->end_date,
                'status' => $sprint->status,
                'project' => [
                    'id' => $sprint->project->id,
                    'name' => $sprint->project->name
                ],
                'statistics' => [
                    'total_tasks' => $totalTasks,
                    'completed_tasks' => $completedTasks,
                    'in_progress_tasks' => $inProgressTasks,
                    'pending_tasks' => $pendingTasks,
                    'progress' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0
                ],
                'is_current' => Carbon::now()->between($sprint->start_date, $sprint->end_date),
                'created_at' => $sprint->created_at,
                'updated_at' => $sprint->updated_at
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $sprints
        ]);
    }

    /**
     * Get tasks for the authenticated client.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTasks()
    {
        $user = Auth::user();
        
        // Obtener tareas de los proyectos del cliente, excluyendo bugs
        $tasks = Task::whereHas('project.users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })
        ->where('task_type', '!=', 'bug') // Excluir bugs
        ->with(['project', 'sprint', 'user'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($task) {
            return [
                'id' => $task->id,
                'name' => $task->name,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'estimated_hours' => $task->estimated_hours,
                'actual_hours' => $task->actual_hours,
                'estimated_finish' => $task->estimated_finish,
                'project' => [
                    'id' => $task->project->id,
                    'name' => $task->project->name
                ],
                'sprint' => $task->sprint ? [
                    'id' => $task->sprint->id,
                    'name' => $task->sprint->name
                ] : null,
                'assigned_to' => $task->user ? [
                    'id' => $task->user->id,
                    'name' => $task->user->name
                ] : null,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at
            ];
        });

        // Obtener proyectos del cliente para filtros
        $projects = $user->projects()->get()->map(function ($project) {
            return [
                'id' => $project->id,
                'name' => $project->name
            ];
        });

        // Obtener sprints de los proyectos del cliente para filtros
        $sprints = Sprint::whereHas('project.users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->get()->map(function ($sprint) {
            return [
                'id' => $sprint->id,
                'name' => $sprint->name
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'tasks' => $tasks,
                'projects' => $projects,
                'sprints' => $sprints
            ]
        ]);
    }

    /**
     * Get projects for the authenticated client.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjects()
    {
        try {
            $user = Auth::user();
            
            // Log para debugging
            \Log::info('getProjects called - Auth::check(): ' . (Auth::check() ? 'true' : 'false'));
            if ($user) {
                \Log::info('getProjects called by user: ' . $user->email . ' (ID: ' . $user->id . ')');
            } else {
                \Log::warning('getProjects called but no user is authenticated');
            }
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado - Auth::check(): ' . (Auth::check() ? 'true' : 'false')
                ], 401);
            }
            
            // Verificar que el usuario tenga rol de cliente
            $hasClientRole = $user->roles()->where('value', 'client')->exists();
            if (!$hasClientRole) {
                $userRoles = $user->roles->pluck('value')->implode(', ');
                \Log::warning('User ' . $user->email . ' does not have client role. Current roles: ' . $userRoles);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado. Se requiere rol de cliente. Usuario actual: ' . $user->email . ', Roles: ' . $userRoles
                ], 403);
            }
            
            // Obtener proyectos asignados al cliente
            $projects = $user->projects()->with(['sprints', 'users.roles'])->get();
            
            $projectsData = [];

            foreach ($projects as $project) {
                // Calcular progreso del proyecto
                $projectTasks = $project->tasks()->count();
                $projectCompletedTasks = $project->tasks()->where('status', 'done')->count();
                $projectProgress = $projectTasks > 0 ? ($projectCompletedTasks / $projectTasks) * 100 : 0;

                // Obtener sprint actual
                $currentSprint = $project->sprints()
                    ->where('start_date', '<=', Carbon::now())
                    ->where('end_date', '>=', Carbon::now())
                    ->first();

                // Obtener equipo del proyecto organizado por roles
                $teamMembers = $project->users()
                    ->whereDoesntHave('roles', function ($query) {
                        $query->where('name', 'client');
                    })
                    ->with('roles')
                    ->get();

                $developers = [];
                $qas = [];
                $teamLeaders = [];

                foreach ($teamMembers as $member) {
                    $memberData = [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                        'roles' => $member->roles->pluck('name')->toArray()
                    ];

                    $roles = $member->roles->pluck('name')->toArray();
                    
                    if (in_array('developer', $roles)) {
                        $developers[] = $memberData;
                    }
                    if (in_array('qa', $roles)) {
                        $qas[] = $memberData;
                    }
                    if (in_array('team_leader', $roles)) {
                        $teamLeaders[] = $memberData;
                    }
                }

                $team = [
                    'developers' => $developers,
                    'qas' => $qas,
                    'team_leaders' => $teamLeaders
                ];

                $projectData = [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'status' => $project->status,
                    'progress_percentage' => round($projectProgress, 2),
                    'total_tasks' => $projectTasks,
                    'completed_tasks' => $projectCompletedTasks,
                    'pending_tasks' => $projectTasks - $projectCompletedTasks,
                    'in_progress_tasks' => $project->tasks()->where('status', 'in progress')->count(),
                    'current_sprint' => $currentSprint ? [
                        'id' => $currentSprint->id,
                        'name' => $currentSprint->name,
                        'start_date' => $currentSprint->start_date,
                        'end_date' => $currentSprint->end_date,
                        'progress_percentage' => $this->calculateSprintProgress($currentSprint)
                    ] : null,
                    'team' => $team,
                    'created_at' => $project->created_at,
                    'updated_at' => $project->updated_at
                ];

                $projectsData[] = $projectData;
            }

            return response()->json([
                'success' => true,
                'data' => $projectsData
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getProjects: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate sprint progress.
     *
     * @param Sprint $sprint
     * @return float
     */
    private function calculateSprintProgress($sprint)
    {
        $totalTasks = $sprint->tasks()->count();
        $completedTasks = $sprint->tasks()->where('status', 'done')->count();
        
        return $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;
    }
}

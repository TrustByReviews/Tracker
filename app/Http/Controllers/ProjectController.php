<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(private EmailService $emailService)
    {
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

        $projectsQuery = null;
        $developers = User::whereHas('roles', function ($query) {
            $query->where('name', 'developer');
        })->get();

        if ($permissions === 'admin') {
            $projectsQuery = Project::with(['users', 'sprints.tasks', 'creator']);
        } elseif ($permissions === 'qa') {
            // QA puede ver proyectos a los que está asignado
            $projectsQuery = $authUser->projects()->with(['users', 'sprints.tasks', 'creator']);
        } elseif ($permissions === 'developer') {
            $projectsQuery = $authUser->projects()->with(['users', 'sprints.tasks', 'creator']);
        }

        // Aplicar filtros
        $filters = $request->only(['status', 'assigned_user_id', 'sort_by', 'sort_order', 'search']);

        // Filtro por estado
        if (!empty($filters['status'])) {
            $projectsQuery->where('status', $filters['status']);
        }

        // Filtro por usuario asignado
        if (!empty($filters['assigned_user_id'])) {
            $projectsQuery->whereHas('users', function ($query) use ($filters) {
                $query->where('users.id', $filters['assigned_user_id']);
            });
        }

        // Filtro de búsqueda por nombre (case-insensitive)
        if (!empty($filters['search'])) {
            $searchTerm = trim($filters['search']);
            $projectsQuery->where(function ($query) use ($searchTerm) {
                $query->where('name', 'ilike', '%' . $searchTerm . '%')
                      ->orWhere('description', 'ilike', '%' . $searchTerm . '%');
            });
        }

        // Aplicar ordenamiento en la consulta SQL cuando sea posible
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        // Ordenamiento que se puede hacer en SQL
        if (in_array($sortBy, ['created_at', 'updated_at', 'name', 'status'])) {
            $projectsQuery->orderBy($sortBy, $sortOrder);
        }

        $projects = $projectsQuery->get();

        // Agregar información adicional a cada proyecto
        $projects->each(function ($project) {
            $project['create_by'] = $project->creator ? $project->creator->name : 'Unknown';
            
            // Calcular estadísticas del proyecto
            $allTasks = $project->sprints->flatMap->tasks;
            $project['total_tasks'] = $allTasks->count();
            $project['completed_tasks'] = $allTasks->where('status', 'done')->count();
            $project['in_progress_tasks'] = $allTasks->where('status', 'in progress')->count();
            $project['pending_tasks'] = $allTasks->where('status', 'to do')->count();
            $project['completion_rate'] = $allTasks->count() > 0 
                ? round(($allTasks->where('status', 'done')->count() / $allTasks->count()) * 100, 2)
                : 0;
            $project['team_members_count'] = $project->users->count();
            $project['sprints_count'] = $project->sprints->count();
        });

        // Aplicar ordenamiento personalizado que requiere cálculos
        if (!in_array($sortBy, ['created_at', 'updated_at', 'name', 'status'])) {
            $projects = $projects->sortBy(function ($project) use ($sortBy) {
                switch ($sortBy) {
                    case 'completion_rate':
                        return $project['completion_rate'];
                    case 'total_tasks':
                        return $project['total_tasks'];
                    case 'completed_tasks':
                        return $project['completed_tasks'];
                    case 'team_members':
                        return $project['team_members_count'];
                    case 'sprints_count':
                        return $project['sprints_count'];
                    default:
                        return $project->created_at;
                }
            });

            // Aplicar orden
            if ($sortOrder === 'desc') {
                $projects = $projects->reverse();
            }
        }

        // Calculate project statistics
        $stats = [
            'total' => $projects->count(),
            'active' => $projects->where('status', 'active')->count(),
            'completed' => $projects->where('status', 'completed')->count(),
            'paused' => $projects->where('status', 'paused')->count(),
            'cancelled' => $projects->where('status', 'cancelled')->count(),
        ];

        return Inertia::render('Project/Index', [
            'projects' => $projects,
            'permissions' => $permissions,
            'developers' => $developers,
            'stats' => $stats,
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

        $project = Project::with(['sprints.tasks', 'users.roles'])->findOrFail($id);
        $developers = $project->users;
        
        // Obtener todos los usuarios disponibles para el modal de gestión
        $allUsers = User::with('roles')->get();

        return Inertia::render('Project/Show', [
            'project' => $project,
            'developers' => $allUsers,
            'permissions' => $permissions,
            'sprints' => $project->sprints,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'objectives' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'category' => 'nullable|in:web,mobile,backend,iot,other',
            'development_type' => 'nullable|in:new,maintenance,improvement',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date|after:planned_start_date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date|after:actual_start_date',
            'methodology' => 'nullable|in:scrum,kanban,waterfall,hybrid',
            
            // Technology and Architecture
            'technologies' => 'nullable|array',
            'technologies.*' => 'string',
            'programming_languages' => 'nullable|array',
            'programming_languages.*' => 'string',
            'frameworks' => 'nullable|array',
            'frameworks.*' => 'string',
            'database_type' => 'nullable|string|max:255',
            'architecture' => 'nullable|in:monolithic,microservices,serverless,hybrid',
            'external_integrations' => 'nullable|array',
            'external_integrations.*' => 'string',
            
            // Team and Stakeholders
            'project_owner' => 'nullable|string|max:255',
            'product_owner' => 'nullable|string|max:255',
            'stakeholders' => 'nullable|array',
            'stakeholders.*' => 'string',
            
            // Advanced Planning
            'milestones' => 'nullable|array',
            'milestones.*' => 'string',
            'estimated_velocity' => 'nullable|integer|min:1',
            'current_sprint' => 'nullable|string|max:255',
            
            // Budget and Resources
            'estimated_budget' => 'nullable|numeric|min:0',
            'used_budget' => 'nullable|numeric|min:0',
            'assigned_resources' => 'nullable|array',
            'assigned_resources.*' => 'string',
            
            // Tracking and Metrics
            'progress_percentage' => 'nullable|numeric|min:0|max:100',
            'identified_risks' => 'nullable|array',
            'identified_risks.*' => 'string',
            'open_issues' => 'nullable|integer|min:0',
            'documentation_url' => 'nullable|url',
            'repository_url' => 'nullable|url',
            'task_board_url' => 'nullable|url',
            
            'developers_ids' => 'nullable|array',
            'developers_ids.*' => 'exists:users,id',
            
            // Client user fields
            'client_name' => 'nullable|string|max:255',
            'client_email' => 'nullable|email|unique:users,email',
            'client_company' => 'nullable|string|max:255',
            'client_password' => 'nullable|string|min:8',
        ]);

        $project = Project::create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'objectives' => $validatedData['objectives'] ?? null,
            'priority' => $validatedData['priority'] ?? 'medium',
            'category' => $validatedData['category'] ?? 'web',
            'development_type' => $validatedData['development_type'] ?? 'new',
            'planned_start_date' => $validatedData['planned_start_date'] ?? null,
            'planned_end_date' => $validatedData['planned_end_date'] ?? null,
            'actual_start_date' => $validatedData['actual_start_date'] ?? null,
            'actual_end_date' => $validatedData['actual_end_date'] ?? null,
            'methodology' => $validatedData['methodology'] ?? 'scrum',
            
            // Technology and Architecture
            'technologies' => $validatedData['technologies'] ?? null,
            'programming_languages' => $validatedData['programming_languages'] ?? null,
            'frameworks' => $validatedData['frameworks'] ?? null,
            'database_type' => $validatedData['database_type'] ?? null,
            'architecture' => $validatedData['architecture'] ?? null,
            'external_integrations' => $validatedData['external_integrations'] ?? null,
            
            // Team and Stakeholders
            'project_owner' => $validatedData['project_owner'] ?? null,
            'product_owner' => $validatedData['product_owner'] ?? null,
            'stakeholders' => $validatedData['stakeholders'] ?? null,
            
            // Advanced Planning
            'milestones' => $validatedData['milestones'] ?? null,
            'estimated_velocity' => $validatedData['estimated_velocity'] ?? null,
            'current_sprint' => $validatedData['current_sprint'] ?? null,
            
            // Budget and Resources
            'estimated_budget' => $validatedData['estimated_budget'] ?? null,
            'used_budget' => $validatedData['used_budget'] ?? null,
            'assigned_resources' => $validatedData['assigned_resources'] ?? null,
            
            // Tracking and Metrics
            'progress_percentage' => $validatedData['progress_percentage'] ?? 0.00,
            'identified_risks' => $validatedData['identified_risks'] ?? null,
            'open_issues' => $validatedData['open_issues'] ?? 0,
            'documentation_url' => $validatedData['documentation_url'] ?? null,
            'repository_url' => $validatedData['repository_url'] ?? null,
            'task_board_url' => $validatedData['task_board_url'] ?? null,
            
            'created_by' => auth()->id(),
        ]);

        if (isset($validatedData['developers_ids'])) {
            $project->users()->attach($validatedData['developers_ids']);
            
            // Send email notifications to assigned users
            $assignedUsers = User::whereIn('id', $validatedData['developers_ids'])->get();
            foreach ($assignedUsers as $user) {
                $this->emailService->sendProjectAssignmentEmail($user, $project->name);
            }
        }

        // Create client user if provided
        if (!empty($validatedData['client_name']) && !empty($validatedData['client_email'])) {
            $this->createClientUser($validatedData, $project);
        }

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Project created successfully');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'objectives' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'category' => 'nullable|in:web,mobile,backend,iot,other',
            'development_type' => 'nullable|in:new,maintenance,improvement',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date|after:planned_start_date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date|after:actual_start_date',
            'methodology' => 'nullable|in:scrum,kanban,waterfall,hybrid',
            
            // Technology and Architecture
            'technologies' => 'nullable|array',
            'technologies.*' => 'string',
            'programming_languages' => 'nullable|array',
            'programming_languages.*' => 'string',
            'frameworks' => 'nullable|array',
            'frameworks.*' => 'string',
            'database_type' => 'nullable|string|max:255',
            'architecture' => 'nullable|in:monolithic,microservices,serverless,hybrid',
            'external_integrations' => 'nullable|array',
            'external_integrations.*' => 'string',
            
            // Team and Stakeholders
            'project_owner' => 'nullable|string|max:255',
            'product_owner' => 'nullable|string|max:255',
            'stakeholders' => 'nullable|array',
            'stakeholders.*' => 'string',
            
            // Advanced Planning
            'milestones' => 'nullable|array',
            'milestones.*' => 'string',
            'estimated_velocity' => 'nullable|integer|min:1',
            'current_sprint' => 'nullable|string|max:255',
            
            // Budget and Resources
            'estimated_budget' => 'nullable|numeric|min:0',
            'used_budget' => 'nullable|numeric|min:0',
            'assigned_resources' => 'nullable|array',
            'assigned_resources.*' => 'string',
            
            // Tracking and Metrics
            'progress_percentage' => 'nullable|numeric|min:0|max:100',
            'identified_risks' => 'nullable|array',
            'identified_risks.*' => 'string',
            'open_issues' => 'nullable|integer|min:0',
            'documentation_url' => 'nullable|url',
            'repository_url' => 'nullable|url',
            'task_board_url' => 'nullable|url',
            
            'developer_ids' => 'nullable|array',
            'developer_ids.*' => 'exists:users,id',
            'status' => 'required|string|max:255',
        ]);

        $project = Project::findOrFail($id);

        $project->update([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'objectives' => $validatedData['objectives'] ?? null,
            'priority' => $validatedData['priority'] ?? 'medium',
            'category' => $validatedData['category'] ?? 'web',
            'development_type' => $validatedData['development_type'] ?? 'new',
            'planned_start_date' => $validatedData['planned_start_date'] ?? null,
            'planned_end_date' => $validatedData['planned_end_date'] ?? null,
            'actual_start_date' => $validatedData['actual_start_date'] ?? null,
            'actual_end_date' => $validatedData['actual_end_date'] ?? null,
            'methodology' => $validatedData['methodology'] ?? 'scrum',
            
            // Technology and Architecture
            'technologies' => $validatedData['technologies'] ?? null,
            'programming_languages' => $validatedData['programming_languages'] ?? null,
            'frameworks' => $validatedData['frameworks'] ?? null,
            'database_type' => $validatedData['database_type'] ?? null,
            'architecture' => $validatedData['architecture'] ?? null,
            'external_integrations' => $validatedData['external_integrations'] ?? null,
            
            // Team and Stakeholders
            'project_owner' => $validatedData['project_owner'] ?? null,
            'product_owner' => $validatedData['product_owner'] ?? null,
            'stakeholders' => $validatedData['stakeholders'] ?? null,
            
            // Advanced Planning
            'milestones' => $validatedData['milestones'] ?? null,
            'estimated_velocity' => $validatedData['estimated_velocity'] ?? null,
            'current_sprint' => $validatedData['current_sprint'] ?? null,
            
            // Budget and Resources
            'estimated_budget' => $validatedData['estimated_budget'] ?? null,
            'used_budget' => $validatedData['used_budget'] ?? null,
            'assigned_resources' => $validatedData['assigned_resources'] ?? null,
            
            // Tracking and Metrics
            'progress_percentage' => $validatedData['progress_percentage'] ?? 0.00,
            'identified_risks' => $validatedData['identified_risks'] ?? null,
            'open_issues' => $validatedData['open_issues'] ?? 0,
            'documentation_url' => $validatedData['documentation_url'] ?? null,
            'repository_url' => $validatedData['repository_url'] ?? null,
            'task_board_url' => $validatedData['task_board_url'] ?? null,
            
            'status' => $validatedData['status'],
        ]);

        // Get current and new developer assignments
        $currentDevelopers = $project->users->pluck('id')->toArray();
        $newDevelopers = $validatedData['developer_ids'] ?? [];
        
        if (isset($validatedData['developer_ids'])) {
            $project->users()->sync($validatedData['developer_ids']);
            
            // Find newly assigned users
            $newlyAssigned = array_diff($newDevelopers, $currentDevelopers);
            if (!empty($newlyAssigned)) {
                $newlyAssignedUsers = User::whereIn('id', $newlyAssigned)->get();
                foreach ($newlyAssignedUsers as $user) {
                    $this->emailService->sendProjectAssignmentEmail($user, $project->name);
                }
            }
        }

        return redirect()->route('projects.show', $project->id);
    }

    public function destroy($id): JsonResponse
    {
        $project = Project::where('id', $id)->firstOrFail();
        $project->delete();

        return response()->json(['message' => 'Project deleted'], 200);
    }

    public function finish(Request $request, $id): RedirectResponse
    {
        $project = Project::findOrFail($id);
        
        // Verificar que el usuario sea admin
        $authUser = Auth::user();
        if (!$authUser || !$authUser->roles->where('name', 'admin')->count()) {
            abort(403, 'Only administrators can finish projects');
        }

        // Validar datos del formulario
        $validatedData = $request->validate([
            'finish_type' => 'required|in:normal,early',
            'termination_reason' => 'required_if:finish_type,early',
            'custom_reason' => 'required_if:termination_reason,other',
            'achievements' => 'nullable|string',
            'difficulties' => 'nullable|string',
            'lessons_learned' => 'nullable|string',
            'final_documentation' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        // Procesar archivos adjuntos
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('project-finish-docs', 'public');
                $attachments[] = $path;
            }
        }

        // Determinar el estado final del proyecto
        $finalStatus = $validatedData['finish_type'] === 'early' ? 'cancelled' : 'completed';

        // Actualizar el proyecto
        $project->update([
            'status' => $finalStatus,
            'actual_end_date' => now(),
            'achievements' => $validatedData['achievements'] ?? null,
            'difficulties' => $validatedData['difficulties'] ?? null,
            'lessons_learned' => $validatedData['lessons_learned'] ?? null,
            'final_documentation' => $validatedData['final_documentation'] ?? null,
            'termination_reason' => $validatedData['termination_reason'] ?? null,
            'custom_reason' => $validatedData['custom_reason'] ?? null,
            'final_attachments' => $attachments,
            'is_finished' => true,
            'finished_at' => now(),
            'finished_by' => $authUser->id,
        ]);

        // Si es cierre anticipado, marcar sprints y tareas como canceladas
        if ($validatedData['finish_type'] === 'early') {
            // Marcar sprints activos como cancelados
            $project->sprints()->where('status', '!=', 'completed')->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => 'Project early termination'
            ]);

            // Marcar tareas en progreso como canceladas
            $project->sprints->each(function ($sprint) {
                $sprint->tasks()->where('status', '!=', 'done')->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => 'Project early termination'
                ]);
            });

            // Marcar bugs críticos como no resueltos
            $project->sprints->each(function ($sprint) {
                $sprint->bugs()->where('priority', 'critical')->where('status', '!=', 'resolved')->update([
                    'status' => 'wont_fix',
                    'resolution_notes' => 'Project terminated early - marked as wont fix'
                ]);
            });
        }

        // Enviar notificación por email a stakeholders
        $this->notifyProjectFinish($project, $validatedData['finish_type']);

        return redirect()->back()->with('success', "Project {$finalStatus} successfully");
    }

    private function notifyProjectFinish(Project $project, string $finishType): void
    {
        $subject = $finishType === 'early' 
            ? "Project {$project->name} - Early Termination Notice"
            : "Project {$project->name} - Successfully Completed";

        $message = $finishType === 'early'
            ? "The project {$project->name} has been terminated early. Please review the project summary for details."
            : "The project {$project->name} has been successfully completed. Please review the final documentation.";

        // Enviar email a todos los usuarios del proyecto
        $project->users->each(function ($user) use ($subject, $message) {
            $this->emailService->sendEmail($user->email, $subject, $message);
        });

        // Enviar email al creador del proyecto si no está en la lista de usuarios
        if (!$project->users->contains($project->created_by)) {
            $creator = User::find($project->created_by);
            $creator?->email && $this->emailService->sendEmail($creator->email, $subject, $message);
        }
    }

    /**
     * Create a client user and assign them to the project
     */
    private function createClientUser(array $validatedData, Project $project): void
    {
        try {
            // Get the client role
            $clientRole = \App\Models\Role::where('name', 'client')->first();
            if (!$clientRole) {
                \Log::error('Client role not found when creating client user');
                return;
            }

            // Generate password if not provided
            $password = $validatedData['client_password'] ?? $this->generatePassword();

            // Create the client user
            $clientUser = User::create([
                'name' => $validatedData['client_name'],
                'email' => $validatedData['client_email'],
                'password' => \Hash::make($password),
                'company' => $validatedData['client_company'] ?? null,
                'email_verified_at' => now(),
            ]);

            // Assign client role
            $clientUser->roles()->attach($clientRole->id);

            // Assign client to the project
            $project->users()->attach($clientUser->id);

            // Send email notification to client
            $this->emailService->sendProjectAssignmentEmail($clientUser, $project->name);

            // Log the creation
            \Log::info("Client user created: {$clientUser->email} for project: {$project->name}");

        } catch (\Exception $e) {
            \Log::error("Failed to create client user: " . $e->getMessage());
        }
    }

    /**
     * Generate a secure random password
     */
    private function generatePassword(): string
    {
        $length = 12;
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $password;
    }

    public function updateUsers(Request $request, $id): RedirectResponse
    {
        $project = Project::findOrFail($id);
        
        // Verificar permisos
        $authUser = Auth::user();
        if (!$authUser || !$authUser->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'users_to_add' => 'array',
            'users_to_add.*' => 'exists:users,id',
            'users_to_remove' => 'array',
            'users_to_remove.*' => 'exists:users,id',
        ]);

        // Añadir usuarios
        if ($request->has('users_to_add') && !empty($request->users_to_add)) {
            $project->users()->attach($request->users_to_add);
        }

        // Remover usuarios
        if ($request->has('users_to_remove') && !empty($request->users_to_remove)) {
            $project->users()->detach($request->users_to_remove);
        }

        return redirect()->back()->with('success', 'Project users updated successfully');
    }

    /**
     * Get projects for reports API endpoint
     * 
     * Returns a list of projects that can be used in payment reports
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectsForReports()
    {
        // Permitir acceso sin autenticación para la página de pagos
        // La página de pagos ya tiene sus propios controles de acceso

        $projects = Project::with(['users', 'sprints.tasks'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'status' => $project->status,
                    'total_tasks' => $project->sprints->sum(function($sprint) {
                        return $sprint->tasks->count();
                    }),
                    'completed_tasks' => $project->sprints->sum(function($sprint) {
                        return $sprint->tasks->where('status', 'done')->count();
                    }),
                    'team_members' => $project->users->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'projects' => $projects
        ]);
    }

    /**
     * Get users by project for reports API endpoint
     * 
     * Returns a list of users that belong to a specific project, optionally filtered by role
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersByProject(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'role' => 'nullable|string|in:admin,team_leader,developer,qa'
        ]);

        $project = Project::findOrFail($request->project_id);
        
        $usersQuery = $project->users()->with(['roles']);

        // Filter by role if specified
        if ($request->has('role') && $request->role) {
            $usersQuery->whereHas('roles', function ($query) use ($request) {
                $query->where('name', $request->role);
            });
        }

        $users = $usersQuery->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'hour_value' => $user->hour_value,
                'roles' => $user->roles->pluck('name')->toArray(),
                'completed_tasks' => $user->tasks()->where('status', 'done')->count(),
                'total_tasks' => $user->tasks()->count(),
                'total_hours' => $user->tasks()->sum('total_time_seconds') / 3600,
                'total_earnings' => ($user->tasks()->sum('total_time_seconds') / 3600) * $user->hour_value,
            ];
        });

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }
}

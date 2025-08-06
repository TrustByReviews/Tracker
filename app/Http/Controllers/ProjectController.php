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

        // Filtro de búsqueda por nombre
        if (!empty($filters['search'])) {
            $projectsQuery->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $projects = $projectsQuery->get();

        // Agregar información adicional a cada proyecto
        $projects->each(function ($project) {
            $project['create_by'] = $project->creator->name;
            
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

        // Aplicar ordenamiento personalizado
        if (!empty($filters['sort_by'])) {
            $projects = $projects->sortBy(function ($project) use ($filters) {
                switch ($filters['sort_by']) {
                    case 'name':
                        return strtolower($project->name);
                    case 'status':
                        $statusOrder = ['active' => 1, 'in progress' => 2, 'paused' => 3, 'completed' => 4, 'cancelled' => 5];
                        return $statusOrder[$project->status] ?? 6;
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
                    case 'created_at':
                        return $project->created_at;
                    case 'updated_at':
                        return $project->updated_at;
                    default:
                        return $project->created_at;
                }
            });

            // Aplicar orden
            if ($filters['sort_order'] === 'desc') {
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

        $project = Project::with('sprints.tasks')->findOrFail($id);
        $developers = $project->users;

        return Inertia::render('Project/Show', [
            'project' => $project,
            'developers' => $developers,
            'permissions' => $permissions,
            'sprints' => $project->sprints,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'developers_ids' => 'nullable|array',
            'developers_ids.*' => 'exists:users,id',
        ]);


        $project = Project::create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'created_by' => auth()->id(),
        ]);

        if (isset($validatedData['developer_ids'])) {
            $project->users()->attach($validatedData['developer_ids']);
            
            // Send email notifications to assigned users
            $assignedUsers = User::whereIn('id', $validatedData['developer_ids'])->get();
            foreach ($assignedUsers as $user) {
                $this->emailService->sendProjectAssignmentEmail($user, $project->name);
            }
        }

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Project created successfully');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'developer_ids' => 'nullable|array',
            'developer_ids.*' => 'exists:users,id',
            'status' => 'required|string|max:255',
        ]);

        $project = Project::findOrFail($id);

        $project->update([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
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

}

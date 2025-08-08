<?php

namespace App\Http\Controllers;

use App\Models\Sprint;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Bug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class SprintController extends Controller
{
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

        $sprints = [];
        $projects = [];

        if ($permissions === 'admin') {
            $sprintsQuery = Sprint::with(['tasks', 'bugs', 'project']);
            $projects = Project::all();
        } elseif ($permissions === 'qa') {
            // QA puede ver sprints de proyectos a los que está asignado
            $sprintsQuery = Sprint::whereHas('project.users', function ($query) use ($authUser) {
                $query->where('users.id', $authUser->id);
            })->with(['tasks', 'bugs', 'project']);
            $projects = $authUser->projects;
        } elseif ($permissions === 'team_leader') {
            // Team Leader puede ver sprints de proyectos a los que está asignado
            $sprintsQuery = Sprint::whereHas('project.users', function ($query) use ($authUser) {
                $query->where('users.id', $authUser->id);
            })->with(['tasks', 'bugs', 'project', 'project.users']);
            $projects = $authUser->projects;
        } elseif ($permissions === 'developer') {
            $sprintsQuery = Sprint::whereHas('project.users', function ($query) use ($authUser) {
                $query->where('users.id', $authUser->id);
            })->with(['tasks', 'bugs', 'project']);
            $projects = $authUser->projects;
        }

        // Aplicar filtros
        $filters = $request->only(['project_id', 'sort_by', 'sort_order', 'status', 'item_type']);

        // Filtro por proyecto
        if (!empty($filters['project_id'])) {
            $sprintsQuery->where('project_id', $filters['project_id']);
        }

        // Filtro por tipo de elemento
        if (!empty($filters['item_type']) && $filters['item_type'] !== 'all') {
            if ($filters['item_type'] === 'tasks') {
                $sprintsQuery->whereHas('tasks');
            } elseif ($filters['item_type'] === 'bugs') {
                $sprintsQuery->whereHas('bugs');
            }
        }

        // Filtro por estado
        if (!empty($filters['status'])) {
            $today = now();
            switch ($filters['status']) {
                case 'active':
                    $sprintsQuery->where('start_date', '<=', $today)
                                ->where('end_date', '>=', $today);
                    break;
                case 'upcoming':
                    $sprintsQuery->where('start_date', '>', $today);
                    break;
                case 'completed':
                    $sprintsQuery->where('end_date', '<', $today);
                    break;
            }
        }



        $sprints = $sprintsQuery->get();

        // Aplicar ordenamiento personalizado
        if (!empty($filters['sort_by'])) {
            $sprints = $sprints->sortBy(function ($sprint) use ($filters) {
                switch ($filters['sort_by']) {
                    case 'task_count':
                        return $sprint->tasks->count() + $sprint->bugs->count();
                    case 'completed_tasks':
                        $completedTasks = $sprint->tasks->where('status', 'done')->count();
                        $completedBugs = $sprint->bugs->whereIn('status', ['resolved', 'verified', 'closed'])->count();
                        return $completedTasks + $completedBugs;
                    case 'pending_tasks':
                        $pendingTasks = $sprint->tasks->whereNotIn('status', ['done'])->count();
                        $pendingBugs = $sprint->bugs->whereNotIn('status', ['resolved', 'verified', 'closed'])->count();
                        return $pendingTasks + $pendingBugs;
                    case 'completion_rate':
                        $totalTasks = $sprint->tasks->count();
                        $completedTasks = $sprint->tasks->where('status', 'done')->count();
                        $totalBugs = $sprint->bugs->count();
                        $completedBugs = $sprint->bugs->whereIn('status', ['resolved', 'verified', 'closed'])->count();
                        $total = $totalTasks + $totalBugs;
                        return $total > 0 ? (($completedTasks + $completedBugs) / $total) * 100 : 0;
                    case 'days_to_end':
                        return now()->diffInDays($sprint->end_date, false);
                    case 'priority_score':
                        // Calcular prioridad basada en tareas y bugs pendientes vs días restantes
                        $pendingTasks = $sprint->tasks->whereNotIn('status', ['done'])->count();
                        $pendingBugs = $sprint->bugs->whereNotIn('status', ['resolved', 'verified', 'closed'])->count();
                        $pendingItems = $pendingTasks + $pendingBugs;
                        $daysToEnd = max(1, now()->diffInDays($sprint->end_date, false));
                        return $pendingItems / $daysToEnd;
                    case 'recent':
                        return $sprint->created_at;
                    default:
                        return $sprint->created_at;
                }
            });

            // Aplicar orden
            if ($filters['sort_order'] === 'desc') {
                $sprints = $sprints->reverse();
            }
        }

        return Inertia::render('Sprint/Index', [
            'sprints' => $sprints,
            'permissions' => $permissions,
            'projects' => $projects,
            'filters' => $filters,
        ]);
    }

    public function show($project_id, $sprint_id): Response
    {

        
        // Validar que project_id sea un UUID válido
        if (!$project_id || $project_id === 'NaN' || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $project_id)) {

            abort(400, 'Invalid project ID');
        }
        
        // Validar que sprint_id sea un UUID válido
        if (!$sprint_id || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $sprint_id)) {

            abort(400, 'Invalid sprint ID');
        }
        
        $authUser = Auth::user();
        
        if (!$authUser) {
            return redirect()->route('login');
        }
        
        $role = $authUser->roles;
        $permissions = 'developer'; // Default permission
        
        if ($role && $role->count() > 0) {
            $permissions = $role->first()->name;
        }
        
        $sprint = Sprint::with(['tasks.user', 'bugs.user'])->findOrFail($sprint_id);
        $tasks = $sprint->tasks;
        $bugs = $sprint->bugs;
        
        // Get developers from the project
        $project = \App\Models\Project::findOrFail($project_id);
        $developers = $project->users;

        return Inertia::render('Sprint/Show', [
            'sprint' => $sprint,
            'tasks' => $tasks,
            'bugs' => $bugs,
            'permissions' => $permissions,
            'project_id' => $project_id,
            'developers' => $developers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            Log::info('Sprint creation started', [
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'goal' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'project_id' => 'required|string|exists:projects,id',
            ]);

            Log::info('Sprint validation passed', [
                'validated_data' => $validatedData
            ]);

            $sprint = Sprint::create([
                'name' => $validatedData['name'],
                'goal' => $validatedData['goal'],
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'project_id' => $validatedData['project_id'],
            ]);

            Log::info('Sprint created successfully', [
                'sprint_id' => $sprint->id,
                'sprint_name' => $sprint->name,
                'project_id' => $sprint->project_id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('projects.show', $validatedData['project_id'])
                ->with('success', 'Sprint created successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Sprint validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Sprint creation failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create sprint. Please try again.']);
        }
    }

    public function update(Request $request, $project_id, $sprint_id): RedirectResponse
    {
        try {
            Log::info('Sprint update started', [
                'sprint_id' => $sprint_id,
                'project_id' => $project_id,
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'goal' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);

            Log::info('Sprint update validation passed', [
                'validated_data' => $validatedData
            ]);

            $sprint = Sprint::findOrFail($sprint_id);

            $sprint->update([
               'name' => $validatedData['name'],
               'goal' => $validatedData['goal'],
               'start_date' => $validatedData['start_date'],
               'end_date' => $validatedData['end_date'],
            ]);

            Log::info('Sprint updated successfully', [
                'sprint_id' => $sprint->id,
                'sprint_name' => $sprint->name,
                'project_id' => $project_id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('sprints.show', ['project' => $project_id, 'sprint' => $sprint->id])
                ->with('success', 'Sprint updated successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Sprint update validation failed', [
                'sprint_id' => $sprint_id,
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Sprint update failed', [
                'sprint_id' => $sprint_id,
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update sprint. Please try again.']);
        }
    }
    public function destroy($proyect_id, $sprint_id): JsonResponse
    {
        $sprint = Sprint::where('id', $sprint_id)->firstOrFail();
        $sprint->delete();

        return response()->json(['message' => 'Sprint deleted'], 200);
    }

    /**
     * Crear una nueva tarea en el sprint
     */
    public function createTask(Request $request, Sprint $sprint): JsonResponse
    {
        $authUser = Auth::user();
        
        if (!$authUser || !$authUser->hasPermission('create-sprint-tasks')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verificar que el TL pertenece al proyecto del sprint
        if (!$sprint->project->users->contains($authUser->id)) {
            return response()->json(['error' => 'You can only create tasks in sprints from your projects'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'story_points' => 'required|integer|min:1|max:13',
        ]);

        // Verificar que el usuario asignado es un desarrollador del proyecto
        if (!$sprint->project->users()->where('users.id', $request->assigned_to)->exists()) {
            return response()->json(['error' => 'The assigned user must be a member of the project'], 422);
        }

        $task = Task::create([
            'name' => $request->name,
            'description' => $request->description,
            'project_id' => $sprint->project_id,
            'sprint_id' => $sprint->id,
            'assigned_to' => $request->assigned_to,
            'created_by' => $authUser->id,
            'status' => 'pending',
            'priority' => $request->priority,
            'story_points' => $request->story_points,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'task' => $task->load(['user', 'project', 'sprint'])
        ]);
    }

    /**
     * Crear un nuevo bug en el sprint
     */
    public function createBug(Request $request, Sprint $sprint): JsonResponse
    {
        $authUser = Auth::user();
        
        if (!$authUser || !$authUser->hasPermission('create-sprint-bugs')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verificar que el TL pertenece al proyecto del sprint
        if (!$sprint->project->users->contains($authUser->id)) {
            return response()->json(['error' => 'You can only create bugs in sprints from your projects'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'severity' => 'required|in:low,medium,high,critical',
        ]);

        // Verificar que el usuario asignado es un desarrollador del proyecto
        if (!$sprint->project->users()->where('users.id', $request->assigned_to)->exists()) {
            return response()->json(['error' => 'The assigned user must be a member of the project'], 422);
        }

        $bug = Bug::create([
            'name' => $request->name,
            'description' => $request->description,
            'project_id' => $sprint->project_id,
            'sprint_id' => $sprint->id,
            'assigned_to' => $request->assigned_to,
            'created_by' => $authUser->id,
            'status' => 'pending',
            'priority' => $request->priority,
            'severity' => $request->severity,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bug created successfully',
            'bug' => $bug->load(['user', 'project', 'sprint'])
        ]);
    }
}

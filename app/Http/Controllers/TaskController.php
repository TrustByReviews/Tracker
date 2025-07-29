<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(): Response
    {
        $authUser = User::find(Auth::id());
        $role = $authUser->roles;
        $permissions = 'developer'; // Default permission

        if ($role->count() > 0) {
            $permissions = $role->first()->name;
        }

        $tasks = [];
        $projects = [];
        $sprints = [];

        if ($permissions === 'admin') {
            $tasks = Task::with(['user', 'sprint', 'project'])->orderBy('created_at', 'desc')->get();
            $projects = Project::orderBy('name')->get();
            $sprints = Sprint::with('project')->orderBy('start_date', 'desc')->get();
        } elseif ($permissions === 'developer') {
            $tasks = Task::whereHas('sprint.project.users', function ($query) use ($authUser) {
                $query->where('users.id', $authUser->id);
            })->with(['user', 'sprint', 'project'])->orderBy('created_at', 'desc')->get();
            $projects = $authUser->projects()->orderBy('name')->get();
            $sprints = Sprint::whereHas('project.users', function ($query) use ($authUser) {
                $query->where('users.id', $authUser->id);
            })->with('project')->orderBy('start_date', 'desc')->get();
        }



        return Inertia::render('Task/Index', [
            'tasks' => $tasks,
            'permissions' => $permissions,
            'projects' => $projects,
            'sprints' => $sprints,
        ]);
    }

    public function show($id): Response
    {
        $authUser = User::find(Auth::id());
        $role = $authUser->roles;
        $permissions = 'developer'; // Default permission

        if ($role->count() > 0) {
            $permissions = $role->first()->name;
        }

        $task = Task::with(['user', 'sprint', 'project'])->findOrFail($id);
        
        // Verificar permisos
        if ($permissions === 'developer') {
            // Verificar que el usuario tenga acceso a la tarea
            $hasAccess = $task->sprint && $task->sprint->project && 
                        $task->sprint->project->users()->where('users.id', $authUser->id)->exists();
            
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
                'priority' => 'required|string|in:low,medium,high',
                'category' => 'required|string|max:255',
                'story_points' => 'required|integer|min:1|max:255',
                'sprint_id' => 'required|string|exists:sprints,id',
                'project_id' => 'required|string|exists:projects,id',
                'estimated_hours' => 'required|integer|min:1|max:255',
                'assigned_user_id' => 'nullable|string|exists:users,id',
                'estimated_start' => 'nullable|date',
                'estimated_finish' => 'nullable|date|after_or_equal:estimated_start',
            ]);

            Log::info('Task validation passed', [
                'validated_data' => $validatedData
            ]);

            $task = Task::create([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'priority' => $validatedData['priority'],
                'category' => $validatedData['category'],
                'story_points' => $validatedData['story_points'],
                'sprint_id' => $validatedData['sprint_id'],
                'project_id' => $validatedData['project_id'],
                'estimated_hours' => $validatedData['estimated_hours'],
                'user_id' => $validatedData['assigned_user_id'] ?? null,
                'estimated_start' => $request->input('estimated_start'),
                'estimated_finish' => $request->input('estimated_finish'),
                'status' => 'to do', // Default status
            ]);

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
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create task. Please try again.']);
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
}

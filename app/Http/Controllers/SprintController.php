<?php

namespace App\Http\Controllers;

use App\Models\Sprint;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class SprintController extends Controller
{
    public function index(): Response
    {
        $authUser = User::find(Auth::id());
        $role = $authUser->roles;
        $permissions = 'developer'; // Default permission

        if ($role->count() > 0) {
            $permissions = $role->first()->name;
        }

        $sprints = [];
        $projects = [];

        if ($permissions === 'admin') {
            $sprints = Sprint::with(['tasks', 'project'])->get();
            $projects = Project::all();
        } elseif ($permissions === 'developer') {
            $sprints = Sprint::whereHas('project.users', function ($query) use ($authUser) {
                $query->where('users.id', $authUser->id);
            })->with(['tasks', 'project'])->get();
            $projects = $authUser->projects;
        }
        




        return Inertia::render('Sprint/Index', [
            'sprints' => $sprints,
            'permissions' => $permissions,
            'projects' => $projects,
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
        
        $authUser = User::find(Auth::id());
        $role = $authUser->roles;
        $permissions = 'developer'; // Default permission
        
        if ($role->count() > 0) {
            $permissions = $role->first()->name;
        }
        
        $sprint = Sprint::with(['tasks.user'])->findOrFail($sprint_id);
        $tasks = $sprint->tasks;
        
        // Get developers from the project
        $project = \App\Models\Project::findOrFail($project_id);
        $developers = $project->users;

        return Inertia::render('Sprint/Show', [
            'sprint' => $sprint,
            'tasks' => $tasks,
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
}

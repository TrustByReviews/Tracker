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

    public function index(): Response
    {
        $authUser = User::find(Auth::id());
        $role = $authUser->roles;

        $developers = User::whereHas('roles', function ($query) {
            $query->where('name', 'developer');
        })->get();

        $projects = [];
        $permissions = 'developer'; // Default permission

        if ($role->count() > 0) {
            $permissions = $role->first()->name;
            
            if ($permissions === 'admin'){
                $projects = Project::with(['users', 'sprints.tasks', 'creator'])->get();
            } elseif ($permissions === 'developer'){
                $projects = $authUser->projects()->with(['users', 'sprints.tasks', 'creator'])->get();
            }
        }

        foreach ($projects as $project) {
            $project['create_by'] = $project->creator->name;
        }

        // Calculate project statistics
        $stats = [
            'total' => $projects->count(),
            'active' => $projects->where('status', 'active')->count(),
            'completed' => $projects->where('status', 'completed')->count(),
            'paused' => $projects->where('status', 'paused')->count(),
        ];

        return Inertia::render('Project/Index', [
            'projects' => $projects,
            'permissions' => $permissions,
            'developers' => $developers,
            'stats' => $stats,
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

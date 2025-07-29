<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamLeaderController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        
        // Obtener proyectos donde el usuario es team leader
        $projects = Project::with(['users', 'sprints.tasks.user'])
            ->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        // Obtener tareas que necesitan revisión
        $tasksForReview = Task::with(['project', 'sprint', 'user'])
            ->where('status', 'ready for test')
            ->whereHas('project.users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        // Obtener tareas rechazadas
        $rejectedTasks = Task::with(['project', 'sprint', 'user', 'rejectedBy'])
            ->where('status', 'rejected')
            ->whereHas('project.users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        // Obtener estadísticas del equipo
        $teamStats = [
            'total_projects' => $projects->count(),
            'active_projects' => $projects->where('status', 'active')->count(),
            'completed_projects' => $projects->where('status', 'completed')->count(),
            'total_tasks' => $projects->sum(function ($project) {
                return $project->sprints->sum(function ($sprint) {
                    return $sprint->tasks->count();
                });
            }),
            'completed_tasks' => $projects->sum(function ($project) {
                return $project->sprints->sum(function ($sprint) {
                    return $sprint->tasks->where('status', 'done')->count();
                });
            }),
            'tasks_for_review' => $tasksForReview->count(),
            'rejected_tasks' => $rejectedTasks->count(),
            'team_members' => $projects->flatMap(function ($project) {
                return $project->users;
            })->unique('id')->count(),
        ];

        // Obtener miembros del equipo
        $teamMembers = User::with(['roles', 'tasks'])
            ->whereHas('projects', function ($query) use ($projects) {
                $query->whereIn('project_id', $projects->pluck('id'));
            })
            ->get()
            ->map(function ($member) {
                $member->total_tasks = $member->tasks->count();
                $member->completed_tasks = $member->tasks->where('status', 'done')->count();
                $member->rejected_tasks = $member->tasks->where('status', 'rejected')->count();
                $member->performance = $member->total_tasks > 0 
                    ? round(($member->completed_tasks / $member->total_tasks) * 100, 1)
                    : 0;
                return $member;
            });

        return Inertia::render('TeamLeader/Index', [
            'projects' => $projects,
            'tasksForReview' => $tasksForReview,
            'rejectedTasks' => $rejectedTasks,
            'teamStats' => $teamStats,
            'teamMembers' => $teamMembers,
            'user' => $user,
        ]);
    }

    public function projectDetails($projectId): Response
    {
        $user = auth()->user();
        
        $project = Project::with(['users', 'sprints.tasks.user'])
            ->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($projectId);

        // Organizar tareas por estado
        $taskColumns = [
            'to do' => collect(),
            'in progress' => collect(),
            'ready for test' => collect(),
            'in review' => collect(),
            'rejected' => collect(),
            'done' => collect(),
        ];

        foreach ($project->sprints as $sprint) {
            foreach ($sprint->tasks as $task) {
                $taskColumns[$task->status]->push($task);
            }
        }

        // Estadísticas del proyecto
        $projectStats = [
            'total_tasks' => $project->sprints->sum(function ($sprint) {
                return $sprint->tasks->count();
            }),
            'completed_tasks' => $project->sprints->sum(function ($sprint) {
                return $sprint->tasks->where('status', 'done')->count();
            }),
            'in_progress_tasks' => $project->sprints->sum(function ($sprint) {
                return $sprint->tasks->where('status', 'in progress')->count();
            }),
            'ready_for_test_tasks' => $project->sprints->sum(function ($sprint) {
                return $sprint->tasks->where('status', 'ready for test')->count();
            }),
            'rejected_tasks' => $project->sprints->sum(function ($sprint) {
                return $sprint->tasks->where('status', 'rejected')->count();
            }),
            'team_members' => $project->users->count(),
            'completion_percentage' => $project->sprints->sum(function ($sprint) {
                $totalTasks = $sprint->tasks->count();
                $completedTasks = $sprint->tasks->where('status', 'done')->count();
                return $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
            }) / max($project->sprints->count(), 1),
        ];

        return Inertia::render('TeamLeader/ProjectDetails', [
            'project' => $project,
            'taskColumns' => $taskColumns,
            'projectStats' => $projectStats,
        ]);
    }

    public function approveTask(Request $request, $taskId)
    {
        $task = Task::with('project')->findOrFail($taskId);
        $user = auth()->user();

        // Verificar que el usuario es team leader del proyecto
        if (!$task->project->users->contains($user->id) || 
            !$user->roles->contains('value', 'team_leader')) {
            abort(403, 'Only team leaders can approve tasks');
        }

        $task->update([
            'status' => 'done',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'task' => $task->load(['project', 'sprint', 'user', 'rejectedBy', 'approvedBy']),
        ]);
    }

    public function rejectTask(Request $request, $taskId)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $task = Task::with('project')->findOrFail($taskId);
        $user = auth()->user();

        // Verificar que el usuario es team leader del proyecto
        if (!$task->project->users->contains($user->id) || 
            !$user->roles->contains('value', 'team_leader')) {
            abort(403, 'Only team leaders can reject tasks');
        }

        $task->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
            'rejected_by' => $user->id,
            'rejected_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'task' => $task->load(['project', 'sprint', 'user', 'rejectedBy', 'approvedBy']),
        ]);
    }
} 
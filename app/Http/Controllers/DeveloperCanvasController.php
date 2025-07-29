<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeveloperCanvasController extends Controller
{
    public function index(Request $request): Response
    {
        $user = auth()->user();
        
        // Obtener proyectos asignados al desarrollador
        $projects = $user->projects()->with(['sprints.tasks.user', 'users'])->get();
        
        // Obtener todas las tareas del desarrollador organizadas por estado
        $tasks = Task::with(['project', 'sprint', 'user', 'rejectedBy', 'approvedBy'])
            ->where('user_id', $user->id)
            ->orWhereHas('project.users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        // Organizar tareas por estado
        $taskColumns = [
            'to do' => $tasks->where('status', 'to do'),
            'in progress' => $tasks->where('status', 'in progress'),
            'ready for test' => $tasks->where('status', 'ready for test'),
            'in review' => $tasks->where('status', 'in review'),
            'rejected' => $tasks->where('status', 'rejected'),
            'done' => $tasks->where('status', 'done'),
        ];

        // Estadísticas del desarrollador
        $stats = [
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $tasks->where('status', 'done')->count(),
            'in_progress_tasks' => $tasks->where('status', 'in progress')->count(),
            'ready_for_test_tasks' => $tasks->where('status', 'ready for test')->count(),
            'rejected_tasks' => $tasks->where('status', 'rejected')->count(),
            'total_projects' => $projects->count(),
            'total_hours_worked' => $tasks->sum('actual_hours'),
            'total_earnings' => $tasks->where('status', 'done')->sum(function ($task) use ($user) {
                return $task->actual_hours * $user->hour_value;
            }),
        ];

        return Inertia::render('Developer/Canvas', [
            'taskColumns' => $taskColumns,
            'projects' => $projects,
            'stats' => $stats,
            'user' => $user,
        ]);
    }

    public function updateTaskStatus(Request $request, $taskId)
    {
        $request->validate([
            'status' => 'required|in:to do,in progress,ready for test,in review,rejected,done',
        ]);

        $task = Task::findOrFail($taskId);
        $user = auth()->user();

        // Verificar que el usuario puede modificar esta tarea
        if ($task->user_id !== $user->id && !$task->project->users->contains($user->id)) {
            abort(403, 'Unauthorized');
        }

        $task->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'task' => $task->load(['project', 'sprint', 'user', 'rejectedBy', 'approvedBy']),
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
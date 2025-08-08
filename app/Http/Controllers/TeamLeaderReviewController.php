<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Bug;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class TeamLeaderReviewController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    /**
     * Obtener tareas listas para revisión del Team Leader
     */
    public function getTasksReadyForReview(Request $request)
    {
        $authUser = Auth::user();
        
        if (!$authUser || !$authUser->roles->contains('name', 'team_leader')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $tasks = Task::whereHas('sprint.project.users', function ($query) use ($authUser) {
            $query->where('users.id', $authUser->id);
        })
        ->where('qa_status', 'approved')
        ->whereNull('team_leader_reviewed_by')
        ->with(['user', 'sprint', 'project', 'qaReviewedBy'])
        ->orderBy('qa_reviewed_at', 'desc')
        ->get();

        // Si es una petición AJAX, devolver JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'tasks' => $tasks
            ]);
        }

        // Si es una petición normal, devolver vista Inertia
        return Inertia::render('TeamLeader/ReviewTasks', [
            'user' => $authUser,
            'tasks' => $tasks,
        ]);
    }

    /**
     * Obtener bugs listos para revisión del Team Leader
     */
    public function getBugsReadyForReview(Request $request)
    {
        $authUser = Auth::user();
        
        if (!$authUser || !$authUser->roles->contains('name', 'team_leader')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $bugs = Bug::whereHas('sprint.project.users', function ($query) use ($authUser) {
            $query->where('users.id', $authUser->id);
        })
        ->where('qa_status', 'approved')
        ->whereNull('team_leader_reviewed_by')
        ->with(['user', 'sprint', 'project', 'qaReviewedBy'])
        ->orderBy('qa_reviewed_at', 'desc')
        ->get();

        // Si es una petición AJAX, devolver JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'bugs' => $bugs
            ]);
        }

        // Si es una petición normal, devolver vista Inertia
        return Inertia::render('TeamLeader/ReviewBugs', [
            'user' => $authUser,
            'bugs' => $bugs,
        ]);
    }

    /**
     * Aprobar finalmente una tarea
     */
    public function approveTask(Request $request, Task $task): JsonResponse
    {
        $authUser = Auth::user();
        
        if (!$authUser || !$authUser->roles->contains('name', 'team_leader')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verificar que el Team Leader pertenece al proyecto de la tarea
        if (!$task->sprint->project->users->contains($authUser->id)) {
            return response()->json(['error' => 'You can only review tasks from your projects'], 403);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $task->finallyApproveByTeamLeader($authUser, $request->notes);
            
            return response()->json([
                'success' => true,
                'message' => 'Task approved successfully',
                'task' => $task->fresh()->load(['user', 'sprint', 'project', 'qaReviewedBy', 'teamLeaderReviewedBy'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to approve task: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Solicitar cambios en una tarea
     */
    public function requestTaskChanges(Request $request, Task $task): JsonResponse
    {
        $authUser = Auth::user();
        
        if (!$authUser || !$authUser->roles->contains('name', 'team_leader')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verificar que el Team Leader pertenece al proyecto de la tarea
        if (!$task->sprint->project->users->contains($authUser->id)) {
            return response()->json(['error' => 'You can only review tasks from your projects'], 403);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $task->requestChangesByTeamLeader($authUser, $request->notes);
            
            return response()->json([
                'success' => true,
                'message' => 'Changes requested successfully',
                'task' => $task->fresh()->load(['user', 'sprint', 'project', 'qaReviewedBy', 'teamLeaderReviewedBy'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to request changes: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Aprobar finalmente un bug
     */
    public function approveBug(Request $request, Bug $bug): JsonResponse
    {
        $authUser = Auth::user();
        
        if (!$authUser || !$authUser->roles->contains('name', 'team_leader')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verificar que el Team Leader pertenece al proyecto del bug
        if (!$bug->sprint->project->users->contains($authUser->id)) {
            return response()->json(['error' => 'You can only review bugs from your projects'], 403);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $bug->finallyApproveByTeamLeader($authUser, $request->notes);
            
            return response()->json([
                'success' => true,
                'message' => 'Bug approved successfully',
                'bug' => $bug->fresh()->load(['user', 'sprint', 'project', 'qaReviewedBy', 'teamLeaderReviewedBy'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to approve bug: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Solicitar cambios en un bug
     */
    public function requestBugChanges(Request $request, Bug $bug): JsonResponse
    {
        $authUser = Auth::user();
        
        if (!$authUser || !$authUser->roles->contains('name', 'team_leader')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verificar que el Team Leader pertenece al proyecto del bug
        if (!$bug->sprint->project->users->contains($authUser->id)) {
            return response()->json(['error' => 'You can only review bugs from your projects'], 403);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $bug->requestChangesByTeamLeader($authUser, $request->notes);
            
            return response()->json([
                'success' => true,
                'message' => 'Changes requested successfully',
                'bug' => $bug->fresh()->load(['user', 'sprint', 'project', 'qaReviewedBy', 'teamLeaderReviewedBy'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to request changes: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener estadísticas de revisión del Team Leader
     */
    public function getReviewStats(Request $request): JsonResponse
    {
        $authUser = Auth::user();
        
        if (!$authUser || !$authUser->roles->contains('name', 'team_leader')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $projectIds = $authUser->projects->pluck('id');

        $taskStats = [
            'ready_for_review' => Task::whereHas('sprint.project', function ($query) use ($projectIds) {
                $query->whereIn('id', $projectIds);
            })
            ->where('qa_status', 'approved')
            ->where('team_leader_final_approval', false)
            ->where('team_leader_requested_changes', false)
            ->count(),
            'approved' => Task::whereHas('sprint.project', function ($query) use ($projectIds) {
                $query->whereIn('id', $projectIds);
            })
            ->where('team_leader_final_approval', true)
            ->count(),
            'changes_requested' => Task::whereHas('sprint.project', function ($query) use ($projectIds) {
                $query->whereIn('id', $projectIds);
            })
            ->where('team_leader_requested_changes', true)
            ->count(),
        ];

        $bugStats = [
            'ready_for_review' => Bug::whereHas('sprint.project', function ($query) use ($projectIds) {
                $query->whereIn('id', $projectIds);
            })
            ->where('qa_status', 'approved')
            ->where('team_leader_final_approval', false)
            ->where('team_leader_requested_changes', false)
            ->count(),
            'approved' => Bug::whereHas('sprint.project', function ($query) use ($projectIds) {
                $query->whereIn('id', $projectIds);
            })
            ->where('team_leader_final_approval', true)
            ->count(),
            'changes_requested' => Bug::whereHas('sprint.project', function ($query) use ($projectIds) {
                $query->whereIn('id', $projectIds);
            })
            ->where('team_leader_requested_changes', true)
            ->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => [
                'tasks' => $taskStats,
                'bugs' => $bugStats,
            ]
        ]);
    }
}

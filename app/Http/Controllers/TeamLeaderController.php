<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Services\TaskApprovalService;
use App\Services\TaskAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class TeamLeaderController extends Controller
{
    public function __construct(
        private TaskApprovalService $taskApprovalService,
        private TaskAssignmentService $taskAssignmentService
    ) {
    }

    /**
     * Dashboard principal del team leader
     */
    public function dashboard(): Response
    {
        $teamLeader = Auth::user();
        
        // Verificar que el usuario es team leader
        if (!$teamLeader->roles()->where('name', 'team_leader')->exists()) {
            abort(403, 'Access denied. Team leader role required.');
        }
        
        $pendingTasks = $this->taskApprovalService->getPendingTasksForTeamLeader($teamLeader);
        $inProgressTasks = $this->taskApprovalService->getInProgressTasksForTeamLeader($teamLeader);
        $developersWithTasks = $this->taskApprovalService->getDevelopersWithActiveTasks($teamLeader);
        $approvalStats = $this->taskApprovalService->getApprovalStatsForTeamLeader($teamLeader);
        $developerTimeSummary = $this->taskApprovalService->getDeveloperTimeSummary($teamLeader);
        $recentlyCompleted = $this->taskApprovalService->getRecentlyCompletedTasks($teamLeader);
        
        return Inertia::render('TeamLeader/Dashboard', [
            'pendingTasks' => $pendingTasks,
            'inProgressTasks' => $inProgressTasks,
            'developersWithTasks' => $developersWithTasks,
            'approvalStats' => $approvalStats,
            'developerTimeSummary' => $developerTimeSummary,
            'recentlyCompleted' => $recentlyCompleted,
        ]);
    }

    /**
     * Vista de tareas pendientes de aprobación
     */
    public function pendingTasks(): Response
    {
        $teamLeader = Auth::user();
        
        if (!$teamLeader->roles()->where('name', 'team_leader')->exists()) {
            abort(403, 'Access denied. Team leader role required.');
        }
        
        $pendingTasks = $this->taskApprovalService->getPendingTasksForTeamLeader($teamLeader);
        
        return Inertia::render('TeamLeader/PendingTasks', [
            'pendingTasks' => $pendingTasks,
        ]);
    }

    /**
     * Vista de tareas en progreso
     */
    public function inProgressTasks(): Response
    {
        $teamLeader = Auth::user();
        
        if (!$teamLeader->roles()->where('name', 'team_leader')->exists()) {
            abort(403, 'Access denied. Team leader role required.');
        }
        
        $inProgressTasks = $this->taskApprovalService->getInProgressTasksForTeamLeader($teamLeader);
        
        return Inertia::render('TeamLeader/InProgressTasks', [
            'inProgressTasks' => $inProgressTasks,
        ]);
    }

    /**
     * Vista de desarrolladores y sus tareas
     */
    public function developers(): Response
    {
        $teamLeader = Auth::user();
        
        if (!$teamLeader->roles()->where('name', 'team_leader')->exists()) {
            abort(403, 'Access denied. Team leader role required.');
        }
        
        $developersWithTasks = $this->taskApprovalService->getDevelopersWithActiveTasks($teamLeader);
        $developerTimeSummary = $this->taskApprovalService->getDeveloperTimeSummary($teamLeader);
        
        return Inertia::render('TeamLeader/Developers', [
            'developersWithTasks' => $developersWithTasks,
            'developerTimeSummary' => $developerTimeSummary,
        ]);
    }

    /**
     * Aprobar una tarea
     */
    public function approveTask(Request $request, $taskId): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'notes' => 'nullable|string|max:500',
            ]);

            $task = Task::findOrFail($taskId);
            $teamLeader = Auth::user();

            $this->taskApprovalService->approveTask($task, $teamLeader, $validatedData['notes'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Tarea aprobada correctamente',
                'task' => $task->fresh(['user', 'sprint', 'project', 'reviewedBy'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al aprobar tarea', [
                'error' => $e->getMessage(),
                'task_id' => $taskId,
                'team_leader_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Rechazar una tarea
     */
    public function rejectTask(Request $request, $taskId): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'rejection_reason' => 'required|string|max:500',
            ]);

            $task = Task::findOrFail($taskId);
            $teamLeader = Auth::user();

            $this->taskApprovalService->rejectTask($task, $teamLeader, $validatedData['rejection_reason']);

            return response()->json([
                'success' => true,
                'message' => 'Tarea rechazada correctamente',
                'task' => $task->fresh(['user', 'sprint', 'project', 'reviewedBy'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al rechazar tarea', [
                'error' => $e->getMessage(),
                'task_id' => $taskId,
                'team_leader_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtener estadísticas de aprobación
     */
    public function getApprovalStats(): JsonResponse
    {
        try {
            $teamLeader = Auth::user();
            
            if (!$teamLeader->roles()->where('name', 'team_leader')->exists()) {
                throw new \Exception('Access denied. Team leader role required.');
            }
            
            $stats = $this->taskApprovalService->getApprovalStatsForTeamLeader($teamLeader);

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de aprobación', [
                'error' => $e->getMessage(),
                'team_leader_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    /**
     * Obtener resumen de tiempo por desarrollador
     */
    public function getDeveloperTimeSummary(): JsonResponse
    {
        try {
            $teamLeader = Auth::user();
            
            if (!$teamLeader->roles()->where('name', 'team_leader')->exists()) {
                throw new \Exception('Access denied. Team leader role required.');
            }
            
            $summary = $this->taskApprovalService->getDeveloperTimeSummary($teamLeader);

            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener resumen de tiempo por desarrollador', [
                'error' => $e->getMessage(),
                'team_leader_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen de tiempo'
            ], 500);
        }
    }

    /**
     * Obtener tareas recientemente completadas
     */
    public function getRecentlyCompleted(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'days' => 'nullable|integer|min:1|max:30',
            ]);

            $teamLeader = Auth::user();
            
            if (!$teamLeader->roles()->where('name', 'team_leader')->exists()) {
                throw new \Exception('Access denied. Team leader role required.');
            }
            
            $days = $validatedData['days'] ?? 7;
            $recentlyCompleted = $this->taskApprovalService->getRecentlyCompletedTasks($teamLeader, $days);

            return response()->json([
                'success' => true,
                'recentlyCompleted' => $recentlyCompleted
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener tareas recientemente completadas', [
                'error' => $e->getMessage(),
                'team_leader_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tareas completadas'
            ], 500);
        }
    }

    /**
     * Asignar tarea a desarrollador (desde vista de team leader)
     */
    public function assignTask(Request $request, $taskId): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'developer_id' => 'required|string|exists:users,id',
            ]);

            $task = Task::findOrFail($taskId);
            $developer = User::findOrFail($validatedData['developer_id']);
            $teamLeader = Auth::user();

            $this->taskAssignmentService->assignTaskByTeamLeader($task, $developer, $teamLeader);

            return response()->json([
                'success' => true,
                'message' => 'Tarea asignada correctamente',
                'task' => $task->fresh(['user', 'sprint', 'project', 'assignedBy'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error al asignar tarea desde team leader', [
                'error' => $e->getMessage(),
                'task_id' => $taskId,
                'developer_id' => $validatedData['developer_id'] ?? null,
                'team_leader_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
} 
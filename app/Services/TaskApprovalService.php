<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Task Approval Service Class
 * 
 * This service handles all task approval and rejection operations for team leaders.
 * It manages the workflow from task completion to QA testing, including notifications
 * and status management.
 * 
 * Features:
 * - Task approval and rejection by team leaders
 * - Automatic QA assignment after approval
 * - Notification system integration
 * - Approval statistics and reporting
 * - Developer time tracking and summaries
 * - Project permission validation
 * 
 * @package App\Services
 * @author System
 * @version 1.0
 */
class TaskApprovalService
{
    /**
     * Constructor
     * 
     * @param NotificationService $notificationService Service for sending notifications
     */
    public function __construct(private NotificationService $notificationService)
    {
    }

    /**
     * Get pending tasks for approval by a team leader
     * 
     * Returns all tasks that are pending approval and belong to projects
     * where the team leader has review permissions.
     * 
     * @param User $teamLeader The team leader to get pending tasks for
     * @return \Illuminate\Database\Eloquent\Collection Collection of pending tasks
     */
    public function getPendingTasksForTeamLeader(User $teamLeader): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('approval_status', 'pending')
            ->whereHas('sprint.project.users', function ($query) use ($teamLeader) {
                $query->where('users.id', $teamLeader->id);
            })
            ->with(['user', 'sprint', 'project', 'assignedBy', 'timeLogs'])
            ->orderBy('actual_finish', 'desc')
            ->get();
    }

    /**
     * Get all pending tasks across all projects
     * 
     * Returns all tasks that are pending approval regardless of project.
     * Typically used by administrators or for global reporting.
     * 
     * @return \Illuminate\Database\Eloquent\Collection Collection of all pending tasks
     */
    public function getAllPendingTasks(): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('approval_status', 'pending')
            ->with(['user', 'sprint', 'project', 'assignedBy', 'timeLogs'])
            ->orderBy('actual_finish', 'desc')
            ->get();
    }

    /**
     * Approve a task
     * 
     * Approves a completed task and marks it as ready for QA testing.
     * Includes validation of team leader permissions and sends notifications.
     * 
     * @param Task $task The task to approve
     * @param User $teamLeader The team leader approving the task
     * @param string|null $notes Optional notes about the approval
     * @return bool True if task was approved successfully
     * @throws \Exception If validation fails
     */
    public function approveTask(Task $task, User $teamLeader, ?string $notes = null): bool
    {
        try {
            DB::beginTransaction();
            
            // Verify team leader has permissions for the project
            if (!$this->canTeamLeaderReviewProject($teamLeader, $task->project)) {
                throw new \Exception('Team leader does not have permission to review tasks in this project');
            }
            
            // Verify task is pending approval
            if ($task->approval_status !== 'pending') {
                throw new \Exception('The task is not pending approval');
            }
            
            // Approve the task
            $task->update([
                'approval_status' => 'approved',
                'reviewed_by' => $teamLeader->id,
                'reviewed_at' => now(),
                'rejection_reason' => null
            ]);

            // Mark task as ready for QA
            $task->markAsReadyForQa();
            
            // Send notification to project QAs
            $this->notificationService->notifyTaskReadyForQa($task->fresh());
            
            Log::info('Task approved by team leader and marked for QA', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'developer_id' => $task->user_id,
                'developer_name' => $task->user->name,
                'team_leader_id' => $teamLeader->id,
                'team_leader_name' => $teamLeader->name,
                'notes' => $notes
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving task', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'team_leader_id' => $teamLeader->id
            ]);
            throw $e;
        }
    }

    /**
     * Reject a task
     * 
     * Rejects a completed task and returns it to the developer for revision.
     * Includes validation and sends notifications to the developer.
     * 
     * @param Task $task The task to reject
     * @param User $teamLeader The team leader rejecting the task
     * @param string $rejectionReason Reason for rejection
     * @return bool True if task was rejected successfully
     * @throws \Exception If validation fails
     */
    public function rejectTask(Task $task, User $teamLeader, string $rejectionReason): bool
    {
        try {
            DB::beginTransaction();
            
            // Verify team leader has permissions for the project
            if (!$this->canTeamLeaderReviewProject($teamLeader, $task->project)) {
                throw new \Exception('Team leader does not have permission to review tasks in this project');
            }
            
            // Verify task is pending approval
            if ($task->approval_status !== 'pending') {
                throw new \Exception('The task is not pending approval');
            }
            
            // Reject the task
            $task->update([
                'approval_status' => 'rejected',
                'reviewed_by' => $teamLeader->id,
                'reviewed_at' => now(),
                'rejection_reason' => $rejectionReason,
                'status' => 'in progress' // Return to in progress for revision
            ]);
            
            // Send notification to developer
            $this->notificationService->notifyTaskRejected($task->fresh(), $rejectionReason);
            
            Log::info('Task rejected by team leader', [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'developer_id' => $task->user_id,
                'developer_name' => $task->user->name,
                'team_leader_id' => $teamLeader->id,
                'team_leader_name' => $teamLeader->name,
                'rejection_reason' => $rejectionReason
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting task', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'team_leader_id' => $teamLeader->id
            ]);
            throw $e;
        }
    }

    /**
     * Get approval statistics for a team leader
     * 
     * Calculates the number of pending, approved, and rejected tasks for a given team leader.
     * 
     * @param User $teamLeader The team leader to get statistics for
     * @return array An array containing pending, approved, rejected, and total reviewed tasks
     */
    public function getApprovalStatsForTeamLeader(User $teamLeader): array
    {
        $pendingTasks = $this->getPendingTasksForTeamLeader($teamLeader);
        
        $approvedTasks = Task::where('approval_status', 'approved')
            ->where('reviewed_by', $teamLeader->id)
            ->whereHas('sprint.project.users', function ($query) use ($teamLeader) {
                $query->where('users.id', $teamLeader->id);
            })
            ->count();
            
        $rejectedTasks = Task::where('approval_status', 'rejected')
            ->where('reviewed_by', $teamLeader->id)
            ->whereHas('sprint.project.users', function ($query) use ($teamLeader) {
                $query->where('users.id', $teamLeader->id);
            })
            ->count();
        
        return [
            'pending' => $pendingTasks->count(),
            'approved' => $approvedTasks,
            'rejected' => $rejectedTasks,
            'total_reviewed' => $approvedTasks + $rejectedTasks
        ];
    }

    /**
     * Get in-progress tasks for a team leader
     * 
     * Returns all tasks that are currently in progress for a given team leader.
     * 
     * @param User $teamLeader The team leader to get in-progress tasks for
     * @return \Illuminate\Database\Eloquent\Collection Collection of in-progress tasks
     */
    public function getInProgressTasksForTeamLeader(User $teamLeader): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('status', 'in progress')
            ->whereHas('sprint.project.users', function ($query) use ($teamLeader) {
                $query->where('users.id', $teamLeader->id);
            })
            ->with(['user', 'sprint', 'project', 'assignedBy'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get developers and their active tasks for a team leader
     * 
     * Retrieves all developers who are assigned to projects where the team leader
     * has review permissions, along with their active tasks.
     * 
     * @param User $teamLeader The team leader to get developers for
     * @return \Illuminate\Database\Eloquent\Collection Collection of developers with active tasks
     */
    public function getDevelopersWithActiveTasks(User $teamLeader): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'developer');
        })
        ->whereHas('projects', function ($query) use ($teamLeader) {
            $query->whereHas('users', function ($subQuery) use ($teamLeader) {
                $subQuery->where('users.id', $teamLeader->id);
            });
        })
        ->with(['tasks' => function ($query) {
            $query->whereIn('status', ['in progress', 'to do'])
                  ->with(['sprint', 'project']);
        }])
        ->orderBy('name')
        ->get();
    }

    /**
     * Get recently completed tasks for a team leader
     * 
     * Retrieves all tasks that have been completed in the last specified number of days
     * for a given team leader.
     * 
     * @param User $teamLeader The team leader to get completed tasks for
     * @param int $days Number of days to look back for completed tasks
     * @return \Illuminate\Database\Eloquent\Collection Collection of completed tasks
     */
    public function getRecentlyCompletedTasks(User $teamLeader, int $days = 7): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('status', 'done')
            ->where('actual_finish', '>=', now()->subDays($days))
            ->whereHas('sprint.project.users', function ($query) use ($teamLeader) {
                $query->where('users.id', $teamLeader->id);
            })
            ->with(['user', 'sprint', 'project', 'assignedBy', 'reviewedBy'])
            ->orderBy('actual_finish', 'desc')
            ->get();
    }

    /**
     * Check if a team leader can review tasks in a project
     * 
     * Verifies if a given user (team leader) has the necessary role and is assigned
     * to the project for which tasks are being reviewed.
     * 
     * @param User $teamLeader The user to check
     * @param Project $project The project to check
     * @return bool True if the user has permission, false otherwise
     */
    public function canTeamLeaderReviewProject(User $teamLeader, Project $project): bool
    {
        // Verify user is a team leader
        if (!$teamLeader->roles()->where('name', 'team_leader')->exists()) {
            return false;
        }
        
        // Verify team leader is assigned to the project
        return $project->users()->where('users.id', $teamLeader->id)->exists();
    }

    /**
     * Get developer time summary for a team leader
     * 
     * Calculates the total time spent by developers on active and completed tasks.
     * 
     * @param User $teamLeader The team leader to get time summary for
     * @return array An array of developer time summaries
     */
    public function getDeveloperTimeSummary(User $teamLeader): array
    {
        $developers = $this->getDevelopersWithActiveTasks($teamLeader);
        $summary = [];
        
        foreach ($developers as $developer) {
            $totalTime = 0;
            $activeTasks = 0;
            $completedTasks = 0;
            
            foreach ($developer->tasks as $task) {
                if ($task->status === 'in progress') {
                    $activeTasks++;
                    $totalTime += $task->total_time_seconds;
                } elseif ($task->status === 'done') {
                    $completedTasks++;
                    $totalTime += $task->total_time_seconds;
                }
            }
            
            $summary[] = [
                'developer' => $developer,
                'total_time_seconds' => $totalTime,
                'formatted_time' => gmdate('H:i:s', $totalTime),
                'active_tasks' => $activeTasks,
                'completed_tasks' => $completedTasks
            ];
        }
        
        return $summary;
    }
} 
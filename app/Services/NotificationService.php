<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Task;
use App\Models\Bug;
use App\Models\User;

/**
 * Notification Service Class
 * 
 * This service handles all notification operations throughout the task management system.
 * It manages notifications for task and bug lifecycle events, QA testing, approvals,
 * and team communications.
 * 
 * Features:
 * - Task and bug lifecycle notifications
 * - QA testing workflow notifications
 * - Team leader approval notifications
 * - Developer completion notifications
 * - Notification management (read/unread status)
 * - Multi-user notification targeting
 * 
 * @package App\Services
 * @author System
 * @version 1.0
 */
class NotificationService
{
    /**
     * Create notification for task ready for QA testing
     * 
     * Notifies all QA users assigned to the project that a task is ready
     * for testing. This is triggered when a team leader approves a task.
     * 
     * @param Task $task The task that is ready for QA testing
     * @return void
     */
    public function notifyTaskReadyForQa(Task $task): void
    {
        // Get all QA users assigned to the project
        $qaUsers = User::whereHas('roles', function ($query) {
            $query->where('value', 'qa');
        })->whereHas('projects', function ($query) use ($task) {
            $query->where('id', $task->project_id);
        })->get();

        foreach ($qaUsers as $qaUser) {
            Notification::create([
                'user_id' => $qaUser->id,
                'type' => 'task_ready_for_qa',
                'title' => 'Task Ready for QA Testing',
                'message' => "Task '{$task->name}' is ready for QA testing in project '{$task->project->name}'",
                'data' => [
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'task_name' => $task->name,
                    'project_name' => $task->project->name,
                ],
            ]);
        }
    }

    /**
     * Create notification for task approved by QA
     * 
     * Notifies team leaders and the original developer when a task
     * has been approved by QA testing.
     * 
     * @param Task $task The task that was approved
     * @param User $qaUser The QA user who approved the task
     * @return void
     */
    public function notifyTaskApprovedByQa(Task $task, User $qaUser): void
    {
        // Notify project team leaders
        $teamLeaders = User::whereHas('roles', function ($query) {
            $query->where('value', 'team_leader');
        })->whereHas('projects', function ($query) use ($task) {
            $query->where('id', $task->project_id);
        })->get();

        foreach ($teamLeaders as $teamLeader) {
            Notification::create([
                'user_id' => $teamLeader->id,
                'type' => 'task_approved',
                'title' => 'Task Approved by QA',
                'message' => "Task '{$task->name}' has been approved by QA {$qaUser->name}",
                'data' => [
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'task_name' => $task->name,
                    'project_name' => $task->project->name,
                    'qa_user_id' => $qaUser->id,
                    'qa_user_name' => $qaUser->name,
                ],
            ]);
        }

        // Notify the original developer
        if ($task->user) {
            Notification::create([
                'user_id' => $task->user_id,
                'type' => 'task_approved',
                'title' => 'Your Task Approved by QA',
                'message' => "Your task '{$task->name}' has been approved by QA {$qaUser->name}",
                'data' => [
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'task_name' => $task->name,
                    'project_name' => $task->project->name,
                    'qa_user_id' => $qaUser->id,
                    'qa_user_name' => $qaUser->name,
                ],
            ]);
        }
    }

    /**
     * Create notification for task rejected by QA
     * 
     * Notifies the original developer when a task has been rejected
     * by QA testing, including the rejection reason.
     * 
     * @param Task $task The task that was rejected
     * @param User $qaUser The QA user who rejected the task
     * @param string $reason The reason for rejection
     * @return void
     */
    public function notifyTaskRejectedByQa(Task $task, User $qaUser, string $reason): void
    {
        // Notify the original developer
        if ($task->user) {
            Notification::create([
                'user_id' => $task->user_id,
                'type' => 'task_rejected',
                'title' => 'Task Rejected by QA',
                'message' => "Your task '{$task->name}' has been rejected by QA {$qaUser->name}. Reason: {$reason}",
                'data' => [
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'task_name' => $task->name,
                    'project_name' => $task->project->name,
                    'qa_user_id' => $qaUser->id,
                    'qa_user_name' => $qaUser->name,
                    'rejection_reason' => $reason,
                ],
            ]);
        }

        // Notify project team leaders
        $teamLeaders = User::whereHas('roles', function ($query) {
            $query->where('value', 'team_leader');
        })->whereHas('projects', function ($query) use ($task) {
            $query->where('id', $task->project_id);
        })->get();

        foreach ($teamLeaders as $teamLeader) {
            Notification::create([
                'user_id' => $teamLeader->id,
                'type' => 'task_rejected',
                'title' => 'Task Rejected by QA',
                'message' => "Task '{$task->name}' has been rejected by QA {$qaUser->name}. Reason: {$reason}",
                'data' => [
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'task_name' => $task->name,
                    'project_name' => $task->project->name,
                    'qa_user_id' => $qaUser->id,
                    'qa_user_name' => $qaUser->name,
                    'rejection_reason' => $reason,
                ],
            ]);
        }
    }

    /**
     * Create notification for bug ready for QA testing
     * 
     * Notifies all QA users assigned to the project that a bug is ready
     * for testing. This is triggered when a team leader approves a bug.
     * 
     * @param Bug $bug The bug that is ready for QA testing
     * @return void
     */
    public function notifyBugReadyForQa(Bug $bug): void
    {
        // Get all QA users assigned to the project
        $qaUsers = User::whereHas('roles', function ($query) {
            $query->where('value', 'qa');
        })->whereHas('projects', function ($query) use ($bug) {
            $query->where('id', $bug->project_id);
        })->get();

        foreach ($qaUsers as $qaUser) {
            Notification::create([
                'user_id' => $qaUser->id,
                'type' => 'bug_ready_for_qa',
                'title' => 'Bug Ready for QA Testing',
                'message' => "Bug '{$bug->title}' is ready for QA testing in project '{$bug->project->name}'",
                'data' => [
                    'bug_id' => $bug->id,
                    'project_id' => $bug->project_id,
                    'bug_title' => $bug->title,
                    'project_name' => $bug->project->name,
                ],
            ]);
        }
    }

    /**
     * Create notification for bug approved by QA
     * 
     * Notifies team leaders and the original developer when a bug
     * has been approved by QA testing.
     * 
     * @param Bug $bug The bug that was approved
     * @param User $qaUser The QA user who approved the bug
     * @return void
     */
    public function notifyBugApprovedByQa(Bug $bug, User $qaUser): void
    {
        // Notify project team leaders
        $teamLeaders = User::whereHas('roles', function ($query) {
            $query->where('value', 'team_leader');
        })->whereHas('projects', function ($query) use ($bug) {
            $query->where('id', $bug->project_id);
        })->get();

        foreach ($teamLeaders as $teamLeader) {
            Notification::create([
                'user_id' => $teamLeader->id,
                'type' => 'bug_approved',
                'title' => 'Bug Approved by QA',
                'message' => "Bug '{$bug->title}' has been approved by QA {$qaUser->name}",
                'data' => [
                    'bug_id' => $bug->id,
                    'project_id' => $bug->project_id,
                    'bug_title' => $bug->title,
                    'project_name' => $bug->project->name,
                    'qa_user_id' => $qaUser->id,
                    'qa_user_name' => $qaUser->name,
                ],
            ]);
        }

        // Notify the original developer
        if ($bug->user) {
            Notification::create([
                'user_id' => $bug->user_id,
                'type' => 'bug_approved',
                'title' => 'Your Bug Approved by QA',
                'message' => "Your bug '{$bug->title}' has been approved by QA {$qaUser->name}",
                'data' => [
                    'bug_id' => $bug->id,
                    'project_id' => $bug->project_id,
                    'bug_title' => $bug->title,
                    'project_name' => $bug->project->name,
                    'qa_user_id' => $qaUser->id,
                    'qa_user_name' => $qaUser->name,
                ],
            ]);
        }
    }

    /**
     * Create notification for bug rejected by QA
     * 
     * Notifies the original developer when a bug has been rejected
     * by QA testing, including the rejection reason.
     * 
     * @param Bug $bug The bug that was rejected
     * @param User $qaUser The QA user who rejected the bug
     * @param string $reason The reason for rejection
     * @return void
     */
    public function notifyBugRejectedByQa(Bug $bug, User $qaUser, string $reason): void
    {
        // Notify the original developer
        if ($bug->user) {
            Notification::create([
                'user_id' => $bug->user_id,
                'type' => 'bug_rejected',
                'title' => 'Bug Rejected by QA',
                'message' => "Your bug '{$bug->title}' has been rejected by QA {$qaUser->name}. Reason: {$reason}",
                'data' => [
                    'bug_id' => $bug->id,
                    'project_id' => $bug->project_id,
                    'bug_title' => $bug->title,
                    'project_name' => $bug->project->name,
                    'qa_user_id' => $qaUser->id,
                    'qa_user_name' => $qaUser->name,
                    'rejection_reason' => $reason,
                ],
            ]);
        }

        // Notify project team leaders
        $teamLeaders = User::whereHas('roles', function ($query) {
            $query->where('value', 'team_leader');
        })->whereHas('projects', function ($query) use ($bug) {
            $query->where('id', $bug->project_id);
        })->get();

        foreach ($teamLeaders as $teamLeader) {
            Notification::create([
                'user_id' => $teamLeader->id,
                'type' => 'bug_rejected',
                'title' => 'Bug Rejected by QA',
                'message' => "Bug '{$bug->title}' has been rejected by QA {$qaUser->name}. Reason: {$reason}",
                'data' => [
                    'bug_id' => $bug->id,
                    'project_id' => $bug->project_id,
                    'bug_title' => $bug->title,
                    'project_name' => $bug->project->name,
                    'qa_user_id' => $qaUser->id,
                    'qa_user_name' => $qaUser->name,
                    'rejection_reason' => $reason,
                ],
            ]);
        }
    }

    /**
     * Mark all notifications as read for a user
     * 
     * Updates the 'read' and 'read_at' status of all unread notifications
     * for a given user.
     * 
     * @param User $user The user whose notifications to mark as read
     * @return void
     */
    public function markAllAsRead(User $user): void
    {
        $user->notifications()
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Get unread notifications for a user
     * 
     * Retrieves a collection of unread notifications for a user,
     * ordered by creation date in descending order.
     * 
     * @param User $user The user to retrieve notifications for
     * @param int $limit The maximum number of notifications to return
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnreadNotifications(User $user, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $user->notifications()
            ->where('read', false)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread notification count for a user
     * 
     * Counts the number of unread notifications for a given user.
     * 
     * @param User $user The user to count notifications for
     * @return int
     */
    public function getUnreadCount(User $user): int
    {
        return $user->notifications()
            ->where('read', false)
            ->count();
    }

    /**
     * Notify the developer that their task was finally approved
     * 
     * Creates a notification for the original developer when a task
     * has been approved by the team leader.
     * 
     * @param Task $task The task that was finally approved
     * @param User $teamLeader The team leader who approved the task
     * @return void
     */
    public function notifyTaskFinalApproved(Task $task, User $teamLeader): void
    {
        if ($task->user) {
            Notification::create([
                'user_id' => $task->user_id,
                'type' => 'task_final_approved',
                'title' => 'Task Finally Approved',
                'message' => "Your task '{$task->name}' has been finally approved by Team Leader {$teamLeader->name}",
                'data' => [
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'task_name' => $task->name,
                    'project_name' => $task->project->name,
                    'team_leader_id' => $teamLeader->id,
                    'team_leader_name' => $teamLeader->name,
                ],
            ]);
        }
    }

    /**
     * Notify the developer that changes were requested for their task
     * 
     * Creates a notification for the original developer when changes
     * were requested by the team leader for their task.
     * 
     * @param Task $task The task for which changes were requested
     * @param User $teamLeader The team leader requesting changes
     * @param string $notes Notes provided by the team leader
     * @return void
     */
    public function notifyTaskChangesRequested(Task $task, User $teamLeader, string $notes): void
    {
        if ($task->user) {
            Notification::create([
                'user_id' => $task->user_id,
                'type' => 'task_changes_requested',
                'title' => 'Task Changes Requested',
                'message' => "Team Leader {$teamLeader->name} has requested changes to your task '{$task->name}'. Notes: {$notes}",
                'data' => [
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'task_name' => $task->name,
                    'project_name' => $task->project->name,
                    'team_leader_id' => $teamLeader->id,
                    'team_leader_name' => $teamLeader->name,
                    'notes' => $notes,
                ],
            ]);
        }
    }

    /**
     * Notify the developer that their bug was finally approved
     * 
     * Creates a notification for the original developer when a bug
     * has been approved by the team leader.
     * 
     * @param Bug $bug The bug that was finally approved
     * @param User $teamLeader The team leader who approved the bug
     * @return void
     */
    public function notifyBugFinalApproved(Bug $bug, User $teamLeader): void
    {
        if ($bug->user) {
            Notification::create([
                'user_id' => $bug->user_id,
                'type' => 'bug_final_approved',
                'title' => 'Bug Finally Approved',
                'message' => "Your bug '{$bug->title}' has been finally approved by Team Leader {$teamLeader->name}",
                'data' => [
                    'bug_id' => $bug->id,
                    'project_id' => $bug->project_id,
                    'bug_title' => $bug->title,
                    'project_name' => $bug->project->name,
                    'team_leader_id' => $teamLeader->id,
                    'team_leader_name' => $teamLeader->name,
                ],
            ]);
        }
    }

    /**
     * Notify the developer that changes were requested for their bug
     * 
     * Creates a notification for the original developer when changes
     * were requested by the team leader for their bug.
     * 
     * @param Bug $bug The bug for which changes were requested
     * @param User $teamLeader The team leader requesting changes
     * @param string $notes Notes provided by the team leader
     * @return void
     */
    public function notifyBugChangesRequested(Bug $bug, User $teamLeader, string $notes): void
    {
        if ($bug->user) {
            Notification::create([
                'user_id' => $bug->user_id,
                'type' => 'bug_changes_requested',
                'title' => 'Bug Changes Requested',
                'message' => "Team Leader {$teamLeader->name} has requested changes to your bug '{$bug->title}'. Notes: {$notes}",
                'data' => [
                    'bug_id' => $bug->id,
                    'project_id' => $bug->project_id,
                    'bug_title' => $bug->title,
                    'project_name' => $bug->project->name,
                    'team_leader_id' => $teamLeader->id,
                    'team_leader_name' => $teamLeader->name,
                    'notes' => $notes,
                ],
            ]);
        }
    }

    /**
     * Notify task ready for team leader review
     * 
     * Creates a notification for the team leader of the project
     * when a task is ready for their review after QA testing.
     * 
     * @param Task $task The task ready for review
     * @return void
     */
    public function notifyTaskReadyForTeamLeaderReview(Task $task): void
    {
        // Notify project team leaders
        $teamLeaders = User::whereHas('roles', function ($query) {
            $query->where('value', 'team_leader');
        })->whereHas('projects', function ($query) use ($task) {
            $query->where('id', $task->project_id);
        })->get();

        foreach ($teamLeaders as $teamLeader) {
            Notification::create([
                'user_id' => $teamLeader->id,
                'type' => 'task_ready_for_review',
                'title' => 'Task Ready for Team Leader Review',
                'message' => "Task '{$task->name}' has been tested by QA and is ready for your review",
                'data' => [
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'task_name' => $task->name,
                    'project_name' => $task->project->name,
                    'qa_user_id' => $task->qa_assigned_to,
                ],
            ]);
        }
    }

    /**
     * Notify bug ready for team leader review
     * 
     * Creates a notification for the team leader of the project
     * when a bug is ready for their review after QA testing.
     * 
     * @param Bug $bug The bug ready for review
     * @return void
     */
    public function notifyBugReadyForTeamLeaderReview(Bug $bug): void
    {
        // Notify project team leaders
        $teamLeaders = User::whereHas('roles', function ($query) {
            $query->where('value', 'team_leader');
        })->whereHas('projects', function ($query) use ($bug) {
            $query->where('id', $bug->sprint->project_id);
        })->get();

        foreach ($teamLeaders as $teamLeader) {
            Notification::create([
                'user_id' => $teamLeader->id,
                'type' => 'bug_ready_for_review',
                'title' => 'Bug Ready for Team Leader Review',
                'message' => "Bug '{$bug->title}' has been tested by QA and is ready for your review",
                'data' => [
                    'bug_id' => $bug->id,
                    'project_id' => $bug->sprint->project_id,
                    'bug_title' => $bug->title,
                    'project_name' => $bug->sprint->project->name,
                    'qa_user_id' => $bug->qa_assigned_to,
                ],
            ]);
        }
    }

    /**
     * Notify task finished by developer
     * 
     * Creates a notification for QA users assigned to the project
     * when a task has been finished by the developer.
     * 
     * @param Task $task The task finished by the developer
     * @return void
     */
    public function notifyTaskFinishedByDeveloper(Task $task): void
    {
        // Get all QA users assigned to the project
        $qaUsers = User::whereHas('roles', function ($query) {
            $query->where('value', 'qa');
        })->whereHas('projects', function ($query) use ($task) {
            $query->where('id', $task->project_id);
        })->get();

        foreach ($qaUsers as $qaUser) {
            Notification::create([
                'user_id' => $qaUser->id,
                'type' => 'task_finished_by_developer',
                'title' => 'Task Finished by Developer',
                'message' => "Task '{$task->name}' has been finished by developer and is ready for QA testing",
                'data' => [
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'task_name' => $task->name,
                    'project_name' => $task->project->name,
                    'developer_id' => $task->user_id,
                    'developer_name' => $task->user->name ?? 'Unknown',
                ],
            ]);
        }
    }

    /**
     * Notify bug finished by developer
     * 
     * Creates a notification for QA users assigned to the project
     * when a bug has been finished by the developer.
     * 
     * @param Bug $bug The bug finished by the developer
     * @return void
     */
    public function notifyBugFinishedByDeveloper(Bug $bug): void
    {
        // Get all QA users assigned to the project
        $qaUsers = User::whereHas('roles', function ($query) {
            $query->where('value', 'qa');
        })->whereHas('projects', function ($query) use ($bug) {
            $query->where('id', $bug->sprint->project_id);
        })->get();

        foreach ($qaUsers as $qaUser) {
            Notification::create([
                'user_id' => $qaUser->id,
                'type' => 'bug_finished_by_developer',
                'title' => 'Bug Finished by Developer',
                'message' => "Bug '{$bug->title}' has been finished by developer and is ready for QA testing",
                'data' => [
                    'bug_id' => $bug->id,
                    'project_id' => $bug->sprint->project_id,
                    'bug_title' => $bug->title,
                    'project_name' => $bug->sprint->project->name,
                    'developer_id' => $bug->user_id,
                    'developer_name' => $bug->user->name ?? 'Unknown',
                ],
            ]);
        }
    }

    /**
     * Notify team leader that a task was completed by QA
     * 
     * Creates a notification for the team leader of the project
     * when a task has been completed by QA.
     * 
     * @param Task $task The task completed by QA
     * @param User $qaUser The QA user who completed the task
     * @return void
     */
    public function notifyTaskCompletedByQa(Task $task, User $qaUser): void
    {
        // Notify project team leaders
        $teamLeaders = User::whereHas('roles', function ($query) {
            $query->where('value', 'team_leader');
        })->whereHas('projects', function ($query) use ($task) {
            $query->where('id', $task->project_id);
        })->get();

        foreach ($teamLeaders as $teamLeader) {
            Notification::create([
                'user_id' => $teamLeader->id,
                'type' => 'task_completed_by_qa',
                'title' => 'Task Completed by QA - Ready for Review',
                'message' => "Task '{$task->name}' has been completed by QA {$qaUser->name} and is ready for your review",
                'data' => [
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'task_name' => $task->name,
                    'project_name' => $task->project->name,
                    'qa_user_id' => $qaUser->id,
                    'qa_user_name' => $qaUser->name,
                ],
            ]);
        }
    }

    /**
     * Notify team leader that a bug was completed by QA
     * 
     * Creates a notification for the team leader of the project
     * when a bug has been completed by QA.
     * 
     * @param Bug $bug The bug completed by QA
     * @param User $qaUser The QA user who completed the bug
     * @return void
     */
    public function notifyBugCompletedByQa(Bug $bug, User $qaUser): void
    {
        // Notify project team leaders
        $teamLeaders = User::whereHas('roles', function ($query) {
            $query->where('value', 'team_leader');
        })->whereHas('projects', function ($query) use ($bug) {
            $query->where('id', $bug->sprint->project_id);
        })->get();

        foreach ($teamLeaders as $teamLeader) {
            Notification::create([
                'user_id' => $teamLeader->id,
                'type' => 'bug_completed_by_qa',
                'title' => 'Bug Completed by QA - Ready for Review',
                'message' => "Bug '{$bug->title}' has been completed by QA {$qaUser->name} and is ready for your review",
                'data' => [
                    'bug_id' => $bug->id,
                    'project_id' => $bug->sprint->project_id,
                    'bug_title' => $bug->title,
                    'project_name' => $bug->sprint->project->name,
                    'qa_user_id' => $qaUser->id,
                    'qa_user_name' => $qaUser->name,
                ],
            ]);
        }
    }

    /**
     * Notify the developer that their task was approved but changes were requested
     * 
     * Creates a notification for the original developer when a task
     * was approved by QA but changes were requested by the team leader.
     * 
     * @param Task $task The task approved with changes
     * @param User $teamLeader The team leader requesting changes
     * @param string $notes Notes provided by the team leader
     * @return void
     */
    public function notifyTaskApprovedWithChanges(Task $task, User $teamLeader, string $notes): void
    {
        if ($task->user) {
            Notification::create([
                'user_id' => $task->user_id,
                'type' => 'task_approved_with_changes',
                'title' => 'Task Approved with Changes Required',
                'message' => "Your task '{$task->name}' was approved by QA but Team Leader {$teamLeader->name} has requested changes. Notes: {$notes}",
                'data' => [
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'task_name' => $task->name,
                    'project_name' => $task->project->name,
                    'team_leader_id' => $teamLeader->id,
                    'team_leader_name' => $teamLeader->name,
                    'notes' => $notes,
                ],
            ]);
        }
    }

    /**
     * Notify the developer that their bug was approved but changes were requested
     * 
     * Creates a notification for the original developer when a bug
     * was approved by QA but changes were requested by the team leader.
     * 
     * @param Bug $bug The bug approved with changes
     * @param User $teamLeader The team leader requesting changes
     * @param string $notes Notes provided by the team leader
     * @return void
     */
    public function notifyBugApprovedWithChanges(Bug $bug, User $teamLeader, string $notes): void
    {
        if ($bug->user) {
            Notification::create([
                'user_id' => $bug->user_id,
                'type' => 'bug_approved_with_changes',
                'title' => 'Bug Approved with Changes Required',
                'message' => "Your bug '{$bug->title}' was approved by QA but Team Leader {$teamLeader->name} has requested changes. Notes: {$notes}",
                'data' => [
                    'bug_id' => $bug->id,
                    'project_id' => $bug->sprint->project_id,
                    'bug_title' => $bug->title,
                    'project_name' => $bug->sprint->project->name,
                    'team_leader_id' => $teamLeader->id,
                    'team_leader_name' => $teamLeader->name,
                    'notes' => $notes,
                ],
            ]);
        }
    }
} 
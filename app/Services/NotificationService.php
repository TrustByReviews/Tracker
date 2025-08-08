<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Task;
use App\Models\Bug;
use App\Models\User;

class NotificationService
{
    /**
     * Crear notificación de tarea lista para QA
     */
    public function notifyTaskReadyForQa(Task $task): void
    {
        // Obtener todos los QAs del proyecto
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
     * Crear notificación de tarea aprobada por QA
     */
    public function notifyTaskApprovedByQa(Task $task, User $qaUser): void
    {
        // Notificar al team leader del proyecto
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

        // Notificar al desarrollador original
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
     * Crear notificación de tarea rechazada por QA
     */
    public function notifyTaskRejectedByQa(Task $task, User $qaUser, string $reason): void
    {
        // Notificar al desarrollador original
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

        // Notificar al team leader del proyecto
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
     * Crear notificación de bug lista para QA
     */
    public function notifyBugReadyForQa(Bug $bug): void
    {
        // Obtener todos los QAs del proyecto
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
     * Crear notificación de bug aprobado por QA
     */
    public function notifyBugApprovedByQa(Bug $bug, User $qaUser): void
    {
        // Notificar al team leader del proyecto
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

        // Notificar al desarrollador original
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
     * Crear notificación de bug rechazado por QA
     */
    public function notifyBugRejectedByQa(Bug $bug, User $qaUser, string $reason): void
    {
        // Notificar al desarrollador original
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

        // Notificar al team leader del proyecto
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
     * Marcar todas las notificaciones de un usuario como leídas
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
     * Obtener notificaciones no leídas de un usuario
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
     * Obtener el conteo de notificaciones no leídas de un usuario
     */
    public function getUnreadCount(User $user): int
    {
        return $user->notifications()
            ->where('read', false)
            ->count();
    }

    /**
     * Notificar al desarrollador que su tarea fue aprobada completamente
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
     * Notificar al desarrollador que se solicitaron cambios en su tarea
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
     * Notificar al desarrollador que su bug fue aprobado completamente
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
     * Notificar al desarrollador que se solicitaron cambios en su bug
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
     * Notificar tarea lista para revisión del Team Leader
     */
    public function notifyTaskReadyForTeamLeaderReview(Task $task): void
    {
        // Notificar al team leader del proyecto
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
     * Notificar bug listo para revisión del Team Leader
     */
    public function notifyBugReadyForTeamLeaderReview(Bug $bug): void
    {
        // Notificar al team leader del proyecto
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
     * Notificar tarea finalizada por desarrollador
     */
    public function notifyTaskFinishedByDeveloper(Task $task): void
    {
        // Obtener todos los QAs del proyecto
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
     * Notificar bug finalizado por desarrollador
     */
    public function notifyBugFinishedByDeveloper(Bug $bug): void
    {
        // Obtener todos los QAs del proyecto
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
     * Notificar al Team Leader que una tarea fue completada por QA
     */
    public function notifyTaskCompletedByQa(Task $task, User $qaUser): void
    {
        // Notificar al team leader del proyecto
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
     * Notificar al Team Leader que un bug fue completado por QA
     */
    public function notifyBugCompletedByQa(Bug $bug, User $qaUser): void
    {
        // Notificar al team leader del proyecto
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
     * Notificar al desarrollador que su tarea fue aprobada pero se solicitaron cambios
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
     * Notificar al desarrollador que su bug fue aprobado pero se solicitaron cambios
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
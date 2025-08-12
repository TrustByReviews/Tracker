<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    protected $table = 'tasks';

    use softDeletes, HasUuid;

    protected $fillable = [
        'name',
        'description',
        'long_description',
        'attachments',
        'status',
        'priority',
        'category',
        'tags',
        'story_points',
        'sprint_id',
        'project_id',
        'user_id',
        'assigned_by',
        'assigned_at',
        'estimated_hours',
        'estimated_minutes',
        'actual_start',
        'actual_finish',
        'actual_hours',
        'actual_minutes',
        'total_time_seconds',
        'work_started_at',
        'is_working',
        'approval_status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'auto_close_at',
        'alert_count',
        'last_alert_at',
        'auto_paused',
        'auto_paused_at',
        'auto_pause_reason',
        'acceptance_criteria',
        'technical_notes',
        'complexity_level',
        'task_type',
        'qa_status',
        'qa_assigned_to',
        'qa_assigned_at',
        'qa_started_at',
        'qa_completed_at',
        'qa_notes',
        'qa_rejection_reason',
        'qa_reviewed_by',
        'qa_reviewed_at',
        'team_leader_final_approval',
        'team_leader_final_approval_at',
        'team_leader_final_notes',
        'team_leader_requested_changes',
        'team_leader_requested_changes_at',
        'team_leader_change_notes',
        'team_leader_reviewed_by',
        'qa_testing_started_at',
        'qa_testing_paused_at',
        'qa_testing_finished_at',
        // Re-work tracking fields
        'original_time_seconds',
        'retwork_time_seconds',
        'original_work_finished_at',
        'retwork_started_at',
        'has_been_returned',
        'return_count',
        'last_returned_by',
        'last_returned_at',
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'long_description' => 'string',
        'attachments' => 'array',
        'status' => 'string',
        'priority' => 'string',
        'category' => 'string',
        'tags' => 'string',
        'story_points' => 'integer',
        'sprint_id' => 'string',
        'project_id' => 'string',
        'user_id' => 'string',
        'assigned_by' => 'string',
        'assigned_at' => 'datetime',
        'estimated_hours' => 'integer',
        'estimated_minutes' => 'integer',
        'actual_start' => 'date',
        'actual_finish' => 'date',
        'actual_hours' => 'integer',
        'actual_minutes' => 'integer',
        'total_time_seconds' => 'integer',
        'work_started_at' => 'datetime',
        'is_working' => 'boolean',
        'approval_status' => 'string',
        'rejection_reason' => 'string',
        'reviewed_by' => 'string',
        'reviewed_at' => 'datetime',
        'auto_close_at' => 'datetime',
        'alert_count' => 'integer',
        'last_alert_at' => 'datetime',
        'auto_paused' => 'boolean',
        'auto_paused_at' => 'datetime',
        'auto_pause_reason' => 'string',
        'acceptance_criteria' => 'string',
        'technical_notes' => 'string',
        'complexity_level' => 'string',
        'task_type' => 'string',
        'qa_status' => 'string',
        'qa_assigned_to' => 'string',
        'qa_assigned_at' => 'datetime',
        'qa_started_at' => 'datetime',
        'qa_completed_at' => 'datetime',
        'qa_notes' => 'string',
        'qa_rejection_reason' => 'string',
        'qa_reviewed_by' => 'string',
        'qa_reviewed_at' => 'datetime',
        'team_leader_final_approval' => 'boolean',
        'team_leader_final_approval_at' => 'datetime',
        'team_leader_final_notes' => 'string',
        'team_leader_requested_changes' => 'boolean',
        'team_leader_requested_changes_at' => 'datetime',
        'team_leader_change_notes' => 'string',
        'team_leader_reviewed_by' => 'string',
        'qa_testing_started_at' => 'datetime',
        'qa_testing_paused_at' => 'datetime',
        'qa_testing_finished_at' => 'datetime',
        // Re-work tracking casts
        'original_time_seconds' => 'integer',
        'retwork_time_seconds' => 'integer',
        'original_work_finished_at' => 'datetime',
        'retwork_started_at' => 'datetime',
        'has_been_returned' => 'boolean',
        'return_count' => 'integer',
        'last_returned_by' => 'string',
        'last_returned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function qaAssignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'qa_assigned_to');
    }

    public function qaReviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'qa_reviewed_by');
    }

    public function teamLeaderReviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_leader_reviewed_by');
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TaskTimeLog::class);
    }

    // Helper methods for time tracking
    public function getFormattedTotalTime(): string
    {
        $hours = floor($this->total_time_seconds / 3600);
        $minutes = floor(($this->total_time_seconds % 3600) / 60);
        $seconds = $this->total_time_seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function getCurrentSessionTime(): int
    {
        if (!$this->is_working || !$this->work_started_at) {
            return 0;
        }
        
        return time() - $this->work_started_at->timestamp;
    }

    public function getFormattedCurrentTime(): string
    {
        $currentTime = $this->total_time_seconds + $this->getCurrentSessionTime();
        $hours = floor($currentTime / 3600);
        $minutes = floor(($currentTime % 3600) / 60);
        $seconds = $currentTime % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Verificar si la tarea tiene sesiones pausadas sin reanudar
     */
    public function hasPausedSessions(): bool
    {
        return $this->timeLogs()
            ->whereNotNull('paused_at')
            ->whereNull('resumed_at')
            ->whereNull('finished_at')
            ->exists();
    }

    /**
     * Calcular el tiempo total estimado en minutos
     */
    public function getTotalEstimatedMinutes(): int
    {
        return ($this->estimated_hours * 60) + ($this->estimated_minutes ?? 0);
    }

    /**
     * Calcular el tiempo total real en minutos
     */
    public function getTotalActualMinutes(): int
    {
        return ($this->actual_hours * 60) + ($this->actual_minutes ?? 0);
    }

    /**
     * Obtener el color del nivel de complejidad
     */
    public function getComplexityColor(): string
    {
        return match($this->complexity_level) {
            'low' => 'bg-green-100 text-green-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'high' => 'bg-orange-100 text-orange-800',
            'critical' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Obtener el icono del tipo de tarea
     */
    public function getTaskTypeIcon(): string
    {
        return match($this->task_type) {
            'feature' => 'plus-circle',
            'bug' => 'bug',
            'improvement' => 'arrow-up-circle',
            'documentation' => 'file-text',
            default => 'circle',
        };
    }

    /**
     * Obtener el color del tipo de tarea
     */
    public function getTaskTypeColor(): string
    {
        return match($this->task_type) {
            'feature' => 'bg-blue-100 text-blue-800',
            'bug' => 'bg-red-100 text-red-800',
            'improvement' => 'bg-green-100 text-green-800',
            'documentation' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Obtener el color del estado de QA
     */
    public function getQaStatusColor(): string
    {
        return match($this->qa_status) {
            'pending' => 'bg-gray-100 text-gray-800',
            'ready_for_test' => 'bg-blue-100 text-blue-800',
            'testing' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Verificar si la tarea está lista para QA
     */
    public function isReadyForQa(): bool
    {
        return $this->status === 'done' && $this->approval_status === 'approved' && $this->qa_status === 'pending';
    }

    /**
     * Verificar si la tarea está siendo testeada por QA
     */
    public function isBeingTestedByQa(): bool
    {
        return $this->qa_status === 'testing';
    }

    /**
     * Verificar si la tarea fue aprobada por QA
     */
    public function isApprovedByQa(): bool
    {
        return $this->qa_status === 'approved';
    }

    /**
     * Verificar si la tarea fue rechazada por QA
     */
    public function isRejectedByQa(): bool
    {
        return $this->qa_status === 'rejected';
    }

    /**
     * Marcar la tarea como lista para QA
     */
    public function markAsReadyForQa(): void
    {
        $this->update([
            'qa_status' => 'ready_for_test',
        ]);
    }

    /**
     * Asignar la tarea a un QA
     */
    public function assignToQa(User $qaUser): void
    {
        $this->update([
            'qa_assigned_to' => $qaUser->id,
            'qa_assigned_at' => now(),
            'qa_status' => 'testing',
            'qa_started_at' => now(),
        ]);
    }

    /**
     * Aprobar la tarea por QA
     */
    public function approveByQa(User $qaUser, ?string $notes = null): void
    {
        $this->update([
            'qa_status' => 'approved',
            'qa_completed_at' => now(),
            'qa_notes' => $notes,
            'qa_reviewed_by' => $qaUser->id,
            'qa_reviewed_at' => now(),
        ]);

        // Enviar notificaciones automáticamente
        $notificationService = app(NotificationService::class);
        $notificationService->notifyTaskApprovedByQa($this->fresh(), $qaUser);
        $notificationService->notifyTaskCompletedByQa($this->fresh(), $qaUser);
    }

    /**
     * Rechazar la tarea por QA
     */
    public function rejectByQa(User $qaUser, string $reason): void
    {
        $this->update([
            'qa_status' => 'rejected',
            'qa_completed_at' => now(),
            'qa_rejection_reason' => $reason,
            'qa_reviewed_by' => $qaUser->id,
            'qa_reviewed_at' => now(),
        ]);

        // Enviar notificaciones automáticamente
        $notificationService = app(NotificationService::class);
        $notificationService->notifyTaskRejectedByQa($this->fresh(), $qaUser, $reason);
    }

    /**
     * Verificar si la tarea está lista para revisión del Team Leader
     */
    public function isReadyForTeamLeaderReview(): bool
    {
        return $this->qa_status === 'approved' && !$this->team_leader_final_approval && !$this->team_leader_requested_changes;
    }

    /**
     * Verificar si la tarea fue aprobada finalmente por el Team Leader
     */
    public function isFinallyApprovedByTeamLeader(): bool
    {
        return $this->team_leader_final_approval === true;
    }

    /**
     * Verificar si el Team Leader solicitó cambios
     */
    public function hasTeamLeaderRequestedChanges(): bool
    {
        return $this->team_leader_requested_changes === true;
    }

    /**
     * Aprobar finalmente la tarea por el Team Leader
     */
    public function finallyApproveByTeamLeader(User $teamLeader, ?string $notes = null): void
    {
        $this->update([
            'team_leader_final_approval' => true,
            'team_leader_final_approval_at' => now(),
            'team_leader_final_notes' => $notes,
            'team_leader_reviewed_by' => $teamLeader->id,
        ]);

        // Enviar notificaciones automáticamente
        $notificationService = app(NotificationService::class);
        $notificationService->notifyTaskFinalApproved($this->fresh(), $teamLeader);
    }

    /**
     * Solicitar cambios por el Team Leader
     */
    public function requestChangesByTeamLeader(User $teamLeader, string $notes): void
    {
        $this->update([
            'team_leader_requested_changes' => true,
            'team_leader_requested_changes_at' => now(),
            'team_leader_change_notes' => $notes,
            'team_leader_reviewed_by' => $teamLeader->id,
        ]);

        // Enviar notificaciones automáticamente
        $notificationService = app(NotificationService::class);
        $notificationService->notifyTaskChangesRequested($this->fresh(), $teamLeader, $notes);
    }

    /**
     * Verificar si el desarrollador puede tener más tareas activas
     */
    public static function canDeveloperHaveMoreActiveTasks(User $developer): bool
    {
        $activeTasksCount = self::where('user_id', $developer->id)
            ->where(function ($query) {
                $query->where('is_working', true)
                    ->orWhere('status', 'in progress')
                    ->orWhere('qa_status', 'rejected')
                    ->orWhere('team_leader_requested_changes', true);
            })
            ->count();

        return $activeTasksCount < 3;
    }

    /**
     * Obtener el conteo de tareas activas de un desarrollador
     */
    public static function getActiveTasksCount(User $developer): int
    {
        return self::where('user_id', $developer->id)
            ->where(function ($query) {
                $query->where('is_working', true)
                    ->orWhere('status', 'in progress')
                    ->orWhere('qa_status', 'rejected')
                    ->orWhere('team_leader_requested_changes', true);
            })
            ->count();
    }
}

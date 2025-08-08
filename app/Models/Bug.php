<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bug extends Model
{
    protected $table = 'bugs';

    use SoftDeletes, HasUuid;

    protected $fillable = [
        'title',
        'description',
        'long_description',
        'status',
        'importance',
        'bug_type',
        'environment',
        'browser_info',
        'os_info',
        'steps_to_reproduce',
        'expected_behavior',
        'actual_behavior',
        'attachments',
        'sprint_id',
        'project_id',
        'user_id',
        'assigned_by',
        'assigned_at',
        'estimated_hours',
        'estimated_minutes',
        'actual_hours',
        'actual_minutes',
        'total_time_seconds',
        'work_started_at',
        'current_session_start',
        'is_working',
        'auto_close_at',
        'alert_count',
        'last_alert_at',
        'auto_paused',
        'auto_paused_at',
        'auto_pause_reason',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
        'verified_by',
        'verified_at',
        'tags',
        'priority_score',
        'reproducibility',
        'severity',
        'related_task_id',
        'qa_status',
        'qa_assigned_to',
        'qa_assigned_at',
        'qa_started_at',
        'qa_completed_at',
        'qa_notes',
        'qa_rejection_reason',
        'qa_reviewed_by',
        'qa_reviewed_at',
        'qa_testing_started_at',
        'qa_testing_paused_at',
        'qa_testing_finished_at',
        'team_leader_final_approval',
        'team_leader_final_approval_at',
        'team_leader_final_notes',
        'team_leader_requested_changes',
        'team_leader_requested_changes_at',
        'team_leader_change_notes',
        'team_leader_reviewed_by',
    ];

    protected $casts = [
        'title' => 'string',
        'description' => 'string',
        'long_description' => 'string',
        'status' => 'string',
        'importance' => 'string',
        'bug_type' => 'string',
        'environment' => 'string',
        'browser_info' => 'string',
        'os_info' => 'string',
        'steps_to_reproduce' => 'string',
        'expected_behavior' => 'string',
        'actual_behavior' => 'string',
        'attachments' => 'array',
        'sprint_id' => 'string',
        'project_id' => 'string',
        'user_id' => 'string',
        'assigned_by' => 'string',
        'assigned_at' => 'datetime',
        'estimated_hours' => 'integer',
        'estimated_minutes' => 'integer',
        'actual_hours' => 'integer',
        'actual_minutes' => 'integer',
        'total_time_seconds' => 'integer',
        'work_started_at' => 'datetime',
        'current_session_start' => 'datetime',
        'is_working' => 'boolean',
        'auto_close_at' => 'datetime',
        'alert_count' => 'integer',
        'last_alert_at' => 'datetime',
        'auto_paused' => 'boolean',
        'auto_paused_at' => 'datetime',
        'auto_pause_reason' => 'string',
        'resolution_notes' => 'string',
        'resolved_by' => 'string',
        'resolved_at' => 'datetime',
        'verified_by' => 'string',
        'verified_at' => 'datetime',
        'tags' => 'string',
        'priority_score' => 'integer',
        'reproducibility' => 'string',
        'severity' => 'string',
        'qa_status' => 'string',
        'qa_assigned_to' => 'string',
        'qa_assigned_at' => 'datetime',
        'qa_started_at' => 'datetime',
        'qa_completed_at' => 'datetime',
        'qa_notes' => 'string',
        'qa_rejection_reason' => 'string',
        'qa_reviewed_by' => 'string',
        'qa_reviewed_at' => 'datetime',
        'qa_testing_started_at' => 'datetime',
        'qa_testing_paused_at' => 'datetime',
        'qa_testing_finished_at' => 'datetime',
        'team_leader_final_approval' => 'boolean',
        'team_leader_final_approval_at' => 'datetime',
        'team_leader_final_notes' => 'string',
        'team_leader_requested_changes' => 'boolean',
        'team_leader_requested_changes_at' => 'datetime',
        'team_leader_change_notes' => 'string',
        'team_leader_reviewed_by' => 'string',
    ];

    // Estados posibles del bug
    const STATUS_NEW = 'new';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_IN_PROGRESS = 'in progress';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_VERIFIED = 'verified';
    const STATUS_CLOSED = 'closed';
    const STATUS_REOPENED = 'reopened';

    // Tipos de importancia
    const IMPORTANCE_LOW = 'low';
    const IMPORTANCE_MEDIUM = 'medium';
    const IMPORTANCE_HIGH = 'high';
    const IMPORTANCE_CRITICAL = 'critical';

    // Tipos de bug
    const TYPE_FRONTEND = 'frontend';
    const TYPE_BACKEND = 'backend';
    const TYPE_DATABASE = 'database';
    const TYPE_API = 'api';
    const TYPE_UI_UX = 'ui_ux';
    const TYPE_PERFORMANCE = 'performance';
    const TYPE_SECURITY = 'security';
    const TYPE_OTHER = 'other';

    // Reproductibilidad
    const REPRODUCIBILITY_ALWAYS = 'always';
    const REPRODUCIBILITY_SOMETIMES = 'sometimes';
    const REPRODUCIBILITY_RARELY = 'rarely';
    const REPRODUCIBILITY_UNABLE = 'unable';

    // Severidad
    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';

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

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(BugTimeLog::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BugComment::class);
    }

    public function relatedTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'related_task_id');
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
     * Verificar si el bug tiene sesiones pausadas sin reanudar
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
     * Obtener el color del estado
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_NEW => 'bg-blue-100 text-blue-800',
            self::STATUS_ASSIGNED => 'bg-yellow-100 text-yellow-800',
            self::STATUS_IN_PROGRESS => 'bg-orange-100 text-orange-800',
            self::STATUS_RESOLVED => 'bg-green-100 text-green-800',
            self::STATUS_VERIFIED => 'bg-purple-100 text-purple-800',
            self::STATUS_CLOSED => 'bg-gray-100 text-gray-800',
            self::STATUS_REOPENED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Obtener el color de la importancia
     */
    public function getImportanceColor(): string
    {
        return match($this->importance) {
            self::IMPORTANCE_LOW => 'bg-green-100 text-green-800',
            self::IMPORTANCE_MEDIUM => 'bg-yellow-100 text-yellow-800',
            self::IMPORTANCE_HIGH => 'bg-orange-100 text-orange-800',
            self::IMPORTANCE_CRITICAL => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Obtener el icono del tipo de bug
     */
    public function getBugTypeIcon(): string
    {
        return match($this->bug_type) {
            self::TYPE_FRONTEND => 'monitor',
            self::TYPE_BACKEND => 'server',
            self::TYPE_DATABASE => 'database',
            self::TYPE_API => 'link',
            self::TYPE_UI_UX => 'layout',
            self::TYPE_PERFORMANCE => 'zap',
            self::TYPE_SECURITY => 'shield',
            default => 'bug',
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
     * Verificar si el bug está lista para QA
     */
    public function isReadyForQa(): bool
    {
        return $this->status === self::STATUS_RESOLVED && $this->qa_status === 'pending';
    }

    /**
     * Verificar si el bug está siendo testeado por QA
     */
    public function isBeingTestedByQa(): bool
    {
        return $this->qa_status === 'testing';
    }

    /**
     * Verificar si el bug fue aprobado por QA
     */
    public function isApprovedByQa(): bool
    {
        return $this->qa_status === 'approved';
    }

    /**
     * Verificar si el bug fue rechazado por QA
     */
    public function isRejectedByQa(): bool
    {
        return $this->qa_status === 'rejected';
    }

    /**
     * Marcar el bug como lista para QA
     */
    public function markAsReadyForQa(): void
    {
        $this->update([
            'qa_status' => 'ready_for_test',
        ]);
    }

    /**
     * Asignar el bug a un QA
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
     * Aprobar el bug por QA
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
        $notificationService->notifyBugApprovedByQa($this->fresh(), $qaUser);
        $notificationService->notifyBugCompletedByQa($this->fresh(), $qaUser);
    }

    /**
     * Rechazar el bug por QA
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
        $notificationService->notifyBugRejectedByQa($this->fresh(), $qaUser, $reason);
    }

    /**
     * Verificar si el bug está lista para revisión del Team Leader
     */
    public function isReadyForTeamLeaderReview(): bool
    {
        return $this->qa_status === 'approved' && !$this->team_leader_final_approval && !$this->team_leader_requested_changes;
    }

    /**
     * Verificar si el bug fue aprobado finalmente por el Team Leader
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
     * Aprobar finalmente el bug por el Team Leader
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
        $notificationService->notifyBugFinalApproved($this->fresh(), $teamLeader);
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
        $notificationService->notifyBugChangesRequested($this->fresh(), $teamLeader, $notes);
    }

    /**
     * Verificar si el desarrollador puede tener más bugs activos
     */
    public static function canDeveloperHaveMoreActiveBugs(User $developer): bool
    {
        $activeBugsCount = self::where('user_id', $developer->id)
            ->where(function ($query) {
                $query->where('is_working', true)
                    ->orWhere('status', 'in progress')
                    ->orWhere('qa_status', 'rejected')
                    ->orWhere('team_leader_requested_changes', true);
            })
            ->count();

        return $activeBugsCount < 3;
    }

    /**
     * Obtener el conteo de bugs activos de un desarrollador
     */
    public static function getActiveBugsCount(User $developer): int
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

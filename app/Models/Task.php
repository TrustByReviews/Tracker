<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
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
}

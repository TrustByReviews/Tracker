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
        'status',
        'priority',
        'category',
        'story_points',
        'sprint_id',
        'project_id',
        'user_id',
        'assigned_by',
        'assigned_at',
        'estimated_hours',
        'actual_start',
        'actual_finish',
        'actual_hours',
        'total_time_seconds',
        'work_started_at',
        'is_working',
        'approval_status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'status' => 'string',
        'priority' => 'string',
        'category' => 'string',
        'story_points' => 'integer',
        'sprint_id' => 'string',
        'project_id' => 'string',
        'user_id' => 'string',
        'assigned_by' => 'string',
        'assigned_at' => 'datetime',
        'estimated_hours' => 'integer',
        'actual_start' => 'date',
        'actual_finish' => 'date',
        'actual_hours' => 'integer',
        'total_time_seconds' => 'integer',
        'work_started_at' => 'datetime',
        'is_working' => 'boolean',
        'approval_status' => 'string',
        'rejection_reason' => 'string',
        'reviewed_by' => 'string',
        'reviewed_at' => 'datetime',
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
}

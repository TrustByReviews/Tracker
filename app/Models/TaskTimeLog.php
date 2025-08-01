<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskTimeLog extends Model
{
    protected $table = 'task_time_logs';

    use SoftDeletes, HasUuid;

    protected $fillable = [
        'task_id',
        'user_id',
        'started_at',
        'paused_at',
        'resumed_at',
        'finished_at',
        'duration_seconds',
        'action',
        'notes',
    ];

    protected $casts = [
        'task_id' => 'string',
        'user_id' => 'string',
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'resumed_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration_seconds' => 'integer',
        'action' => 'string',
        'notes' => 'string',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function getFormattedDuration(): string
    {
        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function isActive(): bool
    {
        return $this->started_at && !$this->finished_at && !$this->paused_at;
    }

    public function isPaused(): bool
    {
        return $this->paused_at && !$this->resumed_at;
    }
} 
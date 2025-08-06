<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BugTimeLog extends Model
{
    protected $table = 'bug_time_logs';

    use HasUuid;

    protected $fillable = [
        'bug_id',
        'user_id',
        'started_at',
        'paused_at',
        'resumed_at',
        'finished_at',
        'duration_seconds',
        'notes',
        'auto_paused',
        'auto_pause_reason',
    ];

    protected $casts = [
        'bug_id' => 'string',
        'user_id' => 'string',
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'resumed_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration_seconds' => 'integer',
        'notes' => 'string',
        'auto_paused' => 'boolean',
        'auto_pause_reason' => 'string',
    ];

    public function bug(): BelongsTo
    {
        return $this->belongsTo(Bug::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener la duración formateada
     */
    public function getFormattedDuration(): string
    {
        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Verificar si la sesión está activa
     */
    public function isActive(): bool
    {
        return $this->started_at && !$this->finished_at && !$this->paused_at;
    }

    /**
     * Verificar si la sesión está pausada
     */
    public function isPaused(): bool
    {
        return $this->paused_at && !$this->resumed_at && !$this->finished_at;
    }

    /**
     * Verificar si la sesión está completada
     */
    public function isCompleted(): bool
    {
        return $this->started_at && $this->finished_at;
    }
} 
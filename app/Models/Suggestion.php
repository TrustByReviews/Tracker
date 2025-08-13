<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suggestion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'project_id',
        'task_id',
        'sprint_id',
        'title',
        'description',
        'status',
        'admin_response',
        'responded_by',
        'responded_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'responded_at' => 'datetime',
    ];

    /**
     * Get the user that created the suggestion.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project that the suggestion is for.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the admin user that responded to the suggestion.
     */
    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * Get the task that the suggestion is related to.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the sprint that the suggestion is related to.
     */
    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }

    /**
     * Scope a query to only include pending suggestions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include reviewed suggestions.
     */
    public function scopeReviewed($query)
    {
        return $query->where('status', 'reviewed');
    }

    /**
     * Scope a query to only include implemented suggestions.
     */
    public function scopeImplemented($query)
    {
        return $query->where('status', 'implemented');
    }

    /**
     * Scope a query to only include rejected suggestions.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Get the status label for display.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'reviewed' => 'Revisado',
            'implemented' => 'Implementado',
            'rejected' => 'Rechazado',
            'in_progress' => 'En Progreso',
            default => 'Desconocido'
        };
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'reviewed' => 'blue',
            'implemented' => 'green',
            'rejected' => 'red',
            'in_progress' => 'orange',
            default => 'gray'
        };
    }

    /**
     * Check if the suggestion has been responded to.
     */
    public function hasResponse(): bool
    {
        return !is_null($this->admin_response);
    }

    /**
     * Check if the suggestion is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the suggestion has been implemented.
     */
    public function isImplemented(): bool
    {
        return $this->status === 'implemented';
    }

    /**
     * Check if the suggestion has been rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if the suggestion is related to a specific task.
     */
    public function hasTask(): bool
    {
        return !is_null($this->task_id);
    }

    /**
     * Check if the suggestion is related to a specific sprint.
     */
    public function hasSprint(): bool
    {
        return !is_null($this->sprint_id);
    }

    /**
     * Get the related entity name (task or sprint).
     */
    public function getRelatedEntityNameAttribute(): ?string
    {
        if ($this->hasTask()) {
            return $this->task->name ?? 'Tarea';
        }
        
        if ($this->hasSprint()) {
            return $this->sprint->name ?? 'Sprint';
        }
        
        return null;
    }

    /**
     * Get the related entity type.
     */
    public function getRelatedEntityTypeAttribute(): ?string
    {
        if ($this->hasTask()) {
            return 'task';
        }
        
        if ($this->hasSprint()) {
            return 'sprint';
        }
        
        return null;
    }
}

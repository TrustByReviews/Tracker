<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Marcar la notificación como leída
     */
    public function markAsRead(): void
    {
        $this->update([
            'read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Marcar la notificación como no leída
     */
    public function markAsUnread(): void
    {
        $this->update([
            'read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Obtener el icono según el tipo de notificación
     */
    public function getIcon(): string
    {
        return match($this->type) {
            'task_ready_for_qa' => 'check-circle',
            'task_approved' => 'check-circle',
            'task_rejected' => 'x-circle',
            'bug_ready_for_qa' => 'bug',
            'bug_approved' => 'check-circle',
            'bug_rejected' => 'x-circle',
            default => 'bell',
        };
    }

    /**
     * Obtener el color según el tipo de notificación
     */
    public function getColor(): string
    {
        return match($this->type) {
            'task_ready_for_qa', 'bug_ready_for_qa' => 'text-blue-600',
            'task_approved', 'bug_approved' => 'text-green-600',
            'task_rejected', 'bug_rejected' => 'text-red-600',
            default => 'text-gray-600',
        };
    }
} 
<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPermission extends Model
{
    use HasUuid, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'permission_id',
        'type',
        'expires_at',
        'reason',
        'granted_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'type' => 'string',
    ];

    /**
     * Get the user that has this permission
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the permission
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * Get the user who granted this permission
     */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Check if the permission is expired
     */
    public function isExpired(): bool
    {
        if ($this->type === 'permanent') {
            return false;
        }

        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the permission is active
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Scope to get active permissions
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('type', 'permanent')
              ->orWhere(function ($subQ) {
                  $subQ->where('type', 'temporary')
                       ->where('expires_at', '>', now());
              });
        });
    }

    /**
     * Scope to get expired permissions
     */
    public function scopeExpired($query)
    {
        return $query->where('type', 'temporary')
                     ->where('expires_at', '<=', now());
    }
} 
<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PasswordResetOtp extends Model
{
    use HasUuid;

    protected $table = 'password_reset_otps';

    protected $fillable = [
        'email',
        'otp',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'email' => 'string',
        'otp' => 'string',
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Check if the OTP is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the OTP is valid (not expired and not used)
     */
    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->used;
    }

    /**
     * Mark the OTP as used
     */
    public function markAsUsed(): void
    {
        $this->update(['used' => true]);
    }
} 
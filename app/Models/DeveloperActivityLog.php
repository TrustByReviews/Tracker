<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DeveloperActivityLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'task_id',
        'activity_type',
        'activity_time_colombia',
        'activity_time_utc',
        'session_duration_minutes',
        'time_period',
        'metadata'
    ];

    protected $casts = [
        'activity_time_colombia' => 'datetime',
        'activity_time_utc' => 'datetime',
        'metadata' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get time period based on hour in Colombia timezone
     */
    public static function getTimePeriod($hour): string
    {
        if ($hour >= 6 && $hour < 12) {
            return 'morning';
        } elseif ($hour >= 12 && $hour < 18) {
            return 'afternoon';
        } elseif ($hour >= 18 && $hour < 22) {
            return 'evening';
        } else {
            return 'night';
        }
    }

    /**
     * Convert Colombia time to Italy time
     */
    public function getActivityTimeItalyAttribute(): Carbon
    {
        return $this->activity_time_colombia->copy()->setTimezone('Europe/Rome');
    }

    /**
     * Get formatted time for display
     */
    public function getFormattedTimeColombiaAttribute(): string
    {
        return $this->activity_time_colombia->format('H:i');
    }

    public function getFormattedTimeItalyAttribute(): string
    {
        return $this->getActivityTimeItalyAttribute()->format('H:i');
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->activity_time_colombia->format('Y-m-d');
    }
}

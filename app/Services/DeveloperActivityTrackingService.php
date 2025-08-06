<?php

namespace App\Services;

use App\Models\DeveloperActivityLog;
use App\Models\User;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeveloperActivityTrackingService
{
    /**
     * Log developer activity
     */
    public function logActivity(
        User $user,
        string $activityType,
        ?Task $task = null,
        array $metadata = []
    ): DeveloperActivityLog {
        $now = Carbon::now();
        $colombiaTime = $now->copy()->setTimezone('America/Bogota');
        
        $timePeriod = DeveloperActivityLog::getTimePeriod($colombiaTime->hour);
        
        return DeveloperActivityLog::create([
            'user_id' => $user->id,
            'task_id' => $task?->id,
            'activity_type' => $activityType,
            'activity_time_colombia' => $colombiaTime,
            'activity_time_utc' => $now,
            'time_period' => $timePeriod,
            'metadata' => array_merge($metadata, [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'session_id' => session()->getId()
            ])
        ]);
    }

    /**
     * Get developer activity statistics
     */
    public function getDeveloperStats(
        User $developer,
        Carbon $startDate = null,
        Carbon $endDate = null
    ): array {
        $startDate = $startDate ?? Carbon::now()->subDays(30);
        $endDate = $endDate ?? Carbon::now();

        // Total sessions
        $totalSessions = DeveloperActivityLog::where('user_id', $developer->id)
            ->whereBetween('activity_time_colombia', [$startDate, $endDate])
            ->where('activity_type', 'login')
            ->count();

        // Average session duration
        $avgSessionDuration = DeveloperActivityLog::where('user_id', $developer->id)
            ->whereBetween('activity_time_colombia', [$startDate, $endDate])
            ->whereNotNull('session_duration_minutes')
            ->avg('session_duration_minutes');

        // Activity by time period
        $activityByPeriod = DeveloperActivityLog::where('user_id', $developer->id)
            ->whereBetween('activity_time_colombia', [$startDate, $endDate])
            ->selectRaw('time_period, COUNT(*) as count')
            ->groupBy('time_period')
            ->get()
            ->pluck('count', 'time_period')
            ->toArray();

        // Most active hours
        $hourlyActivity = DeveloperActivityLog::where('user_id', $developer->id)
            ->whereBetween('activity_time_colombia', [$startDate, $endDate])
            ->selectRaw('EXTRACT(HOUR FROM activity_time_colombia) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->pluck('count', 'hour')
            ->toArray();

        // Task activity
        $taskActivity = DeveloperActivityLog::where('user_id', $developer->id)
            ->whereBetween('activity_time_colombia', [$startDate, $endDate])
            ->whereIn('activity_type', ['task_start', 'task_pause', 'task_resume', 'task_finish'])
            ->count();

        return [
            'developer' => $developer->name,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ],
            'total_sessions' => $totalSessions,
            'avg_session_duration_minutes' => round($avgSessionDuration ?? 0, 2),
            'activity_by_period' => $activityByPeriod,
            'most_active_hours' => $hourlyActivity,
            'task_activities' => $taskActivity,
            'preferred_work_time' => $this->getPreferredWorkTime($activityByPeriod)
        ];
    }

    /**
     * Get team activity overview
     */
    public function getTeamActivityOverview(
        Carbon $startDate = null,
        Carbon $endDate = null
    ): array {
        $startDate = $startDate ?? Carbon::now()->subDays(30);
        $endDate = $endDate ?? Carbon::now();

        $developers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['developer', 'team_leader']);
        })->get();

        $teamStats = [];
        $totalActivityByPeriod = [
            'morning' => 0,
            'afternoon' => 0,
            'evening' => 0,
            'night' => 0
        ];

        foreach ($developers as $developer) {
            $stats = $this->getDeveloperStats($developer, $startDate, $endDate);
            $teamStats[] = $stats;

            // Aggregate period activity
            foreach ($stats['activity_by_period'] as $period => $count) {
                $totalActivityByPeriod[$period] += $count;
            }
        }

        // Team preferences
        $teamPreferredTime = $this->getPreferredWorkTime($totalActivityByPeriod);

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ],
            'total_developers' => $developers->count(),
            'team_activity_by_period' => $totalActivityByPeriod,
            'team_preferred_work_time' => $teamPreferredTime,
            'developers' => $teamStats
        ];
    }

    /**
     * Get daily activity patterns
     */
    public function getDailyActivityPatterns(
        User $developer = null,
        Carbon $startDate = null,
        Carbon $endDate = null
    ): array {
        $startDate = $startDate ?? Carbon::now()->subDays(7);
        $endDate = $endDate ?? Carbon::now();

        $query = DeveloperActivityLog::whereBetween('activity_time_colombia', [$startDate, $endDate]);

        if ($developer) {
            $query->where('user_id', $developer->id);
        }

        $dailyPatterns = $query->selectRaw('
                DATE(activity_time_colombia) as date,
                time_period,
                COUNT(*) as activity_count
            ')
            ->groupBy('date', 'time_period')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        return $dailyPatterns->toArray();
    }

    /**
     * Get time zone conversion helper
     */
    public function getTimeZoneInfo(): array
    {
        $now = Carbon::now();
        
        // Common timezones for different regions
        $timezones = [
            'colombia' => [
                'name' => 'Colombia',
                'timezone' => 'America/Bogota',
                'offset' => '-05:00'
            ],
            'italy' => [
                'name' => 'Italy',
                'timezone' => 'Europe/Rome',
                'offset' => '+01:00 (DST: +02:00)'
            ],
            'spain' => [
                'name' => 'Spain',
                'timezone' => 'Europe/Madrid',
                'offset' => '+01:00 (DST: +02:00)'
            ],
            'mexico' => [
                'name' => 'Mexico',
                'timezone' => 'America/Mexico_City',
                'offset' => '-06:00 (DST: -05:00)'
            ],
            'argentina' => [
                'name' => 'Argentina',
                'timezone' => 'America/Argentina/Buenos_Aires',
                'offset' => '-03:00'
            ],
            'brazil' => [
                'name' => 'Brazil',
                'timezone' => 'America/Sao_Paulo',
                'offset' => '-03:00 (DST: -02:00)'
            ],
            'usa_east' => [
                'name' => 'USA (East)',
                'timezone' => 'America/New_York',
                'offset' => '-05:00 (DST: -04:00)'
            ],
            'usa_west' => [
                'name' => 'USA (West)',
                'timezone' => 'America/Los_Angeles',
                'offset' => '-08:00 (DST: -07:00)'
            ]
        ];
        
        $timezoneData = [];
        foreach ($timezones as $key => $tz) {
            $timezoneData[$key . '_time'] = $now->copy()->setTimezone($tz['timezone'])->format('Y-m-d H:i:s');
            $timezoneData[$key . '_offset'] = $tz['offset'];
        }
        
        $timezoneData['utc_time'] = $now->format('Y-m-d H:i:s');
        $timezoneData['time_difference'] = 'Colombia is 6 hours behind Italy (7 hours during DST)';
        
        return $timezoneData;
    }

    /**
     * Determine preferred work time based on activity
     */
    private function getPreferredWorkTime(array $activityByPeriod): string
    {
        if (empty($activityByPeriod)) {
            return 'No data available';
        }

        $maxActivity = max($activityByPeriod);
        $preferredPeriods = array_keys($activityByPeriod, $maxActivity);

        if (count($preferredPeriods) === 1) {
            return ucfirst($preferredPeriods[0]);
        }

        return 'Mixed (' . implode(', ', array_map('ucfirst', $preferredPeriods)) . ')';
    }

    /**
     * Calculate session duration when user logs out or finishes work
     */
    public function calculateSessionDuration(User $user): void
    {
        $lastLogin = DeveloperActivityLog::where('user_id', $user->id)
            ->where('activity_type', 'login')
            ->latest('activity_time_colombia')
            ->first();

        if ($lastLogin) {
            $now = Carbon::now()->setTimezone('America/Bogota');
            $duration = $lastLogin->activity_time_colombia->diffInMinutes($now);

            // Update the login record with session duration
            $lastLogin->update(['session_duration_minutes' => $duration]);
        }
    }
} 
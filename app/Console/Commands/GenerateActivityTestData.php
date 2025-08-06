<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\DeveloperActivityLog;
use App\Models\Task;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateActivityTestData extends Command
{
    protected $signature = 'test:generate-activity-data {--days=30}';
    protected $description = 'Generate test activity data for developers';

    public function handle()
    {
        $this->info('🚀 Generating developer activity test data...');

        $days = $this->option('days');
        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();

        // Get all developers
        $developers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['developer', 'team_leader']);
        })->get();

        $this->info("Found {$developers->count()} developers");
        $this->info("Generating data for {$days} days (from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')})");

        $totalActivities = 0;

        foreach ($developers as $developer) {
            $this->info("Generating activity for: {$developer->name}");
            $activities = $this->generateDeveloperActivity($developer, $startDate, $endDate);
            $totalActivities += $activities;
            $this->info("  - Created {$activities} activities");
        }

        $this->info('✅ Activity test data generated successfully!');
        $this->info("📊 Total activities created: {$totalActivities}");
    }

    private function generateDeveloperActivity(User $developer, Carbon $startDate, Carbon $endDate): int
    {
        $activities = 0;
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($currentDate->dayOfWeek === 0 || $currentDate->dayOfWeek === 6) {
                $currentDate->addDay();
                continue;
            }

            // Generate 1-3 work sessions per day
            $sessionsPerDay = rand(1, 3);
            
            for ($session = 0; $session < $sessionsPerDay; $session++) {
                $activities += $this->generateWorkSession($developer, $currentDate);
            }

            $currentDate->addDay();
        }

        return $activities;
    }

    private function generateWorkSession(User $developer, Carbon $date): int
    {
        $activities = 0;

        // Determine work hours based on developer preferences
        $workStartHour = $this->getWorkStartHour($developer);
        $workEndHour = $workStartHour + rand(6, 10); // 6-10 hour work day

        // Login activity
        $loginTime = $date->copy()->setTime($workStartHour, rand(0, 59), 0);
        $this->createActivityLog($developer, 'login', null, $loginTime);
        $activities++;

        // Generate task activities throughout the day
        $taskActivities = rand(3, 8); // 3-8 task activities per session
        $currentTime = $loginTime->copy();

        for ($i = 0; $i < $taskActivities; $i++) {
            // Add some random time between activities
            $currentTime->addMinutes(rand(30, 180)); // 30 minutes to 3 hours between activities

            if ($currentTime->hour >= $workEndHour) {
                break; // Stop if we've reached the end of the work day
            }

            $activityType = $this->getRandomActivityType();
            $task = $this->getRandomTask($developer);

            $this->createActivityLog($developer, $activityType, $task, $currentTime);
            $activities++;
        }

        // Logout activity
        $logoutTime = $date->copy()->setTime($workEndHour, rand(0, 59), 0);
        $this->createActivityLog($developer, 'logout', null, $logoutTime);
        $activities++;

        return $activities;
    }

    private function getWorkStartHour(User $developer): int
    {
        // Simulate different work patterns
        $patterns = [
            'early_bird' => [6, 7, 8],    // 6-8 AM
            'normal' => [8, 9, 10],       // 8-10 AM
            'late_start' => [10, 11, 12]  // 10 AM - 12 PM
        ];

        // Assign patterns based on developer ID (for consistency)
        $patternIndex = crc32($developer->id) % 3;
        $patterns = array_values($patterns);
        $pattern = $patterns[$patternIndex];

        return $pattern[array_rand($pattern)];
    }

    private function getRandomActivityType(): string
    {
        $types = ['task_start', 'task_pause', 'task_resume', 'task_finish'];
        return $types[array_rand($types)];
    }

    private function getRandomTask(User $developer): ?Task
    {
        // Get a random task assigned to this developer
        $task = Task::where('user_id', $developer->id)
            ->inRandomOrder()
            ->first();

        return $task;
    }

    private function createActivityLog(User $developer, string $activityType, ?Task $task, Carbon $time): void
    {
        $colombiaTime = $time->copy()->setTimezone('America/Bogota');
        $utcTime = $time->copy()->setTimezone('UTC');
        
        $timePeriod = DeveloperActivityLog::getTimePeriod($colombiaTime->hour);

        DeveloperActivityLog::create([
            'user_id' => $developer->id,
            'task_id' => $task?->id,
            'activity_type' => $activityType,
            'activity_time_colombia' => $colombiaTime,
            'activity_time_utc' => $utcTime,
            'time_period' => $timePeriod,
            'metadata' => [
                'generated' => true,
                'session_id' => 'test-session-' . uniqid()
            ]
        ]);
    }
} 
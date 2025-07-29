<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Role;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class TestPaymentReportsCommand extends Command
{
    protected $signature = 'payment-reports:test';
    protected $description = 'Test payment reports functionality';

    public function handle()
    {
        $this->info('🧪 Testing payment reports...');

        // Create roles if they don't exist
        $this->createRoles();

        // Create admin user
        $admin = $this->createAdminUser();

        // Create developer users
        $developer1 = $this->createDeveloperUser('john@example.com', 'John Doe', 25);
        $developer2 = $this->createDeveloperUser('jane@example.com', 'Jane Smith', 30);

        // Create projects
        $project1 = $this->createProject('E-commerce Platform', 'Build a modern e-commerce platform');
        $project2 = $this->createProject('Mobile App', 'Develop a mobile application');

        // Assign developers to projects
        $project1->users()->attach([$developer1->id, $developer2->id]);
        $project2->users()->attach([$developer1->id]);

        // Create sprints
        $sprint1 = $this->createSprint($project1, 'Sprint 1');
        $sprint2 = $this->createSprint($project1, 'Sprint 2');
        $sprint3 = $this->createSprint($project2, 'Sprint 1');

        // Create tasks with different statuses
        $this->createTasks($sprint1, $developer1, [
            ['User Authentication', 'Implement user login and registration', 'done', 'high', 'backend', 8, 10],
            ['Database Design', 'Design the database schema', 'done', 'medium', 'backend', 6, 8],
            ['API Development', 'Create REST API endpoints', 'in progress', 'high', 'backend', 12, 6],
        ]);

        $this->createTasks($sprint2, $developer2, [
            ['Frontend Dashboard', 'Build the admin dashboard', 'done', 'high', 'frontend', 10, 12],
            ['Payment Integration', 'Integrate payment gateway', 'done', 'high', 'backend', 8, 9],
            ['Mobile UI', 'Design mobile interface', 'to do', 'medium', 'design', 6, 0],
        ]);

        $this->createTasks($sprint3, $developer1, [
            ['App Setup', 'Initialize mobile app project', 'done', 'low', 'frontend', 4, 5],
            ['Navigation', 'Implement app navigation', 'in progress', 'medium', 'frontend', 6, 3],
        ]);

        $this->info('✅ Payment reports test data created successfully!');
        $this->info('👤 Admin: admin@example.com / password');
        $this->info('👤 Developer 1: john@example.com / password (Hour rate: $25)');
        $this->info('👤 Developer 2: jane@example.com / password (Hour rate: $30)');
        $this->info('');
        $this->info('📊 Test the payment reports at: /payment-reports');
    }

    private function createRoles()
    {
        Role::firstOrCreate(['value' => 'admin']);
        Role::firstOrCreate(['value' => 'developer']);
    }

    private function createAdminUser()
    {
        return User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'hour_value' => 0,
            ]
        );
        
        $user->roles()->sync([Role::where('value', 'admin')->first()->id]);
        return $user;
    }

    private function createDeveloperUser($email, $name, $hourValue)
    {
        return User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'hour_value' => $hourValue,
            ]
        );
        
        $user->roles()->sync([Role::where('value', 'developer')->first()->id]);
        return $user;
    }

    private function createProject($name, $description)
    {
        return Project::firstOrCreate(
            ['name' => $name],
            [
                'name' => $name,
                'description' => $description,
                'status' => 'active',
                'creator_id' => User::where('email', 'admin@example.com')->first()->id,
            ]
        );
    }

    private function createSprint($project, $name)
    {
        return Sprint::firstOrCreate(
            ['name' => $name, 'project_id' => $project->id],
            [
                'name' => $name,
                'goal' => "Complete key features for {$project->name}",
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(15),
                'project_id' => $project->id,
            ]
        );
    }

    private function createTasks($sprint, $developer, $tasksData)
    {
        foreach ($tasksData as $taskData) {
            [$name, $description, $status, $priority, $category, $estimatedHours, $actualHours] = $taskData;
            
            Task::firstOrCreate(
                ['name' => $name, 'sprint_id' => $sprint->id],
                [
                    'name' => $name,
                    'description' => $description,
                    'status' => $status,
                    'priority' => $priority,
                    'category' => $category,
                    'estimated_hours' => $estimatedHours,
                    'actual_hours' => $actualHours,
                    'user_id' => $developer->id,
                    'sprint_id' => $sprint->id,
                    'story_points' => rand(3, 8),
                    'actual_finish' => $status === 'done' ? now()->subDays(rand(1, 10)) : null,
                ]
            );
        }
    }
} 
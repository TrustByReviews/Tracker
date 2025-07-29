<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Role;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class TestScrumFeaturesCommand extends Command
{
    protected $signature = 'scrum:test';
    protected $description = 'Test Scrum features with comprehensive data';

    public function handle()
    {
        $this->info('🧪 Testing Scrum features...');

        // Create roles if they don't exist
        $this->createRoles();

        // Create admin user
        $admin = $this->createAdminUser();

        // Create developer users
        $developer1 = $this->createDeveloperUser('john@example.com', 'John Doe', 25);
        $developer2 = $this->createDeveloperUser('jane@example.com', 'Jane Smith', 30);
        $developer3 = $this->createDeveloperUser('mike@example.com', 'Mike Johnson', 28);

        // Create projects with different statuses
        $project1 = $this->createProject('E-commerce Platform', 'Build a modern e-commerce platform', 'active');
        $project2 = $this->createProject('Mobile App', 'Develop a mobile application', 'active');
        $project3 = $this->createProject('API Gateway', 'Create a microservices API gateway', 'completed');
        $project4 = $this->createProject('Dashboard Redesign', 'Redesign the admin dashboard', 'paused');

        // Assign developers to projects
        $project1->users()->attach([$developer1->id, $developer2->id]);
        $project2->users()->attach([$developer1->id, $developer3->id]);
        $project3->users()->attach([$developer2->id, $developer3->id]);
        $project4->users()->attach([$developer1->id]);

        // Create sprints for each project
        $sprint1 = $this->createSprint($project1, 'Sprint 1 - Foundation');
        $sprint2 = $this->createSprint($project1, 'Sprint 2 - Core Features');
        $sprint3 = $this->createSprint($project2, 'Sprint 1 - Setup');
        $sprint4 = $this->createSprint($project3, 'Sprint 1 - Architecture');
        $sprint5 = $this->createSprint($project4, 'Sprint 1 - Design');

        // Create tasks with various statuses and priorities
        $this->createTasks($sprint1, $developer1, [
            ['User Authentication', 'Implement user login and registration', 'done', 'high', 'backend', 8, 10],
            ['Database Design', 'Design the database schema', 'done', 'medium', 'backend', 6, 8],
            ['API Development', 'Create REST API endpoints', 'in progress', 'high', 'backend', 12, 6],
        ]);

        $this->createTasks($sprint2, $developer2, [
            ['Frontend Dashboard', 'Build the admin dashboard', 'done', 'high', 'frontend', 10, 12],
            ['Payment Integration', 'Integrate payment gateway', 'done', 'high', 'backend', 8, 9],
            ['Mobile UI', 'Design mobile interface', 'to do', 'medium', 'design', 6, 0],
            ['Testing Suite', 'Create comprehensive tests', 'in progress', 'low', 'full stack', 4, 2],
        ]);

        $this->createTasks($sprint3, $developer1, [
            ['App Setup', 'Initialize mobile app project', 'done', 'low', 'frontend', 4, 5],
            ['Navigation', 'Implement app navigation', 'in progress', 'medium', 'frontend', 6, 3],
            ['State Management', 'Set up Redux store', 'to do', 'high', 'frontend', 8, 0],
        ]);

        $this->createTasks($sprint4, $developer2, [
            ['API Architecture', 'Design microservices architecture', 'done', 'high', 'backend', 16, 18],
            ['Load Balancer', 'Configure load balancer', 'done', 'medium', 'deployment', 6, 7],
            ['Monitoring', 'Set up monitoring and logging', 'done', 'low', 'deployment', 4, 5],
        ]);

        $this->createTasks($sprint5, $developer1, [
            ['UI Design', 'Create new dashboard mockups', 'done', 'high', 'design', 8, 10],
            ['Component Library', 'Build reusable components', 'in progress', 'medium', 'frontend', 12, 8],
            ['Responsive Design', 'Make dashboard responsive', 'to do', 'medium', 'frontend', 6, 0],
        ]);

        $this->info('✅ Scrum features test data created successfully!');
        $this->info('👤 Admin: admin@example.com / password');
        $this->info('👤 Developer 1: john@example.com / password (Hour rate: $25)');
        $this->info('👤 Developer 2: jane@example.com / password (Hour rate: $30)');
        $this->info('👤 Developer 3: mike@example.com / password (Hour rate: $28)');
        $this->info('');
        $this->info('📊 Test the new features at:');
        $this->info('   - Projects: /projects');
        $this->info('   - Users: /users');
        $this->info('   - Dashboard: /dashboard');
    }

    private function createRoles()
    {
        Role::firstOrCreate(['value' => 'admin']);
        Role::firstOrCreate(['value' => 'developer']);
    }

    private function createAdminUser()
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'hour_value' => 0,
                'work_time' => 'full',
                'status' => 'active',
            ]
        );
        
        $user->roles()->sync([Role::where('value', 'admin')->first()->id]);
        return $user;
    }

    private function createDeveloperUser($email, $name, $hourValue)
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'hour_value' => $hourValue,
                'work_time' => 'full',
                'status' => 'active',
            ]
        );
        
        $user->roles()->sync([Role::where('value', 'developer')->first()->id]);
        return $user;
    }

    private function createProject($name, $description, $status)
    {
        return Project::firstOrCreate(
            ['name' => $name],
            [
                'name' => $name,
                'description' => $description,
                'status' => $status,
                'created_by' => User::where('email', 'admin@example.com')->first()->id,
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
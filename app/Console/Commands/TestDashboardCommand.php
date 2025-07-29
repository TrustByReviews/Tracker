<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Role;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;

class TestDashboardCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test data for dashboard testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🧪 Creating test data for dashboard...");

        // Crear roles si no existen
        $this->createRoles();

        // Crear usuarios de prueba
        $admin = $this->createAdminUser();
        $developer1 = $this->createDeveloperUser('John Doe', 'john@example.com', 50);
        $developer2 = $this->createDeveloperUser('Jane Smith', 'jane@example.com', 60);

        // Crear proyectos
        $project1 = $this->createProject('E-commerce Platform', 'Online shopping platform with payment integration');
        $project2 = $this->createProject('Mobile App', 'Cross-platform mobile application');

        // Asignar usuarios a proyectos
        $project1->users()->attach([$developer1->id, $developer2->id]);
        $project2->users()->attach([$developer1->id]);

        // Crear sprints
        $sprint1 = $this->createSprint($project1, 'Sprint 1', '2024-01-01', '2024-01-15');
        $sprint2 = $this->createSprint($project1, 'Sprint 2', '2024-01-16', '2024-01-31');
        $sprint3 = $this->createSprint($project2, 'Sprint 1', '2024-01-01', '2024-01-20');

        // Crear tareas
        $this->createTasks($sprint1, $developer1, $developer2);
        $this->createTasks($sprint2, $developer1, $developer2);
        $this->createTasks($sprint3, $developer1, null);

        $this->info("✅ Test data created successfully!");
        $this->info("👤 Admin user: admin@example.com / password");
        $this->info("👤 Developer 1: john@example.com / password");
        $this->info("👤 Developer 2: jane@example.com / password");
    }

    private function createRoles()
    {
        if (!Role::where('value', 'admin')->exists()) {
            Role::create(['name' => 'Admin', 'value' => 'admin']);
        }
        if (!Role::where('value', 'developer')->exists()) {
            Role::create(['name' => 'Developer', 'value' => 'developer']);
        }
    }

    private function createAdminUser()
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'nickname' => 'admin',
                'password' => bcrypt('password'),
                'hour_value' => 100,
                'work_time' => 'full',
                'status' => 'active'
            ]
        );

        $adminRole = Role::where('value', 'admin')->first();
        if (!$user->roles()->where('role_id', $adminRole->id)->exists()) {
            $user->roles()->attach($adminRole->id);
        }

        return $user;
    }

    private function createDeveloperUser($name, $email, $hourValue)
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'nickname' => strtolower(str_replace(' ', '', $name)),
                'password' => bcrypt('password'),
                'hour_value' => $hourValue,
                'work_time' => 'full',
                'status' => 'active'
            ]
        );

        $developerRole = Role::where('value', 'developer')->first();
        if (!$user->roles()->where('role_id', $developerRole->id)->exists()) {
            $user->roles()->attach($developerRole->id);
        }

        return $user;
    }

    private function createProject($name, $description)
    {
        return Project::firstOrCreate(
            ['name' => $name],
            [
                'description' => $description,
                'status' => 'active',
                'create_by' => User::first()->id
            ]
        );
    }

    private function createSprint($project, $name, $startDate, $endDate)
    {
        return Sprint::create([
            'name' => $name,
            'goal' => "Complete key features for {$project->name}",
            'start_date' => $startDate,
            'end_date' => $endDate,
            'project_id' => $project->id
        ]);
    }

    private function createTasks($sprint, $developer1, $developer2)
    {
        $tasks = [
            [
                'name' => 'User Authentication',
                'description' => 'Implement user login and registration',
                'priority' => 'high',
                'status' => 'done',
                'user_id' => $developer1->id,
                'estimated_hours' => 8,
                'actual_hours' => 10,
                'category' => 'backend'
            ],
            [
                'name' => 'Database Design',
                'description' => 'Design and implement database schema',
                'priority' => 'high',
                'status' => 'in progress',
                'user_id' => $developer1->id,
                'estimated_hours' => 12,
                'actual_hours' => 6,
                'category' => 'backend'
            ],
            [
                'name' => 'Frontend UI',
                'description' => 'Create responsive user interface',
                'priority' => 'medium',
                'status' => 'to do',
                'user_id' => $developer2 ? $developer2->id : null,
                'estimated_hours' => 16,
                'actual_hours' => null,
                'category' => 'frontend'
            ],
            [
                'name' => 'API Integration',
                'description' => 'Integrate third-party APIs',
                'priority' => 'high',
                'status' => 'to do',
                'user_id' => null,
                'estimated_hours' => 10,
                'actual_hours' => null,
                'category' => 'backend'
            ],
            [
                'name' => 'Testing',
                'description' => 'Write unit and integration tests',
                'priority' => 'medium',
                'status' => 'to do',
                'user_id' => null,
                'estimated_hours' => 8,
                'actual_hours' => null,
                'category' => 'fixes'
            ]
        ];

        foreach ($tasks as $taskData) {
            Task::create(array_merge($taskData, [
                'sprint_id' => $sprint->id,
                'story_points' => rand(1, 8)
            ]));
        }
    }
} 
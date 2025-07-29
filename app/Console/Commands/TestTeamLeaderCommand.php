<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Role;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class TestTeamLeaderCommand extends Command
{
    protected $signature = 'test:team-leader';
    protected $description = 'Generate test data for Team Leader functionality';

    public function handle()
    {
        $this->info('Generating Team Leader test data...');

        // Crear roles si no existen
        $this->createRoles();

        // Crear usuarios
        $admin = $this->createAdminUser();
        $teamLeader = $this->createTeamLeaderUser();
        $developers = $this->createDeveloperUsers();

        // Crear proyectos
        $projects = $this->createProjects($admin, $teamLeader, $developers);

        // Crear sprints y tareas
        $this->createSprintsAndTasks($projects, $developers);

        $this->info('Team Leader test data generated successfully!');
        $this->info("Admin: admin@example.com / password");
        $this->info("Team Leader: teamleader@example.com / password");
        $this->info("Developers: dev1@example.com, dev2@example.com / password");
    }

    private function createRoles()
    {
        $roles = [
            ['name' => 'Admin', 'value' => 'admin'],
            ['name' => 'Team Leader', 'value' => 'team_leader'],
            ['name' => 'Developer', 'value' => 'developer'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['value' => $roleData['value']],
                [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'name' => $roleData['name'],
                    'value' => $roleData['value'],
                ]
            );
        }
    }

    private function createAdminUser()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'name' => 'Admin User',
                'nickname' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'hour_value' => 50,
                'work_time' => 'full',
                'status' => 'active',
            ]
        );

        $adminRole = Role::where('value', 'admin')->first();
        if (!$admin->roles->contains($adminRole->id)) {
            $admin->roles()->attach($adminRole->id);
        }

        return $admin;
    }

    private function createTeamLeaderUser()
    {
        $teamLeader = User::firstOrCreate(
            ['email' => 'teamleader@example.com'],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'name' => 'Team Leader',
                'nickname' => 'tl',
                'email' => 'teamleader@example.com',
                'password' => Hash::make('password'),
                'hour_value' => 40,
                'work_time' => 'full',
                'status' => 'active',
            ]
        );

        $teamLeaderRole = Role::where('value', 'team_leader')->first();
        if (!$teamLeader->roles->contains($teamLeaderRole->id)) {
            $teamLeader->roles()->attach($teamLeaderRole->id);
        }

        return $teamLeader;
    }

    private function createDeveloperUsers()
    {
        $developers = [];
        $developerRole = Role::where('value', 'developer')->first();

        for ($i = 1; $i <= 3; $i++) {
            $developer = User::firstOrCreate(
                ['email' => "dev{$i}@example.com"],
                [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'name' => "Developer {$i}",
                    'nickname' => "dev{$i}",
                    'email' => "dev{$i}@example.com",
                    'password' => Hash::make('password'),
                    'hour_value' => 25 + ($i * 5),
                    'work_time' => 'full',
                    'status' => 'active',
                ]
            );

            if (!$developer->roles->contains($developerRole->id)) {
                $developer->roles()->attach($developerRole->id);
            }

            $developers[] = $developer;
        }

        return $developers;
    }

    private function createProjects($admin, $teamLeader, $developers)
    {
        $projectData = [
            [
                'name' => 'E-commerce Platform',
                'description' => 'Online shopping platform with payment integration',
                'status' => 'active',
            ],
            [
                'name' => 'Mobile App',
                'description' => 'Cross-platform mobile application',
                'status' => 'active',
            ],
            [
                'name' => 'API Gateway',
                'description' => 'Create a microservices API gateway',
                'status' => 'completed',
            ],
        ];

        $projects = [];
        foreach ($projectData as $data) {
            $project = Project::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'name' => $data['name'],
                'description' => $data['description'],
                'status' => $data['status'],
                'created_by' => $admin->id,
            ]);

            // Asignar team leader y desarrolladores al proyecto
            $project->users()->attach($teamLeader->id);
            foreach ($developers as $developer) {
                $project->users()->attach($developer->id);
            }

            $projects[] = $project;
        }

        return $projects;
    }

    private function createSprintsAndTasks($projects, $developers)
    {
        $taskStatuses = ['to do', 'in progress', 'ready for test', 'in review', 'done'];
        $priorities = ['low', 'medium', 'high'];
        $categories = ['frontend', 'backend', 'full stack', 'design', 'deployment', 'fixes'];

        foreach ($projects as $project) {
            // Crear sprints para cada proyecto
            for ($sprintNum = 1; $sprintNum <= 3; $sprintNum++) {
                $sprint = Sprint::create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'name' => "Sprint {$sprintNum}",
                    'goal' => "Complete sprint {$sprintNum} objectives",
                    'start_date' => now()->addDays($sprintNum * 14),
                    'end_date' => now()->addDays(($sprintNum * 14) + 14),
                    'project_id' => $project->id,
                ]);

                // Crear tareas para cada sprint
                for ($taskNum = 1; $taskNum <= 5; $taskNum++) {
                    $status = $taskStatuses[array_rand($taskStatuses)];
                    $developer = $developers[array_rand($developers)];

                    $task = Task::create([
                        'id' => \Illuminate\Support\Str::uuid(),
                        'name' => "Task {$taskNum} - Sprint {$sprintNum}",
                        'description' => "Description for task {$taskNum} in sprint {$sprintNum}",
                        'status' => $status,
                        'priority' => $priorities[array_rand($priorities)],
                        'category' => $categories[array_rand($categories)],
                        'story_points' => rand(1, 8),
                        'estimated_hours' => rand(2, 16),
                        'actual_hours' => $status === 'done' ? rand(2, 20) : null,
                        'sprint_id' => $sprint->id,
                        'project_id' => $project->id,
                        'user_id' => $developer->id,
                    ]);
                }
            }
        }
    }
} 
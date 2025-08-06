<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateComprehensiveTestData extends Command
{
    protected $signature = 'test:generate-comprehensive-data';
    protected $description = 'Generate comprehensive test data for all developers including completed and in-progress tasks';

    public function handle()
    {
        $this->info('🚀 Generating comprehensive test data for all developers...');

        // Obtener todos los usuarios desarrolladores (excluyendo admins)
        $developers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['developer', 'team_leader']);
        })->get();

        $this->info("Found {$developers->count()} developers");

        // Obtener o crear proyectos
        $projects = $this->createProjects();
        
        // Obtener o crear sprints
        $sprints = $this->createSprints($projects);

        // Generar tareas para cada desarrollador
        foreach ($developers as $developer) {
            $this->info("Generating tasks for: {$developer->name}");
            $this->generateTasksForDeveloper($developer, $projects, $sprints);
        }

        $this->info('✅ Comprehensive test data generated successfully!');
        $this->info('📊 Summary:');
        $this->info('- Total tasks created: ' . Task::count());
        $this->info('- Completed tasks: ' . Task::where('status', 'done')->count());
        $this->info('- In-progress tasks: ' . Task::where('status', 'in_progress')->count());
        $this->info('- To-do tasks: ' . Task::where('status', 'to do')->count());
    }

    private function createProjects()
    {
        $projects = [];
        
        $projectData = [
            [
                'name' => 'E-commerce Platform',
                'description' => 'Modern e-commerce platform with advanced features',
                'status' => 'active',
                'start_date' => '2024-01-15',
                'end_date' => '2024-12-31'
            ],
            [
                'name' => 'Mobile Banking App',
                'description' => 'Secure mobile banking application',
                'status' => 'active',
                'start_date' => '2024-03-01',
                'end_date' => '2024-11-30'
            ],
            [
                'name' => 'CRM System',
                'description' => 'Customer relationship management system',
                'status' => 'active',
                'start_date' => '2024-02-01',
                'end_date' => '2024-10-31'
            ],
            [
                'name' => 'Learning Management System',
                'description' => 'Online learning platform',
                'status' => 'active',
                'start_date' => '2024-04-01',
                'end_date' => '2024-12-31'
            ],
            [
                'name' => 'Inventory Management',
                'description' => 'Warehouse and inventory management system',
                'status' => 'active',
                'start_date' => '2024-01-01',
                'end_date' => '2024-09-30'
            ]
        ];

        foreach ($projectData as $data) {
            $project = Project::firstOrCreate(
                ['name' => $data['name']],
                [
                    'description' => $data['description'],
                    'status' => $data['status'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'created_by' => User::whereHas('roles', function ($q) {
                        $q->where('name', 'admin');
                    })->first()->id
                ]
            );
            $projects[] = $project;
        }

        return $projects;
    }

    private function createSprints($projects)
    {
        $sprints = [];
        
        foreach ($projects as $project) {
            // Crear múltiples sprints por proyecto
            for ($i = 1; $i <= 4; $i++) {
                $startDate = Carbon::parse($project->start_date)->addWeeks(($i - 1) * 2);
                $endDate = $startDate->copy()->addWeeks(2);
                
                $sprint = Sprint::firstOrCreate(
                    [
                        'name' => "Sprint {$i}",
                        'project_id' => $project->id
                    ],
                    [
                        'goal' => "Complete features for sprint {$i} of {$project->name}",
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'status' => $i <= 2 ? 'completed' : ($i == 3 ? 'active' : 'planned'),
                        'created_by' => User::whereHas('roles', function ($q) {
                            $q->where('name', 'admin');
                        })->first()->id
                    ]
                );
                $sprints[] = $sprint;
            }
        }

        return $sprints;
    }

    private function generateTasksForDeveloper($developer, $projects, $sprints)
    {
        $taskTypes = [
            'Frontend Development' => ['React Components', 'Vue.js Pages', 'UI/UX Implementation', 'Responsive Design'],
            'Backend Development' => ['API Development', 'Database Design', 'Authentication System', 'Business Logic'],
            'Testing' => ['Unit Tests', 'Integration Tests', 'E2E Tests', 'Performance Testing'],
            'DevOps' => ['CI/CD Pipeline', 'Docker Configuration', 'Server Setup', 'Monitoring'],
            'Documentation' => ['API Documentation', 'User Manuals', 'Technical Specs', 'Code Comments']
        ];

        $statuses = ['done', 'in_progress', 'to do'];
        $weights = [40, 35, 25]; // 40% completadas, 35% en progreso, 25% pendientes

        $taskCount = 0;
        
        foreach ($projects as $project) {
            $projectSprints = collect($sprints)->where('project_id', $project->id);
            
            foreach ($projectSprints as $sprint) {
                // Generar 3-6 tareas por sprint
                $tasksPerSprint = rand(3, 6);
                
                for ($i = 0; $i < $tasksPerSprint; $i++) {
                    $taskType = array_rand($taskTypes);
                    $taskNames = $taskTypes[$taskType];
                    $taskName = $taskNames[array_rand($taskNames)];
                    
                    // Seleccionar status basado en pesos
                    $status = $this->weightedRandomChoice($statuses, $weights);
                    
                    // Generar fechas y horas basadas en el status
                    $dates = $this->generateTaskDates($sprint, $status);
                    
                    // Generar horas estimadas y reales
                    $estimatedHours = rand(4, 16);
                    $actualHours = $status === 'done' ? rand($estimatedHours - 2, $estimatedHours + 4) : 
                                  ($status === 'in_progress' ? rand(1, $estimatedHours - 1) : 0);
                    
                    $task = Task::create([
                        'name' => "{$taskName} - {$project->name}",
                        'description' => "Implement {$taskName} for {$project->name} project",
                        'status' => $status,
                        'priority' => rand(1, 3),
                        'estimated_hours' => $estimatedHours,
                        'actual_hours' => $actualHours,
                        'user_id' => $developer->id,
                        'sprint_id' => $sprint->id,
                        'assigned_by' => User::whereHas('roles', function ($q) {
                            $q->where('name', 'admin');
                        })->first()->id,
                        'created_at' => $dates['created_at'],
                        'updated_at' => $dates['updated_at'],
                        'actual_start' => $dates['actual_start'],
                        'actual_finish' => $dates['actual_finish'],
                        'is_working' => $status === 'in_progress' && rand(0, 1),
                        'work_started_at' => $status === 'in_progress' ? $dates['work_started_at'] : null
                    ]);
                    
                    $taskCount++;
                }
            }
        }
        
        $this->info("  - Created {$taskCount} tasks for {$developer->name}");
    }

    private function weightedRandomChoice($choices, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        $currentWeight = 0;
        
        foreach ($choices as $index => $choice) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $choice;
            }
        }
        
        return $choices[0];
    }

    private function generateTaskDates($sprint, $status)
    {
        $sprintStart = Carbon::parse($sprint->start_date);
        $sprintEnd = Carbon::parse($sprint->end_date);
        
        $createdAt = $sprintStart->copy()->addDays(rand(0, 5));
        $updatedAt = $createdAt->copy()->addDays(rand(1, 10));
        
        switch ($status) {
            case 'done':
                $actualStart = $createdAt->copy()->addDays(rand(0, 3));
                $actualFinish = $actualStart->copy()->addDays(rand(1, 7));
                $workStartedAt = $actualStart;
                break;
                
            case 'in_progress':
                $actualStart = $createdAt->copy()->addDays(rand(0, 5));
                $actualFinish = null;
                $workStartedAt = $actualStart->copy()->addDays(rand(0, 3));
                break;
                
            case 'to do':
            default:
                $actualStart = null;
                $actualFinish = null;
                $workStartedAt = null;
                break;
        }
        
        return [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'actual_start' => $actualStart ? $actualStart->format('Y-m-d') : null,
            'actual_finish' => $actualFinish ? $actualFinish->format('Y-m-d') : null,
            'work_started_at' => $workStartedAt
        ];
    }
} 
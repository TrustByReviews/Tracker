<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use Carbon\Carbon;

class CreateTestTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:create-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test tasks for payment reports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creando tareas de prueba para reportes de pago...');

        // Obtener desarrolladores
        $developers = User::whereHas('roles', function($query) {
            $query->where('name', 'developer');
        })->get();

        if ($developers->isEmpty()) {
            $this->error('No se encontraron desarrolladores');
            return Command::FAILURE;
        }

        // Obtener un usuario admin para crear el proyecto
        $admin = User::whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->first();

        if (!$admin) {
            $this->error('No se encontró un usuario administrador');
            return Command::FAILURE;
        }

        // Crear proyecto de prueba si no existe
        $project = Project::firstOrCreate(
            ['name' => 'Sistema de Tracking'],
            [
                'description' => 'Proyecto de prueba para reportes',
                'status' => 'active',
                'start_date' => Carbon::now()->subMonths(2),
                'end_date' => Carbon::now()->addMonths(2),
                'created_by' => $admin->id,
            ]
        );

        // Crear sprint de prueba
        $sprint = Sprint::firstOrCreate(
            [
                'name' => 'Sprint 1 - Desarrollo',
                'project_id' => $project->id
            ],
            [
                'description' => 'Sprint de desarrollo inicial',
                'goal' => 'Completar funcionalidades básicas del sistema',
                'start_date' => Carbon::now()->subMonth(),
                'end_date' => Carbon::now()->addWeek(),
                'status' => 'active',
                'created_by' => $admin->id
            ]
        );

        $tasks = [
            [
                'name' => 'Implementar sistema de autenticación',
                'description' => 'Desarrollar login y registro de usuarios',
                'estimated_hours' => 8,
                'actual_hours' => 7,
                'status' => 'done',
                'actual_finish' => Carbon::now()->subDays(5)
            ],
            [
                'name' => 'Crear dashboard principal',
                'description' => 'Desarrollar interfaz del dashboard',
                'estimated_hours' => 12,
                'actual_hours' => 11,
                'status' => 'done',
                'actual_finish' => Carbon::now()->subDays(3)
            ],
            [
                'name' => 'Implementar gestión de proyectos',
                'description' => 'CRUD completo para proyectos',
                'estimated_hours' => 16,
                'actual_hours' => 14,
                'status' => 'done',
                'actual_finish' => Carbon::now()->subDays(2)
            ],
            [
                'name' => 'Desarrollar sistema de tareas',
                'description' => 'Gestión completa de tareas y asignaciones',
                'estimated_hours' => 20,
                'actual_hours' => 18,
                'status' => 'done',
                'actual_finish' => Carbon::now()->subDays(1)
            ],
            [
                'name' => 'Implementar reportes de pago',
                'description' => 'Sistema de reportes Excel y PDF',
                'estimated_hours' => 10,
                'actual_hours' => 9,
                'status' => 'done',
                'actual_finish' => Carbon::now()
            ]
        ];

        $createdCount = 0;

        foreach ($developers as $developer) {
            foreach ($tasks as $taskData) {
                $task = Task::firstOrCreate(
                    [
                        'name' => $taskData['name'] . ' - ' . $developer->name,
                        'sprint_id' => $sprint->id,
                        'assigned_by' => $developer->id
                    ],
                    [
                        'description' => $taskData['description'],
                        'estimated_hours' => $taskData['estimated_hours'],
                        'actual_hours' => $taskData['actual_hours'],
                        'status' => $taskData['status'],
                        'actual_finish' => $taskData['actual_finish'],
                        'created_by' => $developer->id
                    ]
                );

                if ($task->wasRecentlyCreated) {
                    $createdCount++;
                    $this->line("✓ Tarea creada: {$task->name} para {$developer->name}");
                }
            }
        }

        $this->info("Se crearon {$createdCount} tareas de prueba");
        $this->info("Proyecto: {$project->name}");
        $this->info("Sprint: {$sprint->name}");

        return Command::SUCCESS;
    }
}

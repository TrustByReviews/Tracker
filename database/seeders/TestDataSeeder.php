<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\TaskTimeLog;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $teamLeaderRole = Role::firstOrCreate(['name' => 'team_leader']);
        $developerRole = Role::firstOrCreate(['name' => 'developer']);
        $qaRole = Role::firstOrCreate(['name' => 'qa']);

        // Crear usuarios de prueba
        $users = [
            // Administradores
            [
                'name' => 'Administrador Principal',
                'email' => 'admin@example.com',
                'password' => 'password',
                'role' => 'admin'
            ],
            [
                'name' => 'Admin Secundario',
                'email' => 'admin2@example.com',
                'password' => 'password',
                'role' => 'admin'
            ],

            // Team Leaders
            [
                'name' => 'María González - Team Leader',
                'email' => 'teamleader1@example.com',
                'password' => 'password',
                'role' => 'team_leader'
            ],
            [
                'name' => 'Carlos Rodríguez - Team Leader',
                'email' => 'teamleader2@example.com',
                'password' => 'password',
                'role' => 'team_leader'
            ],
            [
                'name' => 'Ana López - Team Leader',
                'email' => 'teamleader3@example.com',
                'password' => 'password',
                'role' => 'team_leader'
            ],

            // Desarrolladores
            [
                'name' => 'Luis Pérez - Desarrollador',
                'email' => 'developer1@example.com',
                'password' => 'password',
                'role' => 'developer'
            ],
            [
                'name' => 'Sofia García - Desarrolladora',
                'email' => 'developer2@example.com',
                'password' => 'password',
                'role' => 'developer'
            ],
            [
                'name' => 'Juan Martínez - Desarrollador',
                'email' => 'developer3@example.com',
                'password' => 'password',
                'role' => 'developer'
            ],
            [
                'name' => 'Carmen Ruiz - Desarrolladora',
                'email' => 'developer4@example.com',
                'password' => 'password',
                'role' => 'developer'
            ],
            [
                'name' => 'Roberto Silva - Desarrollador',
                'email' => 'developer5@example.com',
                'password' => 'password',
                'role' => 'developer'
            ],

            // QA
            [
                'name' => 'Carlos Rodríguez - QA',
                'email' => 'carlos.rodriguez270@test.com',
                'password' => 'password',
                'role' => 'qa'
            ],
            [
                'name' => 'QA Tester',
                'email' => 'qa1@test.com',
                'password' => 'password',
                'role' => 'qa'
            ]
        ];

        $createdUsers = [];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make($userData['password']),
                    'email_verified_at' => now()
                ]
            );

            // Asignar rol
            switch ($userData['role']) {
                case 'admin':
                    $user->roles()->sync([$adminRole->id]);
                    break;
                case 'team_leader':
                    $user->roles()->sync([$teamLeaderRole->id]);
                    break;
                case 'developer':
                    $user->roles()->sync([$developerRole->id]);
                    break;
                case 'qa':
                    $user->roles()->sync([$qaRole->id]);
                    break;
            }

            $createdUsers[$userData['role']][] = $user;
        }

        // Crear proyectos
        $projects = [
            [
                'name' => 'E-commerce Platform',
                'description' => 'Plataforma de comercio electrónico completa con carrito de compras, pagos y gestión de inventario',
                'status' => 'active',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(60),
                'created_by' => $createdUsers['admin'][0]->id
            ],
            [
                'name' => 'Mobile App',
                'description' => 'Aplicación móvil para iOS y Android con funcionalidades de geolocalización',
                'status' => 'active',
                'start_date' => now()->subDays(15),
                'end_date' => now()->addDays(45),
                'created_by' => $createdUsers['admin'][0]->id
            ],
            [
                'name' => 'Analytics Dashboard',
                'description' => 'Panel de análisis con gráficos interactivos y reportes en tiempo real',
                'status' => 'active',
                'start_date' => now()->subDays(7),
                'end_date' => now()->addDays(30),
                'created_by' => $createdUsers['admin'][0]->id
            ],
            [
                'name' => 'CRM System',
                'description' => 'Sistema de gestión de relaciones con clientes',
                'status' => 'active',
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(40),
                'created_by' => $createdUsers['admin'][0]->id
            ]
        ];

        $createdProjects = [];

        foreach ($projects as $projectData) {
            $project = Project::firstOrCreate(
                ['name' => $projectData['name']],
                $projectData
            );
            $createdProjects[] = $project;
        }

        // Asignar usuarios a proyectos
        $createdProjects[0]->users()->sync([
            $createdUsers['team_leader'][0]->id,
            $createdUsers['developer'][0]->id,
            $createdUsers['developer'][1]->id,
            $createdUsers['qa'][0]->id, // Carlos Rodríguez - QA
            $createdUsers['qa'][1]->id  // QA Tester
        ]);

        $createdProjects[1]->users()->sync([
            $createdUsers['team_leader'][1]->id,
            $createdUsers['developer'][1]->id,
            $createdUsers['developer'][2]->id
        ]);

        $createdProjects[2]->users()->sync([
            $createdUsers['team_leader'][2]->id,
            $createdUsers['developer'][2]->id,
            $createdUsers['developer'][3]->id
        ]);

        $createdProjects[3]->users()->sync([
            $createdUsers['team_leader'][0]->id,
            $createdUsers['developer'][3]->id,
            $createdUsers['developer'][4]->id
        ]);

        // Crear sprints para cada proyecto
        $sprints = [];
        foreach ($createdProjects as $project) {
            $sprints[$project->id] = [
                Sprint::firstOrCreate(
                    [
                        'project_id' => $project->id,
                        'name' => 'Sprint 1'
                    ],
                    [
                        'project_id' => $project->id,
                        'name' => 'Sprint 1',
                        'description' => 'Primer sprint del proyecto ' . $project->name,
                        'start_date' => now()->subDays(7),
                        'end_date' => now()->addDays(7),
                        'status' => 'active'
                    ]
                ),
                Sprint::firstOrCreate(
                    [
                        'project_id' => $project->id,
                        'name' => 'Sprint 2'
                    ],
                    [
                        'project_id' => $project->id,
                        'name' => 'Sprint 2',
                        'description' => 'Segundo sprint del proyecto ' . $project->name,
                        'start_date' => now()->addDays(8),
                        'end_date' => now()->addDays(21),
                        'status' => 'planned'
                    ]
                )
            ];
        }

        // Crear tareas con diferentes estados
        $tasks = [
            // E-commerce Platform - Sprint 1
            [
                'name' => 'Implementar sistema de login',
                'description' => 'Crear sistema de autenticación con JWT y validación de usuarios',
                'project_id' => $createdProjects[0]->id,
                'sprint_id' => $sprints[$createdProjects[0]->id][0]->id,
                'status' => 'to do',
                'priority' => 'high',
                'estimated_hours' => 8,
                'created_by' => $createdUsers['team_leader'][0]->id
            ],
            [
                'name' => 'Desarrollar carrito de compras',
                'description' => 'Implementar funcionalidad de carrito con persistencia y cálculos',
                'project_id' => $createdProjects[0]->id,
                'sprint_id' => $sprints[$createdProjects[0]->id][0]->id,
                'status' => 'in progress',
                'priority' => 'high',
                'estimated_hours' => 12,
                'user_id' => $createdUsers['developer'][0]->id,
                'assigned_by' => $createdUsers['team_leader'][0]->id,
                'assigned_at' => now()->subDays(2),
                'work_started_at' => now()->subDays(1),
                'is_working' => true,
                'total_time_seconds' => 14400, // 4 horas
                'created_by' => $createdUsers['team_leader'][0]->id
            ],
            [
                'name' => 'Integrar pasarela de pagos',
                'description' => 'Integrar Stripe para procesamiento de pagos seguros',
                'project_id' => $createdProjects[0]->id,
                'sprint_id' => $sprints[$createdProjects[0]->id][0]->id,
                'status' => 'done',
                'priority' => 'medium',
                'estimated_hours' => 10,
                'user_id' => $createdUsers['developer'][1]->id,
                'assigned_by' => $createdUsers['team_leader'][0]->id,
                'assigned_at' => now()->subDays(5),
                'total_time_seconds' => 28800, // 8 horas
                'approval_status' => 'approved',
                'reviewed_by' => $createdUsers['team_leader'][0]->id,
                'reviewed_at' => now()->subDays(1),
                'created_by' => $createdUsers['team_leader'][0]->id
            ],

            // Mobile App - Sprint 1
            [
                'name' => 'Diseñar interfaz de usuario',
                'description' => 'Crear mockups y prototipos de la interfaz móvil',
                'project_id' => $createdProjects[1]->id,
                'sprint_id' => $sprints[$createdProjects[1]->id][0]->id,
                'status' => 'to do',
                'priority' => 'high',
                'estimated_hours' => 6,
                'created_by' => $createdUsers['team_leader'][1]->id
            ],
            [
                'name' => 'Implementar geolocalización',
                'description' => 'Agregar funcionalidad de GPS y mapas interactivos',
                'project_id' => $createdProjects[1]->id,
                'sprint_id' => $sprints[$createdProjects[1]->id][0]->id,
                'status' => 'in progress',
                'priority' => 'medium',
                'estimated_hours' => 15,
                'user_id' => $createdUsers['developer'][1]->id,
                'assigned_by' => $createdUsers['team_leader'][1]->id,
                'assigned_at' => now()->subDays(3),
                'work_started_at' => now()->subDays(2),
                'is_working' => false,
                'total_time_seconds' => 21600, // 6 horas
                'created_by' => $createdUsers['team_leader'][1]->id
            ],

            // Analytics Dashboard - Sprint 1
            [
                'name' => 'Crear gráficos interactivos',
                'description' => 'Implementar gráficos con Chart.js y filtros dinámicos',
                'project_id' => $createdProjects[2]->id,
                'sprint_id' => $sprints[$createdProjects[2]->id][0]->id,
                'status' => 'done',
                'priority' => 'high',
                'estimated_hours' => 14,
                'user_id' => $createdUsers['developer'][2]->id,
                'assigned_by' => $createdUsers['team_leader'][2]->id,
                'assigned_at' => now()->subDays(8),
                'total_time_seconds' => 36000, // 10 horas
                'approval_status' => 'pending',
                'created_by' => $createdUsers['team_leader'][2]->id
            ],
            [
                'name' => 'Implementar filtros avanzados',
                'description' => 'Crear sistema de filtros para los reportes',
                'project_id' => $createdProjects[2]->id,
                'sprint_id' => $sprints[$createdProjects[2]->id][0]->id,
                'status' => 'in progress',
                'priority' => 'medium',
                'estimated_hours' => 8,
                'user_id' => $createdUsers['developer'][3]->id,
                'assigned_by' => $createdUsers['team_leader'][2]->id,
                'assigned_at' => now()->subDays(1),
                'work_started_at' => now()->subHours(4),
                'is_working' => true,
                'total_time_seconds' => 14400, // 4 horas
                'created_by' => $createdUsers['team_leader'][2]->id
            ],

            // CRM System - Sprint 1
            [
                'name' => 'Diseñar base de datos',
                'description' => 'Crear esquema de base de datos para el CRM',
                'project_id' => $createdProjects[3]->id,
                'sprint_id' => $sprints[$createdProjects[3]->id][0]->id,
                'status' => 'done',
                'priority' => 'high',
                'estimated_hours' => 6,
                'user_id' => $createdUsers['developer'][3]->id,
                'assigned_by' => $createdUsers['team_leader'][0]->id,
                'assigned_at' => now()->subDays(10),
                'total_time_seconds' => 21600, // 6 horas
                'approval_status' => 'approved',
                'reviewed_by' => $createdUsers['team_leader'][0]->id,
                'reviewed_at' => now()->subDays(8),
                'created_by' => $createdUsers['team_leader'][0]->id
            ],
            [
                'name' => 'Crear API REST',
                'description' => 'Desarrollar endpoints para el CRM',
                'project_id' => $createdProjects[3]->id,
                'sprint_id' => $sprints[$createdProjects[3]->id][0]->id,
                'status' => 'in progress',
                'priority' => 'high',
                'estimated_hours' => 16,
                'user_id' => $createdUsers['developer'][4]->id,
                'assigned_by' => $createdUsers['team_leader'][0]->id,
                'assigned_at' => now()->subDays(5),
                'work_started_at' => now()->subDays(4),
                'is_working' => false,
                'total_time_seconds' => 28800, // 8 horas
                'created_by' => $createdUsers['team_leader'][0]->id
            ]
        ];

        $createdTasks = [];

        foreach ($tasks as $taskData) {
            $task = Task::firstOrCreate(
                ['name' => $taskData['name']],
                $taskData
            );
            $createdTasks[] = $task;
        }

        // Crear logs de tiempo para tareas en progreso
        $timeLogs = [
            // Log para tarea de carrito de compras (en progreso)
            [
                'task_id' => $createdTasks[1]->id,
                'user_id' => $createdUsers['developer'][0]->id,
                'started_at' => now()->subDays(1)->setTime(9, 0),
                'action' => 'start',
                'notes' => 'Inicio de trabajo en carrito de compras'
            ],
            // Log para tarea de geolocalización (pausada)
            [
                'task_id' => $createdTasks[4]->id,
                'user_id' => $createdUsers['developer'][1]->id,
                'started_at' => now()->subDays(2)->setTime(10, 0),
                'paused_at' => now()->subDays(2)->setTime(16, 0),
                'action' => 'pause',
                'duration_seconds' => 21600, // 6 horas
                'notes' => 'Sesión pausada para revisión de requerimientos'
            ],
            // Log para filtros avanzados (en progreso)
            [
                'task_id' => $createdTasks[6]->id,
                'user_id' => $createdUsers['developer'][3]->id,
                'started_at' => now()->subHours(4)->setTime(14, 0),
                'action' => 'start',
                'notes' => 'Inicio de implementación de filtros'
            ]
        ];

        foreach ($timeLogs as $logData) {
            TaskTimeLog::firstOrCreate(
                [
                    'task_id' => $logData['task_id'],
                    'user_id' => $logData['user_id'],
                    'action' => $logData['action']
                ],
                $logData
            );
        }

        $this->command->info('✅ Datos de prueba creados exitosamente!');
        $this->command->info('');
        $this->command->info('🎯 CREDENCIALES DE PRUEBA:');
        $this->command->info('');
        $this->command->info('👨‍💻 ADMINISTRADORES:');
        $this->command->info('   admin@example.com / password');
        $this->command->info('   admin2@example.com / password');
        $this->command->info('');
        $this->command->info('👨‍💼 TEAM LEADERS:');
        $this->command->info('   teamleader1@example.com / password');
        $this->command->info('   teamleader2@example.com / password');
        $this->command->info('   teamleader3@example.com / password');
        $this->command->info('');
        $this->command->info('👨‍💻 DESARROLLADORES:');
        $this->command->info('   developer1@example.com / password');
        $this->command->info('   developer2@example.com / password');
        $this->command->info('   developer3@example.com / password');
        $this->command->info('   developer4@example.com / password');
        $this->command->info('   developer5@example.com / password');
        $this->command->info('');
        $this->command->info('👨‍💻 QA:');
        $this->command->info('   carlos.rodriguez270@test.com / password');
        $this->command->info('   qa1@test.com / password');
        $this->command->info('');
        $this->command->info('📊 DATOS CREADOS:');
        $this->command->info('   - 9 usuarios (2 admin, 3 team leaders, 5 developers, 2 QA)');
        $this->command->info('   - 4 proyectos');
        $this->command->info('   - 8 sprints (2 por proyecto)');
        $this->command->info('   - 10 tareas con diferentes estados');
        $this->command->info('   - 3 logs de tiempo');
        $this->command->info('');
        $this->command->info('🚀 ¡Listo para probar todas las funcionalidades!');
    }
} 
<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== GENERANDO DATOS BÁSICOS DE PRUEBA ===\n\n";

try {
    // Inicializar Laravel
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "1. Limpiando datos existentes...\n";
    
    // Limpiar datos existentes (cuidadosamente)
    \App\Models\Task::truncate();
    \App\Models\Sprint::truncate();
    \App\Models\Project::truncate();
    
    // Obtener el primer usuario (admin) y eliminar el resto
    $adminUser = \App\Models\User::first();
    if ($adminUser) {
        \App\Models\User::where('id', '!=', $adminUser->id)->delete();
    }
    
    \App\Models\PaymentReport::truncate();
    \App\Models\DeveloperActivityLog::truncate();
    
    echo "✓ Datos limpiados\n\n";
    
    echo "2. Creando usuarios básicos...\n";
    
    $users = [];
    $userNames = [
        'Maria Gonzalez', 'Carlos Rodriguez', 'Ana Lopez', 'Luis Perez', 'Sofia Garcia',
        'Juan Martinez', 'Carmen Ruiz', 'Roberto Silva', 'Elena Vargas', 'Diego Morales'
    ];
    
    $roles = ['admin', 'team_leader', 'developer'];
    $statuses = ['active', 'inactive', 'paused', 'completed'];
    $workTimes = ['full', 'part time'];
    
    foreach ($userNames as $index => $name) {
        $role = $roles[array_rand($roles)];
        $status = $statuses[array_rand($statuses)];
        $workTime = $workTimes[array_rand($workTimes)];
        $hourValue = rand(15, 50);
        
        $user = \App\Models\User::create([
            'name' => $name,
            'nickname' => explode(' ', $name)[0] . rand(1, 999),
            'email' => strtolower(str_replace(' ', '.', $name)) . rand(1, 999) . '@test.com',
            'password' => bcrypt('password123'),
            'hour_value' => $hourValue,
            'work_time' => $workTime,
            'status' => $status,
            'email_verified_at' => now(),
        ]);
        
        // Asignar rol
        $roleModel = \App\Models\Role::where('name', $role)->first();
        if ($roleModel) {
            $user->roles()->attach($roleModel->id);
        }
        
        $users[] = $user;
        echo "✓ Usuario creado: {$name} ({$role}) - {$status}\n";
    }
    
    echo "\n3. Creando proyectos básicos...\n";
    
    $projects = [];
    $projectNames = [
        'E-commerce Platform', 'Mobile Banking App', 'CRM System', 'Inventory Management',
        'Learning Management System', 'Social Media Platform', 'Real Estate Portal', 'Food Delivery App'
    ];
    
    $projectStatuses = ['active', 'inactive', 'completed', 'cancelled', 'paused'];
    
    foreach ($projectNames as $index => $name) {
        $status = $projectStatuses[array_rand($projectStatuses)];
        $developers = array_rand($users, rand(2, 4));
        $developers = is_array($developers) ? $developers : [$developers];
        
        $project = \App\Models\Project::create([
            'name' => $name . ' v' . rand(1, 3),
            'description' => 'Descripción del proyecto ' . $name . ' con funcionalidades avanzadas.',
            'status' => $status,
            'created_by' => $users[array_rand($users)]->id,
        ]);
        
        // Asignar desarrolladores
        foreach ($developers as $devIndex) {
            if (isset($users[$devIndex])) {
                $project->users()->attach($users[$devIndex]->id);
            }
        }
        
        $projects[] = $project;
        echo "✓ Proyecto creado: {$name} ({$status}) - " . count($developers) . " desarrolladores\n";
    }
    
    echo "\n4. Creando sprints básicos...\n";
    
    $sprints = [];
    $sprintStatuses = ['active', 'completed', 'cancelled'];
    
    foreach ($projects as $project) {
        $sprintCount = rand(2, 4);
        
        for ($i = 1; $i <= $sprintCount; $i++) {
            $status = $sprintStatuses[array_rand($sprintStatuses)];
            $startDate = now()->subDays(rand(1, 365)); // 1 año atrás
            $endDate = $startDate->copy()->addDays(rand(7, 21));
            
            $sprint = \App\Models\Sprint::create([
                'name' => "Sprint {$i}",
                'description' => "Descripción del sprint {$i} del proyecto {$project->name}",
                'goal' => "Objetivo del sprint {$i}: Completar funcionalidades principales",
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
                'project_id' => $project->id,
            ]);
            
            $sprints[] = $sprint;
        }
    }
    
    echo "✓ " . count($sprints) . " sprints creados\n";
    
    echo "\n5. Creando tareas básicas...\n";
    
    $tasks = [];
    $taskNames = [
        'Implementar autenticación', 'Crear API endpoints', 'Diseñar base de datos', 'Configurar CI/CD',
        'Optimizar consultas SQL', 'Implementar cache', 'Crear tests unitarios', 'Configurar logging'
    ];
    
    $taskStatuses = ['to do', 'in progress', 'done'];
    $priorities = ['low', 'medium', 'high'];
    
    foreach ($sprints as $sprint) {
        $taskCount = rand(3, 8);
        
        for ($i = 1; $i <= $taskCount; $i++) {
            $status = $taskStatuses[array_rand($taskStatuses)];
            $priority = $priorities[array_rand($priorities)];
            $assignedUser = $users[array_rand($users)];
            
            $estimatedHours = rand(2, 20);
            $actualHours = $status === 'done' ? rand(1, $estimatedHours * 2) : null;
            
            $task = \App\Models\Task::create([
                'name' => $taskNames[array_rand($taskNames)] . ' - ' . $i,
                'description' => 'Descripción detallada de la tarea con requisitos específicos.',
                'status' => $status,
                'priority' => $priority,
                'estimated_hours' => $estimatedHours,
                'actual_hours' => $actualHours,
                'assigned_to' => $assignedUser->id,
                'sprint_id' => $sprint->id,
                'project_id' => $sprint->project_id,
                'created_by' => $users[array_rand($users)]->id,
            ]);
            
            // Agregar fechas de inicio y fin para tareas completadas
            if ($status === 'done') {
                $startDate = $sprint->start_date->copy()->addDays(rand(0, 14));
                $endDate = $startDate->copy()->addDays(rand(1, 7));
                
                $task->update([
                    'actual_start_date' => $startDate,
                    'actual_finish_date' => $endDate,
                ]);
            } elseif ($status === 'in progress') {
                $startDate = $sprint->start_date->copy()->addDays(rand(0, 14));
                $task->update([
                    'actual_start_date' => $startDate,
                ]);
            }
            
            $tasks[] = $task;
        }
    }
    
    echo "✓ " . count($tasks) . " tareas creadas\n";
    
    echo "\n6. Creando logs de actividad básicos...\n";
    
    $activityTypes = ['login', 'logout', 'task_start', 'task_pause', 'task_resume', 'task_finish'];
    
    foreach ($users as $user) {
        // Generar actividad para los últimos 30 días
        $startDate = now()->subDays(30);
        $endDate = now();
        
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            // Generar 1-3 sesiones por día
            $sessionsPerDay = rand(1, 3);
            
            for ($session = 0; $session < $sessionsPerDay; $session++) {
                $sessionStart = $currentDate->copy()->addHours(rand(6, 22))->addMinutes(rand(0, 59));
                $sessionDuration = rand(30, 240); // 30 minutos a 4 horas
                $sessionEnd = $sessionStart->copy()->addMinutes($sessionDuration);
                
                // Crear log de inicio de sesión
                \App\Models\DeveloperActivityLog::create([
                    'user_id' => $user->id,
                    'activity_type' => 'login',
                    'activity_time_colombia' => $sessionStart,
                    'activity_time_utc' => $sessionStart->copy()->subHours(5), // Colombia está en UTC-5
                    'session_duration_minutes' => $sessionDuration,
                    'time_period' => $sessionStart->hour < 12 ? 'morning' : ($sessionStart->hour < 17 ? 'afternoon' : ($sessionStart->hour < 21 ? 'evening' : 'night')),
                    'metadata' => json_encode([
                        'timezone' => 'America/Bogota',
                        'location' => 'Bogotá, Colombia',
                        'device' => 'Desktop'
                    ])
                ]);
                
                // Crear logs de actividad durante la sesión
                $activityCount = rand(3, 10);
                for ($activity = 0; $activity < $activityCount; $activity++) {
                    $activityTime = $sessionStart->copy()->addMinutes(rand(0, $sessionDuration));
                    $activityType = $activityTypes[array_rand($activityTypes)];
                    
                    \App\Models\DeveloperActivityLog::create([
                        'user_id' => $user->id,
                        'activity_type' => $activityType,
                        'activity_time_colombia' => $activityTime,
                        'activity_time_utc' => $activityTime->copy()->subHours(5),
                        'time_period' => $activityTime->hour < 12 ? 'morning' : ($activityTime->hour < 17 ? 'afternoon' : ($activityTime->hour < 21 ? 'evening' : 'night')),
                        'metadata' => json_encode([
                            'timezone' => 'America/Bogota',
                            'location' => 'Bogotá, Colombia',
                            'device' => 'Desktop',
                            'task_id' => $tasks[array_rand($tasks)]->id ?? null,
                            'project_id' => $projects[array_rand($projects)]->id ?? null
                        ])
                    ]);
                }
                
                // Crear log de fin de sesión
                \App\Models\DeveloperActivityLog::create([
                    'user_id' => $user->id,
                    'activity_type' => 'logout',
                    'activity_time_colombia' => $sessionEnd,
                    'activity_time_utc' => $sessionEnd->copy()->subHours(5),
                    'session_duration_minutes' => $sessionDuration,
                    'time_period' => $sessionEnd->hour < 12 ? 'morning' : ($sessionEnd->hour < 17 ? 'afternoon' : ($sessionEnd->hour < 21 ? 'evening' : 'night')),
                    'metadata' => json_encode([
                        'timezone' => 'America/Bogota',
                        'location' => 'Bogotá, Colombia',
                        'device' => 'Desktop'
                    ])
                ]);
            }
            
            $currentDate->addDay();
        }
    }
    
    echo "✓ Logs de actividad creados\n";
    
    echo "\n7. Creando reportes de pago básicos...\n";
    
    $paymentStatuses = ['pending', 'approved', 'paid', 'cancelled'];
    
    foreach ($users as $user) {
        // Generar reportes mensuales para los últimos 6 meses
        $startDate = now()->subMonths(6)->startOfMonth();
        $endDate = now()->endOfMonth();
        
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $status = $paymentStatuses[array_rand($paymentStatuses)];
            $totalHours = rand(80, 160);
            $totalAmount = $totalHours * $user->hour_value;
            
            $report = \App\Models\PaymentReport::create([
                'user_id' => $user->id,
                'week_start_date' => $currentDate->copy()->startOfWeek(),
                'week_end_date' => $currentDate->copy()->endOfWeek(),
                'total_hours' => $totalHours,
                'hourly_rate' => $user->hour_value,
                'total_payment' => $totalAmount,
                'completed_tasks_count' => rand(5, 15),
                'in_progress_tasks_count' => rand(2, 8),
                'status' => $status,
                'approved_at' => $status === 'approved' || $status === 'paid' ? $currentDate->copy()->addDays(rand(29, 35)) : null,
                'paid_at' => $status === 'paid' ? $currentDate->copy()->addDays(rand(36, 45)) : null,
                'notes' => $status === 'cancelled' ? 'Reporte cancelado por inconsistencias.' : null,
            ]);
            
            $currentDate->addMonth();
        }
    }
    
    echo "✓ Reportes de pago creados\n";
    
    echo "\n8. Estadísticas finales...\n";
    
    $stats = [
        'users' => \App\Models\User::count(),
        'projects' => \App\Models\Project::count(),
        'sprints' => \App\Models\Sprint::count(),
        'tasks' => \App\Models\Task::count(),
        'activity_logs' => \App\Models\DeveloperActivityLog::count(),
        'payment_reports' => \App\Models\PaymentReport::count(),
    ];
    
    echo "📊 ESTADÍSTICAS FINALES:\n";
    foreach ($stats as $key => $value) {
        echo "  - {$key}: {$value}\n";
    }
    
    echo "\n✅ GENERACIÓN DE DATOS BÁSICOS COMPLETADA!\n";
    echo "🎯 DATOS GENERADOS:\n";
    echo "  - Usuarios: {$stats['users']} (con diferentes roles y estados)\n";
    echo "  - Proyectos: {$stats['projects']} (con diferentes estados)\n";
    echo "  - Sprints: {$stats['sprints']} (distribuidos en 1 año)\n";
    echo "  - Tareas: {$stats['tasks']} (con diferentes estados y prioridades)\n";
    echo "  - Logs de actividad: {$stats['activity_logs']} (actividad de 30 días)\n";
    echo "  - Reportes de pago: {$stats['payment_reports']} (reportes mensuales)\n";
    
    echo "\n🔗 Para probar el dashboard:\n";
    echo "  - URL: http://127.0.0.1:8000/developer-activity\n";
    echo "  - Filtros disponibles: fechas, desarrolladores, estados\n";
    echo "  - Exportación: Excel y PDF\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
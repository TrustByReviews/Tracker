<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== GENERANDO DATOS MASIVOS DE PRUEBA ===\n\n";

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
    
    echo "2. Creando usuarios masivos...\n";
    
    $users = [];
    $userNames = [
        'Maria Gonzalez', 'Carlos Rodriguez', 'Ana Lopez', 'Luis Perez', 'Sofia Garcia',
        'Juan Martinez', 'Carmen Ruiz', 'Roberto Silva', 'Elena Vargas', 'Diego Morales',
        'Patricia Herrera', 'Fernando Castro', 'Isabella Torres', 'Miguel Rojas', 'Valentina Jimenez',
        'Alejandro Mendoza', 'Camila Ortiz', 'Sebastian Vega', 'Daniela Paredes', 'Andres Guzman',
        'Laura Moreno', 'Ricardo Salazar', 'Gabriela Rios', 'Felipe Cordoba', 'Natalia Herrera',
        'Oscar Valencia', 'Monica Zapata', 'Hector Duarte', 'Claudia Molina', 'Jorge Pineda'
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
    
    echo "\n3. Creando proyectos masivos...\n";
    
    $projects = [];
    $projectNames = [
        'E-commerce Platform', 'Mobile Banking App', 'CRM System', 'Inventory Management',
        'Learning Management System', 'Social Media Platform', 'Real Estate Portal', 'Food Delivery App',
        'Healthcare Management', 'Fitness Tracking App', 'Travel Booking System', 'Event Management',
        'Document Management', 'Customer Support Portal', 'Analytics Dashboard', 'Payment Gateway',
        'Content Management System', 'Email Marketing Tool', 'Project Management Tool', 'Time Tracking App'
    ];
    
    $projectStatuses = ['active', 'inactive', 'completed', 'cancelled', 'paused'];
    
    foreach ($projectNames as $index => $name) {
        $status = $projectStatuses[array_rand($projectStatuses)];
        $developers = array_rand($users, rand(2, 5));
        $developers = is_array($developers) ? $developers : [$developers];
        
        $project = \App\Models\Project::create([
            'name' => $name . ' v' . rand(1, 5),
            'description' => 'Descripción detallada del proyecto ' . $name . ' con funcionalidades avanzadas y integraciones complejas.',
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
    
    echo "\n4. Creando sprints masivos...\n";
    
    $sprints = [];
    $sprintStatuses = ['active', 'completed', 'cancelled'];
    
    foreach ($projects as $project) {
        $sprintCount = rand(2, 8);
        
        for ($i = 1; $i <= $sprintCount; $i++) {
            $status = $sprintStatuses[array_rand($sprintStatuses)];
            $startDate = now()->subDays(rand(1, 1825)); // 5 años atrás
            $endDate = $startDate->copy()->addDays(rand(7, 21));
            
            $sprint = \App\Models\Sprint::create([
                'name' => "Sprint {$i}",
                'description' => "Descripción del sprint {$i} del proyecto {$project->name}",
                'goal' => "Objetivo del sprint {$i}: Completar funcionalidades principales del proyecto {$project->name}",
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
                'project_id' => $project->id,
            ]);
            
            $sprints[] = $sprint;
        }
    }
    
    echo "✓ " . count($sprints) . " sprints creados\n";
    
    echo "\n5. Creando tareas masivas...\n";
    
    $tasks = [];
    $taskNames = [
        'Implementar autenticación', 'Crear API endpoints', 'Diseñar base de datos', 'Configurar CI/CD',
        'Optimizar consultas SQL', 'Implementar cache', 'Crear tests unitarios', 'Configurar logging',
        'Implementar paginación', 'Crear documentación', 'Optimizar imágenes', 'Implementar búsqueda',
        'Configurar emails', 'Crear dashboard', 'Implementar notificaciones', 'Configurar backup',
        'Optimizar performance', 'Implementar seguridad', 'Crear reportes', 'Configurar monitoreo'
    ];
    
    $taskStatuses = ['to do', 'in progress', 'ready for test', 'in review', 'rejected', 'done'];
    $priorities = ['low', 'medium', 'high', 'urgent'];
    
    foreach ($sprints as $sprint) {
        $taskCount = rand(5, 20);
        
        for ($i = 1; $i <= $taskCount; $i++) {
            $status = $taskStatuses[array_rand($taskStatuses)];
            $priority = $priorities[array_rand($priorities)];
            $assignedUser = $users[array_rand($users)];
            
            $estimatedHours = rand(2, 40);
            $actualHours = $status === 'done' ? rand(1, $estimatedHours * 2) : null;
            
            $task = \App\Models\Task::create([
                'name' => $taskNames[array_rand($taskNames)] . ' - ' . $i,
                'description' => 'Descripción detallada de la tarea con requisitos específicos y criterios de aceptación.',
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
    
    echo "\n6. Creando logs de actividad masivos...\n";
    
    $activities = [];
    $activityTypes = ['login', 'task_start', 'task_complete', 'break_start', 'break_end', 'logout'];
    
    foreach ($users as $user) {
        // Generar actividad para los últimos 5 años
        $startDate = now()->subYears(5);
        $endDate = now();
        
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            // Generar 1-5 sesiones por día
            $sessionsPerDay = rand(1, 5);
            
            for ($session = 0; $session < $sessionsPerDay; $session++) {
                $sessionStart = $currentDate->copy()->addHours(rand(6, 22))->addMinutes(rand(0, 59));
                $sessionDuration = rand(30, 480); // 30 minutos a 8 horas
                $sessionEnd = $sessionStart->copy()->addMinutes($sessionDuration);
                
                // Crear log de inicio de sesión
                \App\Models\DeveloperActivityLog::create([
                    'user_id' => $user->id,
                    'activity_type' => 'login',
                    'timestamp' => $sessionStart,
                    'session_id' => uniqid(),
                    'ip_address' => '192.168.1.' . rand(1, 255),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'metadata' => json_encode([
                        'timezone' => 'America/Bogota',
                        'location' => 'Bogotá, Colombia',
                        'device' => 'Desktop'
                    ])
                ]);
                
                // Crear logs de actividad durante la sesión
                $activityCount = rand(5, 20);
                for ($activity = 0; $activity < $activityCount; $activity++) {
                    $activityTime = $sessionStart->copy()->addMinutes(rand(0, $sessionDuration));
                    $activityType = $activityTypes[array_rand($activityTypes)];
                    
                    \App\Models\DeveloperActivityLog::create([
                        'user_id' => $user->id,
                        'activity_type' => $activityType,
                        'timestamp' => $activityTime,
                        'session_id' => uniqid(),
                        'ip_address' => '192.168.1.' . rand(1, 255),
                        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
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
                    'timestamp' => $sessionEnd,
                    'session_id' => uniqid(),
                    'ip_address' => '192.168.1.' . rand(1, 255),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'metadata' => json_encode([
                        'timezone' => 'America/Bogota',
                        'location' => 'Bogotá, Colombia',
                        'device' => 'Desktop',
                        'session_duration_minutes' => $sessionDuration
                    ])
                ]);
            }
            
            $currentDate->addDay();
        }
    }
    
    echo "✓ Logs de actividad creados\n";
    
    echo "\n7. Creando reportes de pago masivos...\n";
    
    $paymentReports = [];
    $paymentStatuses = ['pending', 'approved', 'paid', 'cancelled'];
    
    foreach ($users as $user) {
        // Generar reportes mensuales para los últimos 5 años
        $startDate = now()->subYears(5)->startOfMonth();
        $endDate = now()->endOfMonth();
        
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $status = $paymentStatuses[array_rand($paymentStatuses)];
            $totalHours = rand(80, 200);
            $totalAmount = $totalHours * $user->hour_value;
            
            $report = \App\Models\PaymentReport::create([
                'user_id' => $user->id,
                'period_start' => $currentDate->copy()->startOfMonth(),
                'period_end' => $currentDate->copy()->endOfMonth(),
                'total_hours' => $totalHours,
                'hourly_rate' => $user->hour_value,
                'total_amount' => $totalAmount,
                'status' => $status,
                'submitted_at' => $currentDate->copy()->addDays(rand(1, 28)),
                'approved_at' => $status === 'approved' || $status === 'paid' ? $currentDate->copy()->addDays(rand(29, 35)) : null,
                'paid_at' => $status === 'paid' ? $currentDate->copy()->addDays(rand(36, 45)) : null,
                'notes' => $status === 'cancelled' ? 'Reporte cancelado por inconsistencias en las horas registradas.' : null,
            ]);
            
            $paymentReports[] = $report;
            $currentDate->addMonth();
        }
    }
    
    echo "✓ " . count($paymentReports) . " reportes de pago creados\n";
    
    echo "\n8. Generando errores de sintaxis y base de datos intencionales...\n";
    
    // Intentar crear registros con datos inválidos para probar validaciones
    try {
        \App\Models\User::create([
            'name' => '', // Nombre vacío
            'email' => 'invalid-email', // Email inválido
            'password' => '123', // Contraseña muy corta
        ]);
    } catch (Exception $e) {
        echo "✓ Error de validación capturado: " . $e->getMessage() . "\n";
    }
    
    try {
        \App\Models\Project::create([
            'name' => str_repeat('A', 1000), // Nombre muy largo
            'description' => null, // Descripción nula
        ]);
    } catch (Exception $e) {
        echo "✓ Error de validación capturado: " . $e->getMessage() . "\n";
    }
    
    try {
        \App\Models\Task::create([
            'name' => 'Tarea sin sprint',
            'sprint_id' => 99999, // Sprint inexistente
            'project_id' => 99999, // Proyecto inexistente
        ]);
    } catch (Exception $e) {
        echo "✓ Error de clave foránea capturado: " . $e->getMessage() . "\n";
    }
    
    // Intentar consultas SQL problemáticas
    try {
        $result = \DB::select("SELECT * FROM users WHERE name = 'Maria' OR 1=1; DROP TABLE users;");
        echo "✓ Consulta SQL problemática ejecutada\n";
    } catch (Exception $e) {
        echo "✓ Error SQL capturado: " . $e->getMessage() . "\n";
    }
    
    echo "\n9. Estadísticas finales...\n";
    
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
    
    echo "\n✅ GENERACIÓN DE DATOS COMPLETADA EXITOSAMENTE!\n";
    echo "🎯 DATOS GENERADOS:\n";
    echo "  - Usuarios: {$stats['users']} (con diferentes roles y estados)\n";
    echo "  - Proyectos: {$stats['projects']} (con diferentes estados)\n";
    echo "  - Sprints: {$stats['sprints']} (distribuidos en 5 años)\n";
    echo "  - Tareas: {$stats['tasks']} (con diferentes estados y prioridades)\n";
    echo "  - Logs de actividad: {$stats['activity_logs']} (actividad real de 5 años)\n";
    echo "  - Reportes de pago: {$stats['payment_reports']} (reportes mensuales)\n";
    
    echo "\n🔗 Para probar el dashboard:\n";
    echo "  - URL: http://127.0.0.1:8000/developer-activity\n";
    echo "  - Filtros disponibles: fechas, desarrolladores, estados\n";
    echo "  - Exportación: Excel y PDF\n";
    
    echo "\n⚠️ NOTA: Se generaron errores intencionales para probar la robustez del sistema\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
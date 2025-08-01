<?php

/**
 * Script simple para crear datos básicos usando solo las columnas existentes
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "📊 CREANDO DATOS SIMPLES DEL SISTEMA\n";
echo "====================================\n\n";

// Configurar la aplicación
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "✅ Aplicación inicializada\n\n";

try {
    // Verificar que los usuarios existen
    echo "🔍 Verificando usuarios existentes...\n";
    $users = \App\Models\User::with('roles')->get();
    echo "✅ Usuarios encontrados: " . $users->count() . "\n\n";
    
    // Separar usuarios por rol
    $admins = [];
    $teamLeaders = [];
    $developers = [];
    
    foreach ($users as $user) {
        $role = $user->roles->first();
        if ($role) {
            switch ($role->name) {
                case 'admin':
                    $admins[] = $user;
                    break;
                case 'team_leader':
                    $teamLeaders[] = $user;
                    break;
                case 'developer':
                    $developers[] = $user;
                    break;
            }
        }
    }
    
    echo "📋 Usuarios por rol:\n";
    echo "   Administradores: " . count($admins) . "\n";
    echo "   Team Leaders: " . count($teamLeaders) . "\n";
    echo "   Desarrolladores: " . count($developers) . "\n\n";
    
    // Crear proyectos si no existen
    echo "🏗️  Creando proyectos...\n";
    $projects = [
        [
            'name' => 'E-commerce Platform',
            'description' => 'Plataforma de comercio electrónico completa',
            'status' => 'active'
        ],
        [
            'name' => 'Mobile App',
            'description' => 'Aplicación móvil con geolocalización',
            'status' => 'active'
        ],
        [
            'name' => 'Analytics Dashboard',
            'description' => 'Panel de análisis con gráficos',
            'status' => 'active'
        ]
    ];
    
    $createdProjects = [];
    foreach ($projects as $projectData) {
        $project = \App\Models\Project::firstOrCreate(
            ['name' => $projectData['name']],
            $projectData
        );
        $createdProjects[] = $project;
        echo "   ✅ Proyecto creado: {$project->name}\n";
    }
    echo "\n";
    
    // Crear sprints para cada proyecto
    echo "📅 Creando sprints...\n";
    $sprints = [];
    foreach ($createdProjects as $project) {
        $sprint1 = \App\Models\Sprint::firstOrCreate(
            [
                'project_id' => $project->id,
                'name' => 'Sprint 1'
            ],
            [
                'project_id' => $project->id,
                'name' => 'Sprint 1',
                'goal' => 'Completar funcionalidades básicas del proyecto ' . $project->name,
                'start_date' => now()->subDays(7),
                'end_date' => now()->addDays(7)
            ]
        );
        
        $sprint2 = \App\Models\Sprint::firstOrCreate(
            [
                'project_id' => $project->id,
                'name' => 'Sprint 2'
            ],
            [
                'project_id' => $project->id,
                'name' => 'Sprint 2',
                'goal' => 'Implementar funcionalidades avanzadas del proyecto ' . $project->name,
                'start_date' => now()->addDays(8),
                'end_date' => now()->addDays(21)
            ]
        );
        
        $sprints[$project->id] = [$sprint1, $sprint2];
        echo "   ✅ Sprints creados para: {$project->name}\n";
    }
    echo "\n";
    
    // Crear tareas usando solo las columnas que existen
    echo "📝 Creando tareas...\n";
    $tasks = [
        // E-commerce Platform
        [
            'name' => 'Implementar sistema de login',
            'description' => 'Crear sistema de autenticación con JWT',
            'sprint_id' => $sprints[$createdProjects[0]->id][0]->id,
            'status' => 'to do',
            'priority' => 'high',
            'category' => 'backend',
            'story_points' => 8,
            'estimated_hours' => 8
        ],
        [
            'name' => 'Desarrollar carrito de compras',
            'description' => 'Implementar funcionalidad de carrito',
            'sprint_id' => $sprints[$createdProjects[0]->id][0]->id,
            'status' => 'in progress',
            'priority' => 'high',
            'category' => 'full stack',
            'story_points' => 13,
            'estimated_hours' => 12,
            'user_id' => $developers[0]->id ?? null
        ],
        [
            'name' => 'Integrar pasarela de pagos',
            'description' => 'Integrar Stripe para pagos',
            'sprint_id' => $sprints[$createdProjects[0]->id][0]->id,
            'status' => 'done',
            'priority' => 'medium',
            'category' => 'backend',
            'story_points' => 10,
            'estimated_hours' => 10,
            'user_id' => $developers[1]->id ?? null
        ],
        
        // Mobile App
        [
            'name' => 'Diseñar interfaz de usuario',
            'description' => 'Crear mockups de la interfaz móvil',
            'sprint_id' => $sprints[$createdProjects[1]->id][0]->id,
            'status' => 'to do',
            'priority' => 'high',
            'category' => 'design',
            'story_points' => 5,
            'estimated_hours' => 6
        ],
        [
            'name' => 'Implementar geolocalización',
            'description' => 'Agregar funcionalidad de GPS',
            'sprint_id' => $sprints[$createdProjects[1]->id][0]->id,
            'status' => 'in progress',
            'priority' => 'medium',
            'category' => 'full stack',
            'story_points' => 15,
            'estimated_hours' => 15,
            'user_id' => $developers[1]->id ?? null
        ],
        
        // Analytics Dashboard
        [
            'name' => 'Crear gráficos interactivos',
            'description' => 'Implementar gráficos con Chart.js',
            'sprint_id' => $sprints[$createdProjects[2]->id][0]->id,
            'status' => 'done',
            'priority' => 'high',
            'category' => 'frontend',
            'story_points' => 12,
            'estimated_hours' => 14,
            'user_id' => $developers[2]->id ?? null
        ]
    ];
    
    foreach ($tasks as $taskData) {
        $task = \App\Models\Task::firstOrCreate(
            ['name' => $taskData['name']],
            $taskData
        );
        echo "   ✅ Tarea creada: {$task->name} ({$task->status})\n";
    }
    echo "\n";
    
    // Asignar usuarios a proyectos
    echo "👥 Asignando usuarios a proyectos...\n";
    if (!empty($createdProjects) && !empty($teamLeaders) && !empty($developers)) {
        // Proyecto 1: E-commerce
        $createdProjects[0]->users()->sync([
            $teamLeaders[0]->id,
            $developers[0]->id,
            $developers[1]->id
        ]);
        echo "   ✅ Usuarios asignados a: {$createdProjects[0]->name}\n";
        
        // Proyecto 2: Mobile App
        $createdProjects[1]->users()->sync([
            $teamLeaders[1]->id,
            $developers[1]->id,
            $developers[2]->id
        ]);
        echo "   ✅ Usuarios asignados a: {$createdProjects[1]->name}\n";
        
        // Proyecto 3: Analytics Dashboard
        $createdProjects[2]->users()->sync([
            $teamLeaders[2]->id,
            $developers[2]->id,
            $developers[3]->id ?? $developers[0]->id
        ]);
        echo "   ✅ Usuarios asignados a: {$createdProjects[2]->name}\n";
    }
    echo "\n";
    
    // Verificar datos creados
    echo "📊 Verificando datos creados...\n";
    $projectCount = \App\Models\Project::count();
    $sprintCount = \App\Models\Sprint::count();
    $taskCount = \App\Models\Task::count();
    
    echo "✅ Proyectos: {$projectCount}\n";
    echo "✅ Sprints: {$sprintCount}\n";
    echo "✅ Tareas: {$taskCount}\n\n";
    
    echo "🎉 ¡DATOS SIMPLES CREADOS EXITOSAMENTE!\n";
    echo "=======================================\n\n";
    
    echo "🎯 CREDENCIALES DISPONIBLES:\n";
    echo "----------------------------\n";
    if (!empty($admins)) {
        echo "👨‍💻 ADMINISTRADORES:\n";
        foreach ($admins as $admin) {
            echo "   {$admin->email} / password\n";
        }
        echo "\n";
    }
    
    if (!empty($teamLeaders)) {
        echo "👨‍💼 TEAM LEADERS:\n";
        foreach ($teamLeaders as $tl) {
            echo "   {$tl->email} / password\n";
        }
        echo "\n";
    }
    
    if (!empty($developers)) {
        echo "👨‍💻 DESARROLLADORES:\n";
        foreach ($developers as $dev) {
            echo "   {$dev->email} / password\n";
        }
        echo "\n";
    }
    
    echo "🚀 PRÓXIMOS PASOS:\n";
    echo "==================\n";
    echo "1. Compilar assets: npm run build\n";
    echo "2. Iniciar servidor: php artisan serve\n";
    echo "3. Acceder a: http://localhost:8000\n";
    echo "4. Usar las credenciales de arriba para probar\n\n";
    
    echo "✅ ¡Listo para probar el sistema con datos básicos!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n💡 SUGERENCIAS:\n";
    echo "1. Verifica que las migraciones básicas se ejecutaron\n";
    echo "2. Verifica que los usuarios existen\n";
    echo "3. Ejecuta: php artisan migrate:status\n";
} 
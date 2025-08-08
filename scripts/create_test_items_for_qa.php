<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Task;
use App\Models\Bug;
use App\Models\Project;
use App\Models\Sprint;
use App\Services\NotificationService;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CREANDO ITEMS DE PRUEBA PARA QA ===\n\n";

try {
    // Buscar usuarios existentes
    $developer = User::whereHas('roles', function ($query) {
        $query->where('value', 'developer');
    })->where('status', 'active')->first();
    
    $qa = User::where('email', 'qa@tracker.com')->first();
    $teamLeader = User::whereHas('roles', function ($query) {
        $query->where('value', 'team_leader');
    })->first();
    
    if (!$developer) {
        echo "❌ No se encontró ningún desarrollador activo\n";
        exit(1);
    }
    
    if (!$qa) {
        echo "❌ Usuario qa@tracker.com no encontrado\n";
        exit(1);
    }
    
    if (!$teamLeader) {
        echo "❌ No se encontró ningún team leader activo\n";
        exit(1);
    }
    
    echo "✅ Usuarios encontrados:\n";
    echo "   - Developer: {$developer->name} ({$developer->email})\n";
    echo "   - QA: {$qa->name} ({$qa->email})\n";
    echo "   - Team Leader: {$teamLeader->name} ({$teamLeader->email})\n";
    
    // Buscar proyectos asignados al QA
    $projects = $qa->projects;
    if ($projects->isEmpty()) {
        echo "❌ El QA no tiene proyectos asignados\n";
        exit(1);
    }
    
    $project = $projects->first();
    echo "✅ Proyecto encontrado: {$project->name}\n";
    
    // Buscar sprint o crear uno
    $sprint = $project->sprints()->first();
    if (!$sprint) {
        echo "❌ No hay sprints en el proyecto\n";
        exit(1);
    }
    
    echo "✅ Sprint encontrado: {$sprint->name}\n";
    
    // Crear tareas finalizadas para testing
    $tasks = [
        [
            'name' => 'Implementar login de usuarios',
            'description' => 'Crear sistema de autenticación con validación de credenciales',
            'priority' => 'high',
            'status' => 'done',
            'qa_status' => 'ready_for_test'
        ],
        [
            'name' => 'Diseñar interfaz de dashboard',
            'description' => 'Crear mockups y prototipos del dashboard principal',
            'priority' => 'medium',
            'status' => 'done',
            'qa_status' => 'ready_for_test'
        ],
        [
            'name' => 'Configurar base de datos',
            'description' => 'Instalar y configurar PostgreSQL con migraciones',
            'priority' => 'high',
            'status' => 'done',
            'qa_status' => 'ready_for_test'
        ]
    ];
    
    // Crear bugs finalizados para testing
    $bugs = [
        [
            'title' => 'Error en validación de formulario',
            'description' => 'El formulario de registro no valida correctamente el email',
            'importance' => 'high',
            'status' => 'resolved',
            'qa_status' => 'ready_for_test'
        ],
        [
            'title' => 'Problema de rendimiento en listado',
            'description' => 'La lista de tareas tarda mucho en cargar con muchos registros',
            'importance' => 'medium',
            'status' => 'resolved',
            'qa_status' => 'ready_for_test'
        ]
    ];
    
    echo "\n📋 CREANDO TAREAS FINALIZADAS:\n";
    foreach ($tasks as $taskData) {
        $task = Task::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => $taskData['name'],
            'description' => $taskData['description'],
            'priority' => $taskData['priority'],
            'status' => $taskData['status'],
            'qa_status' => $taskData['qa_status'],
            'project_id' => $project->id,
            'sprint_id' => $sprint->id,
            'user_id' => $developer->id,
            'assigned_to' => $developer->id,
            'qa_assigned_to' => $qa->id,
            'estimated_hours' => rand(2, 8),
            'actual_hours' => rand(3, 10),
            'created_at' => now()->subDays(rand(1, 7)),
            'updated_at' => now()->subHours(rand(1, 24))
        ]);
        
        echo "   ✅ Tarea creada: {$task->name} (QA Status: {$task->qa_status})\n";
    }
    
    echo "\n🐛 CREANDO BUGS FINALIZADOS:\n";
    foreach ($bugs as $bugData) {
        $bug = Bug::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'title' => $bugData['title'],
            'description' => $bugData['description'],
            'importance' => $bugData['importance'],
            'status' => $bugData['status'],
            'qa_status' => $bugData['qa_status'],
            'project_id' => $project->id,
            'sprint_id' => $sprint->id,
            'user_id' => $developer->id,
            'assigned_to' => $developer->id,
            'qa_assigned_to' => $qa->id,
            'created_at' => now()->subDays(rand(1, 7)),
            'updated_at' => now()->subHours(rand(1, 24))
        ]);
        
        echo "   ✅ Bug creado: {$bug->title} (QA Status: {$bug->qa_status})\n";
    }
    
    // Crear notificaciones para el QA
    $notificationService = new NotificationService();
    
    $finishedTasks = Task::where('qa_status', 'ready_for_test')->get();
    $finishedBugs = Bug::where('qa_status', 'ready_for_test')->get();
    
    echo "\n🔔 CREANDO NOTIFICACIONES:\n";
    
    foreach ($finishedTasks as $task) {
        $notificationService->notifyTaskReadyForQa($task);
        echo "   ✅ Notificación creada para tarea: {$task->name}\n";
    }
    
    foreach ($finishedBugs as $bug) {
        $notificationService->notifyBugReadyForQa($bug);
        echo "   ✅ Notificación creada para bug: {$bug->title}\n";
    }
    
    // Verificar estado final
    $totalTasks = Task::where('qa_status', 'ready_for_test')->count();
    $totalBugs = Bug::where('qa_status', 'ready_for_test')->count();
    $totalNotifications = $qa->notifications()->count();
    
    echo "\n📊 ESTADO FINAL:\n";
    echo "   - Tareas listas para testing: {$totalTasks}\n";
    echo "   - Bugs listos para testing: {$totalBugs}\n";
    echo "   - Notificaciones para QA: {$totalNotifications}\n";
    
    echo "\n🎯 CREDENCIALES PARA TESTING:\n";
    echo "   - QA: qa@tracker.com / password\n";
    echo "   - Developer: {$developer->email} / password\n";
    echo "   - Team Leader: {$teamLeader->email} / password\n";
    
    echo "\n🔗 URLs PARA TESTING:\n";
    echo "   - Dashboard QA: http://127.0.0.1:8000/dashboard\n";
    echo "   - Finished Items: http://127.0.0.1:8000/qa/finished-items\n";
    echo "   - Notifications: Campana en esquina superior derecha\n";
    
    echo "\n✅ ¡ITEMS DE PRUEBA CREADOS EXITOSAMENTE!\n";
    echo "   El QA ahora puede probar el cronómetro de testing en la vista 'Finished Items'\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 
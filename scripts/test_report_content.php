<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PRUEBA DE CONTENIDO DE REPORTES ===\n\n";

// Autenticar como admin
$admin = \App\Models\User::where('email', 'admin@admin.com')->first();
if (!$admin) {
    echo "❌ Usuario admin no encontrado\n";
    exit(1);
}

Auth::login($admin);
echo "✅ Usuario autenticado: {$admin->name}\n\n";

// Crear datos de prueba si no existen
echo "🔧 Preparando datos de prueba...\n";

// Crear proyecto de prueba
$project = \App\Models\Project::firstOrCreate([
    'name' => 'Proyecto de Prueba Reportes',
    'description' => 'Proyecto para probar reportes',
], [
    'status' => 'active',
    'start_date' => now()->subDays(30),
    'end_date' => now()->addDays(30),
]);

// Crear sprint de prueba
$sprint = \App\Models\Sprint::firstOrCreate([
    'name' => 'Sprint de Prueba',
    'project_id' => $project->id,
], [
    'start_date' => now()->subDays(15),
    'end_date' => now()->addDays(15),
    'status' => 'active',
]);

// Crear desarrollador de prueba
$developer = \App\Models\User::firstOrCreate([
    'email' => 'dev.test@example.com',
], [
    'name' => 'Desarrollador de Prueba',
    'password' => bcrypt('password'),
    'hour_value' => 25.00,
]);

// Asignar rol de developer
$developerRole = \App\Models\Role::where('name', 'developer')->first();
if ($developerRole) {
    $developer->roles()->sync([$developerRole->id]);
}

// Crear QA de prueba
$qa = \App\Models\User::firstOrCreate([
    'email' => 'qa.test@example.com',
], [
    'name' => 'QA de Prueba',
    'password' => bcrypt('password'),
    'hour_value' => 20.00,
]);

// Asignar rol de QA
$qaRole = \App\Models\Role::where('name', 'qa')->first();
if ($qaRole) {
    $qa->roles()->sync([$qaRole->id]);
}

// Asignar usuarios al proyecto
$project->users()->sync([$developer->id, $qa->id]);

// Crear tareas de prueba
$tasks = [
    [
        'name' => 'Tarea Completada 1',
        'description' => 'Tarea completada para reporte',
        'status' => 'done',
        'actual_hours' => 8.5,
        'actual_finish' => now()->subDays(5),
        'total_time_seconds' => 30600, // 8.5 horas
    ],
    [
        'name' => 'Tarea Completada 2',
        'description' => 'Otra tarea completada',
        'status' => 'done',
        'actual_hours' => 12.0,
        'actual_finish' => now()->subDays(3),
        'total_time_seconds' => 43200, // 12 horas
    ],
    [
        'name' => 'Tarea en Progreso',
        'description' => 'Tarea en progreso para reporte',
        'status' => 'in progress',
        'actual_hours' => 4.0,
        'actual_start' => now()->subDays(2),
        'total_time_seconds' => 14400, // 4 horas
    ],
];

foreach ($tasks as $taskData) {
    $task = \App\Models\Task::firstOrCreate([
        'name' => $taskData['name'],
        'sprint_id' => $sprint->id,
    ], array_merge($taskData, [
        'assigned_to' => $developer->id,
        'priority' => 'medium',
        'estimated_hours' => 10,
    ]));
}

// Crear bugs de prueba
$bugs = [
    [
        'title' => 'Bug Resuelto 1',
        'description' => 'Bug resuelto para reporte',
        'status' => 'resolved',
        'actual_hours' => 3.5,
        'resolved_at' => now()->subDays(4),
        'total_time_seconds' => 12600, // 3.5 horas
    ],
    [
        'title' => 'Bug Resuelto 2',
        'description' => 'Otro bug resuelto',
        'status' => 'resolved',
        'actual_hours' => 6.0,
        'resolved_at' => now()->subDays(2),
        'total_time_seconds' => 21600, // 6 horas
    ],
    [
        'title' => 'Bug en Progreso',
        'description' => 'Bug en progreso para reporte',
        'status' => 'in progress',
        'actual_hours' => 2.0,
        'assigned_at' => now()->subDays(1),
        'total_time_seconds' => 7200, // 2 horas
    ],
];

foreach ($bugs as $bugData) {
    $bug = \App\Models\Bug::firstOrCreate([
        'title' => $bugData['title'],
        'project_id' => $project->id,
    ], array_merge($bugData, [
        'assigned_to' => $developer->id,
        'importance' => 'medium',
        'bug_type' => 'functional',
    ]));
}

// Crear datos de QA testing
$qaTasks = \App\Models\Task::whereIn('name', ['Tarea Completada 1', 'Tarea Completada 2'])->get();
foreach ($qaTasks as $task) {
    $task->update([
        'qa_assigned_to' => $qa->id,
        'qa_status' => 'testing_finished',
        'qa_testing_started_at' => now()->subDays(6),
        'qa_testing_finished_at' => now()->subDays(5),
        'qa_testing_total_seconds' => 7200, // 2 horas de testing
        'qa_notes' => 'Testing completado exitosamente',
    ]);
}

$qaBugs = \App\Models\Bug::whereIn('title', ['Bug Resuelto 1', 'Bug Resuelto 2'])->get();
foreach ($qaBugs as $bug) {
    $bug->update([
        'qa_assigned_to' => $qa->id,
        'qa_status' => 'testing_finished',
        'qa_testing_started_at' => now()->subDays(5),
        'qa_testing_finished_at' => now()->subDays(4),
        'qa_testing_total_seconds' => 3600, // 1 hora de testing
        'qa_notes' => 'Testing de bug completado',
    ]);
}

echo "✅ Datos de prueba creados\n\n";

// Probar generación de reporte
echo "🔍 Probando generación de reporte detallado...\n";

$controller = new \App\Http\Controllers\PaymentController(new \App\Services\PaymentService());

$request = new \Illuminate\Http\Request();
$request->merge([
    'developer_ids' => [$developer->id, $qa->id],
    'start_date' => now()->subDays(30)->format('Y-m-d'),
    'end_date' => now()->format('Y-m-d'),
    'format' => 'view',
]);

try {
    $response = $controller->generateDetailedReport($request);
    $data = json_decode($response->getContent(), true);
    
    if ($data['success']) {
        $reportData = $data['data'];
        echo "✅ Reporte generado exitosamente\n";
        echo "📊 Total de desarrolladores: " . count($reportData['developers']) . "\n";
        echo "💰 Total de ganancias: $" . number_format($reportData['totalEarnings'], 2) . "\n";
        echo "⏰ Total de horas: " . number_format($reportData['totalHours'], 2) . "\n\n";
        
        foreach ($reportData['developers'] as $dev) {
            echo "👤 {$dev['name']} ({$dev['email']})\n";
            echo "   💰 Tarifa por hora: $" . number_format($dev['hour_value'], 2) . "\n";
            echo "   📋 Tareas completadas: {$dev['completed_tasks']}\n";
            echo "   ⏰ Total horas: " . number_format($dev['total_hours'], 2) . "\n";
            echo "   💵 Total ganancias: $" . number_format($dev['total_earnings'], 2) . "\n";
            echo "   📝 Detalles de trabajo:\n";
            
            foreach ($dev['tasks'] as $task) {
                echo "      - {$task['name']} ({$task['type']})\n";
                echo "        Proyecto: {$task['project']}\n";
                echo "        Horas: " . number_format($task['hours'], 2) . "\n";
                echo "        Ganancias: $" . number_format($task['earnings'], 2) . "\n";
                if ($task['completed_at']) {
                    echo "        Completado: " . date('Y-m-d', strtotime($task['completed_at'])) . "\n";
                }
                echo "\n";
            }
            echo "\n";
        }
    } else {
        echo "❌ Error generando reporte\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "✅ Prueba completada\n"; 
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Task;
use App\Models\Bug;
use App\Models\Project;
use App\Models\Sprint;
use Carbon\Carbon;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CREANDO DATOS DE PRUEBA PARA QA TESTING ===\n\n";

try {
    // Buscar QA
    $qa = User::where('email', 'qa@tracker.com')->first();
    
    if (!$qa) {
        echo "❌ Usuario qa@tracker.com no encontrado\n";
        exit(1);
    }
    
    echo "✅ QA encontrado: {$qa->name} ({$qa->email})\n";
    
    // Buscar desarrollador
    $developer = User::where('email', 'sofia.garcia113@test.com')->first();
    
    if (!$developer) {
        echo "❌ Usuario sofia.garcia113@test.com no encontrado\n";
        exit(1);
    }
    
    echo "✅ Developer encontrado: {$developer->name} ({$developer->email})\n";
    
    // Buscar proyecto
    $project = Project::first();
    
    if (!$project) {
        echo "❌ No se encontraron proyectos\n";
        exit(1);
    }
    
    echo "✅ Proyecto encontrado: {$project->name}\n";
    
    // Buscar sprint
    $sprint = Sprint::where('project_id', $project->id)->first();
    
    if (!$sprint) {
        echo "❌ No se encontraron sprints para el proyecto\n";
        exit(1);
    }
    
    echo "✅ Sprint encontrado: {$sprint->name}\n\n";
    
    // Crear tareas testeadas por QA
    echo "📋 CREANDO TAREAS TESTEADAS POR QA:\n";
    
    $qaTasks = [
        [
            'name' => 'Implementar login de usuarios',
            'description' => 'Crear sistema de autenticación con JWT',
            'estimated_hours' => 8,
            'actual_hours' => 6,
            'qa_testing_hours' => 2,
            'qa_status' => 'approved',
            'qa_notes' => 'Funcionalidad completa y bien implementada'
        ],
        [
            'name' => 'Diseñar dashboard principal',
            'description' => 'Crear interfaz del dashboard con métricas',
            'estimated_hours' => 12,
            'actual_hours' => 10,
            'qa_testing_hours' => 3,
            'qa_status' => 'approved',
            'qa_notes' => 'Dashboard responsive y funcional'
        ],
        [
            'name' => 'Configurar base de datos',
            'description' => 'Migraciones y seeders para el proyecto',
            'estimated_hours' => 6,
            'actual_hours' => 4,
            'qa_testing_hours' => 1,
            'qa_status' => 'rejected',
            'qa_notes' => null,
            'qa_rejection_reason' => 'Faltan índices en tablas principales'
        ]
    ];
    
    foreach ($qaTasks as $index => $taskData) {
        $task = Task::create([
            'name' => $taskData['name'],
            'description' => $taskData['description'],
            'status' => 'done',
            'priority' => 'medium',
            'estimated_hours' => $taskData['estimated_hours'],
            'actual_hours' => $taskData['actual_hours'],
            'actual_start' => Carbon::now()->subDays(7 + $index),
            'actual_finish' => Carbon::now()->subDays(5 + $index),
            'user_id' => $developer->id,
            'sprint_id' => $sprint->id,
            'qa_assigned_to' => $qa->id,
            'qa_assigned_at' => Carbon::now()->subDays(4 + $index),
            'qa_status' => $taskData['qa_status'],
            'qa_testing_started_at' => Carbon::now()->subDays(4 + $index)->addHours(2),
            'qa_testing_finished_at' => Carbon::now()->subDays(4 + $index)->addHours(2 + $taskData['qa_testing_hours']),
            'qa_notes' => $taskData['qa_notes'],
            'qa_rejection_reason' => $taskData['qa_rejection_reason'] ?? null,
            'qa_reviewed_by' => $qa->id,
            'qa_reviewed_at' => Carbon::now()->subDays(4 + $index)->addHours(2 + $taskData['qa_testing_hours']),
        ]);
        
        echo "   ✅ Tarea creada: {$task->name}\n";
        echo "      - Horas de testing: {$taskData['qa_testing_hours']}h\n";
        echo "      - Estado: {$taskData['qa_status']}\n";
        echo "      - Ganancias QA: $" . ($taskData['qa_testing_hours'] * $qa->hour_value) . "\n";
    }
    
    // Crear bugs testeados por QA
    echo "\n🐛 CREANDO BUGS TESTEADOS POR QA:\n";
    
    $qaBugs = [
        [
            'title' => 'Error en validación de email',
            'description' => 'No se valida correctamente el formato de email',
            'importance' => 'high',
            'bug_type' => 'frontend',
            'actual_hours' => 2,
            'qa_testing_hours' => 1,
            'qa_status' => 'approved',
            'qa_notes' => 'Validación corregida y funcionando'
        ],
        [
            'title' => 'Problema de rendimiento en listado',
            'description' => 'Listado de usuarios muy lento con muchos registros',
            'importance' => 'medium',
            'bug_type' => 'performance',
            'actual_hours' => 4,
            'qa_testing_hours' => 2,
            'qa_status' => 'approved',
            'qa_notes' => 'Optimización implementada correctamente'
        ],
        [
            'title' => 'Botón de guardar no funciona',
            'description' => 'Botón de guardar formulario no responde',
            'importance' => 'critical',
            'bug_type' => 'frontend',
            'actual_hours' => 1,
            'qa_testing_hours' => 1,
            'qa_status' => 'rejected',
            'qa_notes' => null,
            'qa_rejection_reason' => 'Botón sigue sin funcionar en móviles'
        ]
    ];
    
    foreach ($qaBugs as $index => $bugData) {
        $bug = Bug::create([
            'title' => $bugData['title'],
            'description' => $bugData['description'],
            'status' => 'resolved',
            'importance' => $bugData['importance'],
            'bug_type' => $bugData['bug_type'],
            'actual_hours' => $bugData['actual_hours'],
            'assigned_at' => Carbon::now()->subDays(6 + $index),
            'resolved_at' => Carbon::now()->subDays(3 + $index),
            'assigned_to' => $developer->id,
            'project_id' => $project->id,
            'qa_assigned_to' => $qa->id,
            'qa_assigned_at' => Carbon::now()->subDays(2 + $index),
            'qa_status' => $bugData['qa_status'],
            'qa_testing_started_at' => Carbon::now()->subDays(2 + $index)->addHours(1),
            'qa_testing_finished_at' => Carbon::now()->subDays(2 + $index)->addHours(1 + $bugData['qa_testing_hours']),
            'qa_notes' => $bugData['qa_notes'],
            'qa_rejection_reason' => $bugData['qa_rejection_reason'] ?? null,
            'qa_reviewed_by' => $qa->id,
            'qa_reviewed_at' => Carbon::now()->subDays(2 + $index)->addHours(1 + $bugData['qa_testing_hours']),
        ]);
        
        echo "   ✅ Bug creado: {$bug->title}\n";
        echo "      - Horas de testing: {$bugData['qa_testing_hours']}h\n";
        echo "      - Estado: {$bugData['qa_status']}\n";
        echo "      - Ganancias QA: $" . ($bugData['qa_testing_hours'] * $qa->hour_value) . "\n";
    }
    
    // Calcular totales
    $totalQaTaskHours = array_sum(array_column($qaTasks, 'qa_testing_hours'));
    $totalQaBugHours = array_sum(array_column($qaBugs, 'qa_testing_hours'));
    $totalQaHours = $totalQaTaskHours + $totalQaBugHours;
    $totalQaEarnings = $totalQaHours * $qa->hour_value;
    
    echo "\n💰 RESUMEN DE DATOS CREADOS:\n";
    echo "   - Tareas testeadas: " . count($qaTasks) . "\n";
    echo "   - Bugs testeados: " . count($qaBugs) . "\n";
    echo "   - Total horas de testing: {$totalQaHours}h\n";
    echo "   - Ganancias totales QA: \${$totalQaEarnings}\n";
    
    echo "\n🎯 DATOS DE PRUEBA CREADOS EXITOSAMENTE!\n";
    echo "   Ahora puedes probar el módulo de pagos con datos reales de QA.\n";
    echo "   Ejecuta: php scripts/test_payment_qa_integration.php\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 
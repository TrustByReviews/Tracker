<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Task;

echo "=== Verificación de Tareas Completadas ===\n\n";

// 1. Verificar todas las tareas
echo "1. Verificando todas las tareas...\n";

$allTasks = Task::with(['user', 'sprint.project'])->get();

echo "✅ Total de tareas: " . $allTasks->count() . "\n";

$statusCounts = $allTasks->groupBy('status')->map(function($tasks) {
    return $tasks->count();
});

echo "Distribución por estado:\n";
foreach ($statusCounts as $status => $count) {
    echo "   - {$status}: {$count} tareas\n";
}

echo "\n";

// 2. Verificar tareas completadas
echo "2. Verificando tareas completadas...\n";

$completedTasks = Task::where('status', 'done')->with(['user', 'sprint.project'])->get();

echo "✅ Tareas completadas: " . $completedTasks->count() . "\n";

if ($completedTasks->count() > 0) {
    echo "Detalles de tareas completadas:\n";
    foreach ($completedTasks as $task) {
        echo "   - {$task->name} (Usuario: {$task->user->name}, Proyecto: {$task->sprint->project->name}, Finalizada: {$task->actual_finish})\n";
    }
} else {
    echo "❌ No hay tareas completadas\n";
}

echo "\n";

// 3. Verificar tareas por desarrollador
echo "3. Verificando tareas por desarrollador...\n";

$developers = User::whereHas('roles', function($q) { 
    $q->where('name', 'developer'); 
})->get();

foreach ($developers as $developer) {
    $developerTasks = Task::where('user_id', $developer->id)->get();
    $completedDeveloperTasks = Task::where('user_id', $developer->id)->where('status', 'done')->get();
    
    echo "   - {$developer->name}:\n";
    echo "     * Total tareas: {$developerTasks->count()}\n";
    echo "     * Completadas: {$completedDeveloperTasks->count()}\n";
    
    if ($completedDeveloperTasks->count() > 0) {
        foreach ($completedDeveloperTasks as $task) {
            echo "       - {$task->name} (Finalizada: {$task->actual_finish})\n";
        }
    }
}

echo "\n";

// 4. Verificar tareas completadas en el último mes
echo "4. Verificando tareas completadas en el último mes...\n";

$lastMonth = now()->subMonth();
$recentCompletedTasks = Task::where('status', 'done')
    ->where('actual_finish', '>=', $lastMonth)
    ->with(['user', 'sprint.project'])
    ->get();

echo "✅ Tareas completadas en el último mes: " . $recentCompletedTasks->count() . "\n";

if ($recentCompletedTasks->count() > 0) {
    echo "Detalles:\n";
    foreach ($recentCompletedTasks as $task) {
        echo "   - {$task->name} (Usuario: {$task->user->name}, Finalizada: {$task->actual_finish})\n";
    }
} else {
    echo "❌ No hay tareas completadas en el último mes\n";
}

echo "\n";

// 5. Verificar tareas completadas en la última semana
echo "5. Verificando tareas completadas en la última semana...\n";

$lastWeek = now()->subWeek();
$recentWeekTasks = Task::where('status', 'done')
    ->where('actual_finish', '>=', $lastWeek)
    ->with(['user', 'sprint.project'])
    ->get();

echo "✅ Tareas completadas en la última semana: " . $recentWeekTasks->count() . "\n";

if ($recentWeekTasks->count() > 0) {
    echo "Detalles:\n";
    foreach ($recentWeekTasks as $task) {
        echo "   - {$task->name} (Usuario: {$task->user->name}, Finalizada: {$task->actual_finish})\n";
    }
} else {
    echo "❌ No hay tareas completadas en la última semana\n";
}

echo "\n";

// 6. Crear algunas tareas de prueba si no hay ninguna
echo "6. Verificando si necesitamos crear tareas de prueba...\n";

if ($completedTasks->count() === 0) {
    echo "❌ No hay tareas completadas. Creando tareas de prueba...\n";
    
    // Buscar un desarrollador
    $developer = User::whereHas('roles', function($q) { 
        $q->where('name', 'developer'); 
    })->first();
    
    if ($developer) {
        // Buscar un sprint
        $sprint = \App\Models\Sprint::first();
        
        if ($sprint) {
            // Crear tareas de prueba
            $testTasks = [
                [
                    'name' => 'Tarea de Prueba 1 - Frontend',
                    'description' => 'Desarrollar componentes de interfaz',
                    'estimated_hours' => 8,
                    'actual_hours' => 6,
                    'status' => 'done',
                    'actual_finish' => now()->subDays(3),
                ],
                [
                    'name' => 'Tarea de Prueba 2 - Backend',
                    'description' => 'Implementar API endpoints',
                    'estimated_hours' => 12,
                    'actual_hours' => 10,
                    'status' => 'done',
                    'actual_finish' => now()->subDays(1),
                ],
                [
                    'name' => 'Tarea de Prueba 3 - Testing',
                    'description' => 'Escribir pruebas unitarias',
                    'estimated_hours' => 6,
                    'actual_hours' => 5,
                    'status' => 'done',
                    'actual_finish' => now()->subHours(12),
                ]
            ];
            
            foreach ($testTasks as $taskData) {
                $task = new Task();
                $task->id = \Illuminate\Support\Str::uuid();
                $task->name = $taskData['name'];
                $task->description = $taskData['description'];
                $task->estimated_hours = $taskData['estimated_hours'];
                $task->actual_hours = $taskData['actual_hours'];
                $task->status = $taskData['status'];
                $task->actual_finish = $taskData['actual_finish'];
                $task->user_id = $developer->id;
                $task->sprint_id = $sprint->id;
                $task->save();
                
                echo "   ✅ Creada: {$task->name}\n";
            }
            
            echo "✅ Tareas de prueba creadas exitosamente\n";
        } else {
            echo "❌ No se encontró ningún sprint para crear tareas de prueba\n";
        }
    } else {
        echo "❌ No se encontró ningún desarrollador para crear tareas de prueba\n";
    }
} else {
    echo "✅ Ya hay tareas completadas, no es necesario crear tareas de prueba\n";
}

echo "\n";

// 7. Verificar nuevamente después de crear tareas de prueba
if ($completedTasks->count() === 0) {
    echo "7. Verificando tareas después de crear tareas de prueba...\n";
    
    $newCompletedTasks = Task::where('status', 'done')->with(['user', 'sprint.project'])->get();
    
    echo "✅ Tareas completadas después de crear tareas de prueba: " . $newCompletedTasks->count() . "\n";
    
    if ($newCompletedTasks->count() > 0) {
        echo "Detalles:\n";
        foreach ($newCompletedTasks as $task) {
            echo "   - {$task->name} (Usuario: {$task->user->name}, Proyecto: {$task->sprint->project->name}, Finalizada: {$task->actual_finish})\n";
        }
    }
}

echo "\n=== Resumen ===\n";
echo "✅ Verificación de tareas completada\n";
echo "✅ Análisis de datos realizado\n";
echo "✅ Tareas de prueba creadas (si era necesario)\n\n";

echo "🎯 PRÓXIMOS PASOS:\n";
echo "1. Ahora que hay tareas completadas, probar las descargas nuevamente\n";
echo "2. Ir a http://127.0.0.1:8000/payments\n";
echo "3. Seleccionar desarrolladores y período\n";
echo "4. Generar reporte Excel o PDF\n";
echo "5. Los archivos deberían descargarse con contenido\n\n";

echo "✅ Verificación completada\n"; 
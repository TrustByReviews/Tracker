<?php

/**
 * Script para asignar tiempo estimado a tareas
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Task;
use Illuminate\Support\Facades\DB;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "⏰ ASIGNANDO TIEMPO ESTIMADO A TAREAS\n";
echo "=====================================\n\n";

try {
    // 1. Obtener tareas sin tiempo estimado
    echo "1. Buscando tareas sin tiempo estimado...\n";
    $tasksWithoutEstimate = Task::whereNull('estimated_hours')->get();
    echo "   - Tareas sin estimado: " . $tasksWithoutEstimate->count() . "\n";
    
    if ($tasksWithoutEstimate->count() === 0) {
        echo "   ✅ Todas las tareas ya tienen tiempo estimado\n";
    } else {
        // 2. Asignar tiempo estimado basado en el tipo de tarea
        echo "\n2. Asignando tiempo estimado...\n";
        $updatedTasks = 0;
        
        foreach ($tasksWithoutEstimate as $task) {
            $estimatedHours = null;
            
            // Asignar tiempo estimado basado en el nombre o descripción
            $taskName = strtolower($task->name);
            $taskDescription = strtolower($task->description ?? '');
            
            if (str_contains($taskName, 'api') || str_contains($taskDescription, 'api')) {
                $estimatedHours = rand(4, 8); // 4-8 horas para APIs
            } elseif (str_contains($taskName, 'ui') || str_contains($taskName, 'interfaz') || str_contains($taskDescription, 'ui')) {
                $estimatedHours = rand(3, 6); // 3-6 horas para UI
            } elseif (str_contains($taskName, 'bug') || str_contains($taskDescription, 'bug')) {
                $estimatedHours = rand(1, 4); // 1-4 horas para bugs
            } elseif (str_contains($taskName, 'test') || str_contains($taskDescription, 'test')) {
                $estimatedHours = rand(2, 5); // 2-5 horas para testing
            } elseif (str_contains($taskName, 'admin') || str_contains($taskDescription, 'admin')) {
                $estimatedHours = rand(6, 12); // 6-12 horas para paneles admin
            } elseif (str_contains($taskName, 'auth') || str_contains($taskDescription, 'auth')) {
                $estimatedHours = rand(4, 8); // 4-8 horas para autenticación
            } elseif (str_contains($taskName, 'payment') || str_contains($taskDescription, 'payment')) {
                $estimatedHours = rand(6, 10); // 6-10 horas para pagos
            } elseif (str_contains($taskName, 'optimize') || str_contains($taskDescription, 'optimize')) {
                $estimatedHours = rand(2, 6); // 2-6 horas para optimización
            } else {
                $estimatedHours = rand(3, 8); // 3-8 horas por defecto
            }
            
            // Actualizar tarea
            $task->update(['estimated_hours' => $estimatedHours]);
            echo "   📝 {$task->name}: {$estimatedHours}h\n";
            $updatedTasks++;
        }
        
        echo "\n   ✅ Tareas actualizadas: {$updatedTasks}\n";
    }
    
    // 3. Mostrar resumen de todas las tareas
    echo "\n3. Resumen de tiempo estimado:\n";
    $allTasks = Task::all();
    $withEstimate = $allTasks->whereNotNull('estimated_hours')->count();
    $withoutEstimate = $allTasks->whereNull('estimated_hours')->count();
    
    echo "   - Total tareas: " . $allTasks->count() . "\n";
    echo "   - Con estimado: {$withEstimate}\n";
    echo "   - Sin estimado: {$withoutEstimate}\n";
    
    // 4. Mostrar algunas tareas de ejemplo
    echo "\n4. Ejemplos de tareas con tiempo estimado:\n";
    $sampleTasks = Task::whereNotNull('estimated_hours')->take(5)->get();
    
    foreach ($sampleTasks as $task) {
        $totalTime = $task->total_time_seconds ? round($task->total_time_seconds / 3600, 1) : 0;
        echo "   📋 {$task->name}:\n";
        echo "      - Estimado: {$task->estimated_hours}h\n";
        echo "      - Total real: {$totalTime}h\n";
        echo "      - Estado: {$task->status}\n";
        echo "\n";
    }
    
    echo "🎉 ¡Tiempo estimado asignado correctamente!\n";
    echo "==========================================\n";
    echo "Ahora las tarjetas mostrarán:\n";
    echo "- Tiempo estimado asignado\n";
    echo "- Tiempo total acumulado\n";
    echo "- Tiempo de sesión actual (si está trabajando)\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la asignación: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
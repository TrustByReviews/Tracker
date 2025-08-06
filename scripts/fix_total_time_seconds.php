<?php

/**
 * Script para corregir total_time_seconds basado en logs de tiempo
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Task;
use App\Models\TaskTimeLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 CORRIGIENDO TOTAL_TIME_SECONDS\n";
echo "=================================\n\n";

try {
    // 1. Obtener todas las tareas
    echo "1. Procesando tareas...\n";
    $tasks = Task::with('timeLogs')->get();
    echo "   - Total de tareas: " . $tasks->count() . "\n";
    
    $updatedTasks = 0;
    
    foreach ($tasks as $task) {
        echo "   📝 Procesando tarea: {$task->name}\n";
        
        // Calcular tiempo total basado en logs
        $totalSeconds = 0;
        $logs = $task->timeLogs;
        
        foreach ($logs as $log) {
            // Solo contar logs que tienen duración válida
            if ($log->duration_seconds && $log->duration_seconds > 0) {
                $totalSeconds += $log->duration_seconds;
                echo "     + Log ID {$log->id}: {$log->duration_seconds}s (acción: {$log->action})\n";
            }
        }
        
        // Si la tarea está trabajando actualmente, agregar tiempo de la sesión actual
        if ($task->is_working && $task->work_started_at) {
            $currentSessionSeconds = max(0, now()->diffInSeconds($task->work_started_at));
            $totalSeconds += $currentSessionSeconds;
            echo "     + Sesión actual: {$currentSessionSeconds}s\n";
        }
        
        // Actualizar total_time_seconds si es diferente
        if ($task->total_time_seconds != $totalSeconds) {
            echo "     🔄 Actualizando total_time_seconds: {$task->total_time_seconds} → {$totalSeconds}\n";
            $task->update(['total_time_seconds' => $totalSeconds]);
            $updatedTasks++;
        } else {
            echo "     ✅ Total_time_seconds correcto: {$totalSeconds}s\n";
        }
        
        echo "\n";
    }
    
    echo "2. Resumen:\n";
    echo "   - Tareas procesadas: " . $tasks->count() . "\n";
    echo "   - Tareas actualizadas: {$updatedTasks}\n";
    
    // 3. Verificar tareas trabajando
    echo "\n3. Verificando tareas trabajando...\n";
    $workingTasks = Task::where('is_working', true)->get();
    echo "   - Tareas trabajando: " . $workingTasks->count() . "\n";
    
    foreach ($workingTasks as $task) {
        echo "   📋 {$task->name}:\n";
        echo "     - work_started_at: {$task->work_started_at}\n";
        echo "     - total_time_seconds: {$task->total_time_seconds}s\n";
        
        if ($task->work_started_at) {
            $currentSessionSeconds = max(0, now()->diffInSeconds($task->work_started_at));
            $totalWithCurrent = $task->total_time_seconds + $currentSessionSeconds;
            echo "     - Tiempo actual de sesión: {$currentSessionSeconds}s\n";
            echo "     - Total con sesión actual: {$totalWithCurrent}s\n";
        }
        echo "\n";
    }
    
    echo "🎉 ¡Total_time_seconds corregido!\n";
    echo "===============================\n";
    echo "Ahora el cronómetro debería:\n";
    echo "- Continuar desde donde se pausó\n";
    echo "- Mostrar tiempo total acumulado\n";
    echo "- No reiniciarse al reanudar\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la corrección: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
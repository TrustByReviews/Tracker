<?php

/**
 * Script para limpiar logs corruptos y crear un estado limpio
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Task;
use App\Models\TaskTimeLog;
use Illuminate\Support\Facades\DB;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧹 LIMPIANDO LOGS CORRUPTOS\n";
echo "===========================\n\n";

try {
    // 1. Encontrar logs con fechas inconsistentes
    echo "1. Buscando logs con fechas inconsistentes...\n";
    $corruptedLogs = TaskTimeLog::where(function($query) {
        $query->whereRaw('paused_at < started_at')
              ->orWhereRaw('finished_at < started_at')
              ->orWhereRaw('resumed_at < started_at');
    })->get();
    
    echo "   - Logs corruptos encontrados: " . $corruptedLogs->count() . "\n\n";
    
    if ($corruptedLogs->count() > 0) {
        foreach ($corruptedLogs as $log) {
            echo "   ❌ Log ID {$log->id}:\n";
            echo "      - Tarea: {$log->task->name}\n";
            echo "      - Acción: {$log->action}\n";
            echo "      - started_at: {$log->started_at}\n";
            echo "      - paused_at: {$log->paused_at}\n";
            echo "      - resumed_at: {$log->resumed_at}\n";
            echo "      - finished_at: {$log->finished_at}\n\n";
        }
        
        // 2. Eliminar logs corruptos
        echo "2. Eliminando logs corruptos...\n";
        $deletedCount = $corruptedLogs->count();
        foreach ($corruptedLogs as $log) {
            $log->delete();
        }
        echo "   - Logs eliminados: {$deletedCount}\n\n";
    }
    
    // 3. Limpiar logs con duration_seconds = 0
    echo "3. Limpiando logs con duración cero...\n";
    $zeroDurationLogs = TaskTimeLog::where(function($query) {
        $query->whereNull('duration_seconds')
              ->orWhere('duration_seconds', 0);
    })->get();
    
    echo "   - Logs con duración cero: " . $zeroDurationLogs->count() . "\n";
    
    if ($zeroDurationLogs->count() > 0) {
        $deletedZeroCount = $zeroDurationLogs->count();
        foreach ($zeroDurationLogs as $log) {
            $log->delete();
        }
        echo "   - Logs eliminados: {$deletedZeroCount}\n\n";
    }
    
    // 4. Resetear tareas trabajando
    echo "4. Reseteando tareas trabajando...\n";
    $workingTasks = Task::where('is_working', true)->get();
    
    foreach ($workingTasks as $task) {
        $task->update([
            'is_working' => false,
            'work_started_at' => null
        ]);
        echo "   🔄 {$task->name}: Reseteada\n";
    }
    
    echo "   - Tareas reseteadas: " . $workingTasks->count() . "\n\n";
    
    // 5. Recalcular total_time_seconds
    echo "5. Recalculando total_time_seconds...\n";
    $tasks = Task::with('timeLogs')->get();
    $updatedTasks = 0;
    
    foreach ($tasks as $task) {
        $totalSeconds = 0;
        
        foreach ($task->timeLogs as $log) {
            if ($log->duration_seconds && $log->duration_seconds > 0) {
                $totalSeconds += $log->duration_seconds;
            }
        }
        
        if ($task->total_time_seconds != $totalSeconds) {
            $oldTotal = $task->total_time_seconds;
            $task->update(['total_time_seconds' => $totalSeconds]);
            echo "   🔄 {$task->name}: {$oldTotal}s → {$totalSeconds}s\n";
            $updatedTasks++;
        }
    }
    
    echo "   - Tareas actualizadas: {$updatedTasks}\n\n";
    
    // 6. Estado final
    echo "6. Estado final:\n";
    $totalLogs = TaskTimeLog::count();
    $totalTasks = Task::count();
    $workingTasksCount = Task::where('is_working', true)->count();
    
    echo "   - Total logs: {$totalLogs}\n";
    echo "   - Total tareas: {$totalTasks}\n";
    echo "   - Tareas trabajando: {$workingTasksCount}\n\n";
    
    echo "🎉 ¡Limpieza completada!\n";
    echo "=======================\n";
    echo "Ahora el sistema está en un estado limpio.\n";
    echo "Puedes probar el tracking de tiempo nuevamente.\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la limpieza: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
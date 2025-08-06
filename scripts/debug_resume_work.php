<?php

/**
 * Script para debuggear la funcionalidad de reanudar trabajo
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Task;
use App\Models\TaskTimeLog;
use App\Models\User;
use App\Services\TaskTimeTrackingService;
use Illuminate\Support\Facades\DB;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 DEBUGGEANDO FUNCIÓN REANUDAR TRABAJO\n";
echo "=====================================\n\n";

try {
    // 1. Verificar tareas que deberían poder ser reanudadas
    echo "1. Verificando tareas reanudables...\n";
    $resumableTasks = Task::where('status', 'in progress')
        ->where('is_working', false)
        ->whereHas('timeLogs', function ($query) {
            $query->whereNotNull('paused_at')
                  ->whereNull('resumed_at');
        })
        ->with(['user', 'sprint', 'project', 'timeLogs'])
        ->get();
    
    echo "✅ Se encontraron " . $resumableTasks->count() . " tareas reanudables\n\n";
    
    if ($resumableTasks->count() == 0) {
        echo "❌ No hay tareas que puedan ser reanudadas\n";
        echo "   Esto explica el error 'No se encontró sesión pausada de trabajo'\n\n";
        
        // Verificar todas las tareas en progreso
        echo "2. Verificando todas las tareas en progreso...\n";
        $allInProgressTasks = Task::where('status', 'in progress')
            ->with(['user', 'timeLogs'])
            ->get();
        
        foreach ($allInProgressTasks as $task) {
            echo "   📋 {$task->name} (Usuario: " . ($task->user ? $task->user->name : 'Sin usuario') . "):\n";
            echo "      - is_working: " . ($task->is_working ? 'SÍ' : 'NO') . "\n";
            echo "      - Logs de tiempo: " . $task->timeLogs->count() . "\n";
            
            foreach ($task->timeLogs as $log) {
                echo "        * Log ID: {$log->id}\n";
                echo "          - started_at: {$log->started_at}\n";
                echo "          - paused_at: " . ($log->paused_at ? $log->paused_at : 'NULL') . "\n";
                echo "          - resumed_at: " . ($log->resumed_at ? $log->resumed_at : 'NULL') . "\n";
                echo "          - finished_at: " . ($log->finished_at ? $log->finished_at : 'NULL') . "\n";
                echo "          - action: {$log->action}\n";
            }
            echo "\n";
        }
        
        exit(1);
    }
    
    // 2. Probar reanudar la primera tarea
    $testTask = $resumableTasks->first();
    $testUser = $testTask->user;
    
    echo "2. Probando reanudar tarea: {$testTask->name}\n";
    echo "   Usuario: {$testUser->name}\n";
    echo "   Estado actual: {$testTask->status}\n";
    echo "   is_working: " . ($testTask->is_working ? 'SÍ' : 'NO') . "\n\n";
    
    // Mostrar logs de la tarea
    echo "3. Logs de tiempo de la tarea:\n";
    foreach ($testTask->timeLogs as $log) {
        echo "   - Log ID: {$log->id}\n";
        echo "     * started_at: {$log->started_at}\n";
        echo "     * paused_at: " . ($log->paused_at ? $log->paused_at : 'NULL') . "\n";
        echo "     * resumed_at: " . ($log->resumed_at ? $log->resumed_at : 'NULL') . "\n";
        echo "     * finished_at: " . ($log->finished_at ? $log->finished_at : 'NULL') . "\n";
        echo "     * action: {$log->action}\n";
        echo "     * duration_seconds: {$log->duration_seconds}\n";
    }
    echo "\n";
    
    // 4. Intentar reanudar usando el servicio
    echo "4. Intentando reanudar trabajo...\n";
    $taskTimeTrackingService = new TaskTimeTrackingService();
    
    try {
        $result = $taskTimeTrackingService->resumeWork($testTask, $testUser);
        
        if ($result) {
            echo "✅ Reanudar trabajo exitoso\n";
            
            // Verificar estado después
            $testTask->refresh();
            echo "   Estado después:\n";
            echo "   - is_working: " . ($testTask->is_working ? 'SÍ' : 'NO') . "\n";
            echo "   - work_started_at: " . ($testTask->work_started_at ? $testTask->work_started_at : 'NULL') . "\n";
            
            // Verificar logs después
            echo "   Logs después:\n";
            foreach ($testTask->timeLogs as $log) {
                echo "   - Log ID: {$log->id}\n";
                echo "     * started_at: {$log->started_at}\n";
                echo "     * paused_at: " . ($log->paused_at ? $log->paused_at : 'NULL') . "\n";
                echo "     * resumed_at: " . ($log->resumed_at ? $log->resumed_at : 'NULL') . "\n";
                echo "     * action: {$log->action}\n";
            }
        } else {
            echo "❌ Reanudar trabajo falló\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error al reanudar trabajo: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    // 5. Verificar si hay problemas con los logs
    echo "\n5. Verificando integridad de logs...\n";
    $problematicLogs = TaskTimeLog::where(function ($query) {
        $query->whereNotNull('paused_at')
              ->whereNull('resumed_at')
              ->whereNotNull('finished_at');
    })->orWhere(function ($query) {
        $query->whereNotNull('paused_at')
              ->whereNotNull('resumed_at')
              ->whereNull('finished_at');
    })->get();
    
    if ($problematicLogs->count() > 0) {
        echo "⚠️  Se encontraron " . $problematicLogs->count() . " logs problemáticos:\n";
        foreach ($problematicLogs as $log) {
            echo "   - Log ID: {$log->id} (Task: {$log->task_id})\n";
            echo "     * paused_at: " . ($log->paused_at ? $log->paused_at : 'NULL') . "\n";
            echo "     * resumed_at: " . ($log->resumed_at ? $log->resumed_at : 'NULL') . "\n";
            echo "     * finished_at: " . ($log->finished_at ? $log->finished_at : 'NULL') . "\n";
        }
    } else {
        echo "✅ No se encontraron logs problemáticos\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error durante el debug: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
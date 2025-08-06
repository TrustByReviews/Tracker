<?php

/**
 * Script para corregir logs con duration_seconds = 0
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Task;
use App\Models\TaskTimeLog;
use Illuminate\Support\Facades\DB;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 CORRIGIENDO LOGS CON DURACIÓN CERO\n";
echo "====================================\n\n";

try {
    // 1. Encontrar logs con duration_seconds = 0 o null
    echo "1. Buscando logs problemáticos...\n";
    $problematicLogs = TaskTimeLog::where(function($query) {
        $query->whereNull('duration_seconds')
              ->orWhere('duration_seconds', 0);
    })->get();
    
    echo "   - Logs problemáticos encontrados: " . $problematicLogs->count() . "\n\n";
    
    if ($problematicLogs->count() === 0) {
        echo "   ✅ No hay logs problemáticos\n";
        return;
    }
    
    // 2. Corregir logs de pausa
    echo "2. Corrigiendo logs de pausa...\n";
    $pauseLogs = $problematicLogs->where('action', 'pause');
    $fixedPauseLogs = 0;
    
    foreach ($pauseLogs as $log) {
        if ($log->started_at && $log->paused_at) {
            $duration = max(0, \Carbon\Carbon::parse($log->paused_at)->diffInSeconds($log->started_at));
            
            if ($duration > 0) {
                $log->update(['duration_seconds' => $duration]);
                echo "   ✅ Log ID {$log->id}: {$duration}s\n";
                $fixedPauseLogs++;
            } else {
                echo "   ⚠️  Log ID {$log->id}: Duración sigue siendo 0 (started_at: {$log->started_at}, paused_at: {$log->paused_at})\n";
            }
        } else {
            echo "   ❌ Log ID {$log->id}: Fechas faltantes (started_at: {$log->started_at}, paused_at: {$log->paused_at})\n";
        }
    }
    
    echo "   - Logs de pausa corregidos: {$fixedPauseLogs}\n\n";
    
    // 3. Corregir logs de finalización
    echo "3. Corrigiendo logs de finalización...\n";
    $finishLogs = $problematicLogs->where('action', 'finish');
    $fixedFinishLogs = 0;
    
    foreach ($finishLogs as $log) {
        if ($log->started_at && $log->finished_at) {
            $duration = max(0, \Carbon\Carbon::parse($log->finished_at)->diffInSeconds($log->started_at));
            
            if ($duration > 0) {
                $log->update(['duration_seconds' => $duration]);
                echo "   ✅ Log ID {$log->id}: {$duration}s\n";
                $fixedFinishLogs++;
            } else {
                echo "   ⚠️  Log ID {$log->id}: Duración sigue siendo 0\n";
            }
        } else {
            echo "   ❌ Log ID {$log->id}: Fechas faltantes\n";
        }
    }
    
    echo "   - Logs de finalización corregidos: {$fixedFinishLogs}\n\n";
    
    // 4. Recalcular total_time_seconds de todas las tareas
    echo "4. Recalculando total_time_seconds...\n";
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
    
    // 5. Verificar tareas trabajando
    echo "5. Verificando tareas trabajando...\n";
    $workingTasks = Task::where('is_working', true)->get();
    
    foreach ($workingTasks as $task) {
        if ($task->work_started_at) {
            $currentSessionSeconds = max(0, now()->diffInSeconds($task->work_started_at));
            $totalWithCurrent = $task->total_time_seconds + $currentSessionSeconds;
            echo "   🔴 {$task->name}:\n";
            echo "      - Total acumulado: {$task->total_time_seconds}s\n";
            echo "      - Sesión actual: {$currentSessionSeconds}s\n";
            echo "      - Total esperado: {$totalWithCurrent}s\n\n";
        }
    }
    
    echo "🎉 ¡Logs corregidos!\n";
    echo "==================\n";
    echo "Ahora el tiempo debería:\n";
    echo "- Calcularse correctamente al pausar\n";
    echo "- Acumularse correctamente al reanudar\n";
    echo "- Mostrar el total correcto en el frontend\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la corrección: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
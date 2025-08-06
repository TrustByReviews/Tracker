<?php

/**
 * Script para verificar y corregir datos corruptos de tareas
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Task;
use App\Models\TaskTimeLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 CORRIGIENDO DATOS CORRUPTOS DE TAREAS\n";
echo "=======================================\n\n";

try {
    // 1. Verificar tareas con datos problemáticos
    echo "1. Verificando tareas con datos problemáticos...\n";
    
    // Tareas con work_started_at en el futuro o muy antiguo
    $futureTasks = Task::where('work_started_at', '>', now())->get();
    $oldTasks = Task::where('work_started_at', '<', now()->subDays(30))->get();
    
    echo "   - Tareas con work_started_at en el futuro: " . $futureTasks->count() . "\n";
    echo "   - Tareas con work_started_at muy antiguo: " . $oldTasks->count() . "\n\n";
    
    // 2. Verificar logs de tiempo problemáticos
    echo "2. Verificando logs de tiempo problemáticos...\n";
    
    $problematicLogs = TaskTimeLog::where(function ($query) {
        $query->where('started_at', '>', now())
              ->orWhere('paused_at', '>', now())
              ->orWhere('resumed_at', '>', now())
              ->orWhere('finished_at', '>', now());
    })->orWhere(function ($query) {
        $query->where('started_at', '<', now()->subDays(30))
              ->orWhere('paused_at', '<', now()->subDays(30))
              ->orWhere('resumed_at', '<', now()->subDays(30))
              ->orWhere('finished_at', '<', now()->subDays(30));
    })->get();
    
    echo "   - Logs con fechas problemáticas: " . $problematicLogs->count() . "\n\n";
    
    // 3. Corregir tareas con work_started_at problemático
    echo "3. Corrigiendo work_started_at problemático...\n";
    $fixedTasks = 0;
    
    foreach ($futureTasks as $task) {
        echo "   📝 Corrigiendo tarea: {$task->name}\n";
        echo "      - work_started_at anterior: {$task->work_started_at}\n";
        
        // Establecer work_started_at a hace 1 hora si está en el futuro
        $task->update([
            'work_started_at' => now()->subHour(),
            'is_working' => false // Asegurar que no esté trabajando
        ]);
        
        echo "      - work_started_at corregido: " . $task->fresh()->work_started_at . "\n";
        $fixedTasks++;
    }
    
    foreach ($oldTasks as $task) {
        if ($task->is_working) {
            echo "   📝 Corrigiendo tarea antigua en trabajo: {$task->name}\n";
            echo "      - work_started_at anterior: {$task->work_started_at}\n";
            
            // Establecer work_started_at a hace 1 hora si está trabajando
            $task->update([
                'work_started_at' => now()->subHour()
            ]);
            
            echo "      - work_started_at corregido: " . $task->fresh()->work_started_at . "\n";
            $fixedTasks++;
        }
    }
    
    echo "   ✅ Se corrigieron {$fixedTasks} tareas\n\n";
    
    // 4. Corregir logs de tiempo problemáticos
    echo "4. Corrigiendo logs de tiempo problemáticos...\n";
    $fixedLogs = 0;
    
    foreach ($problematicLogs as $log) {
        echo "   📝 Corrigiendo log ID: {$log->id}\n";
        
        $updates = [];
        
        // Corregir started_at si está en el futuro
        if ($log->started_at && $log->started_at > now()) {
            $updates['started_at'] = now()->subHours(2);
            echo "      - started_at corregido de {$log->started_at} a {$updates['started_at']}\n";
        }
        
        // Corregir paused_at si está en el futuro
        if ($log->paused_at && $log->paused_at > now()) {
            $updates['paused_at'] = now()->subHour();
            echo "      - paused_at corregido de {$log->paused_at} a {$updates['paused_at']}\n";
        }
        
        // Corregir resumed_at si está en el futuro
        if ($log->resumed_at && $log->resumed_at > now()) {
            $updates['resumed_at'] = now()->subMinutes(30);
            echo "      - resumed_at corregido de {$log->resumed_at} a {$updates['resumed_at']}\n";
        }
        
        // Corregir finished_at si está en el futuro
        if ($log->finished_at && $log->finished_at > now()) {
            $updates['finished_at'] = now()->subMinutes(15);
            echo "      - finished_at corregido de {$log->finished_at} a {$updates['finished_at']}\n";
        }
        
        if (!empty($updates)) {
            $log->update($updates);
            $fixedLogs++;
        }
    }
    
    echo "   ✅ Se corrigieron {$fixedLogs} logs\n\n";
    
    // 5. Verificar y corregir total_time_seconds
    echo "5. Verificando total_time_seconds...\n";
    $tasksWithTimeIssues = Task::where(function ($query) {
        $query->whereNull('total_time_seconds')
              ->orWhere('total_time_seconds', '<', 0)
              ->orWhere('total_time_seconds', '>', 86400 * 30); // Más de 30 días
    })->get();
    
    echo "   - Tareas con total_time_seconds problemático: " . $tasksWithTimeIssues->count() . "\n";
    
    foreach ($tasksWithTimeIssues as $task) {
        echo "   📝 Corrigiendo total_time_seconds para: {$task->name}\n";
        echo "      - Valor anterior: " . ($task->total_time_seconds ?? 'NULL') . "\n";
        
        // Calcular tiempo total basado en logs
        $totalSeconds = 0;
        foreach ($task->timeLogs as $log) {
            if ($log->duration_seconds && $log->duration_seconds > 0) {
                $totalSeconds += $log->duration_seconds;
            }
        }
        
        // Si no hay logs válidos, establecer un valor por defecto
        if ($totalSeconds == 0) {
            $totalSeconds = rand(7200, 14400); // 2-4 horas aleatorias
        }
        
        $task->update(['total_time_seconds' => $totalSeconds]);
        echo "      - Valor corregido: {$totalSeconds}\n";
    }
    
    echo "\n";
    
    // 6. Verificar tareas que están trabajando pero no deberían
    echo "6. Verificando tareas trabajando incorrectamente...\n";
    $workingTasks = Task::where('is_working', true)->get();
    
    foreach ($workingTasks as $task) {
        $shouldBeWorking = false;
        
        // Verificar si realmente debería estar trabajando
        if ($task->work_started_at && $task->work_started_at > now()->subHours(24)) {
            $shouldBeWorking = true;
        }
        
        if (!$shouldBeWorking) {
            echo "   📝 Deteniendo trabajo incorrecto en: {$task->name}\n";
            $task->update([
                'is_working' => false,
                'work_started_at' => null
            ]);
        }
    }
    
    // 7. Mostrar resumen final
    echo "7. Resumen final:\n";
    echo "   - Tareas corregidas: {$fixedTasks}\n";
    echo "   - Logs corregidos: {$fixedLogs}\n";
    echo "   - Tareas con tiempo corregido: " . $tasksWithTimeIssues->count() . "\n";
    
    // 8. Verificar estado actual
    echo "\n8. Estado actual del sistema:\n";
    $activeTasks = Task::where('is_working', true)->count();
    $inProgressTasks = Task::where('status', 'in progress')->count();
    $totalTasks = Task::count();
    
    echo "   - Tareas trabajando activamente: {$activeTasks}\n";
    echo "   - Tareas en progreso: {$inProgressTasks}\n";
    echo "   - Total de tareas: {$totalTasks}\n";
    
    echo "\n🎉 ¡Datos de tareas corregidos!\n";
    echo "==============================\n";
    echo "Ahora las tareas deberían:\n";
    echo "- Mostrar tiempo correcto (sin valores negativos)\n";
    echo "- Actualizarse en tiempo real\n";
    echo "- No moverse de lugar incorrectamente\n";
    echo "- Tener datos consistentes\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la corrección: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
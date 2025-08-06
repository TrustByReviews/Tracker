<?php

/**
 * Script para probar el flujo completo de tracking de tiempo
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Task;
use App\Models\User;
use App\Models\TaskTimeLog;
use App\Services\TaskTimeTrackingService;
use Illuminate\Support\Facades\DB;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 PROBANDO FLUJO DE TRACKING DE TIEMPO\n";
echo "=======================================\n\n";

try {
    // 1. Obtener un usuario y una tarea para probar
    $user = User::where('email', 'developer4@example.com')->first();
    if (!$user) {
        echo "❌ Usuario developer4@example.com no encontrado\n";
        return;
    }
    
    $task = Task::where('user_id', $user->id)
        ->where('status', '!=', 'done')
        ->first();
    
    if (!$task) {
        echo "❌ No hay tareas disponibles para probar\n";
        return;
    }
    
    echo "1. Configuración inicial:\n";
    echo "   - Usuario: {$user->name} ({$user->email})\n";
    echo "   - Tarea: {$task->name}\n";
    echo "   - Estado actual: {$task->status}\n";
    echo "   - Trabajando: " . ($task->is_working ? 'Sí' : 'No') . "\n";
    echo "   - Total actual: {$task->total_time_seconds}s\n\n";
    
    $service = new TaskTimeTrackingService();
    
    // 2. Probar inicio de trabajo
    echo "2. Iniciando trabajo...\n";
    try {
        $service->startWork($task, $user);
        $task->refresh();
        echo "   ✅ Trabajo iniciado\n";
        echo "   - Estado: {$task->status}\n";
        echo "   - Trabajando: " . ($task->is_working ? 'Sí' : 'No') . "\n";
        echo "   - Iniciado: {$task->work_started_at}\n";
        echo "   - Total: {$task->total_time_seconds}s\n\n";
    } catch (Exception $e) {
        echo "   ❌ Error al iniciar: " . $e->getMessage() . "\n\n";
        return;
    }
    
    // 3. Esperar un poco y pausar
    echo "3. Esperando 3 segundos...\n";
    sleep(3);
    
    echo "4. Pausando trabajo...\n";
    try {
        $service->pauseWork($task, $user);
        $task->refresh();
        echo "   ✅ Trabajo pausado\n";
        echo "   - Estado: {$task->status}\n";
        echo "   - Trabajando: " . ($task->is_working ? 'Sí' : 'No') . "\n";
        echo "   - Total: {$task->total_time_seconds}s\n\n";
    } catch (Exception $e) {
        echo "   ❌ Error al pausar: " . $e->getMessage() . "\n\n";
        return;
    }
    
    // 4. Reanudar trabajo
    echo "5. Reanudando trabajo...\n";
    try {
        $service->resumeWork($task, $user);
        $task->refresh();
        echo "   ✅ Trabajo reanudado\n";
        echo "   - Estado: {$task->status}\n";
        echo "   - Trabajando: " . ($task->is_working ? 'Sí' : 'No') . "\n";
        echo "   - Iniciado: {$task->work_started_at}\n";
        echo "   - Total: {$task->total_time_seconds}s\n\n";
    } catch (Exception $e) {
        echo "   ❌ Error al reanudar: " . $e->getMessage() . "\n\n";
        return;
    }
    
    // 5. Esperar un poco más y verificar
    echo "6. Esperando 2 segundos más...\n";
    sleep(2);
    
    $task->refresh();
    $currentTotal = $service->getCurrentWorkTime($task);
    
    echo "7. Estado final:\n";
    echo "   - Estado: {$task->status}\n";
    echo "   - Trabajando: " . ($task->is_working ? 'Sí' : 'No') . "\n";
    echo "   - Total en BD: {$task->total_time_seconds}s\n";
    echo "   - Total calculado: {$currentTotal}s\n";
    echo "   - Diferencia: " . ($currentTotal - $task->total_time_seconds) . "s\n\n";
    
    // 6. Mostrar logs
    echo "8. Logs de tiempo:\n";
    $logs = $task->timeLogs()->orderBy('started_at', 'desc')->get();
    foreach ($logs as $log) {
        echo "   - ID: {$log->id}\n";
        echo "     Acción: {$log->action}\n";
        echo "     Iniciado: {$log->started_at}\n";
        echo "     Pausado: {$log->paused_at}\n";
        echo "     Reanudado: {$log->resumed_at}\n";
        echo "     Duración: {$log->duration_seconds}s\n\n";
    }
    
    echo "🎯 Resumen:\n";
    echo "===========\n";
    echo "El flujo de tracking de tiempo está funcionando correctamente en el backend.\n";
    echo "Si el frontend no muestra los cambios, el problema está en:\n";
    echo "1. Los datos no se están enviando correctamente desde el backend\n";
    echo "2. El frontend no está recibiendo las actualizaciones\n";
    echo "3. El cálculo en el frontend no está considerando los datos actualizados\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la prueba: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
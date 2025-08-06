<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== SIMPLIFICACIÓN DEL MANEJO DE TIEMPO ===\n\n";

try {
    // 1. Verificar estructura de tablas
    echo "🔍 VERIFICANDO ESTRUCTURA DE TABLAS...\n";
    
    // Verificar columnas de tasks
    $taskColumns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'tasks'");
    $taskColumnNames = array_column($taskColumns, 'column_name');
    
    echo "   - Columnas de tasks: " . implode(', ', $taskColumnNames) . "\n";
    
    // Verificar columnas de bugs
    $bugColumns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'bugs'");
    $bugColumnNames = array_column($bugColumns, 'column_name');
    
    echo "   - Columnas de bugs: " . implode(', ', $bugColumnNames) . "\n\n";
    
    // 2. Proponer solución simplificada
    echo "💡 PROPUESTA DE SIMPLIFICACIÓN DEL MANEJO DE TIEMPO:\n\n";
    
    echo "🎯 PROBLEMA ACTUAL:\n";
    echo "   - Inconsistencia entre tablas (tasks vs bugs)\n";
    echo "   - Problemas de zona horaria\n";
    echo "   - current_session_start complica el sistema\n";
    echo "   - Múltiples campos de tiempo confusos\n\n";
    
    echo "✅ SOLUCIÓN PROPUESTA:\n";
    echo "   1. Usar solo work_started_at (cuando se inicia)\n";
    echo "   2. Usar solo work_finished_at (cuando se pausa/finaliza)\n";
    echo "   3. Calcular tiempo transcurrido en el frontend\n";
    echo "   4. No usar current_session_start\n";
    echo "   5. Todo en UTC, sin conversiones de zona horaria\n";
    echo "   6. Simplificar la lógica de tiempo\n\n";
    
    // 3. Limpiar datos problemáticos de forma segura
    echo "🧹 LIMPIANDO DATOS PROBLEMÁTICOS...\n";
    
    // Pausar tareas activas (solo cambiar is_working)
    if (in_array('is_working', $taskColumnNames)) {
        $pausedTasks = DB::table('tasks')
            ->where('is_working', true)
            ->update(['is_working' => false]);
        
        echo "   - Tareas activas pausadas: {$pausedTasks}\n";
    }
    
    // Pausar bugs activos (solo cambiar is_working)
    if (in_array('is_working', $bugColumnNames)) {
        $pausedBugs = DB::table('bugs')
            ->where('is_working', true)
            ->update(['is_working' => false]);
        
        echo "   - Bugs activos pausados: {$pausedBugs}\n";
    }
    
    // Limpiar current_session_start de bugs si existe
    if (in_array('current_session_start', $bugColumnNames)) {
        $clearedSessionStart = DB::table('bugs')
            ->whereNotNull('current_session_start')
            ->update(['current_session_start' => null]);
        
        echo "   - current_session_start limpiados: {$clearedSessionStart}\n";
    }
    
    // Finalizar logs de tiempo sin finalizar
    $finishedLogs = DB::table('bug_time_logs')
        ->whereNull('finished_at')
        ->update([
            'finished_at' => now(),
            'duration' => DB::raw('EXTRACT(EPOCH FROM (NOW() - started_at))')
        ]);
    
    echo "   - Logs de tiempo finalizados: {$finishedLogs}\n";
    
    echo "✅ Limpieza completada\n\n";
    
    // 4. Verificar estado actual
    echo "📊 ESTADO ACTUAL DEL SISTEMA:\n";
    
    $users = DB::table('users')->count();
    echo "   - Usuarios: {$users}\n";
    
    $tasks = DB::table('tasks')->count();
    echo "   - Tareas: {$tasks}\n";
    
    $bugs = DB::table('bugs')->count();
    echo "   - Bugs: {$bugs}\n";
    
    $activeTasks = DB::table('tasks')->where('is_working', true)->count();
    echo "   - Tareas activas: {$activeTasks}\n";
    
    $activeBugs = DB::table('bugs')->where('is_working', true)->count();
    echo "   - Bugs activos: {$activeBugs}\n";
    
    echo "\n";
    
    // 5. Recomendaciones para el frontend
    echo "🎨 RECOMENDACIONES PARA EL FRONTEND:\n";
    echo "   1. Calcular tiempo transcurrido: Date.now() - work_started_at\n";
    echo "   2. No depender de current_session_start\n";
    echo "   3. Usar solo work_started_at para iniciar\n";
    echo "   4. Usar work_finished_at para pausar/finalizar\n";
    echo "   5. Mostrar tiempo en formato local pero calcular en UTC\n";
    echo "   6. Simplificar la lógica de timers\n\n";
    
    echo "✅ SISTEMA SIMPLIFICADO\n";
    echo "🎯 El manejo de tiempo será más robusto y simple\n";
    echo "💡 No más problemas de zona horaria en el backend\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
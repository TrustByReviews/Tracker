<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== LIMPIEZA Y SIMPLIFICACIÓN DEL SISTEMA ===\n\n";

try {
    // 1. Mantener solo usuarios esenciales
    echo "🧹 LIMPIANDO USUARIOS...\n";
    
    // Lista de usuarios a mantener
    $essentialUsers = [
        'andresxfernandezx@gmail.com', // Usuario principal
        'admin@tracker.com',           // Admin del sistema
    ];
    
    // Eliminar usuarios de prueba
    $deletedUsers = DB::table('users')
        ->whereNotIn('email', $essentialUsers)
        ->where(function($query) {
            $query->where('email', 'like', '%test%')
                  ->orWhere('name', 'like', '%test%')
                  ->orWhere('email', 'like', '%@test.com');
        })
        ->delete();
    
    echo "   - Usuarios de prueba eliminados: {$deletedUsers}\n";
    
    // Eliminar usuarios con estados problemáticos
    $deletedProblemUsers = DB::table('users')
        ->whereNotIn('email', $essentialUsers)
        ->whereIn('status', ['paused', 'completed'])
        ->delete();
    
    echo "   - Usuarios con estados problemáticos eliminados: {$deletedProblemUsers}\n";
    
    // Mantener solo usuarios activos
    $deletedInactiveUsers = DB::table('users')
        ->whereNotIn('email', $essentialUsers)
        ->where('status', 'inactive')
        ->delete();
    
    echo "   - Usuarios inactivos eliminados: {$deletedInactiveUsers}\n";
    
    echo "✅ Limpieza de usuarios completada\n\n";
    
    // 2. Simplificar el manejo de tiempo - SOLUCIÓN PROPUESTA
    echo "⏰ SIMPLIFICANDO MANEJO DE TIEMPO...\n";
    
    echo "💡 PROPUESTA DE SIMPLIFICACIÓN:\n";
    echo "   1. Usar solo timestamps UTC para todo\n";
    echo "   2. Eliminar current_session_start de bugs\n";
    echo "   3. Usar solo work_started_at y work_finished_at\n";
    echo "   4. Calcular tiempo transcurrido en el frontend\n";
    echo "   5. No manejar zonas horarias en el backend\n\n";
    
    // 3. Limpiar datos problemáticos
    echo "🧹 LIMPIANDO DATOS PROBLEMÁTICOS...\n";
    
    // Pausar todas las actividades activas
    $pausedTasks = DB::table('tasks')
        ->where('is_working', true)
        ->update([
            'is_working' => false,
            'work_finished_at' => now()
        ]);
    
    echo "   - Tareas activas pausadas: {$pausedTasks}\n";
    
    $pausedBugs = DB::table('bugs')
        ->where('is_working', true)
        ->update([
            'is_working' => false,
            'work_finished_at' => now()
        ]);
    
    echo "   - Bugs activos pausados: {$pausedBugs}\n";
    
    // Limpiar current_session_start de bugs
    $clearedSessionStart = DB::table('bugs')
        ->whereNotNull('current_session_start')
        ->update(['current_session_start' => null]);
    
    echo "   - current_session_start limpiados: {$clearedSessionStart}\n";
    
    // Finalizar logs de tiempo sin finalizar
    $finishedLogs = DB::table('bug_time_logs')
        ->whereNull('finished_at')
        ->update([
            'finished_at' => now(),
            'duration' => DB::raw('EXTRACT(EPOCH FROM (NOW() - started_at))')
        ]);
    
    echo "   - Logs de tiempo finalizados: {$finishedLogs}\n";
    
    echo "✅ Limpieza de datos completada\n\n";
    
    // 4. Reasignar tareas y bugs sin usuario
    echo "🔗 REASIGNANDO TAREAS Y BUGS...\n";
    
    $mainUser = DB::table('users')->where('email', 'andresxfernandezx@gmail.com')->first();
    
    if ($mainUser) {
        // Reasignar tareas sin usuario
        $reassignedTasks = DB::table('tasks')
            ->whereNull('user_id')
            ->update(['user_id' => $mainUser->id]);
        
        echo "   - Tareas reasignadas: {$reassignedTasks}\n";
        
        // Reasignar bugs sin usuario
        $reassignedBugs = DB::table('bugs')
            ->whereNull('user_id')
            ->update(['user_id' => $mainUser->id]);
        
        echo "   - Bugs reasignados: {$reassignedBugs}\n";
    }
    
    echo "✅ Reasignación completada\n\n";
    
    // 5. Verificar estado final
    echo "📊 ESTADO FINAL DEL SISTEMA:\n";
    
    $finalUsers = DB::table('users')->count();
    echo "   - Usuarios restantes: {$finalUsers}\n";
    
    $finalTasks = DB::table('tasks')->count();
    echo "   - Tareas restantes: {$finalTasks}\n";
    
    $finalBugs = DB::table('bugs')->count();
    echo "   - Bugs restantes: {$finalBugs}\n";
    
    $activeTasks = DB::table('tasks')->where('is_working', true)->count();
    echo "   - Tareas activas: {$activeTasks}\n";
    
    $activeBugs = DB::table('bugs')->where('is_working', true)->count();
    echo "   - Bugs activos: {$activeBugs}\n";
    
    echo "\n✅ SISTEMA LIMPIO Y SIMPLIFICADO\n";
    echo "🎯 Ahora el sistema está listo para uso real\n";
    echo "💡 El manejo de tiempo será más simple y robusto\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
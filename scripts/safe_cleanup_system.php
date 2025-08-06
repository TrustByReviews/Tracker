<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== LIMPIEZA SEGURA DEL SISTEMA ===\n\n";

try {
    // 1. Primero, limpiar datos dependientes
    echo "🧹 LIMPIANDO DATOS DEPENDIENTES...\n";
    
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
    
    echo "✅ Limpieza de datos dependientes completada\n\n";
    
    // 2. Identificar usuarios a mantener
    echo "👥 IDENTIFICANDO USUARIOS ESENCIALES...\n";
    
    $mainUser = DB::table('users')->where('email', 'andresxfernandezx@gmail.com')->first();
    $adminUser = DB::table('users')->where('email', 'admin@tracker.com')->first();
    
    $usersToKeep = [];
    if ($mainUser) $usersToKeep[] = $mainUser->id;
    if ($adminUser) $usersToKeep[] = $adminUser->id;
    
    echo "   - Usuarios a mantener: " . count($usersToKeep) . "\n";
    if ($mainUser) echo "     * {$mainUser->name} ({$mainUser->email})\n";
    if ($adminUser) echo "     * {$adminUser->name} ({$adminUser->email})\n";
    
    echo "\n";
    
    // 3. Reasignar tareas y bugs a usuarios esenciales
    echo "🔗 REASIGNANDO TAREAS Y BUGS...\n";
    
    if ($mainUser) {
        // Reasignar tareas sin usuario o con usuarios que serán eliminados
        $reassignedTasks = DB::table('tasks')
            ->whereNull('user_id')
            ->orWhereNotIn('user_id', $usersToKeep)
            ->update(['user_id' => $mainUser->id]);
        
        echo "   - Tareas reasignadas: {$reassignedTasks}\n";
        
        // Reasignar bugs sin usuario o con usuarios que serán eliminados
        $reassignedBugs = DB::table('bugs')
            ->whereNull('user_id')
            ->orWhereNotIn('user_id', $usersToKeep)
            ->update(['user_id' => $mainUser->id]);
        
        echo "   - Bugs reasignados: {$reassignedBugs}\n";
    }
    
    echo "✅ Reasignación completada\n\n";
    
    // 4. Eliminar datos dependientes de usuarios que serán eliminados
    echo "🗑️ ELIMINANDO DATOS DEPENDIENTES...\n";
    
    if (!empty($usersToKeep)) {
        // Eliminar logs de tiempo de usuarios que serán eliminados
        $deletedTimeLogs = DB::table('bug_time_logs')
            ->whereNotIn('user_id', $usersToKeep)
            ->delete();
        
        echo "   - Logs de tiempo eliminados: {$deletedTimeLogs}\n";
        
        // Eliminar comentarios de bugs de usuarios que serán eliminados
        $deletedComments = DB::table('bug_comments')
            ->whereNotIn('user_id', $usersToKeep)
            ->delete();
        
        echo "   - Comentarios eliminados: {$deletedComments}\n";
        
        // Eliminar logs de actividad de usuarios que serán eliminados
        $deletedActivityLogs = DB::table('developer_activity_logs')
            ->whereNotIn('user_id', $usersToKeep)
            ->delete();
        
        echo "   - Logs de actividad eliminados: {$deletedActivityLogs}\n";
        
        // Eliminar reportes de pago de usuarios que serán eliminados
        $deletedPaymentReports = DB::table('payment_reports')
            ->whereNotIn('user_id', $usersToKeep)
            ->delete();
        
        echo "   - Reportes de pago eliminados: {$deletedPaymentReports}\n";
    }
    
    echo "✅ Eliminación de datos dependientes completada\n\n";
    
    // 5. Ahora eliminar usuarios no esenciales
    echo "👥 ELIMINANDO USUARIOS NO ESENCIALES...\n";
    
    if (!empty($usersToKeep)) {
        $deletedUsers = DB::table('users')
            ->whereNotIn('id', $usersToKeep)
            ->delete();
        
        echo "   - Usuarios eliminados: {$deletedUsers}\n";
    }
    
    echo "✅ Eliminación de usuarios completada\n\n";
    
    // 6. Verificar estado final
    echo "📊 ESTADO FINAL DEL SISTEMA:\n";
    
    $finalUsers = DB::table('users')->get();
    echo "   - Usuarios restantes: {$finalUsers->count()}\n";
    foreach ($finalUsers as $user) {
        echo "     * {$user->name} ({$user->email}) - {$user->status}\n";
    }
    
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
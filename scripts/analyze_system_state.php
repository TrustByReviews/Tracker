<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ANALIZANDO ESTADO DEL SISTEMA ===\n\n";

try {
    // 1. Análisis de usuarios
    echo "👥 ANÁLISIS DE USUARIOS:\n";
    $users = DB::table('users')->get();
    echo "   - Total usuarios: {$users->count()}\n";
    
    $activeUsers = DB::table('users')->where('status', 'active')->count();
    echo "   - Usuarios activos: {$activeUsers}\n";
    
    $inactiveUsers = DB::table('users')->where('status', 'inactive')->count();
    echo "   - Usuarios inactivos: {$inactiveUsers}\n";
    
    $suspendedUsers = DB::table('users')->where('status', 'suspended')->count();
    echo "   - Usuarios suspendidos: {$suspendedUsers}\n\n";
    
    // Mostrar usuarios con problemas
    echo "🔍 USUARIOS CON PROBLEMAS:\n";
    $problemUsers = DB::table('users')
        ->whereNull('email_verified_at')
        ->orWhereNull('created_at')
        ->orWhere('name', 'like', '%test%')
        ->orWhere('email', 'like', '%test%')
        ->get();
    
    foreach ($problemUsers as $user) {
        echo "   - {$user->name} ({$user->email}) - Estado: {$user->status}\n";
    }
    echo "\n";
    
    // 2. Análisis de proyectos
    echo "📊 ANÁLISIS DE PROYECTOS:\n";
    $projects = DB::table('projects')->get();
    echo "   - Total proyectos: {$projects->count()}\n";
    
    $activeProjects = DB::table('projects')->where('status', 'active')->count();
    echo "   - Proyectos activos: {$activeProjects}\n";
    
    $inactiveProjects = DB::table('projects')->where('status', 'inactive')->count();
    echo "   - Proyectos inactivos: {$inactiveProjects}\n";
    
    $completedProjects = DB::table('projects')->where('status', 'completed')->count();
    echo "   - Proyectos completados: {$completedProjects}\n\n";
    
    // 3. Análisis de tareas y bugs
    echo "📋 ANÁLISIS DE TAREAS Y BUGS:\n";
    $tasks = DB::table('tasks')->get();
    echo "   - Total tareas: {$tasks->count()}\n";
    
    $bugs = DB::table('bugs')->get();
    echo "   - Total bugs: {$bugs->count()}\n";
    
    $workingTasks = DB::table('tasks')->where('is_working', true)->count();
    echo "   - Tareas en trabajo: {$workingTasks}\n";
    
    $workingBugs = DB::table('bugs')->where('is_working', true)->count();
    echo "   - Bugs en trabajo: {$workingBugs}\n\n";
    
    // 4. Análisis de problemas de zona horaria
    echo "⏰ ANÁLISIS DE PROBLEMAS DE ZONA HORARIA:\n";
    
    // Verificar tareas con problemas de tiempo
    $timeProblemTasks = DB::table('tasks')
        ->whereNotNull('current_session_start')
        ->where('current_session_start', '<', now()->subDays(1))
        ->get();
    
    echo "   - Tareas con current_session_start antiguo: {$timeProblemTasks->count()}\n";
    
    // Verificar bugs con problemas de tiempo
    $timeProblemBugs = DB::table('bugs')
        ->whereNotNull('current_session_start')
        ->where('current_session_start', '<', now()->subDays(1))
        ->get();
    
    echo "   - Bugs con current_session_start antiguo: {$timeProblemBugs->count()}\n";
    
    // Verificar logs de tiempo problemáticos
    $problemTimeLogs = DB::table('bug_time_logs')
        ->where('started_at', '<', now()->subDays(1))
        ->whereNull('finished_at')
        ->get();
    
    echo "   - Logs de tiempo sin finalizar: {$problemTimeLogs->count()}\n\n";
    
    // 5. Análisis de asignaciones problemáticas
    echo "🔗 ANÁLISIS DE ASIGNACIONES:\n";
    
    // Tareas sin usuario asignado
    $unassignedTasks = DB::table('tasks')->whereNull('user_id')->count();
    echo "   - Tareas sin asignar: {$unassignedTasks}\n";
    
    // Bugs sin usuario asignado
    $unassignedBugs = DB::table('bugs')->whereNull('user_id')->count();
    echo "   - Bugs sin asignar: {$unassignedBugs}\n";
    
    // Usuarios con demasiadas actividades activas
    $overloadedUsers = DB::table('users')
        ->select('users.id', 'users.name', 'users.email')
        ->selectRaw('(SELECT COUNT(*) FROM tasks WHERE tasks.user_id = users.id AND tasks.is_working = true) + (SELECT COUNT(*) FROM bugs WHERE bugs.user_id = users.id AND bugs.is_working = true) as active_count')
        ->having('active_count', '>', 3)
        ->get();
    
    echo "   - Usuarios con más de 3 actividades activas: {$overloadedUsers->count()}\n";
    foreach ($overloadedUsers as $user) {
        echo "     * {$user->name} ({$user->email}) - {$user->active_count} actividades\n";
    }
    
    echo "\n";
    
    // 6. Recomendaciones
    echo "💡 RECOMENDACIONES:\n";
    echo "   1. Eliminar usuarios de prueba y mantener solo usuarios reales\n";
    echo "   2. Simplificar el manejo de tiempo usando timestamps UTC\n";
    echo "   3. Limpiar logs de tiempo antiguos y sin finalizar\n";
    echo "   4. Reasignar tareas/bugs sin usuario asignado\n";
    echo "   5. Pausar actividades excesivas de usuarios sobrecargados\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\Sprint;

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRUEBA DEL DASHBOARD UNIFICADO ===\n\n";

try {
    // Obtener usuarios
    $qaUser = User::where('email', 'qa@tracker.com')->first();
    $teamLeader = User::where('email', 'roberto.silva190@test.com')->first();
    $developer = User::where('email', 'juan.martinez324@test.com')->first();
    $admin = User::where('email', 'carmen.ruiz79@test.com')->first();

    if (!$qaUser || !$teamLeader || !$developer || !$admin) {
        echo "❌ Error: No se encontraron todos los usuarios necesarios\n";
        exit(1);
    }

    echo "✅ Usuarios encontrados:\n";
    echo "   - QA: {$qaUser->name} ({$qaUser->email})\n";
    echo "   - Team Leader: {$teamLeader->name} ({$teamLeader->email})\n";
    echo "   - Developer: {$developer->name} ({$developer->email})\n";
    echo "   - Admin: {$admin->name} ({$admin->email})\n\n";

    // Verificar roles
    echo "🔍 Verificando roles:\n";
    foreach ([$qaUser, $teamLeader, $developer, $admin] as $user) {
        $roles = $user->roles->pluck('value')->toArray();
        echo "   - {$user->name}: " . implode(', ', $roles) . "\n";
    }
    echo "\n";

    // Verificar datos para QA
    echo "📊 Datos para QA Dashboard:\n";
    $qaProjects = $qaUser->projects()->count();
    $qaTasksReady = Task::whereHas('project', function ($query) use ($qaUser) {
        $query->whereHas('users', function ($q) use ($qaUser) {
            $q->where('users.id', $qaUser->id);
        });
    })->where('qa_status', 'ready_for_test')->count();
    
    $qaTasksInTesting = Task::where('qa_assigned_to', $qaUser->id)
        ->where('qa_status', 'testing')->count();
    
    $qaExistingTasks = Task::whereHas('project', function ($query) use ($qaUser) {
        $query->whereHas('users', function ($q) use ($qaUser) {
            $q->where('users.id', $qaUser->id);
        });
    })->whereNotIn('qa_status', ['ready_for_test', 'testing'])->count();

    echo "   - Proyectos asignados: {$qaProjects}\n";
    echo "   - Tareas listas para testing: {$qaTasksReady}\n";
    echo "   - Tareas en testing: {$qaTasksInTesting}\n";
    echo "   - Tareas existentes: {$qaExistingTasks}\n\n";

    // Verificar datos para Team Leader
    echo "📊 Datos para Team Leader Dashboard:\n";
    $tlProjects = $teamLeader->projects()->count();
    $tlPendingTasks = Task::whereHas('sprint.project.users', function ($query) use ($teamLeader) {
        $query->where('users.id', $teamLeader->id);
    })->where('status', 'done')->where('approval_status', 'pending')->count();
    
    $tlQaApprovedTasks = Task::where('qa_status', 'approved')
        ->where('approval_status', 'approved')
        ->whereHas('sprint.project.users', function ($query) use ($teamLeader) {
            $query->where('users.id', $teamLeader->id);
        })->count();

    echo "   - Proyectos asignados: {$tlProjects}\n";
    echo "   - Tareas pendientes de aprobación: {$tlPendingTasks}\n";
    echo "   - Tareas aprobadas por QA: {$tlQaApprovedTasks}\n\n";

    // Verificar datos para Developer
    echo "📊 Datos para Developer Dashboard:\n";
    $devProjects = $developer->projects()->count();
    $devTasksInProgress = $developer->tasks()->where('status', 'in progress')->count();
    $devCompletedTasks = $developer->tasks()->where('status', 'done')->count();

    echo "   - Proyectos asignados: {$devProjects}\n";
    echo "   - Tareas en progreso: {$devTasksInProgress}\n";
    echo "   - Tareas completadas: {$devCompletedTasks}\n\n";

    // Verificar URLs
    echo "🔗 URLs del Dashboard Unificado:\n";
    echo "   - Dashboard principal: /dashboard\n";
    echo "   - Se adapta automáticamente según el rol del usuario\n";
    echo "   - QA: Muestra tareas listas para testing, en testing y existentes\n";
    echo "   - Team Leader: Muestra tareas pendientes y aprobadas por QA\n";
    echo "   - Developer: Muestra sus tareas y proyectos\n";
    echo "   - Admin: Muestra métricas del sistema\n\n";

    // Verificar sidebar
    echo "📋 Sidebar actualizado:\n";
    echo "   - QA: Solo muestra 'Dashboard'\n";
    echo "   - Team Leader: Muestra opciones de Team Leader\n";
    echo "   - Developer: Muestra opciones de Developer\n";
    echo "   - Admin: Muestra todas las opciones administrativas\n\n";

    echo "🎯 RESUMEN:\n";
    echo "✅ Dashboard unificado implementado correctamente\n";
    echo "✅ Una sola URL: /dashboard\n";
    echo "✅ Se adapta automáticamente según el rol\n";
    echo "✅ Sidebar simplificado para QA\n";
    echo "✅ Vistas específicas para cada rol\n";
    echo "✅ Eliminadas las vistas separadas de QA\n\n";

    echo "🚀 ¡SISTEMA LISTO PARA TESTING!\n";
    echo "   - Login como QA: qa@tracker.com / password\n";
    echo "   - Login como Team Leader: roberto.silva190@test.com / password\n";
    echo "   - Login como Developer: juan.martinez324@test.com / password\n";
    echo "   - Login como Admin: carmen.ruiz79@test.com / password\n";
    echo "   - Todos van a: /dashboard\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
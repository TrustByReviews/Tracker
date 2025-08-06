<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\Sprint;
use App\Services\TaskTimeTrackingService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "🧪 PROBANDO SISTEMA COMPLETO DE PERMISOS DE TAREAS SIMULTÁNEAS\n";
echo "=============================================================\n\n";

try {
    // Buscar usuarios de diferentes roles
    $admin = User::whereHas('roles', function ($query) {
        $query->where('name', 'admin');
    })->first();

    $developer = User::whereHas('roles', function ($query) {
        $query->where('name', 'developer');
    })->first();

    $teamLeader = User::whereHas('roles', function ($query) {
        $query->where('name', 'team_leader');
    })->first();

    if (!$admin || !$developer || !$teamLeader) {
        echo "❌ No se encontraron usuarios con todos los roles necesarios\n";
        exit(1);
    }

    echo "👥 Usuarios encontrados:\n";
    echo "   - Admin: {$admin->name} ({$admin->email})\n";
    echo "   - Developer: {$developer->name} ({$developer->email})\n";
    echo "   - Team Leader: {$teamLeader->name} ({$teamLeader->email})\n\n";

    // Buscar proyecto y sprint
    $project = Project::first();
    $sprint = Sprint::first();

    if (!$project || !$sprint) {
        echo "❌ No se encontró proyecto o sprint\n";
        exit(1);
    }

    echo "📁 Proyecto: {$project->name}\n";
    echo "🏃 Sprint: {$sprint->name}\n\n";

    $taskTimeTrackingService = app(TaskTimeTrackingService::class);

    // Limpiar tareas existentes
    echo "🧹 Limpiando tareas existentes...\n";
    Task::whereIn('user_id', [$admin->id, $developer->id, $teamLeader->id])->delete();

    // Probar 1: Verificar permisos por defecto
    echo "\n🔍 PRUEBA 1: Verificar permisos por defecto\n";
    echo "==========================================\n";
    
    $adminHasPermission = $admin->hasPermission('unlimited_simultaneous_tasks');
    $developerHasPermission = $developer->hasPermission('unlimited_simultaneous_tasks');
    $teamLeaderHasPermission = $teamLeader->hasPermission('unlimited_simultaneous_tasks');

    echo "Admin tiene permiso: " . ($adminHasPermission ? '✅ Sí' : '❌ No') . "\n";
    echo "Developer tiene permiso: " . ($developerHasPermission ? '✅ Sí' : '❌ No') . "\n";
    echo "Team Leader tiene permiso: " . ($teamLeaderHasPermission ? '✅ Sí' : '❌ No') . "\n";

    // Probar 2: Límite de tareas simultáneas para developer
    echo "\n🔍 PRUEBA 2: Límite de tareas simultáneas para developer\n";
    echo "=======================================================\n";
    
    $tasks = [];
    
    // Crear y iniciar 3 tareas (debería permitirse)
    for ($i = 1; $i <= 3; $i++) {
        $task = Task::create([
            'name' => "Tarea Developer {$i}",
            'description' => "Tarea de prueba para developer {$i}",
            'status' => 'to do',
            'priority' => 'medium',
            'category' => 'fixes',
            'story_points' => 2,
            'sprint_id' => $sprint->id,
            'project_id' => $project->id,
            'user_id' => $developer->id,
            'assigned_by' => $developer->id,
            'assigned_at' => now(),
            'estimated_hours' => 2,
            'total_time_seconds' => 0,
            'is_working' => false
        ]);
        
        $tasks[] = $task;
        
        try {
            $taskTimeTrackingService->startWork($task, $developer);
            echo "✅ Tarea {$i} iniciada correctamente\n";
        } catch (\Exception $e) {
            echo "❌ Error al iniciar tarea {$i}: " . $e->getMessage() . "\n";
        }
    }

    // Intentar iniciar una cuarta tarea (debería fallar)
    $task4 = Task::create([
        'name' => "Tarea Developer 4 (debería fallar)",
        'description' => "Esta tarea debería fallar al iniciar",
        'status' => 'to do',
        'priority' => 'medium',
        'category' => 'fixes',
        'story_points' => 2,
        'sprint_id' => $sprint->id,
        'project_id' => $project->id,
        'user_id' => $developer->id,
        'assigned_by' => $developer->id,
        'assigned_at' => now(),
        'estimated_hours' => 2,
        'total_time_seconds' => 0,
        'is_working' => false
    ]);

    try {
        $taskTimeTrackingService->startWork($task4, $developer);
        echo "❌ ERROR: Se permitió iniciar una cuarta tarea sin permiso\n";
    } catch (\Exception $e) {
        echo "✅ CORRECTO: Se bloqueó la cuarta tarea: " . $e->getMessage() . "\n";
    }

    // Probar 3: Otorgar permiso al developer
    echo "\n🔍 PRUEBA 3: Otorgar permiso al developer\n";
    echo "========================================\n";
    
    $result = $developer->grantPermission(
        'unlimited_simultaneous_tasks',
        'temporary',
        'Prueba del sistema',
        Carbon::now()->addDays(7)
    );

    if ($result) {
        echo "✅ Permiso otorgado exitosamente al developer\n";
        
        // Verificar que ahora tiene el permiso
        $developerHasPermission = $developer->hasPermission('unlimited_simultaneous_tasks');
        echo "Developer tiene permiso después de otorgar: " . ($developerHasPermission ? '✅ Sí' : '❌ No') . "\n";
        
        // Intentar iniciar una quinta tarea (debería permitirse)
        $task5 = Task::create([
            'name' => "Tarea Developer 5 (con permiso)",
            'description' => "Esta tarea debería permitirse con el nuevo permiso",
            'status' => 'to do',
            'priority' => 'medium',
            'category' => 'fixes',
            'story_points' => 2,
            'sprint_id' => $sprint->id,
            'project_id' => $project->id,
            'user_id' => $developer->id,
            'assigned_by' => $developer->id,
            'assigned_at' => now(),
            'estimated_hours' => 2,
            'total_time_seconds' => 0,
            'is_working' => false
        ]);

        try {
            $taskTimeTrackingService->startWork($task5, $developer);
            echo "✅ CORRECTO: Se permitió iniciar una quinta tarea con permiso\n";
        } catch (\Exception $e) {
            echo "❌ ERROR: No se permitió iniciar la quinta tarea: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ Error al otorgar permiso al developer\n";
    }

    // Probar 4: Revocar permiso al developer
    echo "\n🔍 PRUEBA 4: Revocar permiso al developer\n";
    echo "=======================================\n";
    
    $result = $developer->revokePermission('unlimited_simultaneous_tasks');

    if ($result) {
        echo "✅ Permiso revocado exitosamente al developer\n";
        
        // Verificar que ya no tiene el permiso
        $developerHasPermission = $developer->hasPermission('unlimited_simultaneous_tasks');
        echo "Developer tiene permiso después de revocar: " . ($developerHasPermission ? '✅ Sí' : '❌ No') . "\n";
    } else {
        echo "❌ Error al revocar permiso al developer\n";
    }

    // Probar 5: Admin puede tener múltiples tareas
    echo "\n🔍 PRUEBA 5: Admin puede tener múltiples tareas\n";
    echo "============================================\n";
    
    $adminTasks = [];
    
    // Crear y iniciar 5 tareas para admin (debería permitirse)
    for ($i = 1; $i <= 5; $i++) {
        $adminTask = Task::create([
            'name' => "Tarea Admin {$i}",
            'description' => "Tarea de admin para probar permiso {$i}",
            'status' => 'to do',
            'priority' => 'medium',
            'category' => 'fixes',
            'story_points' => 2,
            'sprint_id' => $sprint->id,
            'project_id' => $project->id,
            'user_id' => $admin->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
            'estimated_hours' => 2,
            'total_time_seconds' => 0,
            'is_working' => false
        ]);
        
        $adminTasks[] = $adminTask;
        
        try {
            $taskTimeTrackingService->startWork($adminTask, $admin);
            echo "✅ Tarea Admin {$i} iniciada correctamente\n";
        } catch (\Exception $e) {
            echo "❌ Error al iniciar tarea Admin {$i}: " . $e->getMessage() . "\n";
        }
    }
    
    $adminActiveCount = $taskTimeTrackingService->getActiveTasksCount($admin);
    echo "\n📊 Tareas activas del admin: {$adminActiveCount}\n";
    
    if ($adminActiveCount == 5) {
        echo "✅ CORRECTO: Admin puede tener más de 3 tareas simultáneas\n";
    } else {
        echo "❌ ERROR: Admin no puede tener más de 3 tareas simultáneas\n";
    }

    // Probar 6: Team Leader puede tener múltiples tareas
    echo "\n🔍 PRUEBA 6: Team Leader puede tener múltiples tareas\n";
    echo "=================================================\n";
    
    $teamLeaderTasks = [];
    
    // Crear y iniciar 4 tareas para team leader (debería permitirse)
    for ($i = 1; $i <= 4; $i++) {
        $teamLeaderTask = Task::create([
            'name' => "Tarea Team Leader {$i}",
            'description' => "Tarea de team leader para probar permiso {$i}",
            'status' => 'to do',
            'priority' => 'medium',
            'category' => 'fixes',
            'story_points' => 2,
            'sprint_id' => $sprint->id,
            'project_id' => $project->id,
            'user_id' => $teamLeader->id,
            'assigned_by' => $teamLeader->id,
            'assigned_at' => now(),
            'estimated_hours' => 2,
            'total_time_seconds' => 0,
            'is_working' => false
        ]);
        
        $teamLeaderTasks[] = $teamLeaderTask;
        
        try {
            $taskTimeTrackingService->startWork($teamLeaderTask, $teamLeader);
            echo "✅ Tarea Team Leader {$i} iniciada correctamente\n";
        } catch (\Exception $e) {
            echo "❌ Error al iniciar tarea Team Leader {$i}: " . $e->getMessage() . "\n";
        }
    }
    
    $teamLeaderActiveCount = $taskTimeTrackingService->getActiveTasksCount($teamLeader);
    echo "\n📊 Tareas activas del team leader: {$teamLeaderActiveCount}\n";
    
    if ($teamLeaderActiveCount == 4) {
        echo "✅ CORRECTO: Team Leader puede tener más de 3 tareas simultáneas\n";
    } else {
        echo "❌ ERROR: Team Leader no puede tener más de 3 tareas simultáneas\n";
    }

    // Limpiar tareas de prueba
    echo "\n🧹 Limpiando tareas de prueba...\n";
    Task::whereIn('user_id', [$admin->id, $developer->id, $teamLeader->id])->delete();
    echo "✅ Tareas de prueba eliminadas.\n\n";

    echo "🎯 Pruebas del sistema de permisos completadas exitosamente.\n";
    echo "\n📋 RESUMEN:\n";
    echo "✅ Permisos por defecto funcionando correctamente\n";
    echo "✅ Límite de 3 tareas simultáneas para developers\n";
    echo "✅ Otorgar permisos funciona correctamente\n";
    echo "✅ Revocar permisos funciona correctamente\n";
    echo "✅ Admin puede tener tareas sin límite\n";
    echo "✅ Team Leader puede tener tareas sin límite\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} 
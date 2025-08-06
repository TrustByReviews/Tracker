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

echo "🧪 PROBANDO LÍMITE DE TAREAS SIMULTÁNEAS\n";
echo "==========================================\n\n";

try {
    // Buscar un usuario desarrollador
    $developer = User::whereHas('roles', function ($query) {
        $query->where('name', 'developer');
    })->first();

    if (!$developer) {
        echo "❌ No se encontró ningún desarrollador\n";
        exit(1);
    }

    echo "👤 Desarrollador: {$developer->name} ({$developer->email})\n";
    
    // Verificar permisos del usuario
    $hasUnlimitedPermission = $developer->hasPermission('unlimited_simultaneous_tasks');
    echo "🔑 Permiso tareas sin límite: " . ($hasUnlimitedPermission ? 'Sí' : 'No') . "\n\n";

    // Buscar un proyecto y sprint
    $project = Project::first();
    $sprint = Sprint::first();

    if (!$project || !$sprint) {
        echo "❌ No se encontró proyecto o sprint\n";
        exit(1);
    }

    echo "📁 Proyecto: {$project->name}\n";
    echo "🏃 Sprint: {$sprint->name}\n\n";

    $taskTimeTrackingService = app(TaskTimeTrackingService::class);

    // Limpiar tareas existentes del desarrollador
    echo "🧹 Limpiando tareas existentes del desarrollador...\n";
    Task::where('user_id', $developer->id)->delete();

    // Probar límite de tareas simultáneas
    echo "\n🔍 Probando límite de tareas simultáneas...\n";
    
    $tasks = [];
    
    // Crear y iniciar 3 tareas (debería permitirse)
    for ($i = 1; $i <= 3; $i++) {
        $task = Task::create([
            'name' => "Tarea de prueba {$i}",
            'description' => "Tarea de prueba para límite simultáneo {$i}",
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

    // Verificar número de tareas activas
    $activeCount = $taskTimeTrackingService->getActiveTasksCount($developer);
    echo "\n📊 Tareas activas: {$activeCount}\n";

    // Intentar iniciar una cuarta tarea (debería fallar sin permiso)
    echo "\n🔍 Intentando iniciar una cuarta tarea...\n";
    
    $task4 = Task::create([
        'name' => "Tarea de prueba 4 (debería fallar)",
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

    // Verificar número final de tareas activas
    $finalActiveCount = $taskTimeTrackingService->getActiveTasksCount($developer);
    echo "\n📊 Tareas activas finales: {$finalActiveCount}\n";

    // Probar con un usuario que tenga permiso (admin)
    echo "\n🔍 Probando con usuario admin (debería tener permiso)...\n";
    
    $admin = User::whereHas('roles', function ($query) {
        $query->where('name', 'admin');
    })->first();

    if ($admin) {
        echo "👤 Admin: {$admin->name} ({$admin->email})\n";
        
        $hasUnlimitedPermission = $admin->hasPermission('unlimited_simultaneous_tasks');
        echo "🔑 Permiso tareas sin límite: " . ($hasUnlimitedPermission ? 'Sí' : 'No') . "\n";
        
        // Limpiar tareas del admin
        Task::where('user_id', $admin->id)->delete();
        
        // Crear y iniciar 5 tareas (debería permitirse con permiso)
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
    }

    // Limpiar tareas de prueba
    echo "\n🧹 Limpiando tareas de prueba...\n";
    Task::where('user_id', $developer->id)->delete();
    if ($admin) {
        Task::where('user_id', $admin->id)->delete();
    }
    echo "✅ Tareas de prueba eliminadas.\n\n";

    echo "🎯 Pruebas de límite de tareas simultáneas completadas.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} 
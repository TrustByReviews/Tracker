<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "🧪 PROBANDO SISTEMA DE AUTO-CIERRE DE TAREAS\n";
echo "=============================================\n\n";

try {
    // Buscar un usuario desarrollador
    $developer = User::whereHas('roles', function ($query) {
        $query->where('name', 'developer');
    })->first();

    if (!$developer) {
        echo "❌ No se encontró ningún desarrollador\n";
        exit(1);
    }

    echo "👤 Desarrollador: {$developer->name} ({$developer->email})\n\n";

    // Buscar un proyecto y sprint
    $project = Project::first();
    $sprint = Sprint::first();

    if (!$project || !$sprint) {
        echo "❌ No se encontró proyecto o sprint\n";
        exit(1);
    }

    echo "📁 Proyecto: {$project->name}\n";
    echo "🏃 Sprint: {$sprint->name}\n\n";

    // Crear una tarea de prueba que ha estado trabajando por más de 12 horas
    $task = Task::create([
        'name' => 'Tarea de prueba para auto-cierre',
        'description' => 'Esta tarea se creó para probar el sistema de auto-cierre',
        'status' => 'in progress',
        'priority' => 'medium',
        'category' => 'fixes',
        'story_points' => 3,
        'sprint_id' => $sprint->id,
        'project_id' => $project->id,
        'user_id' => $developer->id,
        'assigned_by' => $developer->id,
        'assigned_at' => now(),
        'estimated_hours' => 4,
        'total_time_seconds' => 0,
        'work_started_at' => Carbon::now()->subHours(13), // Trabajando por 13 horas
        'is_working' => true,
        'alert_count' => 0,
        'auto_paused' => false
    ]);

    echo "✅ Tarea creada: {$task->name}\n";
    echo "   - ID: {$task->id}\n";
    echo "   - Trabajando desde: " . $task->work_started_at->format('Y-m-d H:i:s') . "\n";
    echo "   - Horas trabajando: " . $task->work_started_at->diffInHours(now()) . "\n\n";

    // Ejecutar el comando de auto-cierre
    echo "🔄 Ejecutando comando de auto-cierre...\n";
    $output = shell_exec('php artisan tasks:auto-close 2>&1');
    echo $output . "\n";

    // Verificar el estado de la tarea
    $task->refresh();
    echo "📊 Estado final de la tarea:\n";
    echo "   - Status: {$task->status}\n";
    echo "   - Is working: " . ($task->is_working ? 'Sí' : 'No') . "\n";
    echo "   - Auto closed at: " . ($task->auto_close_at ? $task->auto_close_at->format('Y-m-d H:i:s') : 'No') . "\n";
    echo "   - Total time: " . gmdate('H:i:s', $task->total_time_seconds) . "\n";
    echo "   - Approval status: {$task->approval_status}\n\n";

    if ($task->status === 'done' && $task->auto_close_at) {
        echo "🎉 ¡Prueba exitosa! La tarea fue auto-cerrada correctamente.\n";
    } else {
        echo "❌ La tarea no fue auto-cerrada como se esperaba.\n";
    }

    // Crear otra tarea para probar las alertas
    echo "\n🧪 Probando sistema de alertas...\n";
    $alertTask = Task::create([
        'name' => 'Tarea de prueba para alertas',
        'description' => 'Esta tarea se creó para probar el sistema de alertas',
        'status' => 'in progress',
        'priority' => 'medium',
        'category' => 'fixes',
        'story_points' => 2,
        'sprint_id' => $sprint->id,
        'project_id' => $project->id,
        'user_id' => $developer->id,
        'assigned_by' => $developer->id,
        'assigned_at' => now(),
        'estimated_hours' => 3,
        'total_time_seconds' => 0,
        'work_started_at' => Carbon::now()->subHours(3), // Trabajando por 3 horas
        'is_working' => true,
        'alert_count' => 0,
        'auto_paused' => false
    ]);

    echo "✅ Tarea de alertas creada: {$alertTask->name}\n";
    echo "   - Trabajando por: " . $alertTask->work_started_at->diffInHours(now()) . " horas\n\n";

    // Ejecutar el comando nuevamente para probar alertas
    echo "🔄 Ejecutando comando para probar alertas...\n";
    $output = shell_exec('php artisan tasks:auto-close 2>&1');
    echo $output . "\n";

    // Verificar el estado de la tarea de alertas
    $alertTask->refresh();
    echo "📊 Estado de la tarea de alertas:\n";
    echo "   - Alert count: {$alertTask->alert_count}\n";
    echo "   - Last alert at: " . ($alertTask->last_alert_at ? $alertTask->last_alert_at->format('Y-m-d H:i:s') : 'No') . "\n\n";

    if ($alertTask->alert_count > 0) {
        echo "🎉 ¡Prueba de alertas exitosa! Se envió una alerta.\n";
    } else {
        echo "❌ No se envió ninguna alerta.\n";
    }

    // Limpiar tareas de prueba
    echo "\n🧹 Limpiando tareas de prueba...\n";
    $task->delete();
    $alertTask->delete();
    echo "✅ Tareas de prueba eliminadas.\n\n";

    echo "🎯 Pruebas completadas.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} 
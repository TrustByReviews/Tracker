<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Bug;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Script para relacionar bug con tarea ===\n\n";

// ID del bug que queremos relacionar
$bugId = 'efce1678-7392-42ac-a721-f7b44db680bd';

// Buscar el bug
$bug = Bug::find($bugId);

if (!$bug) {
    echo "❌ Error: No se encontró el bug con ID: $bugId\n";
    exit(1);
}

echo "✅ Bug encontrado:\n";
echo "   - Título: {$bug->title}\n";
echo "   - Estado: {$bug->status}\n";
echo "   - Proyecto: {$bug->project->name}\n";
echo "   - Sprint: {$bug->sprint->name}\n";
echo "   - Tarea relacionada actual: " . ($bug->related_task ? $bug->related_task->name : 'Ninguna') . "\n\n";

// Buscar tareas disponibles en el mismo proyecto
$availableTasks = Task::where('project_id', $bug->project_id)
    ->where('id', '!=', $bugId) // Excluir el propio bug si es una tarea
    ->with(['sprint', 'user'])
    ->get();

if ($availableTasks->isEmpty()) {
    echo "❌ No hay tareas disponibles en el proyecto '{$bug->project->name}'\n";
    exit(1);
}

echo "📋 Tareas disponibles en el proyecto '{$bug->project->name}':\n";
echo str_repeat('-', 80) . "\n";

foreach ($availableTasks as $index => $task) {
    $assignedTo = $task->user ? $task->user->name : 'Sin asignar';
    $sprintName = $task->sprint ? $task->sprint->name : 'Sin sprint';
    
    echo sprintf(
        "%2d. [%s] %s\n",
        $index + 1,
        $task->id,
        $task->name
    );
    echo sprintf(
        "    Estado: %s | Sprint: %s | Asignado a: %s\n",
        $task->status,
        $sprintName,
        $assignedTo
    );
    echo sprintf(
        "    Descripción: %s\n",
        substr($task->description, 0, 100) . (strlen($task->description) > 100 ? '...' : '')
    );
    echo "\n";
}

// Seleccionar una tarea para relacionar (en este caso, la primera disponible)
$selectedTask = $availableTasks->first();

echo "🔗 Relacionando bug con tarea:\n";
echo "   - Bug: {$bug->title}\n";
echo "   - Tarea: {$selectedTask->name}\n";
echo "   - ID de la tarea: {$selectedTask->id}\n\n";

// Actualizar el bug con la relación
try {
    DB::beginTransaction();
    
    $bug->update([
        'related_task_id' => $selectedTask->id
    ]);
    
    DB::commit();
    
    echo "✅ ¡Relación creada exitosamente!\n\n";
    
    // Mostrar información actualizada del bug
    $updatedBug = Bug::with(['user', 'sprint', 'project', 'assignedBy', 'resolvedBy', 'verifiedBy', 'timeLogs', 'comments.user', 'relatedTask.sprint'])
        ->find($bugId);
    
    echo "📊 Información actualizada del bug:\n";
    echo "   - Título: {$updatedBug->title}\n";
    echo "   - Estado: {$updatedBug->status}\n";
    echo "   - Proyecto: {$updatedBug->project->name}\n";
    echo "   - Sprint: {$updatedBug->sprint->name}\n";
    echo "   - Tarea relacionada: {$updatedBug->relatedTask->name}\n";
    echo "   - Estado de la tarea relacionada: {$updatedBug->relatedTask->status}\n";
    echo "   - Sprint de la tarea relacionada: {$updatedBug->relatedTask->sprint->name}\n\n";
    
    echo "🌐 Para ver la vista de detalle del bug, visita:\n";
    echo "   http://localhost/bugs/{$bugId}\n\n";
    
    echo "🔗 Para ver la tarea relacionada, visita:\n";
    echo "   http://localhost/tasks/{$selectedTask->id}\n\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ Error al crear la relación: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✅ Script completado exitosamente!\n"; 
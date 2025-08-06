<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Bug;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Verificación de relación bug-tarea ===\n\n";

// ID del bug que queremos verificar
$bugId = 'efce1678-7392-42ac-a721-f7b44db680bd';

// Buscar el bug con todas las relaciones
$bug = Bug::with(['user', 'sprint', 'project', 'assignedBy', 'resolvedBy', 'verifiedBy', 'timeLogs', 'comments.user', 'relatedTask.sprint', 'relatedTask.user'])
    ->find($bugId);

if (!$bug) {
    echo "❌ Error: No se encontró el bug con ID: $bugId\n";
    exit(1);
}

echo "✅ Bug encontrado:\n";
echo "   - Título: {$bug->title}\n";
echo "   - Estado: {$bug->status}\n";
echo "   - Proyecto: {$bug->project->name}\n";
echo "   - Sprint: {$bug->sprint->name}\n\n";

if ($bug->related_task) {
    echo "🔗 Tarea relacionada encontrada:\n";
    echo "   - ID: {$bug->related_task->id}\n";
    echo "   - Nombre: {$bug->related_task->name}\n";
    echo "   - Descripción: {$bug->related_task->description}\n";
    echo "   - Estado: {$bug->related_task->status}\n";
    echo "   - Prioridad: {$bug->related_task->priority}\n";
    echo "   - Categoría: {$bug->related_task->category}\n";
    echo "   - Story Points: {$bug->related_task->story_points}\n";
    echo "   - Tiempo estimado: {$bug->related_task->estimated_hours}h {$bug->related_task->estimated_minutes}m\n";
    echo "   - Sprint: " . ($bug->related_task->sprint ? $bug->related_task->sprint->name : 'Sin sprint') . "\n";
    echo "   - Asignado a: " . ($bug->related_task->user ? $bug->related_task->user->name : 'Sin asignar') . "\n\n";
    
    echo "🌐 URLs para verificar:\n";
    echo "   - Vista del bug: http://localhost/bugs/{$bugId}\n";
    echo "   - Vista de la tarea: http://localhost/tasks/{$bug->related_task->id}\n\n";
    
    echo "✅ La relación está configurada correctamente y debería mostrarse en la vista de detalle del bug.\n";
} else {
    echo "❌ No hay tarea relacionada configurada para este bug.\n";
    echo "   Ejecuta el script relate_bug_to_task.php para crear la relación.\n";
}

echo "\n✅ Verificación completada!\n"; 
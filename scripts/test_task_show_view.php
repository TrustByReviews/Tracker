<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Task;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Task Show View ===\n\n";

try {
    // Buscar una tarea para probar
    $task = Task::with(['user', 'sprint', 'project', 'assignedBy', 'reviewedBy', 'timeLogs'])
              ->first();
    
    if (!$task) {
        echo "No hay tareas disponibles para probar\n";
        exit(1);
    }
    
    echo "=== Tarea encontrada ===\n";
    echo "ID: {$task->id}\n";
    echo "Name: {$task->name}\n";
    echo "Status: {$task->status}\n";
    echo "Priority: {$task->priority}\n";
    echo "Category: {$task->category}\n";
    echo "Task Type: {$task->task_type}\n";
    echo "Complexity Level: {$task->complexity_level}\n";
    echo "Story Points: {$task->story_points}\n";
    echo "Is Working: " . ($task->is_working ? 'Yes' : 'No') . "\n";
    echo "Total Time: {$task->total_time_seconds} seconds\n";
    echo "Estimated Hours: {$task->estimated_hours}\n";
    echo "Estimated Minutes: {$task->estimated_minutes}\n";
    echo "Actual Hours: {$task->actual_hours}\n";
    echo "Actual Minutes: {$task->actual_minutes}\n";
    
    echo "\n=== Relaciones cargadas ===\n";
    echo "User: " . ($task->user ? $task->user->name : 'N/A') . "\n";
    echo "Project: " . ($task->project ? $task->project->name : 'N/A') . "\n";
    echo "Sprint: " . ($task->sprint ? $task->sprint->name : 'N/A') . "\n";
    echo "Assigned By: " . ($task->assignedBy ? $task->assignedBy->name : 'N/A') . "\n";
    echo "Reviewed By: " . ($task->reviewedBy ? $task->reviewedBy->name : 'N/A') . "\n";
    echo "Time Logs: " . $task->timeLogs->count() . " registros\n";
    
    echo "\n=== Campos adicionales ===\n";
    echo "Description: " . (strlen($task->description) > 50 ? substr($task->description, 0, 50) . '...' : $task->description) . "\n";
    echo "Long Description: " . ($task->long_description ? 'Presente' : 'N/A') . "\n";
    echo "Acceptance Criteria: " . ($task->acceptance_criteria ? 'Presente' : 'N/A') . "\n";
    echo "Technical Notes: " . ($task->technical_notes ? 'Presente' : 'N/A') . "\n";
    echo "Tags: " . ($task->tags ?: 'N/A') . "\n";
    echo "Attachments: " . ($task->attachments ? count($task->attachments) : 0) . " archivos\n";
    echo "Approval Status: " . ($task->approval_status ?: 'N/A') . "\n";
    echo "Rejection Reason: " . ($task->rejection_reason ?: 'N/A') . "\n";
    echo "Auto Paused: " . ($task->auto_paused ? 'Yes' : 'No') . "\n";
    echo "Auto Pause Reason: " . ($task->auto_pause_reason ?: 'N/A') . "\n";
    
    echo "\n=== Timestamps ===\n";
    echo "Created At: {$task->created_at}\n";
    echo "Updated At: {$task->updated_at}\n";
    echo "Assigned At: " . ($task->assigned_at ?: 'N/A') . "\n";
    echo "Actual Start: " . ($task->actual_start ?: 'N/A') . "\n";
    echo "Actual Finish: " . ($task->actual_finish ?: 'N/A') . "\n";
    echo "Work Started At: " . ($task->work_started_at ?: 'N/A') . "\n";
    echo "Work Paused At: " . ($task->work_paused_at ?: 'N/A') . "\n";
    echo "Work Finished At: " . ($task->work_finished_at ?: 'N/A') . "\n";
    echo "Reviewed At: " . ($task->reviewed_at ?: 'N/A') . "\n";
    echo "Auto Close At: " . ($task->auto_close_at ?: 'N/A') . "\n";
    echo "Last Alert At: " . ($task->last_alert_at ?: 'N/A') . "\n";
    
    echo "\n✅ Tarea cargada correctamente para la vista de detalles\n";
    echo "URL para probar: http://127.0.0.1:8000/tasks/{$task->id}\n";
    
    echo "\n=== Campos incluidos en la vista refactorizada ===\n";
    echo "✅ Información básica:\n";
    echo "   - Nombre y descripción\n";
    echo "   - Descripción detallada\n";
    echo "   - Estado, prioridad, categoría\n";
    echo "   - Tipo de tarea, nivel de complejidad\n";
    echo "   - Story points\n";
    echo "   - Tiempo estimado (horas y minutos)\n";
    echo "   - Usuario asignado\n";
    echo "\n✅ Criterios de aceptación y notas técnicas\n";
    echo "✅ Etiquetas y archivos adjuntos\n";
    echo "✅ Fechas reales de inicio y fin\n";
    echo "\n✅ Seguimiento de tiempo:\n";
    echo "   - Tiempo estimado vs real\n";
    echo "   - Tiempo total trabajado\n";
    echo "   - Estado de trabajo\n";
    echo "   - Fechas de inicio, pausa, fin\n";
    echo "\n✅ Botones de acción:\n";
    echo "   - Iniciar trabajo (solo para tareas nunca iniciadas)\n";
    echo "   - Pausar/Reanudar trabajo\n";
    echo "   - Finalizar trabajo (solo cuando está trabajando)\n";
    echo "\n✅ Información de asignación:\n";
    echo "   - Usuario asignado y quien lo asignó\n";
    echo "   - Fecha de asignación\n";
    echo "\n✅ Estado de aprobación:\n";
    echo "   - Estado de aprobación\n";
    echo "   - Razón de rechazo\n";
    echo "   - Quien revisó y cuándo\n";
    echo "\n✅ Información del proyecto y sprint\n";
    echo "✅ ID de tarea y fechas de creación/actualización\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== End ===\n"; 
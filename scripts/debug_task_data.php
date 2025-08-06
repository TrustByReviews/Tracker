<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Task;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debug Task Data ===\n\n";

try {
    // Buscar una tarea para debuggear
    $task = Task::with(['user', 'sprint', 'project', 'assignedBy', 'reviewedBy', 'timeLogs'])
              ->first();
    
    if (!$task) {
        echo "No hay tareas disponibles para debuggear\n";
        exit(1);
    }
    
    echo "=== Tarea ID: {$task->id} ===\n";
    echo "Name: {$task->name}\n";
    echo "Status: {$task->status}\n";
    echo "Priority: {$task->priority}\n";
    echo "Category: {$task->category}\n";
    echo "Task Type: {$task->task_type}\n";
    echo "Complexity Level: {$task->complexity_level}\n";
    echo "Story Points: {$task->story_points}\n";
    echo "Estimated Hours: {$task->estimated_hours}\n";
    echo "Estimated Minutes: {$task->estimated_minutes}\n";
    echo "Actual Hours: {$task->actual_hours}\n";
    echo "Actual Minutes: {$task->actual_minutes}\n";
    echo "Total Time Seconds: {$task->total_time_seconds}\n";
    echo "Is Working: " . ($task->is_working ? 'Yes' : 'No') . "\n";
    
    echo "\n=== Campos que faltan en la vista ===\n";
    echo "Long Description: " . ($task->long_description ? "PRESENTE: " . substr($task->long_description, 0, 50) . "..." : "AUSENTE") . "\n";
    echo "Acceptance Criteria: " . ($task->acceptance_criteria ? "PRESENTE: " . substr($task->acceptance_criteria, 0, 50) . "..." : "AUSENTE") . "\n";
    echo "Technical Notes: " . ($task->technical_notes ? "PRESENTE: " . substr($task->technical_notes, 0, 50) . "..." : "AUSENTE") . "\n";
    echo "Tags: " . ($task->tags ? "PRESENTE: {$task->tags}" : "AUSENTE") . "\n";
    echo "Attachments: " . ($task->attachments ? "PRESENTE: " . count($task->attachments) . " archivos" : "AUSENTE") . "\n";
    
    echo "\n=== Valores específicos ===\n";
    echo "Long Description (raw): " . var_export($task->long_description, true) . "\n";
    echo "Acceptance Criteria (raw): " . var_export($task->acceptance_criteria, true) . "\n";
    echo "Technical Notes (raw): " . var_export($task->technical_notes, true) . "\n";
    echo "Tags (raw): " . var_export($task->tags, true) . "\n";
    echo "Attachments (raw): " . var_export($task->attachments, true) . "\n";
    
    echo "\n=== Campos adicionales del modelo ===\n";
    echo "Approval Status: " . ($task->approval_status ?: 'N/A') . "\n";
    echo "Rejection Reason: " . ($task->rejection_reason ?: 'N/A') . "\n";
    echo "Auto Paused: " . ($task->auto_paused ? 'Yes' : 'No') . "\n";
    echo "Auto Pause Reason: " . ($task->auto_pause_reason ?: 'N/A') . "\n";
    echo "Alert Count: " . ($task->alert_count ?: '0') . "\n";
    
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
    
    echo "\n=== Relaciones ===\n";
    echo "User: " . ($task->user ? $task->user->name : 'N/A') . "\n";
    echo "Project: " . ($task->project ? $task->project->name : 'N/A') . "\n";
    echo "Sprint: " . ($task->sprint ? $task->sprint->name : 'N/A') . "\n";
    echo "Assigned By: " . ($task->assignedBy ? $task->assignedBy->name : 'N/A') . "\n";
    echo "Reviewed By: " . ($task->reviewedBy ? $task->reviewedBy->name : 'N/A') . "\n";
    echo "Time Logs: " . $task->timeLogs->count() . " registros\n";
    
    echo "\n=== Análisis ===\n";
    if (!$task->long_description && !$task->acceptance_criteria && !$task->technical_notes && !$task->tags) {
        echo "❌ Los campos faltantes están vacíos en la base de datos\n";
        echo "   Esto significa que las tareas existentes no tienen estos datos\n";
        echo "   Necesitas crear nuevas tareas con estos campos o actualizar las existentes\n";
    } else {
        echo "✅ Algunos campos tienen datos\n";
        echo "   El problema puede estar en las condiciones v-if de la vista\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== End ===\n"; 
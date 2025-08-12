<?php

/**
 * Análisis de Datos de Rework - Fase 1
 * 
 * Este script analiza todos los campos y datos relacionados con rework
 * para entender qué información está disponible para incluir en los reportes de pago.
 */

require_once 'vendor/autoload.php';

use App\Models\Task;
use App\Models\Bug;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FASE 1: Análisis de Datos de Rework ===\n\n";

// 1. Análisis de campos de rework en la base de datos
echo "🔍 1. ANÁLISIS DE CAMPOS DE REWORK EN BASE DE DATOS\n";
echo "==================================================\n\n";

// Verificar campos en tabla tasks
echo "📋 Tabla TASKS:\n";
$taskColumns = DB::select("SELECT column_name, data_type, is_nullable, column_default 
                          FROM information_schema.columns 
                          WHERE table_name = 'tasks' 
                          AND column_name LIKE '%rework%' 
                          OR column_name LIKE '%rejection%' 
                          OR column_name LIKE '%change%' 
                          OR column_name LIKE '%return%'
                          ORDER BY column_name");

if (empty($taskColumns)) {
    echo "   ❌ No se encontraron campos específicos de rework en tasks\n";
} else {
    foreach ($taskColumns as $column) {
        echo "   ✅ {$column->column_name} ({$column->data_type}, nullable: {$column->is_nullable})\n";
    }
}

// Verificar campos en tabla bugs
echo "\n📋 Tabla BUGS:\n";
$bugColumns = DB::select("SELECT column_name, data_type, is_nullable, column_default 
                         FROM information_schema.columns 
                         WHERE table_name = 'bugs' 
                         AND column_name LIKE '%rework%' 
                         OR column_name LIKE '%rejection%' 
                         OR column_name LIKE '%change%' 
                         OR column_name LIKE '%return%'
                         ORDER BY column_name");

if (empty($bugColumns)) {
    echo "   ❌ No se encontraron campos específicos de rework en bugs\n";
} else {
    foreach ($bugColumns as $column) {
        echo "   ✅ {$column->column_name} ({$column->data_type}, nullable: {$column->is_nullable})\n";
    }
}

// 2. Análisis de datos de rework existentes
echo "\n\n🔍 2. ANÁLISIS DE DATOS DE REWORK EXISTENTES\n";
echo "============================================\n\n";

// Tareas con cambios solicitados por Team Leader
echo "📊 TAREAS CON CAMBIOS SOLICITADOS POR TEAM LEADER:\n";
$tasksWithTLChanges = Task::where('team_leader_requested_changes', true)->count();
echo "   Total: {$tasksWithTLChanges} tareas\n";

if ($tasksWithTLChanges > 0) {
    $recentTLChanges = Task::where('team_leader_requested_changes', true)
        ->whereNotNull('team_leader_requested_changes_at')
        ->orderBy('team_leader_requested_changes_at', 'desc')
        ->limit(5)
        ->get();
    
    echo "   Ejemplos recientes:\n";
    foreach ($recentTLChanges as $task) {
        echo "   - {$task->name} (ID: {$task->id})\n";
        echo "     Fecha: {$task->team_leader_requested_changes_at}\n";
        echo "     Notas: " . ($task->team_leader_change_notes ?: 'Sin notas') . "\n";
        echo "     Desarrollador: " . ($task->user ? $task->user->name : 'No asignado') . "\n\n";
    }
}

// Tareas rechazadas por QA
echo "📊 TAREAS RECHAZADAS POR QA:\n";
$tasksRejectedByQA = Task::whereNotNull('qa_rejection_reason')->count();
echo "   Total: {$tasksRejectedByQA} tareas\n";

if ($tasksRejectedByQA > 0) {
    $recentQARejections = Task::whereNotNull('qa_rejection_reason')
        ->orderBy('qa_reviewed_at', 'desc')
        ->limit(5)
        ->get();
    
    echo "   Ejemplos recientes:\n";
    foreach ($recentQARejections as $task) {
        echo "   - {$task->name} (ID: {$task->id})\n";
        echo "     Fecha: {$task->qa_reviewed_at}\n";
        echo "     Razón: {$task->qa_rejection_reason}\n";
        echo "     Desarrollador: " . ($task->user ? $task->user->name : 'No asignado') . "\n\n";
    }
}

// Bugs con cambios solicitados por Team Leader
echo "📊 BUGS CON CAMBIOS SOLICITADOS POR TEAM LEADER:\n";
$bugsWithTLChanges = Bug::where('team_leader_requested_changes', true)->count();
echo "   Total: {$bugsWithTLChanges} bugs\n";

if ($bugsWithTLChanges > 0) {
    $recentBugTLChanges = Bug::where('team_leader_requested_changes', true)
        ->whereNotNull('team_leader_requested_changes_at')
        ->orderBy('team_leader_requested_changes_at', 'desc')
        ->limit(5)
        ->get();
    
    echo "   Ejemplos recientes:\n";
    foreach ($recentBugTLChanges as $bug) {
        echo "   - {$bug->title} (ID: {$bug->id})\n";
        echo "     Fecha: {$bug->team_leader_requested_changes_at}\n";
        echo "     Notas: " . ($bug->team_leader_change_notes ?: 'Sin notas') . "\n";
        echo "     Desarrollador: " . ($bug->user ? $bug->user->name : 'No asignado') . "\n\n";
    }
}

// Bugs rechazados por QA
echo "📊 BUGS RECHAZADOS POR QA:\n";
$bugsRejectedByQA = Bug::whereNotNull('qa_rejection_reason')->count();
echo "   Total: {$bugsRejectedByQA} bugs\n";

if ($bugsRejectedByQA > 0) {
    $recentBugQARejections = Bug::whereNotNull('qa_rejection_reason')
        ->orderBy('qa_reviewed_at', 'desc')
        ->limit(5)
        ->get();
    
    echo "   Ejemplos recientes:\n";
    foreach ($recentBugQARejections as $bug) {
        echo "   - {$bug->title} (ID: {$bug->id})\n";
        echo "     Fecha: {$bug->qa_reviewed_at}\n";
        echo "     Razón: {$bug->qa_rejection_reason}\n";
        echo "     Desarrollador: " . ($bug->user ? $bug->user->name : 'No asignado') . "\n\n";
    }
}

// 3. Análisis de campos de tiempo de rework
echo "\n\n🔍 3. ANÁLISIS DE CAMPOS DE TIEMPO DE REWORK\n";
echo "============================================\n\n";

// Verificar campos de tiempo en tasks
echo "📋 CAMPOS DE TIEMPO EN TASKS:\n";
$taskTimeColumns = DB::select("SELECT column_name, data_type 
                              FROM information_schema.columns 
                              WHERE table_name = 'tasks' 
                              AND (column_name LIKE '%time%' 
                              OR column_name LIKE '%start%' 
                              OR column_name LIKE '%finish%' 
                              OR column_name LIKE '%duration%')
                              ORDER BY column_name");

foreach ($taskTimeColumns as $column) {
    echo "   ✅ {$column->column_name} ({$column->data_type})\n";
}

// Verificar campos de tiempo en bugs
echo "\n📋 CAMPOS DE TIEMPO EN BUGS:\n";
$bugTimeColumns = DB::select("SELECT column_name, data_type 
                             FROM information_schema.columns 
                             WHERE table_name = 'bugs' 
                             AND (column_name LIKE '%time%' 
                             OR column_name LIKE '%start%' 
                             OR column_name LIKE '%finish%' 
                             OR column_name LIKE '%duration%')
                             ORDER BY column_name");

foreach ($bugTimeColumns as $column) {
    echo "   ✅ {$column->column_name} ({$column->data_type})\n";
}

// 4. Análisis de usuarios que han tenido rework
echo "\n\n🔍 4. ANÁLISIS DE USUARIOS CON REWORK\n";
echo "=====================================\n\n";

// Desarrolladores con tareas que han tenido rework
echo "👥 DESARROLLADORES CON TAREAS EN REWORK:\n";
$developersWithRework = User::whereHas('tasks', function ($query) {
    $query->where('team_leader_requested_changes', true)
          ->orWhereNotNull('qa_rejection_reason');
})->get();

echo "   Total: {$developersWithRework->count()} desarrolladores\n";

foreach ($developersWithRework as $developer) {
    $tlChangesCount = $developer->tasks()->where('team_leader_requested_changes', true)->count();
    $qaRejectionsCount = $developer->tasks()->whereNotNull('qa_rejection_reason')->count();
    
    echo "   - {$developer->name} ({$developer->email})\n";
    echo "     Cambios TL: {$tlChangesCount} | Rechazos QA: {$qaRejectionsCount}\n";
}

// QAs que han rechazado tareas
echo "\n👥 QAS QUE HAN RECHAZADO TAREAS:\n";
$qasWithRejections = User::whereHas('roles', function ($query) {
    $query->where('name', 'qa');
})->whereHas('qaReviewedTasks', function ($query) {
    $query->whereNotNull('qa_rejection_reason');
})->get();

echo "   Total: {$qasWithRejections->count()} QAs\n";

foreach ($qasWithRejections as $qa) {
    $rejectionsCount = $qa->qaReviewedTasks()->whereNotNull('qa_rejection_reason')->count();
    echo "   - {$qa->name} ({$qa->email}) - {$rejectionsCount} rechazos\n";
}

// 5. Resumen y recomendaciones
echo "\n\n🔍 5. RESUMEN Y RECOMENDACIONES\n";
echo "==============================\n\n";

echo "📊 RESUMEN DE DATOS DISPONIBLES:\n";
echo "   ✅ Tareas con cambios TL: {$tasksWithTLChanges}\n";
echo "   ✅ Tareas rechazadas QA: {$tasksRejectedByQA}\n";
echo "   ✅ Bugs con cambios TL: {$bugsWithTLChanges}\n";
echo "   ✅ Bugs rechazados QA: {$bugsRejectedByQA}\n";
echo "   ✅ Desarrolladores afectados: {$developersWithRework->count()}\n";
echo "   ✅ QAs que han rechazado: {$qasWithRejections->count()}\n\n";

echo "🎯 CAMPOS CLAVE PARA REPORTES DE REWORK:\n";
echo "   📋 TAREAS:\n";
echo "      - team_leader_requested_changes (boolean)\n";
echo "      - team_leader_requested_changes_at (timestamp)\n";
echo "      - team_leader_change_notes (text)\n";
echo "      - qa_rejection_reason (text)\n";
echo "      - qa_reviewed_at (timestamp)\n";
echo "      - total_time_seconds (integer) - para calcular tiempo adicional\n\n";

echo "   📋 BUGS:\n";
echo "      - team_leader_requested_changes (boolean)\n";
echo "      - team_leader_requested_changes_at (timestamp)\n";
echo "      - team_leader_change_notes (text)\n";
echo "      - qa_rejection_reason (text)\n";
echo "      - qa_reviewed_at (timestamp)\n";
echo "      - total_time_seconds (integer) - para calcular tiempo adicional\n\n";

echo "💡 RECOMENDACIONES PARA IMPLEMENTACIÓN:\n";
echo "   1. ✅ Usar team_leader_requested_changes para identificar rework de TL\n";
echo "   2. ✅ Usar qa_rejection_reason para identificar rework de QA\n";
echo "   3. ✅ Usar total_time_seconds para calcular tiempo adicional de rework\n";
echo "   4. ✅ Usar timestamps para filtrar por período de rework\n";
echo "   5. ✅ Incluir notas de cambios para contexto\n";
echo "   6. ✅ Separar rework por tipo (TL vs QA)\n";
echo "   7. ✅ Calcular costos adicionales basados en tiempo de rework\n\n";

echo "✅ FASE 1 COMPLETADA - Análisis de datos de rework finalizado.\n";
echo "🚀 Listo para proceder con la Fase 2: Actualizar PaymentService.\n";

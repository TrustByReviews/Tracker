<?php

/**
 * Script para analizar y corregir problemas de números negativos en reportes
 * 
 * Este script identifica y corrige los problemas que causan números negativos
 * en los reportes de pagos y exportación de Excel.
 */

// Inicializar Laravel
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Task;
use App\Models\Bug;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "=== ANÁLISIS DE PROBLEMAS EN REPORTES ===\n\n";

// 1. Analizar datos de QA testing que pueden estar causando números negativos
echo "1. ANALIZANDO DATOS DE QA TESTING...\n";

$qaTestingTasks = Task::whereNotNull('qa_testing_started_at')
    ->whereNotNull('qa_testing_finished_at')
    ->get();

echo "   - Total de tareas con QA testing: " . $qaTestingTasks->count() . "\n";

$negativeQATasks = [];
foreach ($qaTestingTasks as $task) {
    $startTime = Carbon::parse($task->qa_testing_started_at);
    $finishTime = Carbon::parse($task->qa_testing_finished_at);
    
    if ($task->qa_testing_paused_at) {
        $pausedTime = Carbon::parse($task->qa_testing_paused_at);
        $activeTime = $pausedTime->diffInSeconds($startTime);
    } else {
        $activeTime = $finishTime->diffInSeconds($startTime);
    }
    
    if ($activeTime < 0) {
        $negativeQATasks[] = [
            'task_id' => $task->id,
            'task_name' => $task->name,
            'start_time' => $task->qa_testing_started_at,
            'finish_time' => $task->qa_testing_finished_at,
            'paused_time' => $task->qa_testing_paused_at,
            'active_time_seconds' => $activeTime,
            'active_time_hours' => round($activeTime / 3600, 2)
        ];
    }
}

echo "   - Tareas QA con tiempo negativo: " . count($negativeQATasks) . "\n";

if (count($negativeQATasks) > 0) {
    echo "   - Ejemplos de tareas con tiempo negativo:\n";
    foreach (array_slice($negativeQATasks, 0, 3) as $task) {
        echo "     * Task ID: {$task['task_id']} - {$task['task_name']}\n";
        echo "       Start: {$task['start_time']}, Finish: {$task['finish_time']}\n";
        echo "       Active time: {$task['active_time_hours']} hours\n";
    }
}

// 2. Analizar datos de bugs con QA testing
echo "\n2. ANALIZANDO DATOS DE BUGS CON QA TESTING...\n";

$qaTestingBugs = Bug::whereNotNull('qa_testing_started_at')
    ->whereNotNull('qa_testing_finished_at')
    ->get();

echo "   - Total de bugs con QA testing: " . $qaTestingBugs->count() . "\n";

$negativeQABugs = [];
foreach ($qaTestingBugs as $bug) {
    $startTime = Carbon::parse($bug->qa_testing_started_at);
    $finishTime = Carbon::parse($bug->qa_testing_finished_at);
    
    if ($bug->qa_testing_paused_at) {
        $pausedTime = Carbon::parse($bug->qa_testing_paused_at);
        $activeTime = $pausedTime->diffInSeconds($startTime);
    } else {
        $activeTime = $finishTime->diffInSeconds($startTime);
    }
    
    if ($activeTime < 0) {
        $negativeQABugs[] = [
            'bug_id' => $bug->id,
            'bug_title' => $bug->title,
            'start_time' => $bug->qa_testing_started_at,
            'finish_time' => $bug->qa_testing_finished_at,
            'paused_time' => $bug->qa_testing_paused_at,
            'active_time_seconds' => $activeTime,
            'active_time_hours' => round($activeTime / 3600, 2)
        ];
    }
}

echo "   - Bugs QA con tiempo negativo: " . count($negativeQABugs) . "\n";

// 3. Analizar datos de rework
echo "\n3. ANALIZANDO DATOS DE REWORK...\n";

$reworkTasks = Task::where(function ($query) {
    $query->where('team_leader_requested_changes', true)
          ->orWhereNotNull('qa_rejection_reason');
})->get();

echo "   - Total de tareas con rework: " . $reworkTasks->count() . "\n";

$negativeReworkTasks = [];
foreach ($reworkTasks as $task) {
    // Calcular horas de rework
    $reworkHours = 0;
    
    if ($task->retwork_time_seconds && $task->retwork_time_seconds > 0) {
        $reworkHours = round($task->retwork_time_seconds / 3600, 2);
    } elseif ($task->total_time_seconds && $task->original_time_seconds) {
        $additionalTime = $task->total_time_seconds - $task->original_time_seconds;
        if ($additionalTime > 0) {
            $reworkHours = round($additionalTime / 3600, 2);
        }
    } elseif ($task->team_leader_requested_changes || $task->qa_rejection_reason) {
        $originalHours = $task->original_time_seconds ? round($task->original_time_seconds / 3600, 2) : 0;
        $reworkHours = round($originalHours * 0.25, 2);
    }
    
    if ($reworkHours < 0) {
        $negativeReworkTasks[] = [
            'task_id' => $task->id,
            'task_name' => $task->name,
            'rework_hours' => $reworkHours,
            'total_time_seconds' => $task->total_time_seconds,
            'original_time_seconds' => $task->original_time_seconds,
            'retwork_time_seconds' => $task->retwork_time_seconds
        ];
    }
}

echo "   - Tareas rework con horas negativas: " . count($negativeReworkTasks) . "\n";

// 4. Analizar proyecto específico mencionado
echo "\n4. ANALIZANDO PROYECTO 'E-commerce Platform Development'...\n";

$project = Project::where('name', 'E-commerce Platform Development')->first();

if ($project) {
    echo "   - Proyecto encontrado: {$project->name}\n";
    
    $projectTasks = Task::whereHas('sprint', function ($query) use ($project) {
        $query->where('project_id', $project->id);
    })->get();
    
    echo "   - Total de tareas en el proyecto: " . $projectTasks->count() . "\n";
    
    $projectBugs = Bug::where('project_id', $project->id)->get();
    echo "   - Total de bugs en el proyecto: " . $projectBugs->count() . "\n";
    
    // Verificar si hay datos para exportar
    $projectUsers = $project->users()->whereHas('roles', function ($query) {
        $query->whereIn('name', ['developer', 'qa']);
    })->get();
    
    echo "   - Usuarios asignados al proyecto: " . $projectUsers->count() . "\n";
    
    foreach ($projectUsers as $user) {
        $userTasks = $projectTasks->where('user_id', $user->id);
        $userBugs = $projectBugs->where('user_id', $user->id);
        
        echo "     * {$user->name} ({$user->email}): {$userTasks->count()} tareas, {$userBugs->count()} bugs\n";
    }
} else {
    echo "   - Proyecto 'E-commerce Platform Development' no encontrado\n";
}

// 5. Proponer soluciones
echo "\n5. SOLUCIONES PROPUESTAS:\n";

if (count($negativeQATasks) > 0 || count($negativeQABugs) > 0) {
    echo "   a) CORREGIR CÁLCULO DE TIEMPO DE QA TESTING:\n";
    echo "      - El problema está en el cálculo cuando hay pausas\n";
    echo "      - Necesita considerar el tiempo de reanudación\n";
    echo "      - Fórmula actual: pausedTime->diffInSeconds(startTime)\n";
    echo "      - Fórmula correcta: (pausedTime->diffInSeconds(startTime)) + (finishTime->diffInSeconds(resumeTime))\n";
}

if (count($negativeReworkTasks) > 0) {
    echo "   b) CORREGIR CÁLCULO DE HORAS DE REWORK:\n";
    echo "      - Verificar que total_time_seconds >= original_time_seconds\n";
    echo "      - Asegurar que retwork_time_seconds sea positivo\n";
}

echo "   c) VERIFICAR EXPORTACIÓN DE EXCEL:\n";
echo "      - Asegurar que el proyecto tenga datos válidos\n";
echo "      - Verificar que los usuarios tengan tareas asignadas\n";
echo "      - Comprobar que las fechas de inicio y fin sean válidas\n";

echo "\n=== ANÁLISIS COMPLETADO ===\n";

<?php

/**
 * Script de verificación para la Fase 1
 * Verifica que todos los cambios de la base de datos y modelos estén correctamente implementados
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "=== VERIFICACIÓN FASE 1: BASE DE DATOS Y MODELOS ===\n\n";

// Verificar que las migraciones existen
$migrations = [
    '2025_07_29_085000_modify_tasks_table_for_new_workflow.php',
    '2025_07_29_086000_create_task_time_logs_table.php',
    '2025_07_29_087000_add_team_leader_role.php'
];

echo "1. Verificando migraciones...\n";
foreach ($migrations as $migration) {
    $path = __DIR__ . '/../database/migrations/' . $migration;
    if (file_exists($path)) {
        echo "   ✓ {$migration} existe\n";
    } else {
        echo "   ✗ {$migration} NO existe\n";
    }
}

// Verificar que los modelos existen
$models = [
    'Task.php',
    'TaskTimeLog.php'
];

echo "\n2. Verificando modelos...\n";
foreach ($models as $model) {
    $path = __DIR__ . '/../app/Models/' . $model;
    if (file_exists($path)) {
        echo "   ✓ {$model} existe\n";
        
        // Verificar contenido básico del modelo
        $content = file_get_contents($path);
        if (strpos($content, 'class Task') !== false || strpos($content, 'class TaskTimeLog') !== false) {
            echo "     ✓ Clase definida correctamente\n";
        } else {
            echo "     ✗ Clase NO definida correctamente\n";
        }
    } else {
        echo "   ✗ {$model} NO existe\n";
    }
}

// Verificar campos en el modelo Task
echo "\n3. Verificando campos del modelo Task...\n";
$taskModelPath = __DIR__ . '/../app/Models/Task.php';
if (file_exists($taskModelPath)) {
    $content = file_get_contents($taskModelPath);
    
    $requiredFields = [
        'assigned_by',
        'assigned_at',
        'total_time_seconds',
        'work_started_at',
        'is_working',
        'approval_status',
        'reviewed_by',
        'reviewed_at'
    ];
    
    foreach ($requiredFields as $field) {
        if (strpos($content, $field) !== false) {
            echo "   ✓ Campo '{$field}' presente\n";
        } else {
            echo "   ✗ Campo '{$field}' NO presente\n";
        }
    }
    
    // Verificar métodos helper
    $helperMethods = [
        'getFormattedTotalTime',
        'getCurrentSessionTime',
        'getFormattedCurrentTime',
        'assignedBy',
        'reviewedBy',
        'timeLogs'
    ];
    
    echo "\n4. Verificando métodos helper...\n";
    foreach ($helperMethods as $method) {
        if (strpos($content, $method) !== false) {
            echo "   ✓ Método '{$method}' presente\n";
        } else {
            echo "   ✗ Método '{$method}' NO presente\n";
        }
    }
}

// Verificar modelo TaskTimeLog
echo "\n5. Verificando modelo TaskTimeLog...\n";
$taskTimeLogPath = __DIR__ . '/../app/Models/TaskTimeLog.php';
if (file_exists($taskTimeLogPath)) {
    $content = file_get_contents($taskTimeLogPath);
    
    $requiredFields = [
        'task_id',
        'user_id',
        'started_at',
        'paused_at',
        'resumed_at',
        'finished_at',
        'duration_seconds',
        'action',
        'notes'
    ];
    
    foreach ($requiredFields as $field) {
        if (strpos($content, $field) !== false) {
            echo "   ✓ Campo '{$field}' presente\n";
        } else {
            echo "   ✗ Campo '{$field}' NO presente\n";
        }
    }
    
    // Verificar métodos helper
    $helperMethods = [
        'getFormattedDuration',
        'isActive',
        'isPaused'
    ];
    
    echo "\n6. Verificando métodos helper de TaskTimeLog...\n";
    foreach ($helperMethods as $method) {
        if (strpos($content, $method) !== false) {
            echo "   ✓ Método '{$method}' presente\n";
        } else {
            echo "   ✗ Método '{$method}' NO presente\n";
        }
    }
}

echo "\n=== FIN DE VERIFICACIÓN FASE 1 ===\n";
echo "Si todos los elementos están marcados con ✓, la Fase 1 está correctamente implementada.\n";
echo "Si hay elementos marcados con ✗, revisa la implementación antes de continuar.\n"; 
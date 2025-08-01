<?php

/**
 * Script de verificación para la Fase 3
 * Verifica que el sistema de tracking de tiempo en tiempo real esté correctamente implementado
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== VERIFICACIÓN FASE 3: SISTEMA DE TRACKING DE TIEMPO EN TIEMPO REAL ===\n\n";

// Verificar que el servicio existe
echo "1. Verificando TaskTimeTrackingService...\n";
$servicePath = __DIR__ . '/../app/Services/TaskTimeTrackingService.php';
if (file_exists($servicePath)) {
    echo "   ✓ TaskTimeTrackingService.php existe\n";
    
    $content = file_get_contents($servicePath);
    $requiredMethods = [
        'startWork',
        'pauseWork',
        'resumeWork',
        'finishWork',
        'getCurrentWorkTime',
        'getTaskTimeLogs',
        'getActiveTasksForUser',
        'getPausedTasksForUser'
    ];
    
    foreach ($requiredMethods as $method) {
        if (strpos($content, $method) !== false) {
            echo "     ✓ Método '{$method}' presente\n";
        } else {
            echo "     ✗ Método '{$method}' NO presente\n";
        }
    }
} else {
    echo "   ✗ TaskTimeTrackingService.php NO existe\n";
}

// Verificar que el TaskController tiene los nuevos métodos de tracking
echo "\n2. Verificando TaskController (métodos de tracking)...\n";
$controllerPath = __DIR__ . '/../app/Http/Controllers/TaskController.php';
if (file_exists($controllerPath)) {
    $content = file_get_contents($controllerPath);
    
    // Verificar inyección de dependencia
    if (strpos($content, 'TaskTimeTrackingService') !== false) {
        echo "     ✓ TaskTimeTrackingService inyectado\n";
    } else {
        echo "     ✗ TaskTimeTrackingService NO inyectado\n";
    }
    
    $requiredMethods = [
        'startWork',
        'pauseWork',
        'resumeWork',
        'finishWork',
        'getCurrentWorkTime',
        'getTaskTimeLogs',
        'getActiveTasks',
        'getPausedTasks'
    ];
    
    foreach ($requiredMethods as $method) {
        if (strpos($content, $method) !== false) {
            echo "     ✓ Método '{$method}' presente\n";
        } else {
            echo "     ✗ Método '{$method}' NO presente\n";
        }
    }
} else {
    echo "   ✗ TaskController.php NO existe\n";
}

// Verificar rutas de tracking
echo "\n3. Verificando rutas de tracking...\n";
$routesPath = __DIR__ . '/../routes/web.php';
if (file_exists($routesPath)) {
    echo "   ✓ web.php existe\n";
    
    $content = file_get_contents($routesPath);
    $requiredRoutes = [
        'tasks/{task}/start-work',
        'tasks/{task}/pause-work',
        'tasks/{task}/resume-work',
        'tasks/{task}/finish-work',
        'tasks/{task}/current-time',
        'tasks/{task}/time-logs',
        'tasks/active',
        'tasks/paused'
    ];
    
    foreach ($requiredRoutes as $route) {
        if (strpos($content, $route) !== false) {
            echo "     ✓ Ruta '{$route}' presente\n";
        } else {
            echo "     ✗ Ruta '{$route}' NO presente\n";
        }
    }
} else {
    echo "   ✗ web.php NO existe\n";
}

// Verificar que el modelo TaskTimeLog existe
echo "\n4. Verificando modelo TaskTimeLog...\n";
$taskTimeLogPath = __DIR__ . '/../app/Models/TaskTimeLog.php';
if (file_exists($taskTimeLogPath)) {
    echo "   ✓ TaskTimeLog.php existe\n";
    
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
            echo "     ✓ Campo '{$field}' presente\n";
        } else {
            echo "     ✗ Campo '{$field}' NO presente\n";
        }
    }
    
    // Verificar métodos helper
    $helperMethods = [
        'getFormattedDuration',
        'isActive',
        'isPaused'
    ];
    
    echo "\n5. Verificando métodos helper de TaskTimeLog...\n";
    foreach ($helperMethods as $method) {
        if (strpos($content, $method) !== false) {
            echo "     ✓ Método '{$method}' presente\n";
        } else {
            echo "     ✗ Método '{$method}' NO presente\n";
        }
    }
} else {
    echo "   ✗ TaskTimeLog.php NO existe\n";
}

// Verificar que el modelo Task tiene los campos de tracking
echo "\n6. Verificando campos de tracking en modelo Task...\n";
$taskModelPath = __DIR__ . '/../app/Models/Task.php';
if (file_exists($taskModelPath)) {
    $content = file_get_contents($taskModelPath);
    
    $requiredFields = [
        'total_time_seconds',
        'work_started_at',
        'is_working',
        'approval_status'
    ];
    
    foreach ($requiredFields as $field) {
        if (strpos($content, $field) !== false) {
            echo "     ✓ Campo '{$field}' presente\n";
        } else {
            echo "     ✗ Campo '{$field}' NO presente\n";
        }
    }
    
    // Verificar métodos helper de tiempo
    $timeMethods = [
        'getFormattedTotalTime',
        'getCurrentSessionTime',
        'getFormattedCurrentTime'
    ];
    
    echo "\n7. Verificando métodos helper de tiempo en Task...\n";
    foreach ($timeMethods as $method) {
        if (strpos($content, $method) !== false) {
            echo "     ✓ Método '{$method}' presente\n";
        } else {
            echo "     ✗ Método '{$method}' NO presente\n";
        }
    }
} else {
    echo "   ✗ Task.php NO existe\n";
}

// Verificar migración de task_time_logs
echo "\n8. Verificando migración de task_time_logs...\n";
$migrationPath = __DIR__ . '/../database/migrations/2025_07_29_086000_create_task_time_logs_table.php';
if (file_exists($migrationPath)) {
    echo "   ✓ Migración de task_time_logs existe\n";
} else {
    echo "   ✗ Migración de task_time_logs NO existe\n";
}

// Verificar migración de modificación de tasks
echo "\n9. Verificando migración de modificación de tasks...\n";
$taskMigrationPath = __DIR__ . '/../database/migrations/2025_07_29_085000_modify_tasks_table_for_new_workflow.php';
if (file_exists($taskMigrationPath)) {
    echo "   ✓ Migración de modificación de tasks existe\n";
} else {
    echo "   ✗ Migración de modificación de tasks NO existe\n";
}

echo "\n=== FIN DE VERIFICACIÓN FASE 3 ===\n";
echo "Si todos los elementos están marcados con ✓, la Fase 3 está correctamente implementada.\n";
echo "Si hay elementos marcados con ✗, revisa la implementación antes de continuar.\n"; 
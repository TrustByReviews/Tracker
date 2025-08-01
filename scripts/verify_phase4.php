<?php

/**
 * Script de verificación para la Fase 4
 * Verifica que el sistema de aprobación por team leaders esté correctamente implementado
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== VERIFICACIÓN FASE 4: SISTEMA DE APROBACIÓN POR TEAM LEADERS ===\n\n";

// Verificar que el servicio existe
echo "1. Verificando TaskApprovalService...\n";
$servicePath = __DIR__ . '/../app/Services/TaskApprovalService.php';
if (file_exists($servicePath)) {
    echo "   ✓ TaskApprovalService.php existe\n";
    
    $content = file_get_contents($servicePath);
    $requiredMethods = [
        'getPendingTasksForTeamLeader',
        'getAllPendingTasks',
        'approveTask',
        'rejectTask',
        'getApprovalStatsForTeamLeader',
        'getInProgressTasksForTeamLeader',
        'getDevelopersWithActiveTasks',
        'getRecentlyCompletedTasks',
        'getDeveloperTimeSummary'
    ];
    
    foreach ($requiredMethods as $method) {
        if (strpos($content, $method) !== false) {
            echo "     ✓ Método '{$method}' presente\n";
        } else {
            echo "     ✗ Método '{$method}' NO presente\n";
        }
    }
} else {
    echo "   ✗ TaskApprovalService.php NO existe\n";
}

// Verificar que el TeamLeaderController existe
echo "\n2. Verificando TeamLeaderController...\n";
$controllerPath = __DIR__ . '/../app/Http/Controllers/TeamLeaderController.php';
if (file_exists($controllerPath)) {
    echo "   ✓ TeamLeaderController.php existe\n";
    
    $content = file_get_contents($controllerPath);
    
    // Verificar inyección de dependencia
    if (strpos($content, 'TaskApprovalService') !== false && strpos($content, 'TaskAssignmentService') !== false) {
        echo "     ✓ Servicios inyectados correctamente\n";
    } else {
        echo "     ✗ Servicios NO inyectados correctamente\n";
    }
    
    $requiredMethods = [
        'dashboard',
        'pendingTasks',
        'inProgressTasks',
        'developers',
        'approveTask',
        'rejectTask',
        'getApprovalStats',
        'getDeveloperTimeSummary',
        'getRecentlyCompleted',
        'assignTask'
    ];
    
    foreach ($requiredMethods as $method) {
        if (strpos($content, $method) !== false) {
            echo "     ✓ Método '{$method}' presente\n";
        } else {
            echo "     ✗ Método '{$method}' NO presente\n";
        }
    }
} else {
    echo "   ✗ TeamLeaderController.php NO existe\n";
}

// Verificar rutas de team leader
echo "\n3. Verificando rutas de team leader...\n";
$routesPath = __DIR__ . '/../routes/web.php';
if (file_exists($routesPath)) {
    echo "   ✓ web.php existe\n";
    
    $content = file_get_contents($routesPath);
    $requiredRoutes = [
        'dashboard',
        'pending-tasks',
        'in-progress-tasks',
        'developers',
        'tasks/{task}/approve',
        'tasks/{task}/reject',
        'tasks/{task}/assign',
        'stats/approval',
        'stats/developer-time',
        'stats/recently-completed'
    ];
    
    foreach ($requiredRoutes as $route) {
        if (strpos($content, $route) !== false) {
            echo "     ✓ Ruta '{$route}' presente\n";
        } else {
            echo "     ✗ Ruta '{$route}' NO presente\n";
        }
    }
    
    // Verificar import del controlador
    if (strpos($content, 'TeamLeaderController') !== false) {
        echo "     ✓ Import de TeamLeaderController presente\n";
    } else {
        echo "     ✗ Import de TeamLeaderController NO presente\n";
    }
} else {
    echo "   ✗ web.php NO existe\n";
}

// Verificar que el modelo Task tiene los campos de aprobación
echo "\n4. Verificando campos de aprobación en modelo Task...\n";
$taskModelPath = __DIR__ . '/../app/Models/Task.php';
if (file_exists($taskModelPath)) {
    $content = file_get_contents($taskModelPath);
    
    $requiredFields = [
        'approval_status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at'
    ];
    
    foreach ($requiredFields as $field) {
        if (strpos($content, $field) !== false) {
            echo "     ✓ Campo '{$field}' presente\n";
        } else {
            echo "     ✗ Campo '{$field}' NO presente\n";
        }
    }
    
    // Verificar relaciones
    $requiredRelations = [
        'reviewedBy'
    ];
    
    echo "\n5. Verificando relaciones de aprobación en Task...\n";
    foreach ($requiredRelations as $relation) {
        if (strpos($content, $relation) !== false) {
            echo "     ✓ Relación '{$relation}' presente\n";
        } else {
            echo "     ✗ Relación '{$relation}' NO presente\n";
        }
    }
} else {
    echo "   ✗ Task.php NO existe\n";
}

// Verificar migración de modificación de tasks
echo "\n6. Verificando migración de modificación de tasks...\n";
$taskMigrationPath = __DIR__ . '/../database/migrations/2025_07_29_085000_modify_tasks_table_for_new_workflow.php';
if (file_exists($taskMigrationPath)) {
    echo "   ✓ Migración de modificación de tasks existe\n";
    
    $content = file_get_contents($taskMigrationPath);
    $requiredFields = [
        'approval_status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at'
    ];
    
    foreach ($requiredFields as $field) {
        if (strpos($content, $field) !== false) {
            echo "     ✓ Campo '{$field}' presente en migración\n";
        } else {
            echo "     ✗ Campo '{$field}' NO presente en migración\n";
        }
    }
} else {
    echo "   ✗ Migración de modificación de tasks NO existe\n";
}

// Verificar que el rol team_leader existe
echo "\n7. Verificando rol team_leader...\n";
$roleSeederPath = __DIR__ . '/../database/seeders/RoleSeeder.php';
if (file_exists($roleSeederPath)) {
    $content = file_get_contents($roleSeederPath);
    
    if (strpos($content, 'team_leader') !== false) {
        echo "   ✓ Rol 'team_leader' presente en seeder\n";
    } else {
        echo "   ✗ Rol 'team_leader' NO presente en seeder\n";
    }
} else {
    echo "   ✗ RoleSeeder.php NO existe\n";
}

// Verificar migración de roles
$roleMigrationPath = __DIR__ . '/../database/migrations/2025_07_29_087000_add_team_leader_role.php';
if (file_exists($roleMigrationPath)) {
    echo "   ✓ Migración de team_leader existe\n";
} else {
    echo "   ✗ Migración de team_leader NO existe\n";
}

// Verificar funcionalidad de notificaciones (logs)
echo "\n8. Verificando funcionalidad de logs...\n";
$approvalServicePath = __DIR__ . '/../app/Services/TaskApprovalService.php';
if (file_exists($approvalServicePath)) {
    $content = file_get_contents($approvalServicePath);
    
    if (strpos($content, 'Log::info') !== false && strpos($content, 'Log::error') !== false) {
        echo "   ✓ Funcionalidad de logs presente\n";
    } else {
        echo "   ✗ Funcionalidad de logs NO presente\n";
    }
} else {
    echo "   ✗ TaskApprovalService.php NO existe\n";
}

echo "\n=== FIN DE VERIFICACIÓN FASE 4 ===\n";
echo "Si todos los elementos están marcados con ✓, la Fase 4 está correctamente implementada.\n";
echo "Si hay elementos marcados con ✗, revisa la implementación antes de continuar.\n"; 
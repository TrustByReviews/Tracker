<?php

/**
 * Script de verificación para la Fase 2
 * Verifica que el sistema de asignación de tareas esté correctamente implementado
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== VERIFICACIÓN FASE 2: SISTEMA DE ASIGNACIÓN DE TAREAS ===\n\n";

// Verificar que el servicio existe
echo "1. Verificando TaskAssignmentService...\n";
$servicePath = __DIR__ . '/../app/Services/TaskAssignmentService.php';
if (file_exists($servicePath)) {
    echo "   ✓ TaskAssignmentService.php existe\n";
    
    $content = file_get_contents($servicePath);
    $requiredMethods = [
        'assignTaskByTeamLeader',
        'selfAssignTask',
        'getAvailableTasksForDeveloper',
        'getAssignedTasksForDeveloper',
        'getAvailableDevelopersForProject',
        'getTeamLeadersForProject'
    ];
    
    foreach ($requiredMethods as $method) {
        if (strpos($content, $method) !== false) {
            echo "     ✓ Método '{$method}' presente\n";
        } else {
            echo "     ✗ Método '{$method}' NO presente\n";
        }
    }
} else {
    echo "   ✗ TaskAssignmentService.php NO existe\n";
}

// Verificar que el TaskController tiene los nuevos métodos
echo "\n2. Verificando TaskController...\n";
$controllerPath = __DIR__ . '/../app/Http/Controllers/TaskController.php';
if (file_exists($controllerPath)) {
    echo "   ✓ TaskController.php existe\n";
    
    $content = file_get_contents($controllerPath);
    
    // Verificar inyección de dependencia
    if (strpos($content, 'TaskAssignmentService') !== false) {
        echo "     ✓ TaskAssignmentService inyectado\n";
    } else {
        echo "     ✗ TaskAssignmentService NO inyectado\n";
    }
    
    $requiredMethods = [
        'assignTask',
        'selfAssignTask',
        'getAvailableTasks',
        'getMyTasks',
        'getAvailableDevelopers'
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

// Verificar rutas
echo "\n3. Verificando rutas...\n";
$routesPath = __DIR__ . '/../routes/web.php';
if (file_exists($routesPath)) {
    echo "   ✓ web.php existe\n";
    
    $content = file_get_contents($routesPath);
    $requiredRoutes = [
        'tasks/{task}/assign',
        'tasks/{task}/self-assign',
        'tasks/available',
        'tasks/my-tasks',
        'projects/{project}/developers'
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

// Verificar que el modelo Task tiene las relaciones necesarias
echo "\n4. Verificando modelo Task...\n";
$taskModelPath = __DIR__ . '/../app/Models/Task.php';
if (file_exists($taskModelPath)) {
    $content = file_get_contents($taskModelPath);
    
    $requiredRelations = [
        'assignedBy',
        'reviewedBy',
        'timeLogs'
    ];
    
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

// Verificar que el modelo User tiene el rol team_leader
echo "\n5. Verificando roles...\n";
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

echo "\n=== FIN DE VERIFICACIÓN FASE 2 ===\n";
echo "Si todos los elementos están marcados con ✓, la Fase 2 está correctamente implementada.\n";
echo "Si hay elementos marcados con ✗, revisa la implementación antes de continuar.\n"; 
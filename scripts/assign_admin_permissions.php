<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== ASIGNANDO PERMISOS A ADMINISTRADORES ===\n\n";

// Obtener el rol de administrador
$adminRole = \App\Models\Role::where('value', 'admin')->first();

if (!$adminRole) {
    echo "Error: No se encontró el rol de administrador\n";
    exit(1);
}

echo "Rol de administrador encontrado: {$adminRole->name}\n\n";

// Lista de permisos que necesitan los administradores
$requiredPermissions = [
    // Permisos de administrador
    'admin.dashboard',
    'admin.permissions',
    'admin.users',
    
    // Permisos de usuarios
    'users.view',
    'users.create',
    'users.edit',
    'users.delete',
    
    // Permisos de pagos
    'payments.view',
    'payments.generate',
    'payments.manage',
    
    // Permisos de actividad de desarrolladores
    'developer-activity.view',
    'developer-activity.export',
    
    // Permisos de gestión de permisos
    'permissions.manage',
    'permissions.view',
    
    // Permisos de proyectos
    'projects.view',
    'projects.create',
    'projects.edit',
    'projects.delete',
    
    // Permisos de sprints
    'sprints.view',
    'sprints.create',
    'sprints.edit',
    'sprints.delete',
    
    // Permisos de tareas
    'tasks.view',
    'tasks.create',
    'tasks.edit',
    'tasks.delete',
    'tasks.assign',
    'tasks.approve',
    
    // Permisos de bugs
    'bugs.view',
    'bugs.create',
    'bugs.edit',
    'bugs.delete',
    
    // Permisos de team leader
    'team-leader.dashboard',
    'team-leader.users',
    'team-leader.projects',
    'team-leader.tasks',
    'team-leader.sprints',
    'team-leader.bugs',
    'team-leader.reports',
];

echo "Permisos requeridos: " . count($requiredPermissions) . "\n\n";

// Crear o obtener permisos
$createdPermissions = [];
foreach ($requiredPermissions as $permissionName) {
    $permission = \App\Models\Permission::firstOrCreate([
        'name' => $permissionName
    ], [
        'display_name' => ucwords(str_replace('.', ' ', $permissionName)),
        'description' => 'Permiso para ' . str_replace('.', ' ', $permissionName),
        'module' => explode('.', $permissionName)[0],
        'is_active' => true
    ]);
    
    $createdPermissions[] = $permission;
    echo "- Creado/Obtenido permiso: {$permission->name}\n";
}

echo "\nAsignando permisos al rol de administrador...\n";

// Asignar todos los permisos al rol de administrador
foreach ($createdPermissions as $permission) {
    $adminRole->permissions()->attach($permission->id);
}

echo "Permisos asignados correctamente al rol de administrador.\n\n";

// Verificar que los permisos se asignaron correctamente
$assignedPermissions = $adminRole->permissions;
echo "Permisos asignados al rol de administrador: " . $assignedPermissions->count() . "\n";

foreach ($assignedPermissions as $permission) {
    echo "- {$permission->name}\n";
}

echo "\n=== FIN ===\n";

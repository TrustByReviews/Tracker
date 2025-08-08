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

echo "=== VERIFICANDO PERMISOS DE ADMINISTRADORES ===\n\n";

// Obtener todos los usuarios administradores
$adminUsers = \App\Models\User::whereHas('roles', function($query) {
    $query->where('value', 'admin');
})->with(['roles.permissions', 'directPermissions'])->get();

echo "Usuarios administradores encontrados: " . $adminUsers->count() . "\n\n";

foreach ($adminUsers as $user) {
    echo "Usuario: {$user->name} ({$user->email})\n";
    echo "Roles: ";
    foreach ($user->roles as $role) {
        echo "{$role->name} ({$role->value}) ";
    }
    echo "\n";
    
    echo "Permisos directos: ";
    if ($user->directPermissions->count() > 0) {
        foreach ($user->directPermissions as $permission) {
            echo "{$permission->name} ";
        }
    } else {
        echo "Ninguno";
    }
    echo "\n";
    
    echo "Permisos por roles: ";
    $rolePermissions = [];
    foreach ($user->roles as $role) {
        foreach ($role->permissions as $permission) {
            $rolePermissions[] = $permission->name;
        }
    }
    if (!empty($rolePermissions)) {
        echo implode(', ', array_unique($rolePermissions));
    } else {
        echo "Ninguno";
    }
    echo "\n\n";
}

// Verificar permisos específicos que se usan en el sidebar
echo "=== PERMISOS ESPECÍFICOS DEL SIDEBAR ===\n";
$requiredPermissions = [
    'admin.permissions',
    'users.view',
    'payments.view',
    'developer-activity.view',
    'permissions.manage'
];

$requiredModules = [
    'projects',
    'sprints',
    'tasks',
    'bugs'
];

echo "Permisos requeridos:\n";
foreach ($requiredPermissions as $permission) {
    $hasPermission = false;
    foreach ($adminUsers as $user) {
        // Verificar permisos directos
        if ($user->directPermissions->where('name', $permission)->count() > 0) {
            $hasPermission = true;
            break;
        }
        // Verificar permisos por roles
        foreach ($user->roles as $role) {
            if ($role->permissions->where('name', $permission)->count() > 0) {
                $hasPermission = true;
                break 2;
            }
        }
    }
    echo "- {$permission}: " . ($hasPermission ? 'SÍ' : 'NO') . "\n";
}

echo "\nMódulos requeridos:\n";
foreach ($requiredModules as $module) {
    $hasModule = false;
    foreach ($adminUsers as $user) {
        // Verificar permisos directos
        if ($user->directPermissions->where('module', $module)->where('is_active', true)->count() > 0) {
            $hasModule = true;
            break;
        }
        // Verificar permisos por roles
        foreach ($user->roles as $role) {
            if ($role->permissions->where('module', $module)->where('is_active', true)->count() > 0) {
                $hasModule = true;
                break 2;
            }
        }
    }
    echo "- {$module}: " . ($hasModule ? 'SÍ' : 'NO') . "\n";
}

echo "\n=== FIN ===\n";

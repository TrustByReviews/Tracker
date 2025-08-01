<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

echo "=== VERIFICACIÓN DEL SISTEMA RBAC ===\n\n";

// Verificar roles y permisos
echo "1. ROLES Y PERMISOS:\n";
$roles = Role::with('permissions')->get();
foreach ($roles as $role) {
    echo "- {$role->name}: {$role->permissions->count()} permisos\n";
    foreach ($role->permissions as $permission) {
        echo "  * {$permission->name} ({$permission->module})\n";
    }
    echo "\n";
}

// Verificar usuarios
echo "2. USUARIOS Y SUS ROLES:\n";
$users = User::with('roles')->get();
foreach ($users as $user) {
    echo "- {$user->name} ({$user->email}): ";
    if ($user->roles->count() > 0) {
        $roleNames = $user->roles->pluck('name')->toArray();
        echo implode(', ', $roleNames);
    } else {
        echo "Sin roles";
    }
    echo "\n";
}

// Verificar permisos de un usuario admin
echo "\n3. VERIFICACIÓN DE PERMISOS DE ADMIN:\n";
$adminUser = User::whereHas('roles', function($query) {
    $query->where('name', 'admin');
})->first();

if ($adminUser) {
    echo "Usuario admin encontrado: {$adminUser->name}\n";
    
    // Verificar algunos permisos específicos
    $permissionsToCheck = [
        'admin.dashboard',
        'admin.users',
        'projects.view',
        'tasks.view'
    ];
    
    foreach ($permissionsToCheck as $permission) {
        $hasPermission = $adminUser->hasPermission($permission);
        echo "- {$permission}: " . ($hasPermission ? 'SÍ' : 'NO') . "\n";
    }
    
    // Mostrar todos los permisos del usuario
    echo "\nTodos los permisos del admin:\n";
    $allPermissions = $adminUser->getAllPermissions();
    foreach ($allPermissions as $permission) {
        echo "- {$permission->name} ({$permission->module})\n";
    }
} else {
    echo "No se encontró usuario admin\n";
}

echo "\n=== FIN DE VERIFICACIÓN ===\n"; 
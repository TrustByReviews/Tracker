<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Role;
use App\Models\Permission;

echo "=== VERIFICACIÓN DE ROLES Y PERMISOS ===\n\n";

// Verificar roles
echo "ROLES EXISTENTES:\n";
$roles = Role::all();
foreach ($roles as $role) {
    echo "- {$role->name} (ID: {$role->id})\n";
}

echo "\nPERMISOS EXISTENTES:\n";
$permissions = Permission::all();
foreach ($permissions as $permission) {
    echo "- {$permission->name} ({$permission->module})\n";
}

echo "\n=== FIN DE VERIFICACIÓN ===\n"; 
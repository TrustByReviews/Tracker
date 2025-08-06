<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== CREANDO PERMISOS PARA DASHBOARD DE ACTIVIDAD ===\n\n";

try {
    // Inicializar Laravel
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "1. Creando permisos...\n";
    
    $permissions = [
        'developer-activity.view',
        'developer-activity.export'
    ];
    
    $permissionData = [
        'developer-activity.view' => 'View Developer Activity Dashboard',
        'developer-activity.export' => 'Export Developer Activity Reports'
    ];
    
    foreach ($permissionData as $permission => $displayName) {
        $perm = \App\Models\Permission::firstOrCreate([
            'name' => $permission
        ], [
            'display_name' => $displayName
        ]);
        
        if ($perm->wasRecentlyCreated) {
            echo "✓ Creado permiso: {$permission}\n";
        } else {
            echo "⚠ Permiso ya existe: {$permission}\n";
        }
    }
    
    echo "\n2. Asignando permisos a roles...\n";
    
    // Asignar permisos a admin
    $adminRole = \App\Models\Role::where('name', 'admin')->first();
    if ($adminRole) {
        foreach ($permissions as $permission) {
            $perm = \App\Models\Permission::where('name', $permission)->first();
            if ($perm && !$adminRole->hasPermissionTo($permission)) {
                $adminRole->givePermissionTo($permission);
                echo "✓ Permiso '{$permission}' asignado a rol 'admin'\n";
            }
        }
    }
    
    // Asignar permisos a team_leader
    $teamLeaderRole = \App\Models\Role::where('name', 'team_leader')->first();
    if ($teamLeaderRole) {
        foreach ($permissions as $permission) {
            $perm = \App\Models\Permission::where('name', $permission)->first();
            if ($perm && !$teamLeaderRole->hasPermissionTo($permission)) {
                $teamLeaderRole->givePermissionTo($permission);
                echo "✓ Permiso '{$permission}' asignado a rol 'team_leader'\n";
            }
        }
    }
    
    echo "\n✅ Permisos creados y asignados exitosamente!\n";
    echo "📋 Permisos disponibles:\n";
    foreach ($permissions as $permission) {
        echo "  - {$permission}\n";
    }

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
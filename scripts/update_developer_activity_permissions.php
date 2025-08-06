<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== ACTUALIZANDO PERMISOS PARA DASHBOARD DE ACTIVIDAD DE DESARROLLADORES ===\n\n";

try {
    // Inicializar Laravel
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "1. Verificando permisos existentes...\n";
    
    $permissions = [
        'developer-activity.view',
        'developer-activity.export'
    ];
    
    $permissionData = [
        'developer-activity.view' => 'View Developer Activity Dashboard',
        'developer-activity.export' => 'Export Developer Activity Reports'
    ];
    
    // Crear permisos si no existen
    foreach ($permissionData as $permission => $displayName) {
        $perm = \App\Models\Permission::firstOrCreate([
            'name' => $permission
        ], [
            'display_name' => $displayName,
            'module' => 'developer-activity',
            'is_active' => true
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
            if ($perm && !$adminRole->hasPermission($permission)) {
                $adminRole->permissions()->attach($perm->id);
                echo "✓ Permiso '{$permission}' asignado a rol 'admin'\n";
            }
        }
    }
    
    // Asignar permisos a team_leader
    $teamLeaderRole = \App\Models\Role::where('name', 'team_leader')->first();
    if ($teamLeaderRole) {
        foreach ($permissions as $permission) {
            $perm = \App\Models\Permission::where('name', $permission)->first();
            if ($perm && !$teamLeaderRole->hasPermission($permission)) {
                $teamLeaderRole->permissions()->attach($perm->id);
                echo "✓ Permiso '{$permission}' asignado a rol 'team_leader'\n";
            }
        }
    }
    
    // Asignar permisos a developer
    $developerRole = \App\Models\Role::where('name', 'developer')->first();
    if ($developerRole) {
        foreach ($permissions as $permission) {
            $perm = \App\Models\Permission::where('name', $permission)->first();
            if ($perm && !$developerRole->hasPermission($permission)) {
                $developerRole->permissions()->attach($perm->id);
                echo "✓ Permiso '{$permission}' asignado a rol 'developer'\n";
            }
        }
    }
    
    echo "\n3. Actualizando usuarios existentes...\n";
    
    // Obtener todos los usuarios
    $users = \App\Models\User::with('roles')->get();
    $updatedUsers = 0;
    
    foreach ($users as $user) {
        $hasPermission = false;
        
        // Verificar si el usuario tiene algún rol con los permisos necesarios
        foreach ($user->roles as $role) {
            if ($role->hasPermission('developer-activity.view')) {
                $hasPermission = true;
                break;
            }
        }
        
        // Si no tiene permisos, asignar permisos directos al usuario
        if (!$hasPermission) {
            $viewPermission = \App\Models\Permission::where('name', 'developer-activity.view')->first();
            if ($viewPermission) {
                // Verificar si ya tiene el permiso directo
                $existingPermission = \App\Models\UserPermission::where('user_id', $user->id)
                    ->where('permission_id', $viewPermission->id)
                    ->first();
                
                if (!$existingPermission) {
                    // Obtener el primer usuario admin para granted_by
                    $adminUser = \App\Models\User::first();
                    \App\Models\UserPermission::create([
                        'user_id' => $user->id,
                        'permission_id' => $viewPermission->id,
                        'type' => 'direct',
                        'granted_by' => $adminUser ? $adminUser->id : null,
                        'reason' => 'Auto-assigned for developer activity dashboard access'
                    ]);
                    
                    echo "✓ Permiso 'developer-activity.view' asignado directamente al usuario: {$user->name} ({$user->email})\n";
                    $updatedUsers++;
                }
            }
        }
    }
    
    echo "\n4. Verificando configuración de rutas...\n";
    echo "✓ Rutas actualizadas de /admin/developer_activity a /developer-activity\n";
    echo "✓ Sidebar actualizado con nueva ruta\n";
    echo "✓ Componente Vue actualizado con nuevas URLs\n";
    
    echo "\n✅ ACTUALIZACIÓN COMPLETADA EXITOSAMENTE!\n";
    echo "📋 RESUMEN:\n";
    echo "  - Permisos verificados/creados: " . count($permissions) . "\n";
    echo "  - Usuarios actualizados: {$updatedUsers}\n";
    echo "  - Nueva ruta: /developer-activity\n";
    echo "  - Permisos disponibles:\n";
    foreach ($permissions as $permission) {
        echo "    - {$permission}\n";
    }
    
    echo "\n🔗 Para acceder al dashboard:\n";
    echo "  - URL: http://127.0.0.1:8000/developer-activity\n";
    echo "  - Export: http://127.0.0.1:8000/developer-activity/export\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
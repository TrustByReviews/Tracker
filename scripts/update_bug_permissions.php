<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔐 Actualizando permisos para el módulo de Bugs...\n";

try {
    DB::beginTransaction();

    // Permisos para bugs
    $bugPermissions = [
        'bugs.view' => 'Ver bugs',
        'bugs.create' => 'Crear bugs',
        'bugs.edit' => 'Editar bugs',
        'bugs.delete' => 'Eliminar bugs',
        'bugs.assign' => 'Asignar bugs',
        'bugs.resolve' => 'Resolver bugs',
        'bugs.comment' => 'Comentar en bugs',
        'bugs.export' => 'Exportar bugs',
        'bugs.manage' => 'Gestionar bugs (todos los permisos)',
    ];

    $createdPermissions = [];
    $existingPermissions = [];

    // Crear o verificar permisos
    foreach ($bugPermissions as $permissionName => $permissionDescription) {
        $permission = Permission::where('name', $permissionName)->first();
        
        if (!$permission) {
            $permission = Permission::create([
                'name' => $permissionName,
                'display_name' => $permissionDescription,
                'description' => $permissionDescription,
                'module' => 'bugs',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $createdPermissions[] = $permissionName;
            echo "✅ Permiso creado: {$permissionName}\n";
        } else {
            $existingPermissions[] = $permissionName;
            echo "ℹ️  Permiso ya existe: {$permissionName}\n";
        }
    }

    // Obtener roles
    $adminRole = Role::where('name', 'admin')->first();
    $teamLeaderRole = Role::where('name', 'team_leader')->first();
    $developerRole = Role::where('name', 'developer')->first();

    if (!$adminRole) {
        echo "❌ Error: No se encontró el rol 'admin'\n";
        exit(1);
    }

    if (!$teamLeaderRole) {
        echo "❌ Error: No se encontró el rol 'team_leader'\n";
        exit(1);
    }

    if (!$developerRole) {
        echo "❌ Error: No se encontró el rol 'developer'\n";
        exit(1);
    }

    // Asignar permisos a roles
    echo "\n👥 Asignando permisos a roles...\n";

    // Admin: todos los permisos
    $adminPermissions = array_keys($bugPermissions);
    foreach ($adminPermissions as $permissionName) {
        $permission = Permission::where('name', $permissionName)->first();
        if ($permission && !$adminRole->permissions()->where('permission_id', $permission->id)->exists()) {
            $adminRole->permissions()->attach($permission->id);
            echo "✅ Permiso '{$permissionName}' asignado a Admin\n";
        }
    }

    // Team Leader: permisos de gestión pero no eliminación
    $teamLeaderPermissions = [
        'bugs.view',
        'bugs.create',
        'bugs.edit',
        'bugs.assign',
        'bugs.resolve',
        'bugs.comment',
        'bugs.export',
    ];
    
    foreach ($teamLeaderPermissions as $permissionName) {
        $permission = Permission::where('name', $permissionName)->first();
        if ($permission && !$teamLeaderRole->permissions()->where('permission_id', $permission->id)->exists()) {
            $teamLeaderRole->permissions()->attach($permission->id);
            echo "✅ Permiso '{$permissionName}' asignado a Team Leader\n";
        }
    }

    // Developer: permisos básicos
    $developerPermissions = [
        'bugs.view',
        'bugs.create',
        'bugs.edit',
        'bugs.assign',
        'bugs.resolve',
        'bugs.comment',
    ];
    
    foreach ($developerPermissions as $permissionName) {
        $permission = Permission::where('name', $permissionName)->first();
        if ($permission && !$developerRole->permissions()->where('permission_id', $permission->id)->exists()) {
            $developerRole->permissions()->attach($permission->id);
            echo "✅ Permiso '{$permissionName}' asignado a Developer\n";
        }
    }

    // Actualizar usuarios existentes
    echo "\n👤 Actualizando usuarios existentes...\n";

    // Obtener todos los usuarios con roles
    $users = User::with('roles')->get();
    
    foreach ($users as $user) {
        $userRoles = $user->roles;
        
        if ($userRoles->count() > 0) {
            $role = $userRoles->first();
            echo "👤 Usuario: {$user->name} ({$user->email}) - Rol: {$role->name}\n";
            
            // Verificar que el usuario tenga los permisos correctos según su rol
            $expectedPermissions = [];
            
            switch ($role->name) {
                case 'admin':
                    $expectedPermissions = $adminPermissions;
                    break;
                case 'team_leader':
                    $expectedPermissions = $teamLeaderPermissions;
                    break;
                case 'developer':
                    $expectedPermissions = $developerPermissions;
                    break;
            }
            
            // Verificar permisos del usuario a través de sus roles
            $userPermissions = [];
            foreach ($userRoles as $userRole) {
                $rolePermissions = $userRole->permissions()->pluck('name')->toArray();
                $userPermissions = array_merge($userPermissions, $rolePermissions);
            }
            $userPermissions = array_unique($userPermissions);
            
            // Los permisos ya están asignados a través de los roles, no necesitamos asignarlos directamente al usuario
            echo "  ℹ️  Usuario tiene " . count($userPermissions) . " permisos a través de sus roles\n";
        }
    }

    // Crear permisos adicionales para el módulo de bugs
    $additionalPermissions = [
        'bugs.time_tracking' => 'Seguimiento de tiempo en bugs',
        'bugs.auto_assign' => 'Auto-asignación de bugs',
        'bugs.bulk_operations' => 'Operaciones masivas en bugs',
        'bugs.reports' => 'Reportes de bugs',
        'bugs.analytics' => 'Analíticas de bugs',
    ];

    echo "\n🔧 Creando permisos adicionales...\n";

    foreach ($additionalPermissions as $permissionName => $permissionDescription) {
        $permission = Permission::where('name', $permissionName)->first();
        
        if (!$permission) {
            $permission = Permission::create([
                'name' => $permissionName,
                'display_name' => $permissionDescription,
                'description' => $permissionDescription,
                'module' => 'bugs',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "✅ Permiso adicional creado: {$permissionName}\n";
            
            // Asignar a admin
            if (!$adminRole->permissions()->where('permission_id', $permission->id)->exists()) {
                $adminRole->permissions()->attach($permission->id);
            }
            
            // Asignar a team leader (excepto analytics)
            if ($permissionName !== 'bugs.analytics' && !$teamLeaderRole->permissions()->where('permission_id', $permission->id)->exists()) {
                $teamLeaderRole->permissions()->attach($permission->id);
            }
        }
    }

    DB::commit();

    echo "\n🎉 ¡Permisos actualizados exitosamente!\n";
    echo "📊 Resumen:\n";
    echo "   - Permisos creados: " . count($createdPermissions) . "\n";
    echo "   - Permisos existentes: " . count($existingPermissions) . "\n";
    echo "   - Permisos adicionales: " . count($additionalPermissions) . "\n";
    echo "   - Usuarios actualizados: " . $users->count() . "\n";

    // Mostrar estadísticas finales
    $totalPermissions = Permission::where('module', 'bugs')->count();
    $totalUsers = User::count();
    $usersWithBugPermissions = User::whereHas('roles.permissions', function ($query) {
        $query->where('module', 'bugs');
    })->count();

    echo "\n📈 Estadísticas Finales:\n";
    echo "   - Total de permisos de bugs: {$totalPermissions}\n";
    echo "   - Total de usuarios: {$totalUsers}\n";
    echo "   - Usuarios con permisos de bugs: {$usersWithBugPermissions}\n";

    // Mostrar permisos por rol
    echo "\n🔐 Permisos por Rol:\n";
    
    $roles = Role::with('permissions')->get();
    foreach ($roles as $role) {
        $bugPermissions = $role->permissions()->where('module', 'bugs')->pluck('name')->toArray();
        echo "   - {$role->name}: " . count($bugPermissions) . " permisos\n";
        foreach ($bugPermissions as $permission) {
            echo "     • {$permission}\n";
        }
    }

} catch (Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
    echo "📁 Archivo: " . $e->getFile() . "\n";
    exit(1);
} 
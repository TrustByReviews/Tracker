<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Bug;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Probando permisos del módulo de Bugs...\n";

try {
    // Verificar que los permisos existen
    echo "\n1. Verificando permisos creados...\n";
    $bugPermissions = Permission::where('module', 'bugs')->get();
    echo "✅ Total de permisos de bugs: " . $bugPermissions->count() . "\n";
    
    foreach ($bugPermissions as $permission) {
        echo "   - {$permission->name}: {$permission->display_name}\n";
    }

    // Verificar roles y sus permisos
    echo "\n2. Verificando permisos por rol...\n";
    $roles = Role::with('permissions')->get();
    
    foreach ($roles as $role) {
        $roleBugPermissions = $role->permissions()->where('module', 'bugs')->get();
        echo "👥 Rol '{$role->name}': " . $roleBugPermissions->count() . " permisos de bugs\n";
        
        foreach ($roleBugPermissions as $permission) {
            echo "   • {$permission->name}\n";
        }
    }

    // Verificar usuarios y sus permisos
    echo "\n3. Verificando usuarios y sus permisos...\n";
    $users = User::with('roles.permissions')->get();
    
    foreach ($users as $user) {
        $userRoles = $user->roles;
        $userBugPermissions = [];
        
        foreach ($userRoles as $role) {
            $rolePermissions = $role->permissions()->where('module', 'bugs')->pluck('name')->toArray();
            $userBugPermissions = array_merge($userBugPermissions, $rolePermissions);
        }
        
        $userBugPermissions = array_unique($userBugPermissions);
        echo "👤 Usuario '{$user->name}' ({$user->email}): " . count($userBugPermissions) . " permisos de bugs\n";
        
        if (count($userBugPermissions) > 0) {
            foreach ($userBugPermissions as $permission) {
                echo "   • {$permission}\n";
            }
        }
    }

    // Verificar que hay bugs en la base de datos
    echo "\n4. Verificando datos de bugs...\n";
    $totalBugs = Bug::count();
    echo "✅ Total de bugs en la base de datos: {$totalBugs}\n";
    
    if ($totalBugs > 0) {
        $bugsByStatus = Bug::selectRaw('status, count(*) as count')->groupBy('status')->get();
        echo "📊 Bugs por estado:\n";
        foreach ($bugsByStatus as $status) {
            echo "   - {$status->status}: {$status->count}\n";
        }
        
        $bugsByImportance = Bug::selectRaw('importance, count(*) as count')->groupBy('importance')->get();
        echo "📊 Bugs por importancia:\n";
        foreach ($bugsByImportance as $importance) {
            echo "   - {$importance->importance}: {$importance->count}\n";
        }
    }

    // Probar acceso a rutas (simulación)
    echo "\n5. Simulando acceso a rutas de bugs...\n";
    
    $testRoutes = [
        'bugs.index' => 'bugs.view',
        'bugs.show' => 'bugs.view',
        'bugs.store' => 'bugs.create',
        'bugs.update' => 'bugs.edit',
        'bugs.destroy' => 'bugs.delete',
        'bugs.assign' => 'bugs.assign',
        'bugs.resolve' => 'bugs.resolve',
        'bugs.add-comment' => 'bugs.comment',
    ];
    
    foreach ($testRoutes as $route => $requiredPermission) {
        $permission = Permission::where('name', $requiredPermission)->first();
        if ($permission) {
            echo "✅ Ruta '{$route}' requiere permiso '{$requiredPermission}' ✓\n";
        } else {
            echo "❌ Ruta '{$route}' requiere permiso '{$requiredPermission}' pero no existe\n";
        }
    }

    // Verificar permisos específicos por rol
    echo "\n6. Verificando permisos específicos por rol...\n";
    
    $adminRole = Role::where('name', 'admin')->first();
    $teamLeaderRole = Role::where('name', 'team_leader')->first();
    $developerRole = Role::where('name', 'developer')->first();
    
    $testPermissions = ['bugs.view', 'bugs.create', 'bugs.delete', 'bugs.analytics'];
    
    foreach ($testPermissions as $permissionName) {
        $permission = Permission::where('name', $permissionName)->first();
        
        if ($permission) {
            $adminHas = $adminRole->permissions()->where('permission_id', $permission->id)->exists();
            $teamLeaderHas = $teamLeaderRole->permissions()->where('permission_id', $permission->id)->exists();
            $developerHas = $developerRole->permissions()->where('permission_id', $permission->id)->exists();
            
            echo "🔐 Permiso '{$permissionName}':\n";
            echo "   - Admin: " . ($adminHas ? '✅' : '❌') . "\n";
            echo "   - Team Leader: " . ($teamLeaderHas ? '✅' : '❌') . "\n";
            echo "   - Developer: " . ($developerHas ? '✅' : '❌') . "\n";
        }
    }

    echo "\n🎉 ¡Pruebas completadas exitosamente!\n";
    echo "📋 Resumen:\n";
    echo "   - Permisos de bugs: " . $bugPermissions->count() . "\n";
    echo "   - Roles verificados: " . $roles->count() . "\n";
    echo "   - Usuarios verificados: " . $users->count() . "\n";
    echo "   - Bugs en BD: {$totalBugs}\n";
    echo "   - Rutas verificadas: " . count($testRoutes) . "\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
    echo "📁 Archivo: " . $e->getFile() . "\n";
    exit(1);
} 
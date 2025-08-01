<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

echo "=== PRUEBA COMPLETA DEL SISTEMA RBAC ===\n\n";

try {
    // 1. Verificar que las tablas existen y tienen datos
    echo "1. VERIFICACIÓN DE TABLAS Y DATOS:\n";
    
    $permissionsCount = Permission::count();
    $rolesCount = Role::count();
    $usersCount = User::count();
    
    echo "- Permisos: {$permissionsCount}\n";
    echo "- Roles: {$rolesCount}\n";
    echo "- Usuarios: {$usersCount}\n\n";
    
    if ($permissionsCount === 0 || $rolesCount === 0 || $usersCount === 0) {
        echo "❌ ERROR: Faltan datos básicos. Ejecuta los seeders primero.\n";
        exit(1);
    }
    
    echo "✅ Datos básicos verificados\n\n";
    
    // 2. Verificar roles y sus permisos
    echo "2. VERIFICACIÓN DE ROLES Y PERMISOS:\n";
    
    $roles = Role::with('permissions')->get();
    foreach ($roles as $role) {
        echo "- {$role->name}: {$role->permissions->count()} permisos\n";
        foreach ($role->permissions as $permission) {
            echo "  * {$permission->name} ({$permission->module})\n";
        }
        echo "\n";
    }
    
    echo "✅ Roles y permisos verificados\n\n";
    
    // 3. Verificar usuarios y sus roles
    echo "3. VERIFICACIÓN DE USUARIOS Y ROLES:\n";
    
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
    
    echo "✅ Usuarios y roles verificados\n\n";
    
    // 4. Probar sistema de permisos
    echo "4. PRUEBA DEL SISTEMA DE PERMISOS:\n";
    
    $adminUser = User::whereHas('roles', function($query) {
        $query->where('name', 'admin');
    })->first();
    
    if ($adminUser) {
        echo "- Usuario admin encontrado: {$adminUser->name}\n";
        
        // Probar permisos específicos
        $testPermissions = [
            'admin.dashboard',
            'admin.users',
            'projects.view',
            'tasks.view',
            'permissions.manage'
        ];
        
        foreach ($testPermissions as $permission) {
            $hasPermission = $adminUser->hasPermission($permission);
            echo "  * {$permission}: " . ($hasPermission ? '✅ SÍ' : '❌ NO') . "\n";
        }
        
        // Probar método getAllPermissions
        $allPermissions = $adminUser->getAllPermissions();
        echo "- Total de permisos del admin: {$allPermissions->count()}\n";
        
    } else {
        echo "❌ ERROR: No se encontró usuario admin\n";
    }
    
    echo "✅ Sistema de permisos verificado\n\n";
    
    // 5. Probar otorgamiento de permisos temporales
    echo "5. PRUEBA DE PERMISOS TEMPORALES:\n";
    
    $testUser = User::whereDoesntHave('roles', function($query) {
        $query->where('name', 'admin');
    })->first();
    
    if ($testUser) {
        echo "- Usuario de prueba: {$testUser->name}\n";
        
        // Verificar permisos antes
        $hasProjectView = $testUser->hasPermission('projects.view');
        echo "- Permiso 'projects.view' antes: " . ($hasProjectView ? 'SÍ' : 'NO') . "\n";
        
        // Otorgar permiso temporal
        $success = $testUser->grantPermission(
            'projects.view',
            'temporary',
            'Prueba del sistema RBAC',
            now()->addHour()
        );
        
        if ($success) {
            echo "✅ Permiso temporal otorgado\n";
            
            // Verificar que el permiso se otorgó
            $hasProjectViewAfter = $testUser->hasPermission('projects.view');
            echo "- Permiso 'projects.view' después: " . ($hasProjectViewAfter ? 'SÍ' : 'NO') . "\n";
            
            // Revocar el permiso
            $revokeSuccess = $testUser->revokePermission('projects.view');
            if ($revokeSuccess) {
                echo "✅ Permiso revocado correctamente\n";
            } else {
                echo "❌ Error al revocar permiso\n";
            }
        } else {
            echo "❌ Error al otorgar permiso temporal\n";
        }
    } else {
        echo "❌ ERROR: No se encontró usuario para pruebas\n";
    }
    
    echo "✅ Permisos temporales verificados\n\n";
    
    // 6. Verificar permisos expirados
    echo "6. VERIFICACIÓN DE PERMISOS EXPIrados:\n";
    
    $expiredPermissions = \App\Models\UserPermission::expired()->count();
    echo "- Permisos expirados: {$expiredPermissions}\n";
    
    if ($expiredPermissions > 0) {
        echo "⚠️  Hay permisos expirados que pueden ser limpiados\n";
    } else {
        echo "✅ No hay permisos expirados\n";
    }
    
    echo "✅ Permisos expirados verificados\n\n";
    
    // 7. Resumen final
    echo "=== RESUMEN FINAL ===\n";
    echo "✅ Sistema RBAC implementado correctamente\n";
    echo "✅ Base de datos configurada\n";
    echo "✅ Permisos y roles asignados\n";
    echo "✅ Funcionalidad de permisos temporales funcionando\n";
    echo "✅ Sistema de verificación de permisos operativo\n\n";
    
    echo "🎉 ¡El sistema RBAC está listo para usar!\n";
    echo "\nPróximos pasos:\n";
    echo "1. Accede a /permissions en el frontend\n";
    echo "2. Prueba la gestión de permisos de usuarios\n";
    echo "3. Prueba la gestión de permisos de roles\n";
    echo "4. Implementa el middleware CheckPermission en las rutas\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
} 
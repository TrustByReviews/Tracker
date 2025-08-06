<?php

/**
 * Script para asignar los permisos correctos de pagos a los usuarios
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 ARREGLANDO PERMISOS DE PAGOS\n";
echo "===============================\n\n";

try {
    // 1. Verificar que los permisos existen
    echo "1. Verificando permisos de pagos...\n";
    $paymentPermissions = Permission::where('module', 'payments')->get();
    echo "✅ Se encontraron " . $paymentPermissions->count() . " permisos de pagos\n";
    
    foreach ($paymentPermissions as $permission) {
        echo "   - {$permission->name}: {$permission->display_name}\n";
    }
    echo "\n";
    
    // 2. Verificar roles
    echo "2. Verificando roles...\n";
    $roles = Role::all();
    foreach ($roles as $role) {
        echo "   - {$role->name}: {$role->display_name}\n";
    }
    echo "\n";
    
    // 3. Asignar permisos a roles
    echo "3. Asignando permisos a roles...\n";
    
    // Admin - todos los permisos
    $adminRole = Role::where('name', 'admin')->first();
    if ($adminRole) {
        $adminPermissions = Permission::whereIn('name', [
            'payments.view',
            'payment-reports.view',
            'payment-reports.generate',
            'payment-reports.approve',
            'payment-reports.export',
        ])->get();
        
        $adminRole->permissions()->sync($adminPermissions->pluck('id'));
        echo "   ✅ Admin: " . $adminPermissions->count() . " permisos asignados\n";
    }
    
    // Team Leader - ver y exportar
    $teamLeaderRole = Role::where('name', 'team_leader')->first();
    if ($teamLeaderRole) {
        $teamLeaderPermissions = Permission::whereIn('name', [
            'payments.view',
            'payment-reports.view',
            'payment-reports.export',
        ])->get();
        
        $teamLeaderRole->permissions()->sync($teamLeaderPermissions->pluck('id'));
        echo "   ✅ Team Leader: " . $teamLeaderPermissions->count() . " permisos asignados\n";
    }
    
    // Developer - ver dashboard de pagos
    $developerRole = Role::where('name', 'developer')->first();
    if ($developerRole) {
        $developerPermissions = Permission::whereIn('name', [
            'payments.view',
        ])->get();
        
        $developerRole->permissions()->sync($developerPermissions->pluck('id'));
        echo "   ✅ Developer: " . $developerPermissions->count() . " permisos asignados\n";
    }
    
    echo "\n";
    
    // 4. Verificar usuarios desarrolladores
    echo "4. Verificando usuarios desarrolladores...\n";
    $developers = User::whereHas('roles', function ($query) {
        $query->where('name', 'developer');
    })->get();
    
    foreach ($developers as $developer) {
        echo "   👤 {$developer->name} ({$developer->email}):\n";
        
        // Verificar permisos directos
        $directPermissions = $developer->permissions ?? collect();
        if ($directPermissions->count() > 0) {
            echo "     Permisos directos:\n";
            foreach ($directPermissions as $permission) {
                echo "       - {$permission->name}\n";
            }
        }
        
        // Verificar permisos por roles
        $rolePermissions = $developer->roles->flatMap(function ($role) {
            return $role->permissions ?? collect();
        });
        
        if ($rolePermissions->count() > 0) {
            echo "     Permisos por roles:\n";
            foreach ($rolePermissions->unique('id') as $permission) {
                echo "       - {$permission->name}\n";
            }
        }
        
        // Verificar si tiene acceso a pagos
        $hasPaymentAccess = $developer->hasPermission('payments.view');
        echo "     Acceso a pagos: " . ($hasPaymentAccess ? '✅ SÍ' : '❌ NO') . "\n";
        
        echo "\n";
    }
    
    // 5. Asignar permisos directos si es necesario
    echo "5. Asignando permisos directos a desarrolladores...\n";
    $paymentViewPermission = Permission::where('name', 'payments.view')->first();
    
    if ($paymentViewPermission) {
        foreach ($developers as $developer) {
            if (!$developer->hasPermission('payments.view')) {
                $developer->permissions()->attach($paymentViewPermission->id);
                echo "   ✅ {$developer->name}: Permiso payments.view asignado directamente\n";
            }
        }
    }
    
    echo "\n";
    
    // 6. Verificar rutas disponibles
    echo "6. Verificando rutas de pagos disponibles...\n";
    echo "   - /payments/dashboard (requiere: payments.view)\n";
    echo "   - /payments/admin (requiere: payment-reports.view)\n";
    echo "   - /payments/reports (requiere: payment-reports.view)\n";
    echo "\n";
    
    // 7. Mostrar resumen final
    echo "7. Resumen final:\n";
    echo "   - Desarrolladores con acceso a pagos: " . $developers->filter(function ($dev) {
        return $dev->hasPermission('payments.view');
    })->count() . "/" . $developers->count() . "\n";
    
    echo "\n🎉 ¡Permisos de pagos arreglados!\n";
    echo "===============================\n";
    echo "Ahora los desarrolladores deberían poder acceder a:\n";
    echo "- http://localhost:8000/payments/dashboard\n";
    echo "\n";
    echo "Para probar, usa cualquiera de estos usuarios:\n";
    foreach ($developers as $developer) {
        echo "- {$developer->email} (password: password)\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error durante la corrección: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
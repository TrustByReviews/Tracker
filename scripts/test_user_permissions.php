<?php

/**
 * Script para verificar los permisos de un usuario específico
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 VERIFICANDO PERMISOS DE USUARIO\n";
echo "==================================\n\n";

try {
    // Email del usuario a verificar
    $userEmail = 'developer4@example.com'; // Carmen Ruiz
    
    echo "1. Verificando usuario: {$userEmail}\n";
    $user = User::where('email', $userEmail)->with(['roles.permissions', 'directPermissions'])->first();
    
    if (!$user) {
        echo "❌ Usuario no encontrado\n";
        exit(1);
    }
    
    echo "✅ Usuario encontrado: {$user->name}\n";
    echo "   - ID: {$user->id}\n";
    echo "   - Email: {$user->email}\n";
    echo "   - Estado: {$user->status}\n\n";
    
    // 2. Verificar roles
    echo "2. Roles del usuario:\n";
    if ($user->roles->count() > 0) {
        foreach ($user->roles as $role) {
            echo "   - {$role->name}: {$role->display_name}\n";
        }
    } else {
        echo "   ❌ No tiene roles asignados\n";
    }
    echo "\n";
    
    // 3. Verificar permisos directos
    echo "3. Permisos directos:\n";
    if ($user->directPermissions->count() > 0) {
        foreach ($user->directPermissions as $permission) {
            echo "   - {$permission->name}: {$permission->display_name} (módulo: {$permission->module})\n";
        }
    } else {
        echo "   ℹ️  No tiene permisos directos\n";
    }
    echo "\n";
    
    // 4. Verificar permisos por roles
    echo "4. Permisos por roles:\n";
    $rolePermissions = collect();
    foreach ($user->roles as $role) {
        if ($role->permissions->count() > 0) {
            echo "   Rol {$role->name}:\n";
            foreach ($role->permissions as $permission) {
                echo "     - {$permission->name}: {$permission->display_name} (módulo: {$permission->module})\n";
                $rolePermissions->push($permission);
            }
        }
    }
    
    if ($rolePermissions->count() == 0) {
        echo "   ❌ No tiene permisos por roles\n";
    }
    echo "\n";
    
    // 5. Verificar permisos específicos de pagos
    echo "5. Verificando permisos de pagos:\n";
    $paymentPermissions = ['payments.view', 'payment-reports.view', 'payment-reports.generate', 'payment-reports.approve'];
    
    foreach ($paymentPermissions as $permissionName) {
        $hasPermission = $user->hasPermission($permissionName);
        echo "   - {$permissionName}: " . ($hasPermission ? '✅ SÍ' : '❌ NO') . "\n";
    }
    echo "\n";
    
    // 6. Verificar acceso al módulo de pagos
    echo "6. Verificando acceso al módulo de pagos:\n";
    $allPermissions = collect();
    
    // Agregar permisos directos
    $allPermissions = $allPermissions->merge($user->directPermissions);
    
    // Agregar permisos por roles
    foreach ($user->roles as $role) {
        $allPermissions = $allPermissions->merge($role->permissions);
    }
    
    // Remover duplicados
    $allPermissions = $allPermissions->unique('id');
    
    $paymentModulePermissions = $allPermissions->where('module', 'payments');
    
    if ($paymentModulePermissions->count() > 0) {
        echo "   ✅ Tiene permisos del módulo de pagos:\n";
        foreach ($paymentModulePermissions as $permission) {
            echo "     - {$permission->name}: {$permission->display_name}\n";
        }
    } else {
        echo "   ❌ No tiene permisos del módulo de pagos\n";
    }
    echo "\n";
    
    // 7. Simular verificación de rutas
    echo "7. Verificando acceso a rutas:\n";
    $routes = [
        '/payments/dashboard' => 'payments.view',
        '/payments/admin' => 'payment-reports.view',
        '/payments/reports' => 'payment-reports.view',
    ];
    
    foreach ($routes as $route => $requiredPermission) {
        $hasAccess = $user->hasPermission($requiredPermission);
        echo "   - {$route}: " . ($hasAccess ? '✅ ACCESO' : '❌ DENEGADO') . " (requiere: {$requiredPermission})\n";
    }
    echo "\n";
    
    // 8. Mostrar datos para debug del frontend
    echo "8. Datos para debug del frontend:\n";
    echo "   Para verificar en el navegador, abre la consola y ejecuta:\n";
    echo "   console.log('User:', window.Inertia.props.auth.user);\n";
    echo "   console.log('Roles:', window.Inertia.props.auth.user.roles);\n";
    echo "   console.log('Direct Permissions:', window.Inertia.props.auth.user.directPermissions);\n";
    echo "\n";
    
    // 9. Recomendaciones
    echo "9. Recomendaciones:\n";
    if (!$user->hasPermission('payments.view')) {
        echo "   ❌ El usuario NO tiene acceso al dashboard de pagos\n";
        echo "   💡 Solución: Asignar el permiso 'payments.view' al rol 'developer'\n";
    } else {
        echo "   ✅ El usuario SÍ tiene acceso al dashboard de pagos\n";
        echo "   💡 Si no aparece en el sidebar, verifica:\n";
        echo "      - Que el componente AppSidebar.vue esté cargado correctamente\n";
        echo "      - Que el composable usePermissions esté funcionando\n";
        echo "      - Que no haya errores en la consola del navegador\n";
    }
    
    echo "\n🎯 Para probar el acceso:\n";
    echo "1. Inicia el servidor: php artisan serve\n";
    echo "2. Accede a: http://localhost:8000/login\n";
    echo "3. Usa las credenciales: {$user->email} / password\n";
    echo "4. Verifica si aparece 'Payment Reports' en el sidebar\n";
    echo "5. Si no aparece, accede directamente a: http://localhost:8000/payments/dashboard\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la verificación: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
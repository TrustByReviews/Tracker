<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Bootstrap Laravel
$app = Application::configure(basePath: __DIR__ . '/..')
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CORRECCIÓN DE PERMISOS DE REPORTES DE PAGO ===\n\n";

// Verificar si existen los permisos
$permissions = [
    'payment-reports.view',
    'payment-reports.generate',
    'payment-reports.manage'
];

echo "🔍 Verificando permisos existentes:\n";
foreach ($permissions as $permission) {
    $perm = \App\Models\Permission::where('name', $permission)->first();
    if ($perm) {
        echo "   ✅ Permiso '{$permission}' existe\n";
    } else {
        echo "   ❌ Permiso '{$permission}' no existe - creando...\n";
        \App\Models\Permission::create([
            'name' => $permission,
            'display_name' => ucwords(str_replace('-', ' ', $permission)),
            'module' => 'payment-reports'
        ]);
        echo "   ✅ Permiso '{$permission}' creado\n";
    }
}

// Asignar permisos a roles
echo "\n🔧 Asignando permisos a roles:\n";

// Admin debe tener todos los permisos
$adminRole = \App\Models\Role::where('name', 'admin')->first();
if ($adminRole) {
    $adminPermissions = \App\Models\Permission::whereIn('name', $permissions)->get();
    $adminRole->permissions()->sync($adminPermissions->pluck('id'));
    echo "   ✅ Permisos asignados al rol 'admin'\n";
} else {
    echo "   ❌ Rol 'admin' no encontrado\n";
}

// Team Leader debe tener permisos de vista y generación
$tlRole = \App\Models\Role::where('name', 'team_leader')->first();
if ($tlRole) {
    $tlPermissions = \App\Models\Permission::whereIn('name', ['payment-reports.view', 'payment-reports.generate'])->get();
    $tlRole->permissions()->sync($tlPermissions->pluck('id'));
    echo "   ✅ Permisos asignados al rol 'team_leader'\n";
} else {
    echo "   ❌ Rol 'team_leader' no encontrado\n";
}

// Developer debe tener permiso de vista
$devRole = \App\Models\Role::where('name', 'developer')->first();
if ($devRole) {
    $devPermissions = \App\Models\Permission::whereIn('name', ['payment-reports.view'])->get();
    $devRole->permissions()->sync($devPermissions->pluck('id'));
    echo "   ✅ Permisos asignados al rol 'developer'\n";
} else {
    echo "   ❌ Rol 'developer' no encontrado\n";
}

// QA debe tener permiso de vista
$qaRole = \App\Models\Role::where('name', 'qa')->first();
if ($qaRole) {
    $qaPermissions = \App\Models\Permission::whereIn('name', ['payment-reports.view'])->get();
    $qaRole->permissions()->sync($qaPermissions->pluck('id'));
    echo "   ✅ Permisos asignados al rol 'qa'\n";
} else {
    echo "   ❌ Rol 'qa' no encontrado\n";
}

// Verificar usuarios específicos
echo "\n👥 Verificando permisos de usuarios:\n";

$users = \App\Models\User::with('roles')->get();
foreach ($users as $user) {
    $mainRole = $user->getMainRole();
    echo "   👤 {$user->name} ({$user->email}) - Rol: {$mainRole}\n";
    
    if ($user->hasPermission('payment-reports.view')) {
        echo "      ✅ Tiene permiso 'payment-reports.view'\n";
    } else {
        echo "      ❌ NO tiene permiso 'payment-reports.view'\n";
    }
    
    if ($user->hasPermission('payment-reports.generate')) {
        echo "      ✅ Tiene permiso 'payment-reports.generate'\n";
    } else {
        echo "      ❌ NO tiene permiso 'payment-reports.generate'\n";
    }
}

echo "\n✅ Corrección de permisos completada\n";
echo "\n📋 Resumen:\n";
echo "- Permisos de reportes de pago creados/verificados\n";
echo "- Permisos asignados a roles apropiados\n";
echo "- Usuarios verificados para permisos\n";
echo "- Sistema listo para generar reportes\n"; 
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DIAGNÓSTICO DEL SIDEBAR ===\n\n";

// Verificar usuarios con diferentes roles
echo "1. Verificando usuarios y roles:\n";
$users = \App\Models\User::with('roles')->get();

foreach ($users as $user) {
    echo "- Usuario: {$user->name} (ID: {$user->id})\n";
    echo "  Email: {$user->email}\n";
    echo "  Roles: ";
    if ($user->roles->count() > 0) {
        foreach ($user->roles as $role) {
            echo "{$role->name} ({$role->value}) ";
        }
    } else {
        echo "Sin roles";
    }
    echo "\n";
    echo "  Permisos directos: ";
    if ($user->permissions && $user->permissions->count() > 0) {
        foreach ($user->permissions as $permission) {
            echo "{$permission->name} ";
        }
    } else {
        echo "Sin permisos directos";
    }
    echo "\n\n";
}

// Verificar roles disponibles
echo "2. Verificando roles disponibles:\n";
$roles = \App\Models\Role::all();
foreach ($roles as $role) {
    echo "- {$role->name} ({$role->value})\n";
}

echo "\n3. Verificando middleware de Inertia:\n";
$middleware = \App\Http\Middleware\HandleInertiaRequests::class;
echo "- Middleware: {$middleware}\n";

// Simular una petición para ver qué datos se comparten
echo "\n4. Simulando datos compartidos por Inertia:\n";
$request = \Illuminate\Http\Request::create('/test', 'GET');
$middlewareInstance = new \App\Http\Middleware\HandleInertiaRequests();
$sharedData = $middlewareInstance->share($request);

echo "- sidebarOpen: " . ($sharedData['sidebarOpen'] ? 'true' : 'false') . "\n";
echo "- auth.user: " . ($sharedData['auth']['user'] ? 'presente' : 'null') . "\n";

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";

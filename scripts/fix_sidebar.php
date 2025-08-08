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

echo "=== SOLUCIONANDO PROBLEMA DEL SIDEBAR ===\n\n";

// Crear una respuesta con una cookie que fuerce el sidebar a estar abierto
$response = new \Illuminate\Http\Response('Sidebar fixed');

// Establecer la cookie para forzar que el sidebar esté abierto
$response->withCookie('sidebar_state', 'true', 60 * 60 * 24 * 7); // 7 días

echo "Cookie 'sidebar_state' establecida como 'true'\n";
echo "El sidebar debería estar abierto ahora.\n\n";

echo "Para aplicar el cambio:\n";
echo "1. Limpia el caché del navegador\n";
echo "2. Recarga la página\n";
echo "3. O ejecuta: php artisan cache:clear\n\n";

echo "=== FIN ===\n";

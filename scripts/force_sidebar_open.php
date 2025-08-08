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

echo "=== FORZANDO SIDEBAR ABIERTO ===\n\n";

// Crear una respuesta HTTP
$response = new \Illuminate\Http\Response('Sidebar forced open');

// Establecer la cookie para forzar que el sidebar esté abierto
$response->withCookie('sidebar_state', 'true', 60 * 60 * 24 * 30); // 30 días

echo "Cookie 'sidebar_state' establecida como 'true' por 30 días\n";
echo "Esto debería forzar que el sidebar esté abierto para todos los usuarios.\n\n";

echo "Para aplicar el cambio:\n";
echo "1. Limpia el caché del navegador (Ctrl+Shift+Delete)\n";
echo "2. Recarga la página (F5)\n";
echo "3. O ejecuta: php artisan cache:clear\n\n";

echo "Si el problema persiste, verifica:\n";
echo "- Que el usuario tenga permisos de administrador\n";
echo "- Que no haya errores de JavaScript en la consola\n";
echo "- Que el componente Icon esté registrado correctamente\n\n";

echo "=== FIN ===\n";

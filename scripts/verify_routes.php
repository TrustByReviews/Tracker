<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Route;

echo "=== Verificación de Rutas de Descarga ===\n\n";

// Obtener todas las rutas
$routes = Route::getRoutes();

echo "Rutas relacionadas con pagos y descargas:\n";
echo "=========================================\n\n";

$paymentRoutes = [];

foreach ($routes as $route) {
    $uri = $route->uri();
    $methods = $route->methods();
    $name = $route->getName();
    
    if (strpos($uri, 'payment') !== false || strpos($uri, 'generate') !== false) {
        $paymentRoutes[] = [
            'uri' => $uri,
            'methods' => $methods,
            'name' => $name
        ];
    }
}

if (empty($paymentRoutes)) {
    echo "❌ No se encontraron rutas relacionadas con pagos\n";
} else {
    foreach ($paymentRoutes as $route) {
        echo "📍 URI: /{$route['uri']}\n";
        echo "   Métodos: " . implode(', ', $route['methods']) . "\n";
        if ($route['name']) {
            echo "   Nombre: {$route['name']}\n";
        }
        echo "\n";
    }
}

// Verificar rutas específicas que necesitamos
echo "Verificación de rutas específicas:\n";
echo "==================================\n\n";

$requiredRoutes = [
    'payments/generate-detailed' => 'POST',
    'payments' => 'GET',
];

foreach ($requiredRoutes as $uri => $method) {
    $found = false;
    foreach ($routes as $route) {
        if ($route->uri() === $uri && in_array($method, $route->methods())) {
            $found = true;
            break;
        }
    }
    
    if ($found) {
        echo "✅ /{$uri} ({$method}): Encontrada\n";
    } else {
        echo "❌ /{$uri} ({$method}): NO encontrada\n";
    }
}

echo "\n=== Verificación de Middleware ===\n\n";

// Verificar middleware de autenticación
$authMiddleware = [
    'auth',
    'verified'
];

foreach ($authMiddleware as $middleware) {
    $hasMiddleware = false;
    foreach ($routes as $route) {
        if (in_array($middleware, $route->middleware())) {
            $hasMiddleware = true;
            break;
        }
    }
    
    if ($hasMiddleware) {
        echo "✅ Middleware '{$middleware}': Configurado\n";
    } else {
        echo "⚠️  Middleware '{$middleware}': No encontrado\n";
    }
}

echo "\n=== Instrucciones de Prueba ===\n\n";
echo "Para probar las descargas:\n";
echo "1. Asegúrate de que el servidor esté corriendo: php artisan serve\n";
echo "2. Ve a http://127.0.0.1:8000/payments\n";
echo "3. Inicia sesión como admin o usuario con permisos\n";
echo "4. Selecciona desarrolladores en la pestaña 'Generate Reports'\n";
echo "5. Elige un período de tiempo\n";
echo "6. Selecciona formato (CSV, Excel, PDF)\n";
echo "7. Haz clic en 'Generate Report'\n";
echo "8. El archivo debería descargarse automáticamente\n\n";

echo "Si las descargas no funcionan:\n";
echo "- Verifica que el navegador no esté bloqueando las descargas\n";
echo "- Revisa la consola del navegador para errores JavaScript\n";
echo "- Verifica los logs de Laravel en storage/logs/laravel.log\n";
echo "- Asegúrate de que el usuario tenga permisos para generar reportes\n";

echo "\n✅ Verificación completada\n"; 
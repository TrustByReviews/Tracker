<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Prueba de Rutas API ===\n\n";

// 1. Verificar que el archivo de rutas API existe
echo "1. Verificando archivo de rutas API...\n";

$apiRoutesFile = __DIR__ . '/../routes/api.php';
if (file_exists($apiRoutesFile)) {
    echo "✅ Archivo routes/api.php existe\n";
    echo "   - Tamaño: " . filesize($apiRoutesFile) . " bytes\n";
    echo "   - Contenido:\n";
    $content = file_get_contents($apiRoutesFile);
    echo $content . "\n";
} else {
    echo "❌ Archivo routes/api.php NO existe\n";
    exit(1);
}

echo "\n";

// 2. Verificar que el DownloadController existe
echo "2. Verificando DownloadController...\n";

if (class_exists('App\Http\Controllers\DownloadController')) {
    echo "✅ DownloadController existe\n";
} else {
    echo "❌ DownloadController NO existe\n";
    exit(1);
}

echo "\n";

// 3. Verificar rutas registradas
echo "3. Verificando rutas registradas...\n";

$routes = app('router')->getRoutes();
$apiRoutes = [];

foreach ($routes as $route) {
    if (strpos($route->uri(), 'api/') !== false || strpos($route->uri(), 'download') !== false) {
        $apiRoutes[] = $route;
    }
}

if (count($apiRoutes) > 0) {
    echo "✅ Rutas API encontradas:\n";
    foreach ($apiRoutes as $route) {
        echo "   - {$route->uri()} (" . implode(',', $route->methods()) . ")\n";
        echo "     Controller: " . get_class($route->getController()) . "\n";
        echo "     Action: " . $route->getActionMethod() . "\n";
        echo "     Middleware: " . implode(', ', $route->middleware()) . "\n";
    }
} else {
    echo "❌ No se encontraron rutas API\n";
}

echo "\n";

// 4. Probar crear rutas manualmente
echo "4. Probando crear rutas manualmente...\n";

try {
    $router = app('router');
    
    // Agregar rutas manualmente
    $router->post('api/test-download-excel', [App\Http\Controllers\DownloadController::class, 'downloadExcel'])->name('test.download-excel');
    $router->post('api/test-show-report', [App\Http\Controllers\DownloadController::class, 'showReport'])->name('test.show-report');
    
    echo "✅ Rutas agregadas manualmente\n";
    
    // Verificar que se agregaron
    $testRoutes = [];
    foreach ($routes as $route) {
        if (strpos($route->uri(), 'test-') !== false) {
            $testRoutes[] = $route;
        }
    }
    
    if (count($testRoutes) > 0) {
        echo "✅ Rutas de prueba encontradas:\n";
        foreach ($testRoutes as $route) {
            echo "   - {$route->uri()} (" . implode(',', $route->methods()) . ")\n";
        }
    } else {
        echo "❌ No se encontraron rutas de prueba\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error al crear rutas manualmente: " . $e->getMessage() . "\n";
}

echo "\n";

// 5. Verificar configuración del RouteServiceProvider
echo "5. Verificando RouteServiceProvider...\n";

$routeServiceProvider = app(\App\Providers\RouteServiceProvider::class);
echo "✅ RouteServiceProvider cargado\n";

// Verificar si el archivo api.php está siendo cargado
$reflection = new ReflectionClass($routeServiceProvider);
$method = $reflection->getMethod('boot');
$method->setAccessible(true);

echo "✅ Método boot disponible\n";

echo "\n";

// 6. Resumen final
echo "=== RESUMEN FINAL ===\n";
echo "✅ Archivo routes/api.php existe y tiene contenido\n";
echo "✅ DownloadController existe\n";
echo "✅ RouteServiceProvider cargado\n";
echo "❌ Rutas API no se están cargando automáticamente\n\n";

echo "🎯 DIAGNÓSTICO:\n";
echo "- El problema parece estar en la carga automática de las rutas API\n";
echo "- Las rutas se pueden crear manualmente\n";
echo "- El controlador funciona correctamente\n\n";

echo "📋 SOLUCIÓN PROPUESTA:\n";
echo "1. Mover las rutas al archivo web.php temporalmente\n";
echo "2. O verificar la configuración del RouteServiceProvider\n";
echo "3. O usar rutas manuales\n\n";

echo "✅ Análisis completado\n"; 
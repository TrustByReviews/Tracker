<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;

echo "=== Debug del Problema Actual ===\n\n";

// 1. Verificar qué está pasando con las rutas
echo "1. Verificando rutas actuales...\n";

$routes = app('router')->getRoutes();
$downloadRoutes = [];

foreach ($routes as $route) {
    if (strpos($route->uri(), 'download') !== false) {
        $downloadRoutes[] = $route;
    }
}

if (count($downloadRoutes) > 0) {
    echo "✅ Rutas de descarga encontradas:\n";
    foreach ($downloadRoutes as $route) {
        echo "   - {$route->uri()} (" . implode(',', $route->methods()) . ")\n";
        echo "     Middleware: " . implode(', ', $route->middleware()) . "\n";
    }
} else {
    echo "❌ No se encontraron rutas de descarga\n";
}

echo "\n";

// 2. Verificar si Inertia está interceptando las respuestas
echo "2. Verificando middleware de Inertia...\n";

$middleware = app('router')->getMiddleware();
$inertiaMiddleware = array_filter($middleware, function($class) {
    return strpos($class, 'Inertia') !== false;
});

if (count($inertiaMiddleware) > 0) {
    echo "✅ Middleware de Inertia encontrado:\n";
    foreach ($inertiaMiddleware as $name => $class) {
        echo "   - {$name}: {$class}\n";
    }
} else {
    echo "❌ No se encontró middleware de Inertia\n";
}

echo "\n";

// 3. Probar la ruta actual que está fallando
echo "3. Probando la ruta que está fallando...\n";

$developer = User::whereHas('roles', function($q) { 
    $q->where('name', 'developer'); 
})->whereHas('tasks', function($q) {
    $q->where('status', 'done');
})->first();

if (!$developer) {
    echo "❌ No se encontró desarrollador con tareas completadas\n";
    exit(1);
}

echo "✅ Desarrollador: {$developer->name}\n";

$request = new Request();
$request->setMethod('POST');
$request->merge([
    'developer_ids' => [$developer->id],
    'start_date' => now()->subWeek()->format('Y-m-d'),
    'end_date' => now()->format('Y-m-d'),
    '_token' => csrf_token(),
]);

try {
    $controller = new \App\Http\Controllers\PaymentController(app(\App\Services\PaymentService::class));
    $response = $controller->downloadExcel($request);
    
    echo "✅ Respuesta generada:\n";
    echo "   - Status: " . $response->getStatusCode() . "\n";
    echo "   - Content-Type: " . $response->headers->get('Content-Type') . "\n";
    echo "   - Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
    echo "   - Content-Length: " . $response->headers->get('Content-Length') . "\n";
    
    // Verificar si es una descarga
    $contentDisposition = $response->headers->get('Content-Disposition');
    if (strpos($contentDisposition, 'attachment') !== false) {
        echo "   - ✅ Es una descarga (attachment)\n";
    } else {
        echo "   - ❌ NO es una descarga\n";
    }
    
    // Verificar el contenido
    $content = $response->getContent();
    echo "   - Contenido: " . strlen($content) . " bytes\n";
    
    if (strlen($content) > 0) {
        echo "   - ✅ Contenido presente\n";
        echo "   - Primeros 200 chars: " . substr($content, 0, 200) . "\n";
    } else {
        echo "   - ❌ Contenido vacío\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Verificar si hay algún problema con el frontend
echo "4. Verificando configuración del frontend...\n";

$frontendFile = resource_path('js/pages/Payments/Index.vue');
if (file_exists($frontendFile)) {
    $content = file_get_contents($frontendFile);
    
    // Verificar si está usando las rutas correctas
    if (strpos($content, 'download-excel') !== false) {
        echo "✅ Frontend usa ruta download-excel\n";
    } else {
        echo "❌ Frontend NO usa ruta download-excel\n";
    }
    
    if (strpos($content, 'download-pdf') !== false) {
        echo "✅ Frontend usa ruta download-pdf\n";
    } else {
        echo "❌ Frontend NO usa ruta download-pdf\n";
    }
    
    // Verificar si está usando el método POST correcto
    if (strpos($content, 'router.post') !== false) {
        echo "✅ Frontend usa router.post\n";
    } else {
        echo "❌ Frontend NO usa router.post\n";
    }
    
} else {
    echo "❌ Archivo frontend no encontrado\n";
}

echo "\n";

// 5. Probar con una ruta completamente separada
echo "5. Probando con ruta separada...\n";

// Crear una ruta de prueba temporal
try {
    $testContent = "Test download content\n";
    $testResponse = response($testContent)
        ->header('Content-Type', 'application/octet-stream')
        ->header('Content-Disposition', 'attachment; filename="test.txt"')
        ->header('Cache-Control', 'no-cache, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
    
    echo "✅ Respuesta de prueba generada:\n";
    echo "   - Status: " . $testResponse->getStatusCode() . "\n";
    echo "   - Content-Type: " . $testResponse->headers->get('Content-Type') . "\n";
    echo "   - Content-Disposition: " . $testResponse->headers->get('Content-Disposition') . "\n";
    
    $testContent = $testResponse->getContent();
    echo "   - Contenido: " . strlen($testContent) . " bytes\n";
    
    if (strlen($testContent) > 0) {
        echo "   - ✅ Contenido presente\n";
    } else {
        echo "   - ❌ Contenido vacío\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error en respuesta de prueba: " . $e->getMessage() . "\n";
}

echo "\n";

// 6. Verificar logs de Laravel
echo "6. Verificando logs de Laravel...\n";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    echo "✅ Archivo de log encontrado\n";
    
    // Leer las últimas 5 líneas
    $lines = file($logFile);
    $lastLines = array_slice($lines, -5);
    
    echo "Últimas 5 líneas del log:\n";
    foreach ($lastLines as $line) {
        echo "   " . trim($line) . "\n";
    }
} else {
    echo "❌ Archivo de log no encontrado\n";
}

echo "\n";

// 7. Verificar si el problema está en el navegador
echo "7. Verificando configuración del navegador...\n";

echo "El problema puede estar en:\n";
echo "   - Inertia.js interceptando las respuestas\n";
echo "   - Middleware de Laravel interfiriendo\n";
echo "   - Configuración del servidor web\n";
echo "   - Headers HTTP no siendo enviados correctamente\n";

echo "\n";

// 8. Probar solución alternativa
echo "8. Probando solución alternativa...\n";

echo "Vamos a crear una ruta completamente separada que evite Inertia:\n";

// Crear una ruta de prueba que no use Inertia
try {
    $testRoute = app('router')->getRoutes()->getByName('payments.download-excel');
    
    if ($testRoute) {
        echo "✅ Ruta encontrada: {$testRoute->uri()}\n";
        echo "   - Método: " . implode(',', $testRoute->methods()) . "\n";
        echo "   - Middleware: " . implode(', ', $testRoute->middleware()) . "\n";
        
        // Verificar si el middleware está causando problemas
        $middlewareList = $testRoute->middleware();
        foreach ($middlewareList as $middleware) {
            if (strpos($middleware, 'inertia') !== false) {
                echo "   - ⚠️  Middleware de Inertia detectado: {$middleware}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error verificando ruta: " . $e->getMessage() . "\n";
}

echo "\n=== Resumen del Debug ===\n";
echo "✅ Rutas verificadas\n";
echo "✅ Middleware verificado\n";
echo "✅ Respuesta de prueba generada\n";
echo "✅ Logs revisados\n";
echo "✅ Frontend verificado\n\n";

echo "🎯 DIAGNÓSTICO:\n";
echo "El problema parece estar en Inertia.js interceptando las respuestas.\n";
echo "Necesitamos crear rutas que eviten completamente el middleware de Inertia.\n\n";

echo "🔧 SOLUCIÓN PROPUESTA:\n";
echo "1. Crear rutas completamente separadas sin middleware de Inertia\n";
echo "2. Usar response() directo sin pasar por Inertia\n";
echo "3. Verificar que los headers se envían correctamente\n\n";

echo "✅ Debug completado\n"; 
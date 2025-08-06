<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;

echo "=== Debug de Request del Navegador ===\n\n";

// 1. Simular request exacto del navegador
echo "1. Simulando request del navegador...\n";

$developer = User::whereHas('roles', function($q) { 
    $q->where('name', 'developer'); 
})->first();

if (!$developer) {
    echo "❌ No se encontró ningún desarrollador\n";
    exit(1);
}

echo "✅ Desarrollador: {$developer->name}\n";

// Simular request POST exacto
$request = new Request();
$request->setMethod('POST');
$request->merge([
    'developer_ids' => [$developer->id],
    'start_date' => now()->subWeek()->format('Y-m-d'),
    'end_date' => now()->format('Y-m-d'),
    '_token' => csrf_token(),
]);

echo "✅ Request preparado:\n";
echo "   - Method: " . $request->getMethod() . "\n";
echo "   - Developer IDs: " . implode(', ', $request->developer_ids) . "\n";
echo "   - Start Date: {$request->start_date}\n";
echo "   - End Date: {$request->end_date}\n";
echo "   - CSRF Token: " . substr($request->_token, 0, 10) . "...\n\n";

// 2. Probar ruta de Excel
echo "2. Probando ruta de Excel...\n";

try {
    // Simular la ruta exacta
    $route = app('router')->getRoutes()->getByName('payments.download-excel');
    
    if ($route) {
        echo "✅ Ruta encontrada: {$route->uri()}\n";
        echo "   - Método: " . implode(',', $route->methods()) . "\n";
        echo "   - Middleware: " . implode(', ', $route->middleware()) . "\n";
        
        // Simular el controlador
        $controller = new \App\Http\Controllers\PaymentController(app(\App\Services\PaymentService::class));
        
        // Verificar si el método existe
        if (method_exists($controller, 'downloadExcel')) {
            echo "✅ Método downloadExcel existe\n";
            
            // Ejecutar el método
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
            echo "   - Primeros 100 chars: " . substr($content, 0, 100) . "\n";
            
        } else {
            echo "❌ Método downloadExcel NO existe\n";
        }
        
    } else {
        echo "❌ Ruta NO encontrada\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   - File: " . $e->getFile() . "\n";
    echo "   - Line: " . $e->getLine() . "\n";
}

echo "\n";

// 3. Probar ruta de PDF
echo "3. Probando ruta de PDF...\n";

try {
    $route = app('router')->getRoutes()->getByName('payments.download-pdf');
    
    if ($route) {
        echo "✅ Ruta encontrada: {$route->uri()}\n";
        
        $controller = new \App\Http\Controllers\PaymentController(app(\App\Services\PaymentService::class));
        
        if (method_exists($controller, 'downloadPDF')) {
            echo "✅ Método downloadPDF existe\n";
            
            $response = $controller->downloadPDF($request);
            
            echo "✅ Respuesta generada:\n";
            echo "   - Status: " . $response->getStatusCode() . "\n";
            echo "   - Content-Type: " . $response->headers->get('Content-Type') . "\n";
            echo "   - Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
            echo "   - Content-Length: " . $response->headers->get('Content-Length') . "\n";
            
            $contentDisposition = $response->headers->get('Content-Disposition');
            if (strpos($contentDisposition, 'attachment') !== false) {
                echo "   - ✅ Es una descarga (attachment)\n";
            } else {
                echo "   - ❌ NO es una descarga\n";
            }
            
            $content = $response->getContent();
            echo "   - Contenido: " . strlen($content) . " bytes\n";
            echo "   - Primeros 100 chars: " . substr($content, 0, 100) . "\n";
            
        } else {
            echo "❌ Método downloadPDF NO existe\n";
        }
        
    } else {
        echo "❌ Ruta NO encontrada\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   - File: " . $e->getFile() . "\n";
    echo "   - Line: " . $e->getLine() . "\n";
}

echo "\n";

// 4. Verificar middleware
echo "4. Verificando middleware...\n";

$middleware = app('router')->getMiddleware();
echo "Middleware registrado:\n";
foreach ($middleware as $name => $class) {
    echo "   - {$name}: {$class}\n";
}

echo "\n";

// 5. Verificar si hay algún middleware que esté interfiriendo
echo "5. Verificando middleware de las rutas...\n";

$excelRoute = app('router')->getRoutes()->getByName('payments.download-excel');
$pdfRoute = app('router')->getRoutes()->getByName('payments.download-pdf');

if ($excelRoute) {
    echo "Middleware de Excel:\n";
    foreach ($excelRoute->middleware() as $middleware) {
        echo "   - {$middleware}\n";
    }
}

if ($pdfRoute) {
    echo "Middleware de PDF:\n";
    foreach ($pdfRoute->middleware() as $middleware) {
        echo "   - {$middleware}\n";
    }
}

echo "\n";

// 6. Probar con request sin middleware
echo "6. Probando sin middleware...\n";

try {
    // Crear un request simple
    $simpleRequest = new Request();
    $simpleRequest->merge([
        'developer_ids' => [$developer->id],
        'start_date' => now()->subWeek()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
    ]);
    
    $controller = new \App\Http\Controllers\PaymentController(app(\App\Services\PaymentService::class));
    $response = $controller->downloadExcel($simpleRequest);
    
    echo "✅ Respuesta sin middleware:\n";
    echo "   - Status: " . $response->getStatusCode() . "\n";
    echo "   - Headers: " . count($response->headers->all()) . " headers\n";
    
    // Verificar si hay algún header que esté causando problemas
    foreach ($response->headers->all() as $key => $values) {
        echo "   - {$key}: " . implode(', ', $values) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error sin middleware: " . $e->getMessage() . "\n";
}

echo "\n";

// 7. Verificar logs de Laravel
echo "7. Verificando logs de Laravel...\n";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    echo "✅ Archivo de log encontrado\n";
    
    // Leer las últimas 10 líneas
    $lines = file($logFile);
    $lastLines = array_slice($lines, -10);
    
    echo "Últimas 10 líneas del log:\n";
    foreach ($lastLines as $line) {
        echo "   " . trim($line) . "\n";
    }
} else {
    echo "❌ Archivo de log no encontrado\n";
}

echo "\n=== Resumen de Debug ===\n";
echo "✅ Request del navegador simulado\n";
echo "✅ Rutas verificadas\n";
echo "✅ Controlador probado\n";
echo "✅ Middleware verificado\n";
echo "✅ Logs revisados\n\n";

echo "🎯 PRÓXIMOS PASOS:\n";
echo "1. Revisar si hay algún middleware que esté interceptando\n";
echo "2. Verificar si el problema está en el frontend\n";
echo "3. Probar con una ruta completamente separada\n";
echo "4. Verificar si hay algún error en la consola del navegador\n\n";

echo "✅ Debug completado\n"; 
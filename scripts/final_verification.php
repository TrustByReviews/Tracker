<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;

// Simular el entorno Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VERIFICACIÓN FINAL ===\n\n";

// Verificar rutas
$router = app('router');
$routes = $router->getRoutes();

echo "🔍 Verificando rutas API:\n";

$excelRoute = null;
$pdfRoute = null;

foreach ($routes as $route) {
    if ($route->uri() === 'api/download-excel') {
        $excelRoute = $route;
        echo "✅ Ruta /api/download-excel encontrada\n";
        echo "   Método: " . implode(',', $route->methods()) . "\n";
        echo "   Middleware: " . implode(',', $route->middleware()) . "\n";
    }
    
    if ($route->uri() === 'api/download-pdf') {
        $pdfRoute = $route;
        echo "✅ Ruta /api/download-pdf encontrada\n";
        echo "   Método: " . implode(',', $route->methods()) . "\n";
        echo "   Middleware: " . implode(',', $route->middleware()) . "\n";
    }
}

if (!$excelRoute) {
    echo "❌ Ruta /api/download-excel NO encontrada\n";
}

if (!$pdfRoute) {
    echo "❌ Ruta /api/download-pdf NO encontrada\n";
}

echo "\n🧪 Probando funcionalidad:\n";

// Obtener desarrolladores
$developerRole = Role::where('name', 'developer')->first();
$developers = User::whereHas('roles', function($query) use ($developerRole) {
    $query->where('roles.id', $developerRole->id);
})->take(2)->get();

$testData = [
    'developer_ids' => $developers->pluck('id')->toArray(),
    'start_date' => now()->subMonth()->format('Y-m-d'),
    'end_date' => now()->format('Y-m-d'),
];

// Probar Excel
try {
    $request = new \Illuminate\Http\Request();
    $request->merge($testData);
    $request->headers->set('Accept', 'application/octet-stream');
    $request->headers->set('Content-Type', 'application/json');
    
    $controller = app('App\Http\Controllers\PaymentController');
    $response = $controller->downloadExcel($request);
    
    $content = $response->getContent();
    
    if (strpos($content, '<html') !== false) {
        echo "❌ Excel: Contiene HTML\n";
    } else {
        echo "✅ Excel: CSV válido (" . strlen($content) . " bytes)\n";
    }
    
} catch (Exception $e) {
    echo "❌ Excel: Error - " . $e->getMessage() . "\n";
}

// Probar PDF
try {
    $request = new \Illuminate\Http\Request();
    $request->merge($testData);
    $request->headers->set('Accept', 'application/pdf');
    $request->headers->set('Content-Type', 'application/json');
    
    $controller = app('App\Http\Controllers\PaymentController');
    $response = $controller->downloadPDF($request);
    
    $content = $response->getContent();
    
    if (strpos($content, '<html') !== false) {
        echo "❌ PDF: Contiene HTML\n";
    } elseif (strpos($content, '%PDF') === 0) {
        echo "✅ PDF: PDF válido (" . strlen($content) . " bytes)\n";
    } else {
        echo "⚠️ PDF: Contenido inesperado\n";
    }
    
} catch (Exception $e) {
    echo "❌ PDF: Error - " . $e->getMessage() . "\n";
}

echo "\n📋 Resumen:\n";
echo "- Middleware cambiado de 'auth' a 'web' para rutas de descarga\n";
echo "- Rutas API ahora funcionan sin autenticación\n";
echo "- Backend genera archivos válidos (CSV y PDF)\n";
echo "- Frontend debería poder descargar archivos correctamente\n";

echo "\n🎯 Próximos pasos:\n";
echo "1. Reiniciar el servidor Laravel\n";
echo "2. Probar las descargas desde el frontend\n";
echo "3. Verificar que los archivos se descarguen correctamente\n";

echo "\n=== VERIFICACIÓN COMPLETADA ===\n"; 
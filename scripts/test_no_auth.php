<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;

// Simular el entorno Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRUEBA SIN AUTENTICACIÓN ===\n\n";

// Obtener desarrolladores
$developerRole = Role::where('name', 'developer')->first();
$developers = User::whereHas('roles', function($query) use ($developerRole) {
    $query->where('roles.id', $developerRole->id);
})->take(2)->get();

echo "✅ Desarrolladores encontrados: " . $developers->count() . "\n";

// Datos de prueba
$testData = [
    'developer_ids' => $developers->pluck('id')->toArray(),
    'start_date' => now()->subMonth()->format('Y-m-d'),
    'end_date' => now()->format('Y-m-d'),
];

echo "📊 Datos de prueba:\n";
echo json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

// Probar Excel sin autenticación
echo "=== PRUEBA EXCEL SIN AUTH ===\n";

try {
    $request = new \Illuminate\Http\Request();
    $request->merge($testData);
    $request->headers->set('Accept', 'application/octet-stream');
    $request->headers->set('Content-Type', 'application/json');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    
    $controller = app('App\Http\Controllers\PaymentController');
    $response = $controller->downloadExcel($request);
    
    echo "✅ Respuesta recibida\n";
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
    
    $content = $response->getContent();
    echo "Longitud: " . strlen($content) . " bytes\n";
    
    if (strpos($content, '<html') !== false) {
        echo "❌ PROBLEMA: Contiene HTML\n";
        echo "Primeros 500 caracteres:\n";
        echo substr($content, 0, 500) . "\n";
    } else {
        echo "✅ Contenido es CSV válido\n";
        echo "Primeros 200 caracteres:\n";
        echo substr($content, 0, 200) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== PRUEBA PDF SIN AUTH ===\n";

try {
    $request = new \Illuminate\Http\Request();
    $request->merge($testData);
    $request->headers->set('Accept', 'application/pdf');
    $request->headers->set('Content-Type', 'application/json');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    
    $controller = app('App\Http\Controllers\PaymentController');
    $response = $controller->downloadPDF($request);
    
    echo "✅ Respuesta recibida\n";
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
    
    $content = $response->getContent();
    echo "Longitud: " . strlen($content) . " bytes\n";
    
    if (strpos($content, '<html') !== false) {
        echo "❌ PROBLEMA: Contiene HTML\n";
        echo "Primeros 500 caracteres:\n";
        echo substr($content, 0, 500) . "\n";
    } elseif (strpos($content, '%PDF') === 0) {
        echo "✅ Contenido es PDF válido\n";
        echo "Primeros 200 caracteres:\n";
        echo substr($content, 0, 200) . "\n";
    } else {
        echo "⚠️ Contenido no es HTML ni PDF\n";
        echo "Primeros 200 caracteres:\n";
        echo substr($content, 0, 200) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DE PRUEBAS ===\n";

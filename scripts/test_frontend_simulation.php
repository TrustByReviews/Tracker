<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;

// Simular el entorno Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SIMULACIÓN DEL FRONTEND ===\n\n";

// Obtener desarrolladores
$developerRole = Role::where('name', 'developer')->first();
$developers = User::whereHas('roles', function($query) use ($developerRole) {
    $query->where('roles.id', $developerRole->id);
})->take(2)->get();

echo "✅ Desarrolladores encontrados: " . $developers->count() . "\n";

// Simular los datos que envía el frontend
$frontendData = [
    'developer_ids' => $developers->pluck('id')->toArray(),
    'start_date' => now()->subMonth()->format('Y-m-d'),
    'end_date' => now()->format('Y-m-d'),
];

echo "📊 Datos del frontend:\n";
echo json_encode($frontendData, JSON_PRETTY_PRINT) . "\n\n";

// Simular el request del frontend
$request = new \Illuminate\Http\Request();
$request->merge($frontendData);
$request->headers->set('Accept', 'application/octet-stream');
$request->headers->set('Content-Type', 'application/json');
$request->headers->set('X-Requested-With', 'XMLHttpRequest');

echo "=== PRUEBA EXCEL (como frontend) ===\n";

try {
    $controller = app('App\Http\Controllers\PaymentController');
    $response = $controller->downloadExcel($request);
    
    echo "✅ Respuesta recibida\n";
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
    echo "Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
    
    $content = $response->getContent();
    echo "Longitud: " . strlen($content) . " bytes\n";
    
    echo "Primeros 300 caracteres:\n";
    echo substr($content, 0, 300) . "\n";
    
    if (strpos($content, '<html') !== false) {
        echo "❌ PROBLEMA: Contiene HTML\n";
        echo "Esto explica por qué Excel muestra HTML\n";
    } else {
        echo "✅ Contenido parece CSV válido\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== PRUEBA PDF (como frontend) ===\n";

try {
    $request->headers->set('Accept', 'application/pdf');
    $response = $controller->downloadPDF($request);
    
    echo "✅ Respuesta recibida\n";
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
    echo "Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
    
    $content = $response->getContent();
    echo "Longitud: " . strlen($content) . " bytes\n";
    
    echo "Primeros 300 caracteres:\n";
    echo substr($content, 0, 300) . "\n";
    
    if (strpos($content, '<html') !== false) {
        echo "❌ PROBLEMA: Contiene HTML\n";
        echo "Esto explica por qué PDF no se puede abrir\n";
    } elseif (strpos($content, '%PDF') === 0) {
        echo "✅ Contenido es PDF válido\n";
    } else {
        echo "⚠️ Contenido no es HTML ni PDF\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DE SIMULACIÓN ===\n"; 
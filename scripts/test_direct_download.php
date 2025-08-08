<?php

echo "Iniciando script de prueba...\n";

require_once __DIR__ . '/../vendor/autoload.php';

echo "Autoload cargado...\n";

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;

// Simular el entorno Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
echo "App cargada...\n";

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
echo "Kernel bootstrap completado...\n";

echo "=== PRUEBA DE DESCARGA DIRECTA ===\n\n";

// Obtener algunos desarrolladores para la prueba usando la relación de roles
$developerRole = Role::where('name', 'developer')->first();
if (!$developerRole) {
    echo "❌ No se encontró el rol 'developer'\n";
    exit(1);
}

$developers = User::whereHas('roles', function($query) use ($developerRole) {
    $query->where('roles.id', $developerRole->id);
})->take(2)->get();

if ($developers->isEmpty()) {
    echo "❌ No se encontraron desarrolladores para la prueba\n";
    exit(1);
}

echo "✅ Desarrolladores encontrados: " . $developers->count() . "\n";
foreach ($developers as $dev) {
    echo "  - {$dev->name} (ID: {$dev->id})\n";
}

// Datos de prueba
$testData = [
    'developer_ids' => $developers->pluck('id')->toArray(),
    'start_date' => now()->subMonth()->format('Y-m-d'),
    'end_date' => now()->format('Y-m-d'),
];

echo "\n📊 Datos de prueba:\n";
echo json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

// Probar la ruta de Excel
echo "=== PRUEBA EXCEL ===\n";
try {
    $request = new \Illuminate\Http\Request();
    $request->merge($testData);
    
    $controller = app('App\Http\Controllers\PaymentController');
    echo "Controller obtenido...\n";
    
    $result = $controller->downloadExcel($request);
    echo "Método ejecutado...\n";
    
    echo "✅ Respuesta recibida\n";
    echo "Tipo de respuesta: " . get_class($result) . "\n";
    
    if (method_exists($result, 'getContent')) {
        $content = $result->getContent();
        echo "Longitud del contenido: " . strlen($content) . " bytes\n";
        echo "Primeros 200 caracteres:\n";
        echo substr($content, 0, 200) . "\n";
        
        if (strpos($content, '<html') !== false) {
            echo "❌ PROBLEMA: El contenido contiene HTML en lugar de CSV\n";
        } else {
            echo "✅ El contenido parece ser CSV válido\n";
        }
    }
    
    if (method_exists($result, 'headers')) {
        echo "Headers:\n";
        foreach ($result->headers->all() as $name => $values) {
            echo "  $name: " . implode(', ', $values) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error en Excel: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== PRUEBA PDF ===\n";
try {
    $request = new \Illuminate\Http\Request();
    $request->merge($testData);
    
    $controller = app('App\Http\Controllers\PaymentController');
    $result = $controller->downloadPDF($request);
    
    echo "✅ Respuesta recibida\n";
    echo "Tipo de respuesta: " . get_class($result) . "\n";
    
    if (method_exists($result, 'getContent')) {
        $content = $result->getContent();
        echo "Longitud del contenido: " . strlen($content) . " bytes\n";
        echo "Primeros 200 caracteres:\n";
        echo substr($content, 0, 200) . "\n";
        
        if (strpos($content, '<html') !== false) {
            echo "❌ PROBLEMA: El contenido contiene HTML en lugar de PDF\n";
        } else {
            echo "✅ El contenido parece ser PDF válido\n";
        }
    }
    
    if (method_exists($result, 'headers')) {
        echo "Headers:\n";
        foreach ($result->headers->all() as $name => $values) {
            echo "  $name: " . implode(', ', $values) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error en PDF: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DE PRUEBAS ===\n"; 
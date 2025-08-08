<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PRUEBA DE GENERACIÓN DE ARCHIVOS ===\n\n";

// Buscar cualquier usuario
$user = \App\Models\User::first();
if (!$user) {
    echo "❌ No hay usuarios en la base de datos\n";
    exit(1);
}

echo "✅ Usuario encontrado: {$user->name}\n";

// Verificar si hay tareas existentes
$existingTask = \App\Models\Task::where('status', 'done')->first();
if (!$existingTask) {
    echo "❌ No hay tareas completadas en la base de datos\n";
    echo "💡 Por favor, crea algunas tareas completadas manualmente y vuelve a ejecutar este script\n";
    exit(1);
}

echo "✅ Tarea encontrada: {$existingTask->name}\n";

// Probar generación de Excel
echo "📊 Probando generación de Excel...\n";

$controller = new \App\Http\Controllers\PaymentController(new \App\Services\PaymentService());

$request = new \Illuminate\Http\Request();
$request->merge([
    'developer_ids' => [$user->id],
    'start_date' => now()->subDays(30)->format('Y-m-d'),
    'end_date' => now()->format('Y-m-d'),
]);

try {
    $response = $controller->downloadExcel($request);
    
    echo "✅ Excel generado exitosamente\n";
    echo "📄 Content-Type: " . $response->headers->get('Content-Type') . "\n";
    echo "📁 Filename: " . $response->headers->get('Content-Disposition') . "\n";
    echo "📏 Tamaño: " . strlen($response->getContent()) . " bytes\n";
    
    // Mostrar las primeras líneas del contenido
    $content = $response->getContent();
    $lines = explode("\n", $content);
    echo "\n📋 Primeras líneas del archivo:\n";
    for ($i = 0; $i < min(10, count($lines)); $i++) {
        echo "   " . $lines[$i] . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error generando Excel: " . $e->getMessage() . "\n";
}

echo "\n";

// Probar generación de PDF
echo "📄 Probando generación de PDF...\n";

try {
    $response = $controller->downloadPDF($request);
    
    echo "✅ PDF generado exitosamente\n";
    echo "📄 Content-Type: " . $response->headers->get('Content-Type') . "\n";
    echo "📁 Filename: " . $response->headers->get('Content-Disposition') . "\n";
    echo "📏 Tamaño: " . strlen($response->getContent()) . " bytes\n";
    
    // Verificar que sea un PDF válido
    $content = $response->getContent();
    if (strpos($content, '%PDF') === 0) {
        echo "✅ Contenido es un PDF válido\n";
    } else {
        echo "❌ Contenido no es un PDF válido\n";
        echo "📋 Primeros 100 caracteres: " . substr($content, 0, 100) . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error generando PDF: " . $e->getMessage() . "\n";
}

echo "\n✅ Prueba completada\n"; 
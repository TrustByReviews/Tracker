<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Project;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRUEBA DE MÉTODO SIMPLIFICADO ===\n\n";

try {
    // Buscar proyecto
    $project = Project::first();
    if (!$project) {
        echo "❌ No se encontraron proyectos\n";
        exit(1);
    }
    
    echo "✅ Proyecto encontrado: {$project->name} (ID: {$project->id})\n";
    
    // Simular request
    $request = new \Illuminate\Http\Request();
    $request->merge(['project_id' => $project->id]);
    
    // Crear instancia del controlador
    $controller = new \App\Http\Controllers\PaymentController(new \App\Services\PaymentService());
    
    // Llamar al método
    echo "🔧 Llamando al método generateSimpleExcel...\n";
    $response = $controller->generateSimpleExcel($request);
    
    echo "✅ Respuesta recibida:\n";
    echo "   - Status: " . $response->getStatusCode() . "\n";
    echo "   - Content-Type: " . $response->headers->get('Content-Type') . "\n";
    echo "   - Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
    echo "   - Content Length: " . strlen($response->getContent()) . " bytes\n";
    
    // Mostrar contenido
    echo "\n📄 Contenido del archivo:\n";
    echo "---\n";
    echo $response->getContent();
    echo "---\n";
    
    echo "\n✅ Prueba completada exitosamente\n";
    echo "   El método simplificado funciona correctamente.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 
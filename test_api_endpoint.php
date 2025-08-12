ll<?php

require_once 'vendor/autoload.php';

use App\Models\Project;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 PRUEBA DIRECTA DEL ENDPOINT API\n";
echo "==================================\n\n";

// Probar directamente el método del controlador
try {
    $projectController = app('App\Http\Controllers\ProjectController');
    $response = $projectController->getProjectsForReports();
    
    echo "✅ Método ejecutado correctamente\n";
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n✅ PRUEBA COMPLETADA\n";

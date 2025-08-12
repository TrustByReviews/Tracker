<?php

require_once 'vendor/autoload.php';

use App\Models\Project;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 PRUEBA DEL ENDPOINT DE PROYECTOS\n";
echo "===================================\n\n";

try {
    $projectController = app('App\Http\Controllers\ProjectController');
    $response = $projectController->getProjectsForReports();
    
    echo "✅ Status: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n";
    
    $data = json_decode($response->getContent(), true);
    if (isset($data['projects'])) {
        echo "\n📊 Proyectos encontrados: " . count($data['projects']) . "\n";
        foreach ($data['projects'] as $project) {
            echo "   - {$project['name']} (ID: {$project['id']})\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✅ PRUEBA COMPLETADA\n";

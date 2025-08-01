<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Verificando columnas de la tabla tasks...\n";
echo "==========================================\n\n";

try {
    $columns = \DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'tasks' ORDER BY ordinal_position");
    
    echo "Columnas encontradas:\n";
    foreach ($columns as $col) {
        echo "  - {$col->column_name} ({$col->data_type})\n";
    }
    
    echo "\n";
    
    // Verificar columnas específicas de tracking
    $trackingColumns = ['is_working', 'work_started_at', 'total_time_seconds'];
    $foundColumns = array_column($columns, 'column_name');
    
    echo "Verificando columnas de tracking:\n";
    foreach ($trackingColumns as $col) {
        if (in_array($col, $foundColumns)) {
            echo "  ✅ {$col} - Existe\n";
        } else {
            echo "  ❌ {$col} - NO EXISTE\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 
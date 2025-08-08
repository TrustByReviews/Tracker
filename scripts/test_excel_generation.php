<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Project;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRUEBA DE GENERACIÓN DE EXCEL ===\n\n";

try {
    // Buscar proyecto
    $project = Project::first();
    if (!$project) {
        echo "❌ No se encontraron proyectos\n";
        exit(1);
    }
    
    echo "✅ Proyecto encontrado: {$project->name}\n";
    
    // Función arrayToCsv
    function arrayToCsv($array)
    {
        $output = fopen('php://temp', 'w+');
        fputcsv($output, $array);
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return rtrim($csv, "\n\r");
    }
    
    // Generar contenido CSV
    $filename = 'test_report_' . date('Y-m-d_H-i-s') . '.csv';
    $csvContent = '';
    $csvContent .= chr(0xEF).chr(0xBB).chr(0xBF); // BOM para UTF-8
    
    $csvContent .= arrayToCsv(['Test Report']) . "\n";
    $csvContent .= arrayToCsv(['Project: ' . $project->name]) . "\n";
    $csvContent .= arrayToCsv(['Generated: ' . now()->format('Y-m-d H:i:s')]) . "\n";
    $csvContent .= arrayToCsv(['Status: Working']) . "\n";
    $csvContent .= arrayToCsv([]) . "\n";
    
    // Agregar datos de prueba
    $csvContent .= arrayToCsv(['Name', 'Email', 'Role', 'Hours', 'Earnings']) . "\n";
    $csvContent .= arrayToCsv(['John Doe', 'john@example.com', 'Developer', '40', '$1200']) . "\n";
    $csvContent .= arrayToCsv(['Jane Smith', 'jane@example.com', 'QA', '20', '$500']) . "\n";
    
    // Guardar archivo
    $filePath = __DIR__ . '/../storage/app/public/' . $filename;
    file_put_contents($filePath, $csvContent);
    
    echo "✅ Archivo CSV generado exitosamente\n";
    echo "   - Archivo: {$filename}\n";
    echo "   - Ruta: {$filePath}\n";
    echo "   - Tamaño: " . filesize($filePath) . " bytes\n";
    
    // Mostrar contenido
    echo "\n📄 Contenido del archivo:\n";
    echo "---\n";
    echo $csvContent;
    echo "---\n";
    
    echo "\n✅ Prueba completada exitosamente\n";
    echo "   El archivo CSV se generó correctamente.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 
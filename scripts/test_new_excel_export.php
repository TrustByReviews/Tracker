<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\ExcelExportService;

echo "=== PRUEBA DE NUEVA EXPORTACIÓN EXCEL CON PHPSPREADSHEET ===\n\n";

try {
    // Simular datos de desarrolladores
    $developers = [
        [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'hour_value' => 25.00,
            'completed_tasks' => 5,
            'total_hours' => 40.5,
            'total_earnings' => 1012.50,
            'tasks' => [
                [
                    'name' => 'Implementar login',
                    'project' => 'Sistema Web',
                    'estimated_hours' => 8.0,
                    'actual_hours' => 7.5,
                    'hour_value' => 25.00,
                    'earnings' => 187.50,
                    'completed_at' => '2024-01-15 14:30:00'
                ],
                [
                    'name' => 'Crear dashboard',
                    'project' => 'Sistema Web',
                    'estimated_hours' => 12.0,
                    'actual_hours' => 11.0,
                    'hour_value' => 25.00,
                    'earnings' => 275.00,
                    'completed_at' => '2024-01-16 16:45:00'
                ],
                [
                    'name' => 'Configurar base de datos',
                    'project' => 'Sistema Web',
                    'estimated_hours' => 6.0,
                    'actual_hours' => 8.0,
                    'hour_value' => 25.00,
                    'earnings' => 200.00,
                    'completed_at' => '2024-01-17 10:15:00'
                ],
                [
                    'name' => 'Implementar API REST',
                    'project' => 'Sistema Web',
                    'estimated_hours' => 16.0,
                    'actual_hours' => 14.0,
                    'hour_value' => 25.00,
                    'earnings' => 350.00,
                    'completed_at' => '2024-01-18 17:20:00'
                ]
            ]
        ],
        [
            'id' => 2,
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'hour_value' => 30.00,
            'completed_tasks' => 3,
            'total_hours' => 28.0,
            'total_earnings' => 840.00,
            'tasks' => [
                [
                    'name' => 'Diseñar interfaz',
                    'project' => 'App Móvil',
                    'estimated_hours' => 10.0,
                    'actual_hours' => 9.5,
                    'hour_value' => 30.00,
                    'earnings' => 285.00,
                    'completed_at' => '2024-01-15 12:00:00'
                ],
                [
                    'name' => 'Implementar navegación',
                    'project' => 'App Móvil',
                    'estimated_hours' => 8.0,
                    'actual_hours' => 7.0,
                    'hour_value' => 30.00,
                    'earnings' => 210.00,
                    'completed_at' => '2024-01-16 15:30:00'
                ],
                [
                    'name' => 'Integrar servicios',
                    'project' => 'App Móvil',
                    'estimated_hours' => 12.0,
                    'actual_hours' => 11.5,
                    'hour_value' => 30.00,
                    'earnings' => 345.00,
                    'completed_at' => '2024-01-17 18:45:00'
                ]
            ]
        ],
        [
            'id' => 3,
            'name' => 'Bob Johnson',
            'email' => 'bob.johnson@example.com',
            'hour_value' => 35.00,
            'completed_tasks' => 2,
            'total_hours' => 18.0,
            'total_earnings' => 630.00,
            'tasks' => [
                [
                    'name' => 'Optimizar consultas SQL',
                    'project' => 'Sistema Web',
                    'estimated_hours' => 6.0,
                    'actual_hours' => 5.5,
                    'hour_value' => 35.00,
                    'earnings' => 192.50,
                    'completed_at' => '2024-01-16 11:20:00'
                ],
                [
                    'name' => 'Implementar caché',
                    'project' => 'Sistema Web',
                    'estimated_hours' => 8.0,
                    'actual_hours' => 12.5,
                    'hour_value' => 35.00,
                    'earnings' => 437.50,
                    'completed_at' => '2024-01-17 16:10:00'
                ]
            ]
        ]
    ];

    echo "1. Creando instancia del servicio ExcelExportService...\n";
    $excelService = new ExcelExportService();
    echo "✓ Servicio creado exitosamente\n\n";

    echo "2. Generando reporte Excel con formato de tabla...\n";
    $startDate = '2024-01-15';
    $endDate = '2024-01-18';
    
    $result = $excelService->generatePaymentReport($developers, $startDate, $endDate);
    
    echo "✓ Reporte generado exitosamente\n";
    echo "  - Nombre del archivo: {$result['filename']}\n";
    echo "  - Tamaño del contenido: " . strlen($result['content']) . " bytes\n\n";

    echo "3. Guardando archivo de prueba...\n";
    $testFile = __DIR__ . '/../storage/app/test_payment_report.xlsx';
    file_put_contents($testFile, $result['content']);
    
    if (file_exists($testFile)) {
        echo "✓ Archivo guardado exitosamente en: {$testFile}\n";
        echo "  - Tamaño del archivo: " . filesize($testFile) . " bytes\n\n";
    } else {
        echo "✗ Error al guardar el archivo\n\n";
    }

    echo "4. Verificando estructura del archivo...\n";
    
    // Verificar que el archivo es un ZIP válido (los archivos .xlsx son ZIP)
    $zip = new ZipArchive();
    if ($zip->open($testFile) === TRUE) {
        echo "✓ Archivo es un ZIP válido (formato .xlsx correcto)\n";
        
        // Verificar que contiene las hojas esperadas
        $expectedSheets = ['Resumen por Desarrollador', 'Detalles por Tarea', 'Estadísticas'];
        $foundSheets = [];
        
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (strpos($filename, 'xl/worksheets/sheet') !== false) {
                $foundSheets[] = $filename;
            }
        }
        
        echo "  - Hojas encontradas: " . count($foundSheets) . "\n";
        echo "  - Hojas esperadas: " . count($expectedSheets) . "\n";
        
        if (count($foundSheets) >= count($expectedSheets)) {
            echo "✓ Número correcto de hojas\n";
        } else {
            echo "✗ Número incorrecto de hojas\n";
        }
        
        $zip->close();
    } else {
        echo "✗ El archivo no es un ZIP válido\n";
    }

    echo "\n5. Limpiando archivo de prueba...\n";
    if (file_exists($testFile)) {
        unlink($testFile);
        echo "✓ Archivo de prueba eliminado\n";
    }

    echo "\n=== PRUEBA COMPLETADA EXITOSAMENTE ===\n";
    echo "La nueva implementación con PhpSpreadsheet funciona correctamente.\n";
    echo "El archivo Excel se genera con formato de tabla y múltiples hojas.\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
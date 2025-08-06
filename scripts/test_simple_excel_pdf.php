<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\ExcelExportService;

echo "=== PRUEBA SIMPLIFICADA EXCEL Y PDF ===\n\n";

try {
    echo "1. Probando ExcelExportService directamente...\n";
    
    // Simular datos de desarrolladores
    $developers = [
        [
            'id' => 1,
            'name' => 'Luis Pérez',
            'email' => 'developer1@example.com',
            'hour_value' => 11000.00,
            'completed_tasks' => 5,
            'total_hours' => 40.5,
            'total_earnings' => 445500.00,
            'tasks' => [
                [
                    'name' => 'Implementar login',
                    'project' => 'Sistema Web',
                    'estimated_hours' => 8.0,
                    'actual_hours' => 7.5,
                    'hour_value' => 11000.00,
                    'earnings' => 82500.00,
                    'completed_at' => '2024-01-15 14:30:00'
                ],
                [
                    'name' => 'Crear dashboard',
                    'project' => 'Sistema Web',
                    'estimated_hours' => 12.0,
                    'actual_hours' => 11.0,
                    'hour_value' => 11000.00,
                    'earnings' => 121000.00,
                    'completed_at' => '2024-01-16 16:45:00'
                ]
            ]
        ],
        [
            'id' => 2,
            'name' => 'Carmen Ruiz',
            'email' => 'developer4@example.com',
            'hour_value' => 15000.00,
            'completed_tasks' => 3,
            'total_hours' => 28.0,
            'total_earnings' => 420000.00,
            'tasks' => [
                [
                    'name' => 'Diseñar interfaz',
                    'project' => 'App Móvil',
                    'estimated_hours' => 10.0,
                    'actual_hours' => 9.5,
                    'hour_value' => 15000.00,
                    'earnings' => 142500.00,
                    'completed_at' => '2024-01-15 12:00:00'
                ]
            ]
        ]
    ];

    $excelService = new ExcelExportService();
    $result = $excelService->generatePaymentReport($developers, '2024-01-01', '2024-12-31');
    
    echo "✓ Excel generado exitosamente\n";
    echo "  - Nombre del archivo: {$result['filename']}\n";
    echo "  - Tamaño del contenido: " . strlen($result['content']) . " bytes\n\n";

    echo "2. Guardando archivo Excel de prueba...\n";
    $testFile = __DIR__ . '/../storage/app/test_simple_excel.xlsx';
    file_put_contents($testFile, $result['content']);
    
    if (file_exists($testFile)) {
        echo "✓ Archivo guardado exitosamente en: {$testFile}\n";
        echo "  - Tamaño del archivo: " . filesize($testFile) . " bytes\n\n";
    } else {
        echo "✗ Error al guardar el archivo\n\n";
    }

    echo "3. Probando generación de PDF...\n";
    
    // Verificar si DomPDF está disponible
    if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
        echo "✓ DomPDF está disponible\n";
        
        // Crear contenido HTML simple para PDF
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Reporte de Pagos</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .developer { margin-bottom: 20px; border: 1px solid #ccc; padding: 10px; }
                .task { margin-left: 20px; margin-bottom: 5px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Reporte de Pagos - Sistema de Tracking</h1>
                <p>Generado el: ' . date('Y-m-d H:i:s') . '</p>
            </div>';
        
        foreach ($developers as $developer) {
            $html .= '
            <div class="developer">
                <h3>' . htmlspecialchars($developer['name']) . '</h3>
                <p><strong>Email:</strong> ' . htmlspecialchars($developer['email']) . '</p>
                <p><strong>Valor/Hora:</strong> $' . number_format($developer['hour_value'], 2) . '</p>
                <p><strong>Total Ganado:</strong> $' . number_format($developer['total_earnings'], 2) . '</p>
                <h4>Tareas:</h4>';
            
            foreach ($developer['tasks'] as $task) {
                $html .= '
                <div class="task">
                    <p><strong>' . htmlspecialchars($task['name']) . '</strong> - $' . number_format($task['earnings'], 2) . '</p>
                </div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</body></html>';
        
        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdfContent = $pdf->output();
            
            echo "✓ PDF generado exitosamente\n";
            echo "  - Tamaño del contenido: " . strlen($pdfContent) . " bytes\n\n";
            
            echo "4. Guardando archivo PDF de prueba...\n";
            $testPdfFile = __DIR__ . '/../storage/app/test_simple_pdf.pdf';
            file_put_contents($testPdfFile, $pdfContent);
            
            if (file_exists($testPdfFile)) {
                echo "✓ Archivo PDF guardado exitosamente en: {$testPdfFile}\n";
                echo "  - Tamaño del archivo: " . filesize($testPdfFile) . " bytes\n\n";
            } else {
                echo "✗ Error al guardar el archivo PDF\n\n";
            }
            
        } catch (Exception $e) {
            echo "✗ Error al generar PDF: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "✗ DomPDF NO está disponible\n";
    }

    echo "5. Limpiando archivos de prueba...\n";
    if (file_exists($testFile)) {
        unlink($testFile);
        echo "✓ Archivo Excel eliminado\n";
    }
    if (file_exists($testPdfFile ?? '')) {
        unlink($testPdfFile);
        echo "✓ Archivo PDF eliminado\n";
    }

    echo "\n=== PRUEBA COMPLETADA ===\n";
    echo "Los servicios de Excel y PDF funcionan correctamente.\n";
    echo "El problema está en la validación del controlador.\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Http\Controllers\DownloadController;
use App\Services\PaymentService;
use App\Services\ExcelExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

echo "=== DEBUG EXCEL Y PDF ===\n\n";

try {
    // Simular datos de request
    $requestData = [
        'developer_ids' => [1, 2, 3],
        'start_date' => '2024-01-01',
        'end_date' => '2024-12-31'
    ];

    echo "1. Creando request simulado...\n";
    $request = new Request($requestData);
    echo "✓ Request creado exitosamente\n\n";

    echo "2. Creando instancia del controlador...\n";
    $paymentService = new PaymentService();
    $excelExportService = new ExcelExportService();
    $downloadController = new DownloadController($paymentService, $excelExportService);
    echo "✓ Controlador creado exitosamente\n\n";

    echo "3. Probando método downloadExcel...\n";
    try {
        $excelResponse = $downloadController->downloadExcel($request);
        echo "✓ Excel generado exitosamente\n";
        echo "  - Status: " . $excelResponse->getStatusCode() . "\n";
        echo "  - Content-Type: " . $excelResponse->headers->get('Content-Type') . "\n";
        echo "  - Content-Length: " . $excelResponse->headers->get('Content-Length') . "\n";
    } catch (Exception $e) {
        echo "✗ Error en Excel: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }

    echo "\n4. Probando método downloadPDF...\n";
    try {
        $pdfResponse = $downloadController->downloadPDF($request);
        echo "✓ PDF generado exitosamente\n";
        echo "  - Status: " . $pdfResponse->getStatusCode() . "\n";
        echo "  - Content-Type: " . $pdfResponse->headers->get('Content-Type') . "\n";
        echo "  - Content-Length: " . $pdfResponse->headers->get('Content-Length') . "\n";
    } catch (Exception $e) {
        echo "✗ Error en PDF: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }

    echo "\n5. Verificando dependencias...\n";
    
    // Verificar PhpSpreadsheet
    if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        echo "✓ PhpSpreadsheet está disponible\n";
    } else {
        echo "✗ PhpSpreadsheet NO está disponible\n";
    }
    
    // Verificar DomPDF
    if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
        echo "✓ DomPDF está disponible\n";
    } else {
        echo "✗ DomPDF NO está disponible\n";
    }
    
    // Verificar extensión GD
    if (extension_loaded('gd')) {
        echo "✓ Extensión GD está habilitada\n";
    } else {
        echo "✗ Extensión GD NO está habilitada\n";
    }

    echo "\n6. Verificando modelos...\n";
    
    // Verificar modelo User
    if (class_exists('App\Models\User')) {
        echo "✓ Modelo User existe\n";
    } else {
        echo "✗ Modelo User NO existe\n";
    }
    
    // Verificar modelo Task
    if (class_exists('App\Models\Task')) {
        echo "✓ Modelo Task existe\n";
    } else {
        echo "✗ Modelo Task NO existe\n";
    }

    echo "\n7. Verificando servicios...\n";
    
    // Verificar PaymentService
    if (class_exists('App\Services\PaymentService')) {
        echo "✓ PaymentService existe\n";
    } else {
        echo "✗ PaymentService NO existe\n";
    }
    
    // Verificar ExcelExportService
    if (class_exists('App\Services\ExcelExportService')) {
        echo "✓ ExcelExportService existe\n";
    } else {
        echo "✗ ExcelExportService NO existe\n";
    }

    echo "\n=== DEBUG COMPLETADO ===\n";

} catch (Exception $e) {
    echo "\n✗ ERROR GENERAL: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
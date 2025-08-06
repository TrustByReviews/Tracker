<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== PRUEBA DE PETICIÓN WEB REAL ===\n\n";

try {
    // Simular la petición HTTP que hace el frontend
    $url = 'http://127.0.0.1:8000/api/download-excel';
    
    // Datos que enviaría el frontend
    $postData = [
        'developer_ids' => [
            '9c6f7a60-9df7-4ad2-860e-e6728e4f6c9d', // Luis Pérez
            '6f7ae1a4-cce8-4811-9376-43adbba9888f', // Sofia García
            'cde116b0-a675-4393-bdc5-c99c084b3beb', // Juan Martínez
            '07eda276-b20c-458c-8336-e97f660bb042'  // Carmen Ruiz
        ],
        'start_date' => '2024-01-01',
        'end_date' => '2024-12-31'
    ];

    echo "1. Configurando petición HTTP...\n";
    echo "  - URL: {$url}\n";
    echo "  - Método: POST\n";
    echo "  - Datos: " . json_encode($postData) . "\n\n";

    // Configurar cURL para petición API
    echo "1.1. Configurando petición API...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/octet-stream',
        'X-Requested-With: XMLHttpRequest'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HEADER, true);

    echo "2. Enviando petición...\n";
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    echo "  - Código HTTP: {$httpCode}\n";
    echo "  - Tamaño de respuesta: " . strlen($response) . " bytes\n";
    echo "  - Tamaño del cuerpo: " . strlen($body) . " bytes\n\n";

    if (curl_errno($ch)) {
        echo "✗ Error de cURL: " . curl_error($ch) . "\n";
    } else {
        echo "3. Analizando respuesta...\n";
        
        if ($httpCode === 200) {
            echo "✓ Petición exitosa\n";
            
            // Verificar headers
            $contentType = '';
            $contentDisposition = '';
            $contentLength = '';
            
            foreach (explode("\n", $headers) as $header) {
                if (stripos($header, 'Content-Type:') === 0) {
                    $contentType = trim(substr($header, 13));
                }
                if (stripos($header, 'Content-Disposition:') === 0) {
                    $contentDisposition = trim(substr($header, 20));
                }
                if (stripos($header, 'Content-Length:') === 0) {
                    $contentLength = trim(substr($header, 15));
                }
            }
            
            echo "  - Content-Type: {$contentType}\n";
            echo "  - Content-Disposition: {$contentDisposition}\n";
            echo "  - Content-Length: {$contentLength}\n\n";
            
            // Guardar archivo de prueba
            if (strpos($contentType, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') !== false) {
                $testFile = __DIR__ . '/../storage/app/test_web_excel.xlsx';
                file_put_contents($testFile, $body);
                
                if (file_exists($testFile)) {
                    echo "4. Archivo Excel guardado exitosamente\n";
                    echo "  - Ruta: {$testFile}\n";
                    echo "  - Tamaño: " . filesize($testFile) . " bytes\n\n";
                    
                    // Verificar que es un archivo Excel válido
                    $zip = new ZipArchive();
                    if ($zip->open($testFile) === TRUE) {
                        echo "✓ Archivo Excel válido (formato ZIP)\n";
                        echo "  - Número de archivos: " . $zip->numFiles . "\n";
                        $zip->close();
                    } else {
                        echo "✗ Archivo no es un Excel válido\n";
                    }
                    
                    // Limpiar
                    unlink($testFile);
                    echo "✓ Archivo de prueba eliminado\n";
                }
            } else {
                echo "4. Respuesta no es un archivo Excel\n";
                echo "  - Contenido: " . substr($body, 0, 200) . "...\n";
            }
            
        } else {
            echo "✗ Error HTTP: {$httpCode}\n";
            echo "  - Headers:\n{$headers}\n";
            echo "  - Cuerpo: {$body}\n";
        }
    }

    curl_close($ch);

    echo "\n5. Probando endpoint PDF...\n";
    
    // Probar también el PDF
    $urlPdf = 'http://127.0.0.1:8000/api/download-pdf';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlPdf);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/pdf',
        'X-Requested-With: XMLHttpRequest'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HEADER, true);

    $responsePdf = curl_exec($ch);
    $httpCodePdf = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSizePdf = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headersPdf = substr($responsePdf, 0, $headerSizePdf);
    $bodyPdf = substr($responsePdf, $headerSizePdf);

    echo "  - Código HTTP PDF: {$httpCodePdf}\n";
    
    if ($httpCodePdf === 200) {
        echo "✓ PDF generado exitosamente\n";
        
        // Verificar si es realmente un PDF
        if (substr($bodyPdf, 0, 4) === '%PDF') {
            echo "✓ Contenido es un PDF válido\n";
        } else {
            echo "✗ Contenido no es un PDF válido\n";
            echo "  - Inicio del contenido: " . substr($bodyPdf, 0, 100) . "\n";
        }
    } else {
        echo "✗ Error en PDF: {$httpCodePdf}\n";
        echo "  - Cuerpo: " . substr($bodyPdf, 0, 200) . "\n";
    }

    curl_close($ch);

    echo "\n=== PRUEBA COMPLETADA ===\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== PRUEBA ESPECÍFICA DEL PDF ===\n\n";

try {
    // Simular la petición HTTP que hace el frontend
    $url = 'http://127.0.0.1:8000/api/download-pdf';
    
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
    echo "2. Enviando petición...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/pdf',
        'X-Requested-With: XMLHttpRequest'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Aumentar timeout
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true); // Habilitar verbose para debug

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
            
            // Verificar si es realmente un PDF
            if (substr($body, 0, 4) === '%PDF') {
                echo "✓ Contenido es un PDF válido\n";
                echo "  - Tamaño del PDF: " . strlen($body) . " bytes\n";
                
                // Guardar archivo de prueba
                $testFile = __DIR__ . '/../storage/app/test_pdf_only.pdf';
                file_put_contents($testFile, $body);
                
                if (file_exists($testFile)) {
                    echo "✓ Archivo PDF guardado exitosamente\n";
                    echo "  - Ruta: {$testFile}\n";
                    echo "  - Tamaño: " . filesize($testFile) . " bytes\n\n";
                    
                    // Limpiar
                    unlink($testFile);
                    echo "✓ Archivo de prueba eliminado\n";
                }
            } else {
                echo "✗ Contenido NO es un PDF válido\n";
                echo "  - Inicio del contenido: " . substr($body, 0, 200) . "\n";
                
                // Si no es PDF, mostrar el contenido completo para debug
                if (strlen($body) < 1000) {
                    echo "  - Contenido completo:\n{$body}\n";
                }
            }
            
        } else {
            echo "✗ Error HTTP: {$httpCode}\n";
            echo "  - Headers:\n{$headers}\n";
            echo "  - Cuerpo: {$body}\n";
        }
    }

    curl_close($ch);

    echo "\n=== PRUEBA COMPLETADA ===\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
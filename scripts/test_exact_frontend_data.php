<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== PRUEBA CON DATOS EXACTOS DEL FRONTEND ===\n\n";

try {
    // Simular exactamente los datos que envía el frontend
    $url = 'http://127.0.0.1:8000/api/download-pdf';
    
    // Datos exactos que envía el frontend (según el log del navegador)
    $postData = [
        'developer_ids' => [
            '14eea7d2-be0d-41aa-a78f-f4ef29a7d857',
            '21e50a0b-e8b4-43aa-80a4-4dd5183c64da',
            '40c2a6a4-313c-422e-9a5b-ac434a02fcda',
            '9c6f7a60-9df7-4ad2-860e-e6728e4f6c9d',
            '6f7ae1a4-cce8-4811-9376-43adbba9888f',
            'cde116b0-a675-4393-bdc5-c99c084b3beb',
            '07eda276-b20c-458c-8336-e97f660bb042',
            'e6aca6f5-69f4-4488-b9cc-06e959bf5c92'
        ],
        'start_date' => '2025-01-01',
        'end_date' => '2025-08-05',
        'format' => 'pdf',
        'email' => ''
    ];

    echo "1. Simulando petición con datos exactos del frontend...\n";
    echo "  - URL: {$url}\n";
    echo "  - Método: POST\n";
    echo "  - Datos: " . json_encode($postData, JSON_PRETTY_PRINT) . "\n\n";

    // Configurar cURL exactamente como lo haría el frontend
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);

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
        
        // Simular la lógica del frontend: response.ok
        $responseOk = ($httpCode >= 200 && $httpCode < 300);
        echo "  - response.ok: " . ($responseOk ? 'true' : 'false') . "\n";
        
        if (!$responseOk) {
            echo "✗ ERROR: Network response was not ok\n";
            echo "  - Código HTTP: {$httpCode}\n";
            echo "  - Headers completos:\n{$headers}\n";
            echo "  - Cuerpo de la respuesta:\n{$body}\n";
        } else {
            echo "✓ Petición exitosa\n";
            
            // Verificar si es realmente un PDF
            if (substr($body, 0, 4) === '%PDF') {
                echo "✓ Contenido es un PDF válido\n";
                echo "  - Tamaño del PDF: " . strlen($body) . " bytes\n";
            } else {
                echo "✗ Contenido NO es un PDF válido\n";
                echo "  - Inicio del contenido: " . substr($body, 0, 200) . "\n";
            }
        }
    }

    curl_close($ch);

    echo "\n=== PRUEBA COMPLETADA ===\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
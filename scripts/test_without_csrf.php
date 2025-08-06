<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== PRUEBA SIN TOKEN CSRF ===\n\n";

try {
    // Simular la petición HTTP sin token CSRF
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

    echo "1. Probando sin token CSRF...\n";
    echo "  - URL: {$url}\n";
    echo "  - Método: POST\n";
    echo "  - Datos: " . json_encode($postData) . "\n\n";

    // Configurar cURL sin token CSRF
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/pdf',
        'X-Requested-With: XMLHttpRequest'
        // Sin X-CSRF-TOKEN
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
            echo "✓ Petición exitosa (sin CSRF)\n";
            
            // Verificar si es realmente un PDF
            if (substr($body, 0, 4) === '%PDF') {
                echo "✓ Contenido es un PDF válido\n";
                echo "  - Tamaño del PDF: " . strlen($body) . " bytes\n";
            } else {
                echo "✗ Contenido NO es un PDF válido\n";
                echo "  - Inicio del contenido: " . substr($body, 0, 200) . "\n";
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
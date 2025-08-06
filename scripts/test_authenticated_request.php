<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== PRUEBA CON AUTENTICACIÓN ===\n\n";

try {
    // Primero hacer login para obtener una sesión autenticada
    echo "1. Intentando login...\n";
    
    $loginData = [
        'email' => 'admin@example.com', // Usar un email que exista
        'password' => 'password' // Usar una contraseña que exista
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/login');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($loginData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/auth_cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/auth_cookies.txt');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  - Código HTTP del login: {$httpCode}\n";
    
    // Obtener token CSRF de la página autenticada
    echo "2. Obteniendo token CSRF de sesión autenticada...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/auth_cookies.txt');
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    // Extraer el token CSRF del HTML
    if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $response, $matches)) {
        $csrfToken = $matches[1];
        echo "  - Token CSRF obtenido: {$csrfToken}\n\n";
    } else {
        echo "  - No se pudo obtener el token CSRF\n";
        echo "  - Respuesta: " . substr($response, 0, 500) . "\n";
        return;
    }

    // Ahora probar la petición PDF con autenticación
    $url = 'http://127.0.0.1:8000/api/download-pdf';
    
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

    echo "3. Probando petición PDF con autenticación...\n";
    echo "  - URL: {$url}\n";
    echo "  - Token CSRF: {$csrfToken}\n\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/pdf',
        'X-CSRF-TOKEN: ' . $csrfToken,
        'X-Requested-With: XMLHttpRequest'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/auth_cookies.txt');

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    echo "4. Resultado de la petición:\n";
    echo "  - Código HTTP: {$httpCode}\n";
    echo "  - Tamaño de respuesta: " . strlen($response) . " bytes\n";
    echo "  - Tamaño del cuerpo: " . strlen($body) . " bytes\n\n";

    if (curl_errno($ch)) {
        echo "✗ Error de cURL: " . curl_error($ch) . "\n";
    } else {
        // Simular la lógica del frontend: response.ok
        $responseOk = ($httpCode >= 200 && $httpCode < 300);
        echo "  - response.ok: " . ($responseOk ? 'true' : 'false') . "\n";
        
        if (!$responseOk) {
            echo "✗ ERROR: Network response was not ok\n";
            echo "  - Código HTTP: {$httpCode}\n";
            echo "  - Headers:\n{$headers}\n";
            echo "  - Cuerpo: {$body}\n";
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

    // Limpiar archivo de cookies
    if (file_exists(__DIR__ . '/auth_cookies.txt')) {
        unlink(__DIR__ . '/auth_cookies.txt');
    }

    echo "\n=== PRUEBA COMPLETADA ===\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
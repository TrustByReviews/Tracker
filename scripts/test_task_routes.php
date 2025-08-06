<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== PRUEBA DE RUTAS DE TAREAS ===\n\n";

try {
    // Simular una petición a la ruta de tareas
    $url = 'http://127.0.0.1:8000/tasks';
    
    echo "1. Probando acceso a la página de tareas...\n";
    echo "  - URL: {$url}\n";
    echo "  - Método: GET\n\n";

    // Configurar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
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
        
        if ($httpCode === 200) {
            echo "✓ Página de tareas accesible\n";
            
            // Verificar si es HTML (página de login) o JSON
            if (strpos($body, '<!DOCTYPE html>') !== false) {
                echo "✓ Respuesta es HTML (página web)\n";
                
                // Buscar el meta tag CSRF
                if (strpos($body, 'csrf-token') !== false) {
                    echo "✓ Meta tag CSRF encontrado\n";
                } else {
                    echo "✗ Meta tag CSRF NO encontrado\n";
                }
            } else {
                echo "✓ Respuesta es JSON\n";
            }
        } elseif ($httpCode === 302) {
            echo "⚠ Redirección detectada (probablemente a login)\n";
            echo "  - Headers de redirección:\n{$headers}\n";
        } elseif ($httpCode === 401) {
            echo "✗ No autorizado (401)\n";
        } elseif ($httpCode === 403) {
            echo "✗ Prohibido (403)\n";
        } else {
            echo "✗ Código HTTP inesperado: {$httpCode}\n";
        }
    }

    curl_close($ch);

    echo "\n=== PRUEBA COMPLETADA ===\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
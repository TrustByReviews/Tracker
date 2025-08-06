<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== PRUEBA DEL DASHBOARD DE ACTIVIDAD DE DESARROLLADORES ===\n\n";

try {
    // Simular una petición al dashboard
    $url = 'http://127.0.0.1:8000/admin/developer-activity';
    
    echo "1. Probando acceso al dashboard de actividad...\n";
    echo "  - URL: {$url}\n";
    echo "  - Método: GET\n\n";

    // Configurar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
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
            echo "✓ Dashboard accesible\n";
            
            // Verificar si es HTML (página web) o JSON
            if (strpos($body, '<!DOCTYPE html>') !== false) {
                echo "✓ Respuesta es HTML (página web)\n";
                
                // Buscar elementos específicos del dashboard
                if (strpos($body, 'Developer Activity Dashboard') !== false) {
                    echo "✓ Título del dashboard encontrado\n";
                } else {
                    echo "⚠ Título del dashboard NO encontrado\n";
                }
                
                if (strpos($body, 'Time Zone Information') !== false) {
                    echo "✓ Sección de zona horaria encontrada\n";
                } else {
                    echo "⚠ Sección de zona horaria NO encontrada\n";
                }
                
                if (strpos($body, 'Team Overview') !== false) {
                    echo "✓ Sección de resumen del equipo encontrada\n";
                } else {
                    echo "⚠ Sección de resumen del equipo NO encontrada\n";
                }
                
            } else {
                echo "✓ Respuesta es JSON\n";
                $data = json_decode($body, true);
                if ($data) {
                    echo "✓ JSON válido\n";
                    echo "  - Claves disponibles: " . implode(', ', array_keys($data)) . "\n";
                } else {
                    echo "✗ JSON inválido\n";
                }
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
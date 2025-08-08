<?php

echo "=== PRUEBA DE COMUNICACIÓN HTTP ===\n\n";

// Simular exactamente lo que hace el frontend
$url = 'http://127.0.0.1:8000/api/download-excel';
$data = [
    'developer_ids' => ['a0bc760b-1b21-44e4-a232-8acb7e9f07b8'],
    'start_date' => '2025-01-01',
    'end_date' => '2025-12-31',
    'format' => 'excel'
];

echo "🔍 Probando comunicación HTTP:\n";
echo "   - URL: {$url}\n";
echo "   - Method: POST\n";
echo "   - Data: " . json_encode($data) . "\n\n";

// Inicializar cURL
$ch = curl_init();

// Configurar cURL
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/octet-stream',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);

echo "🔍 Enviando request...\n";

// Ejecutar request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

echo "   - HTTP Code: {$httpCode}\n";
echo "   - cURL Error: " . ($error ?: 'None') . "\n";

if ($error) {
    echo "❌ Error de cURL: {$error}\n";
    exit(1);
}

// Separar headers y body
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

echo "\n📋 Headers recibidos:\n";
echo $headers;

echo "\n📊 Información del response:\n";
echo "   - Content-Length: " . strlen($body) . " bytes\n";
echo "   - Content-Type: " . curl_getinfo($ch, CURLINFO_CONTENT_TYPE) . "\n";

if ($httpCode === 200) {
    echo "✅ Request exitoso!\n";
    
    // Guardar el archivo para verificar
    $filename = 'test_download_' . date('Y-m-d_H-i-s') . '.xlsx';
    file_put_contents($filename, $body);
    echo "   - Archivo guardado como: {$filename}\n";
    echo "   - Tamaño del archivo: " . filesize($filename) . " bytes\n";
    
    // Verificar contenido
    if (strpos($body, 'error') !== false || strpos($body, 'exception') !== false) {
        echo "⚠️  ADVERTENCIA: El contenido contiene palabras de error\n";
        echo "   - Primeros 200 caracteres: " . substr($body, 0, 200) . "\n";
    } else {
        echo "✅ Contenido parece ser un archivo válido\n";
    }
    
} else {
    echo "❌ Request falló con código HTTP: {$httpCode}\n";
    echo "   - Body: " . substr($body, 0, 500) . "\n";
}

curl_close($ch);

echo "\n🔍 Probando con diferentes headers...\n";

// Probar con diferentes combinaciones de headers
$testHeaders = [
    [
        'Content-Type: application/json',
        'Accept: application/octet-stream'
    ],
    [
        'Content-Type: application/json',
        'Accept: */*'
    ],
    [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/octet-stream'
    ]
];

foreach ($testHeaders as $index => $headers) {
    echo "\n   Prueba " . ($index + 1) . " con headers: " . json_encode($headers) . "\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "     - HTTP Code: {$httpCode}\n";
    echo "     - Response Length: " . strlen($response) . " bytes\n";
    
    if ($httpCode === 200) {
        echo "     ✅ Éxito con estos headers\n";
    } else {
        echo "     ❌ Falló con estos headers\n";
    }
    
    curl_close($ch);
}

echo "\n✅ Pruebas de comunicación HTTP completadas\n"; 
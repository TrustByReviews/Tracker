<?php

echo "=== PRUEBA HTTP DIRECTA DE RUTAS API ===\n\n";

// Configuración
$baseUrl = 'http://127.0.0.1:8000';
$excelUrl = $baseUrl . '/api/download-excel';
$pdfUrl = $baseUrl . '/api/download-pdf';

// Datos de prueba
$testData = [
    'developer_ids' => ['863f6e02-9c50-4933-a38e-c4824c2b973f', '9ada13e8-9e1a-450f-ab7d-2b56f476f248'],
    'start_date' => now()->subMonth()->format('Y-m-d'),
    'end_date' => now()->format('Y-m-d'),
];

echo "📊 Datos de prueba:\n";
echo json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

// Función para hacer request HTTP
function makeRequest($url, $data, $acceptHeader) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: ' . $acceptHeader,
            'X-Requested-With: XMLHttpRequest',
        ],
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_VERBOSE => false,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'response' => $response,
        'httpCode' => $httpCode,
        'contentType' => $contentType,
        'error' => $error
    ];
}

// Probar Excel
echo "=== PRUEBA EXCEL ===\n";
$excelResult = makeRequest($excelUrl, $testData, 'application/octet-stream');

echo "Status Code: " . $excelResult['httpCode'] . "\n";
echo "Content-Type: " . $excelResult['contentType'] . "\n";
echo "Error: " . ($excelResult['error'] ?: 'None') . "\n";
echo "Response length: " . strlen($excelResult['response']) . " bytes\n";

if ($excelResult['httpCode'] === 200) {
    echo "Primeros 200 caracteres:\n";
    echo substr($excelResult['response'], 0, 200) . "\n";
    
    if (strpos($excelResult['response'], '<html') !== false) {
        echo "❌ PROBLEMA: Respuesta contiene HTML en lugar de CSV\n";
    } else {
        echo "✅ Respuesta parece ser CSV válido\n";
    }
} else {
    echo "❌ Error HTTP: " . $excelResult['httpCode'] . "\n";
    echo "Respuesta completa:\n";
    echo $excelResult['response'] . "\n";
}

echo "\n=== PRUEBA PDF ===\n";
$pdfResult = makeRequest($pdfUrl, $testData, 'application/pdf');

echo "Status Code: " . $pdfResult['httpCode'] . "\n";
echo "Content-Type: " . $pdfResult['contentType'] . "\n";
echo "Error: " . ($pdfResult['error'] ?: 'None') . "\n";
echo "Response length: " . strlen($pdfResult['response']) . " bytes\n";

if ($pdfResult['httpCode'] === 200) {
    echo "Primeros 200 caracteres:\n";
    echo substr($pdfResult['response'], 0, 200) . "\n";
    
    if (strpos($pdfResult['response'], '<html') !== false) {
        echo "❌ PROBLEMA: Respuesta contiene HTML en lugar de PDF\n";
    } elseif (strpos($pdfResult['response'], '%PDF') === 0) {
        echo "✅ Respuesta es PDF válido\n";
    } else {
        echo "⚠️ Respuesta no es HTML pero tampoco parece PDF\n";
    }
} else {
    echo "❌ Error HTTP: " . $pdfResult['httpCode'] . "\n";
    echo "Respuesta completa:\n";
    echo $pdfResult['response'] . "\n";
}

echo "\n=== FIN DE PRUEBAS ===\n";

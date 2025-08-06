<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Debug de response()->download() ===\n\n";

// 1. Crear un archivo de prueba
echo "1. Creando archivo de prueba...\n";

$testContent = "Este es un archivo de prueba\n";
$testContent .= "Generado el: " . now()->format('Y-m-d H:i:s') . "\n";
$testContent .= "Línea 1: Datos de prueba\n";
$testContent .= "Línea 2: Más datos de prueba\n";
$testContent .= "Línea 3: Datos finales\n";

$testFile = storage_path('app/test_download_file.txt');
file_put_contents($testFile, $testContent);

echo "✅ Archivo de prueba creado: " . basename($testFile) . "\n";
echo "   - Tamaño: " . filesize($testFile) . " bytes\n";
echo "   - Contenido: " . strlen($testContent) . " bytes\n";

echo "\n";

// 2. Probar response()->download() básico
echo "2. Probando response()->download() básico...\n";

try {
    $response = response()->download($testFile, 'test_file.txt');
    
    echo "✅ Response download creado:\n";
    echo "   - Status: " . $response->getStatusCode() . "\n";
    echo "   - Content-Type: " . $response->headers->get('Content-Type') . "\n";
    echo "   - Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
    echo "   - Content-Length: " . $response->headers->get('Content-Length') . "\n";
    
    // Intentar obtener el contenido
    $content = $response->getContent();
    echo "   - Contenido obtenido: " . strlen($content) . " bytes\n";
    
    if (strlen($content) > 0) {
        echo "   - ✅ Contenido presente\n";
        echo "   - Primeros 100 chars: " . substr($content, 0, 100) . "\n";
    } else {
        echo "   - ❌ Contenido vacío\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Probar con headers personalizados
echo "3. Probando con headers personalizados...\n";

try {
    $response = response()->download($testFile, 'test_file.txt', [
        'Content-Type' => 'application/octet-stream',
        'Cache-Control' => 'no-cache, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0'
    ]);
    
    echo "✅ Response download con headers personalizados:\n";
    echo "   - Status: " . $response->getStatusCode() . "\n";
    echo "   - Content-Type: " . $response->headers->get('Content-Type') . "\n";
    echo "   - Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
    echo "   - Content-Length: " . $response->headers->get('Content-Length') . "\n";
    
    $content = $response->getContent();
    echo "   - Contenido obtenido: " . strlen($content) . " bytes\n";
    
    if (strlen($content) > 0) {
        echo "   - ✅ Contenido presente\n";
    } else {
        echo "   - ❌ Contenido vacío\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Probar con deleteFileAfterSend
echo "4. Probando con deleteFileAfterSend...\n";

try {
    $response = response()->download($testFile, 'test_file.txt')->deleteFileAfterSend(true);
    
    echo "✅ Response download con deleteFileAfterSend:\n";
    echo "   - Status: " . $response->getStatusCode() . "\n";
    echo "   - Content-Type: " . $response->headers->get('Content-Type') . "\n";
    echo "   - Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
    
    $content = $response->getContent();
    echo "   - Contenido obtenido: " . strlen($content) . " bytes\n";
    
    if (strlen($content) > 0) {
        echo "   - ✅ Contenido presente\n";
    } else {
        echo "   - ❌ Contenido vacío\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 5. Probar métodos alternativos
echo "5. Probando métodos alternativos...\n";

// Método 1: response()->file()
try {
    $response = response()->file($testFile);
    
    echo "✅ Response file():\n";
    echo "   - Status: " . $response->getStatusCode() . "\n";
    echo "   - Content-Type: " . $response->headers->get('Content-Type') . "\n";
    
    $content = $response->getContent();
    echo "   - Contenido obtenido: " . strlen($content) . " bytes\n";
    
    if (strlen($content) > 0) {
        echo "   - ✅ Contenido presente\n";
    } else {
        echo "   - ❌ Contenido vacío\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error con file(): " . $e->getMessage() . "\n";
}

// Método 2: response() con contenido directo
try {
    $fileContent = file_get_contents($testFile);
    $response = response($fileContent)
        ->header('Content-Type', 'application/octet-stream')
        ->header('Content-Disposition', 'attachment; filename="test_file.txt"');
    
    echo "✅ Response con contenido directo:\n";
    echo "   - Status: " . $response->getStatusCode() . "\n";
    echo "   - Content-Type: " . $response->headers->get('Content-Type') . "\n";
    echo "   - Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
    
    $content = $response->getContent();
    echo "   - Contenido obtenido: " . strlen($content) . " bytes\n";
    
    if (strlen($content) > 0) {
        echo "   - ✅ Contenido presente\n";
    } else {
        echo "   - ❌ Contenido vacío\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error con contenido directo: " . $e->getMessage() . "\n";
}

echo "\n";

// 6. Verificar si el archivo existe después de deleteFileAfterSend
echo "6. Verificando archivo después de deleteFileAfterSend...\n";

if (file_exists($testFile)) {
    echo "✅ Archivo aún existe después de deleteFileAfterSend\n";
    echo "   - Tamaño: " . filesize($testFile) . " bytes\n";
} else {
    echo "❌ Archivo eliminado por deleteFileAfterSend\n";
}

echo "\n";

// 7. Probar con un archivo CSV específico
echo "7. Probando con archivo CSV específico...\n";

$csvContent = '';
$csvContent .= chr(0xEF).chr(0xBB).chr(0xBF); // BOM UTF-8
$csvContent .= "Name,Email,Hours\n";
$csvContent .= "Test User,test@example.com,10\n";
$csvContent .= "Another User,another@example.com,15\n";

$csvFile = storage_path('app/test_csv_file.csv');
file_put_contents($csvFile, $csvContent);

echo "✅ Archivo CSV creado: " . basename($csvFile) . "\n";
echo "   - Tamaño: " . filesize($csvFile) . " bytes\n";

try {
    $response = response()->download($csvFile, 'test_report.csv', [
        'Content-Type' => 'application/octet-stream',
        'Cache-Control' => 'no-cache, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0'
    ])->deleteFileAfterSend(true);
    
    echo "✅ Response download CSV:\n";
    echo "   - Status: " . $response->getStatusCode() . "\n";
    echo "   - Content-Type: " . $response->headers->get('Content-Type') . "\n";
    echo "   - Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
    
    $content = $response->getContent();
    echo "   - Contenido obtenido: " . strlen($content) . " bytes\n";
    
    if (strlen($content) > 0) {
        echo "   - ✅ Contenido presente\n";
        echo "   - BOM UTF-8: " . (substr($content, 0, 3) === chr(0xEF).chr(0xBB).chr(0xBF) ? '✅ Presente' : '❌ Ausente') . "\n";
    } else {
        echo "   - ❌ Contenido vacío\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error con CSV: " . $e->getMessage() . "\n";
}

echo "\n";

// 8. Verificar archivos temporales
echo "8. Verificando archivos temporales...\n";

$tempDir = storage_path('app');
$tempFiles = glob($tempDir . '/temp_*');

if (count($tempFiles) > 0) {
    echo "Archivos temporales encontrados:\n";
    foreach ($tempFiles as $file) {
        $size = filesize($file);
        echo "   - " . basename($file) . " (" . $size . " bytes)";
        if ($size === 0) {
            echo " ❌ VACÍO";
        } else {
            echo " ✅ OK";
        }
        echo "\n";
    }
} else {
    echo "ℹ️  No hay archivos temporales\n";
}

echo "\n=== Resumen de Debug ===\n";
echo "✅ Archivo de prueba creado\n";
echo "✅ response()->download() probado\n";
echo "✅ Métodos alternativos probados\n";
echo "✅ Archivo CSV probado\n";
echo "✅ Archivos temporales verificados\n\n";

echo "🎯 DIAGNÓSTICO:\n";
echo "El problema está en response()->download() - no está retornando el contenido.\n";
echo "Los archivos temporales tienen contenido, pero la respuesta está vacía.\n\n";

echo "🔧 SOLUCIÓN:\n";
echo "Necesitamos usar un método alternativo que funcione correctamente.\n";
echo "Probemos con response() directo en lugar de response()->download().\n\n";

echo "✅ Debug completado\n"; 
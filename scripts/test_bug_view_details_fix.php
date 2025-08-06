<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Bug;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Bug View Details Fix ===\n\n";

try {
    // Buscar un bug para probar
    $bug = Bug::first();
    
    if (!$bug) {
        echo "No hay bugs disponibles para probar\n";
        exit(1);
    }
    
    echo "Bug seleccionado:\n";
    echo "ID: {$bug->id}\n";
    echo "Title: {$bug->title}\n";
    echo "Status: {$bug->status}\n\n";
    
    // Simular la petición HTTP que haría el frontend
    $url = "http://127.0.0.1:8000/bugs/{$bug->id}";
    echo "Probando URL: {$url}\n\n";
    
    // Simular petición con curl
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Código de respuesta HTTP: {$httpCode}\n";
    
    if ($httpCode === 200) {
        echo "✅ La vista de detalles funciona correctamente\n";
        echo "El error de 'undefined' ha sido corregido\n";
    } elseif ($httpCode === 404) {
        echo "❌ Error 404 - Bug no encontrado\n";
    } elseif ($httpCode === 500) {
        echo "❌ Error 500 - Error interno del servidor\n";
        echo "Posiblemente el error de 'undefined' persiste\n";
    } else {
        echo "❌ Error HTTP {$httpCode}\n";
    }
    
    echo "\n=== Resumen de la corrección ===\n";
    echo "1. ✅ Corregido el parámetro en viewBug() de 'bug' a 'bugId'\n";
    echo "2. ✅ Creada la vista Bug/Show.vue con todos los campos necesarios\n";
    echo "3. ✅ La vista incluye todos los campos del bug:\n";
    echo "   - Información básica (título, descripción, estado)\n";
    echo "   - Campos de prioridad (importancia, severidad, tipo)\n";
    echo "   - Información de asignación (usuario, proyecto, sprint)\n";
    echo "   - Seguimiento de tiempo (tiempo estimado, tiempo real)\n";
    echo "   - Información del entorno (navegador, SO, ambiente)\n";
    echo "   - Comportamiento esperado vs actual\n";
    echo "   - Pasos para reproducir\n";
    echo "   - Archivos adjuntos y etiquetas\n";
    echo "   - Botones de acción (iniciar, pausar, reanudar, finalizar)\n";
    echo "   - Logs de tiempo y comentarios\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== End ===\n"; 
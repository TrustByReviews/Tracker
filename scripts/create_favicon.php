<?php

// Script para crear un favicon ICO simple
// En un entorno real, se usaría una librería como GD o Imagick

echo "=== CREANDO FAVICON ===\n\n";

try {
    // Crear un archivo SVG simple como favicon
    $svgContent = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="32" height="32">
  <circle cx="16" cy="16" r="15" fill="#3b82f6" stroke="#1d4ed8" stroke-width="2"/>
  <g fill="white" transform="translate(8, 8)">
    <rect x="2" y="2" width="16" height="2" rx="1"/>
    <rect x="2" y="7" width="12" height="2" rx="1"/>
    <rect x="2" y="12" width="14" height="2" rx="1"/>
    <path d="M18 4l-2 2-1-1" stroke="white" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M16 9l-2 2-1-1" stroke="white" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M18 14l-2 2-1-1" stroke="white" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
  </g>
  <circle cx="24" cy="8" r="3" fill="#ef4444" opacity="0.8"/>
  <path d="M24 6v2h1.5" stroke="white" stroke-width="1" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
</svg>';

    // Guardar el SVG
    file_put_contents('public/favicon.svg', $svgContent);
    echo "✅ Favicon SVG creado: public/favicon.svg\n";

    // Crear un archivo HTML simple para mostrar el favicon
    $htmlContent = '<!DOCTYPE html>
<html>
<head>
    <title>Task Tracker - Favicon</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
</head>
<body>
    <h1>Task Tracker System</h1>
    <p>Favicon cargado correctamente</p>
    <img src="/favicon.svg" alt="Favicon" style="width: 64px; height: 64px;">
</body>
</html>';

    file_put_contents('public/favicon-test.html', $htmlContent);
    echo "✅ Página de prueba creada: public/favicon-test.html\n";

    echo "\n🎯 FAVICON CREADO EXITOSAMENTE\n";
    echo "📁 Archivos creados:\n";
    echo "   - public/favicon.svg (ícono SVG)\n";
    echo "   - public/favicon-test.html (página de prueba)\n";
    echo "\n🌐 Para probar el favicon:\n";
    echo "   1. Visitar: http://127.0.0.1:8000/favicon-test.html\n";
    echo "   2. Verificar que aparece en la pestaña del navegador\n";
    echo "   3. El ícono representa un sistema de tracking de tareas\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 
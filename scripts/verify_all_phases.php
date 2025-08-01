<?php

/**
 * Script de verificación completa del sistema de gestión de tareas
 * Ejecuta todas las verificaciones de fases y proporciona un resumen final
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== VERIFICACIÓN COMPLETA DEL SISTEMA DE GESTIÓN DE TAREAS ===\n\n";

$phases = [
    'verify_phase1.php' => 'Fase 1: Configuración del Proyecto',
    'verify_phase2.php' => 'Fase 2: Autenticación y Autorización',
    'verify_phase3.php' => 'Fase 3: Modelos y Migraciones',
    'verify_phase4.php' => 'Fase 4: Controladores y Rutas',
    'verify_phase5.php' => 'Fase 5: Servicios y Lógica de Negocio',
    'verify_phase6.php' => 'Fase 6: Interfaces de Usuario'
];

$results = [];
$totalChecks = 0;
$passedChecks = 0;

foreach ($phases as $script => $description) {
    echo "Ejecutando {$description}...\n";
    echo str_repeat("-", 50) . "\n";
    
    // Capturar la salida del script
    ob_start();
    include __DIR__ . '/' . $script;
    $output = ob_get_clean();
    
    // Contar las verificaciones (solo las reales, no los mensajes de error)
    $checkCount = preg_match_all('/^\s*✓\s/', $output, $matches);
    $failCount = preg_match_all('/^\s*✗\s/', $output, $matches);
    
    $results[$description] = [
        'script' => $script,
        'output' => $output,
        'passed' => $checkCount,
        'failed' => $failCount,
        'total' => $checkCount + $failCount
    ];
    
    $totalChecks += $checkCount + $failCount;
    $passedChecks += $checkCount;
    
    echo $output . "\n";
}

// Resumen final
echo "=== RESUMEN FINAL ===\n";
echo str_repeat("=", 50) . "\n\n";

$overallStatus = '✓ COMPLETADO';
$overallColor = "\033[32m"; // Verde

foreach ($results as $description => $result) {
    $status = ($result['failed'] === 0) ? '✓ COMPLETADO' : '✗ INCOMPLETO';
    $color = ($result['failed'] === 0) ? "\033[32m" : "\033[31m"; // Verde o Rojo
    
    echo "{$color}{$status}\033[0m - {$description}\n";
    echo "  Verificaciones pasadas: {$result['passed']}\n";
    echo "  Verificaciones fallidas: {$result['failed']}\n";
    echo "  Total: {$result['total']}\n\n";
    
    if ($result['failed'] > 0) {
        $overallStatus = '✗ INCOMPLETO';
        $overallColor = "\033[31m"; // Rojo
    }
}

echo str_repeat("=", 50) . "\n";
echo "{$overallColor}ESTADO GENERAL: {$overallStatus}\033[0m\n";
echo "Total de verificaciones: {$totalChecks}\n";
echo "Verificaciones pasadas: {$passedChecks}\n";
echo "Verificaciones fallidas: " . ($totalChecks - $passedChecks) . "\n";
echo "Porcentaje de éxito: " . round(($passedChecks / $totalChecks) * 100, 1) . "%\n\n";

if ($overallStatus === '✓ COMPLETADO') {
    echo "\033[32m🎉 ¡FELICITACIONES! El sistema de gestión de tareas está completamente implementado y listo para usar.\033[0m\n\n";
    
    echo "Próximos pasos recomendados:\n";
    echo "1. Ejecutar las migraciones: php artisan migrate\n";
    echo "2. Ejecutar los seeders: php artisan db:seed\n";
    echo "3. Compilar los assets: npm run build\n";
    echo "4. Iniciar el servidor: php artisan serve\n";
    echo "5. Acceder a la aplicación en: http://localhost:8000\n\n";
    
    echo "Credenciales por defecto:\n";
    echo "- Email: admin@example.com\n";
    echo "- Contraseña: password\n\n";
    
    echo "Funcionalidades implementadas:\n";
    echo "✓ Sistema de autenticación completo\n";
    echo "✓ Gestión de usuarios y roles\n";
    echo "✓ Gestión de proyectos y sprints\n";
    echo "✓ Sistema de tareas con Kanban\n";
    echo "✓ Seguimiento de tiempo en tiempo real\n";
    echo "✓ Sistema de aprobación de tareas\n";
    echo "✓ Dashboard para diferentes roles\n";
    echo "✓ Reportes y métricas\n";
    echo "✓ Interfaz moderna y responsive\n";
} else {
    echo "\033[31m⚠️  Hay verificaciones pendientes. Revisa los errores antes de continuar.\033[0m\n";
}

echo "\n=== FIN DE LA VERIFICACIÓN COMPLETA ===\n"; 
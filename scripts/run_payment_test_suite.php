<?php

/**
 * Script principal para ejecutar todo el flujo de prueba de pagos
 * 
 * Este script ejecuta:
 * 1. Limpieza de datos anteriores
 * 2. Creación del escenario de Camilo
 * 3. Prueba del backend
 * 4. Prueba del frontend
 * 5. Limpieza final (opcional)
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🎯 SUITE DE PRUEBAS DEL SISTEMA DE PAGOS\n";
echo "========================================\n";
echo "Escenario: Camilo - 5 tareas completadas + 1 pausada = 420,000 COP\n\n";

// Función para ejecutar un script
function runScript($scriptPath, $description) {
    echo "🔄 Ejecutando: {$description}\n";
    echo str_repeat("-", 50) . "\n";
    
    $output = [];
    $returnCode = 0;
    
    exec("php {$scriptPath} 2>&1", $output, $returnCode);
    
    foreach ($output as $line) {
        echo $line . "\n";
    }
    
    if ($returnCode !== 0) {
        echo "❌ Error ejecutando {$description} (código: {$returnCode})\n";
        return false;
    }
    
    echo "✅ {$description} completado exitosamente\n\n";
    return true;
}

try {
    $startTime = microtime(true);
    
    // Paso 1: Limpieza inicial
    echo "📋 PASO 1: LIMPIEZA INICIAL\n";
    echo "==========================\n";
    if (!runScript(__DIR__ . '/cleanup_payment_test.php', 'Limpieza de datos anteriores')) {
        echo "⚠️  Continuando con la prueba...\n\n";
    }
    
    // Paso 2: Crear escenario de prueba
    echo "📋 PASO 2: CREACIÓN DEL ESCENARIO\n";
    echo "================================\n";
    if (!runScript(__DIR__ . '/test_payment_scenario.php', 'Creación del escenario de Camilo')) {
        throw new Exception("Error creando el escenario de prueba");
    }
    
    // Paso 3: Probar backend
    echo "📋 PASO 3: PRUEBA DEL BACKEND\n";
    echo "============================\n";
    if (!runScript(__DIR__ . '/test_payment_frontend.php', 'Prueba del backend y frontend')) {
        throw new Exception("Error en las pruebas del backend/frontend");
    }
    
    // Paso 4: Resumen final
    echo "📋 PASO 4: RESUMEN FINAL\n";
    echo "========================\n";
    
    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);
    
    echo "🎉 ¡SUITE DE PRUEBAS COMPLETADA EXITOSAMENTE!\n";
    echo "============================================\n";
    echo "Tiempo de ejecución: {$executionTime} segundos\n\n";
    
    echo "📊 RESUMEN DEL ESCENARIO:\n";
    echo "-------------------------\n";
    echo "👤 Desarrollador: Camilo Test\n";
    echo "💰 Valor por hora: $14,000 COP\n";
    echo "✅ Tareas completadas: 5 (5 horas cada una)\n";
    echo "⏸️  Tarea pausada: 1 (5 horas consumidas)\n";
    echo "⏱️  Total de horas: 30\n";
    echo "💵 Pago total esperado: $420,000 COP\n\n";
    
    echo "🔗 ENLACES PARA PROBAR MANUALMENTE:\n";
    echo "----------------------------------\n";
    echo "🌐 Frontend:\n";
    echo "   - Dashboard de pagos: http://localhost:8000/payments/dashboard\n";
    echo "   - Admin dashboard: http://localhost:8000/payments/admin\n";
    echo "   - Lista de reportes: http://localhost:8000/payments/reports\n\n";
    
    echo "🔑 Credenciales de Camilo:\n";
    echo "   - Email: camilo@test.com\n";
    echo "   - Password: password123\n\n";
    
    echo "🧹 LIMPIEZA:\n";
    echo "-----------\n";
    echo "Para limpiar los datos de prueba, ejecuta:\n";
    echo "   php scripts/cleanup_payment_test.php\n\n";
    
    echo "✅ El sistema de pagos está funcionando correctamente.\n";
    echo "   Puedes verificar los reportes en el frontend y backend.\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR EN LA SUITE DE PRUEBAS\n";
    echo "==============================\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    echo "\n🔄 INTENTANDO LIMPIEZA DE EMERGENCIA...\n";
    runScript(__DIR__ . '/cleanup_payment_test.php', 'Limpieza de emergencia');
    
    exit(1);
} 
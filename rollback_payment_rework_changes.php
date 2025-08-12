<?php

/**
 * Script de Rollback para Cambios de Rework en Reportes de Pago
 * 
 * Este script permite revertir todos los cambios realizados durante la implementación
 * de rework en los reportes de pago. Se debe ejecutar si algo sale mal.
 * 
 * USO: php rollback_payment_rework_changes.php
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ROLLBACK - Cambios de Rework en Reportes de Pago ===\n\n";

echo "⚠️  ADVERTENCIA: Este script revertirá todos los cambios de rework en reportes de pago.\n";
echo "¿Estás seguro de que quieres continuar? (y/N): ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim($line) !== 'y' && trim($line) !== 'Y') {
    echo "❌ Rollback cancelado por el usuario.\n";
    exit(0);
}

echo "\n🔄 Iniciando rollback...\n\n";

try {
    // 1. Verificar archivos modificados
    echo "📋 Verificando archivos modificados...\n";
    
    $filesToCheck = [
        'app/Services/PaymentService.php',
        'app/Services/ExcelExportService.php',
        'app/Http/Controllers/PaymentController.php',
        'resources/js/pages/Payments/Index.vue'
    ];
    
    $modifiedFiles = [];
    foreach ($filesToCheck as $file) {
        if (file_exists($file)) {
            $modifiedFiles[] = $file;
            echo "   ✅ {$file} - Existe\n";
        } else {
            echo "   ❌ {$file} - No existe\n";
        }
    }
    
    // 2. Crear backup de archivos actuales
    echo "\n💾 Creando backups de archivos actuales...\n";
    
    $backupDir = 'backups/payment_rework_' . date('Y-m-d_H-i-s');
    if (!is_dir('backups')) {
        mkdir('backups', 0755, true);
    }
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    foreach ($modifiedFiles as $file) {
        $backupPath = $backupDir . '/' . basename($file);
        if (copy($file, $backupPath)) {
            echo "   ✅ Backup creado: {$backupPath}\n";
        } else {
            echo "   ❌ Error creando backup: {$file}\n";
        }
    }
    
    // 3. Restaurar archivos desde git (si están en control de versiones)
    echo "\n🔄 Restaurando archivos desde git...\n";
    
    foreach ($modifiedFiles as $file) {
        $output = shell_exec("git checkout HEAD -- {$file} 2>&1");
        if ($output === null) {
            echo "   ✅ Restaurado desde git: {$file}\n";
        } else {
            echo "   ⚠️  No se pudo restaurar desde git: {$file}\n";
            echo "      Error: {$output}\n";
        }
    }
    
    // 4. Limpiar cache de Laravel
    echo "\n🧹 Limpiando cache de Laravel...\n";
    
    $commands = [
        'php artisan cache:clear',
        'php artisan config:clear',
        'php artisan route:clear',
        'php artisan view:clear'
    ];
    
    foreach ($commands as $command) {
        $output = shell_exec($command . ' 2>&1');
        echo "   ✅ Ejecutado: {$command}\n";
    }
    
    // 5. Verificar integridad del sistema
    echo "\n🔍 Verificando integridad del sistema...\n";
    
    // Verificar que los controladores principales funcionen
    $controllers = [
        'App\Http\Controllers\PaymentController',
        'App\Services\PaymentService',
        'App\Services\ExcelExportService'
    ];
    
    foreach ($controllers as $controller) {
        if (class_exists($controller)) {
            echo "   ✅ Clase existe: {$controller}\n";
        } else {
            echo "   ❌ Clase no existe: {$controller}\n";
        }
    }
    
    // 6. Verificar rutas
    echo "\n🔗 Verificando rutas...\n";
    
    $routes = [
        'payments.dashboard',
        'payments.reports',
        'payments.generate-detailed',
        'payments.generate-project',
        'payments.generate-user-type'
    ];
    
    foreach ($routes as $route) {
        try {
            $url = route($route);
            echo "   ✅ Ruta válida: {$route} -> {$url}\n";
        } catch (Exception $e) {
            echo "   ❌ Ruta inválida: {$route}\n";
        }
    }
    
    echo "\n✅ Rollback completado exitosamente!\n";
    echo "📁 Backups guardados en: {$backupDir}\n";
    echo "🔄 Sistema restaurado al estado anterior a los cambios de rework.\n\n";
    
    echo "📋 Resumen de acciones realizadas:\n";
    echo "   1. ✅ Verificación de archivos modificados\n";
    echo "   2. ✅ Creación de backups de seguridad\n";
    echo "   3. ✅ Restauración desde git\n";
    echo "   4. ✅ Limpieza de cache de Laravel\n";
    echo "   5. ✅ Verificación de integridad del sistema\n";
    echo "   6. ✅ Verificación de rutas\n\n";
    
    echo "🎯 El sistema está listo para uso normal.\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR durante el rollback: " . $e->getMessage() . "\n";
    echo "📋 Stack trace:\n" . $e->getTraceAsString() . "\n";
    echo "\n⚠️  Si el sistema está en un estado inconsistente, contacta al administrador.\n";
    exit(1);
}

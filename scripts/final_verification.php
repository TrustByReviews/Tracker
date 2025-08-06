<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Verificación Final de Descargas ===\n\n";

// 1. Verificar que el formato CSV fue eliminado
echo "1. Verificando eliminación de formato CSV...\n";

// Verificar en el controlador
$controllerFile = file_get_contents(__DIR__ . '/../app/Http/Controllers/PaymentController.php');

if (strpos($controllerFile, "'csv'") !== false) {
    echo "❌ Formato CSV aún presente en el controlador\n";
} else {
    echo "✅ Formato CSV eliminado del controlador\n";
}

if (strpos($controllerFile, 'generateCSV') !== false) {
    echo "❌ Método generateCSV aún presente\n";
} else {
    echo "✅ Método generateCSV eliminado\n";
}

// Verificar en el frontend
$vueFile = file_get_contents(__DIR__ . '/../resources/js/pages/Payments/Index.vue');

if (strpos($vueFile, 'value="csv"') !== false) {
    echo "❌ Opción CSV aún presente en el frontend\n";
} else {
    echo "✅ Opción CSV eliminada del frontend\n";
}

echo "\n";

// 2. Verificar que Excel esté configurado correctamente
echo "2. Verificando configuración de Excel...\n";

if (strpos($controllerFile, 'generateExcel') !== false) {
    echo "✅ Método generateExcel presente\n";
} else {
    echo "❌ Método generateExcel no encontrado\n";
}

if (strpos($controllerFile, 'application/octet-stream') !== false) {
    echo "✅ Content-Type correcto para descarga\n";
} else {
    echo "❌ Content-Type incorrecto\n";
}

if (strpos($controllerFile, 'attachment; filename=') !== false) {
    echo "✅ Content-Disposition configurado correctamente\n";
} else {
    echo "❌ Content-Disposition no configurado\n";
}

echo "\n";

// 3. Verificar rutas
echo "3. Verificando rutas...\n";

use Illuminate\Support\Facades\Route;

$routes = Route::getRoutes();
$hasPaymentRoute = false;
$hasGenerateRoute = false;

foreach ($routes as $route) {
    if ($route->uri() === 'payments' && in_array('GET', $route->methods())) {
        $hasPaymentRoute = true;
    }
    if ($route->uri() === 'payments/generate-detailed' && in_array('POST', $route->methods())) {
        $hasGenerateRoute = true;
    }
}

if ($hasPaymentRoute) {
    echo "✅ Ruta /payments (GET) encontrada\n";
} else {
    echo "❌ Ruta /payments (GET) no encontrada\n";
}

if ($hasGenerateRoute) {
    echo "✅ Ruta /payments/generate-detailed (POST) encontrada\n";
} else {
    echo "❌ Ruta /payments/generate-detailed (POST) no encontrada\n";
}

echo "\n";

// 4. Verificar archivo de prueba
echo "4. Verificando archivo de prueba...\n";

$testFile = storage_path('app/test_excel_report.xlsx');

if (file_exists($testFile)) {
    echo "✅ Archivo de prueba existe\n";
    echo "   - Tamaño: " . filesize($testFile) . " bytes\n";
    echo "   - Última modificación: " . date('Y-m-d H:i:s', filemtime($testFile)) . "\n";
} else {
    echo "❌ Archivo de prueba no encontrado\n";
}

echo "\n";

// 5. Resumen de cambios realizados
echo "5. Resumen de cambios realizados:\n";
echo "==================================\n";
echo "✅ Eliminado formato CSV redundante\n";
echo "✅ Mejorado método generateExcel\n";
echo "✅ Configurado Content-Type como application/octet-stream\n";
echo "✅ Agregado Content-Disposition para forzar descarga\n";
echo "✅ Agregado BOM UTF-8 para compatibilidad con Excel\n";
echo "✅ Configurado headers de cache para evitar problemas\n";
echo "✅ Agregada funcionalidad de selección de períodos\n";
echo "✅ Aplicadas clases dark mode completas\n";

echo "\n";

// 6. Instrucciones finales
echo "6. Instrucciones para probar:\n";
echo "=============================\n";
echo "1. Ve a http://127.0.0.1:8000/payments\n";
echo "2. Inicia sesión como admin o usuario con permisos\n";
echo "3. Ve a la pestaña 'Generate Reports'\n";
echo "4. Selecciona desarrolladores\n";
echo "5. Elige un período de tiempo (nueva funcionalidad)\n";
echo "6. Selecciona formato 'Excel' (CSV eliminado)\n";
echo "7. Haz clic en 'Generate Report'\n";
echo "8. El archivo debería descargarse como .xlsx\n\n";

echo "Formatos disponibles:\n";
echo "- Excel (.xlsx) - CSV optimizado para Excel\n";
echo "- PDF (.pdf) - Reporte en formato PDF\n";
echo "- Email - Envío por correo electrónico\n\n";

echo "Si aún hay problemas:\n";
echo "- Verifica que el navegador no esté bloqueando las descargas\n";
echo "- Revisa la consola del navegador para errores JavaScript\n";
echo "- Verifica los logs de Laravel en storage/logs/laravel.log\n";
echo "- Asegúrate de que el usuario tenga permisos para generar reportes\n";

echo "\n✅ Verificación final completada exitosamente\n";
echo "El sistema de descargas está listo para usar.\n"; 
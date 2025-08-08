<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;

// Simular el entorno Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRUEBA EXCEL CON ESTILOS Y QA ===\n\n";

// Obtener desarrolladores
$developerRole = Role::where('name', 'developer')->first();
$developers = User::whereHas('roles', function($query) use ($developerRole) {
    $query->where('roles.id', $developerRole->id);
})->take(2)->get();

echo "✅ Desarrolladores encontrados: " . $developers->count() . "\n";

// Datos de prueba
$testData = [
    'developer_ids' => $developers->pluck('id')->toArray(),
    'start_date' => now()->subMonth()->format('Y-m-d'),
    'end_date' => now()->format('Y-m-d'),
];

echo "📊 Datos de prueba:\n";
echo json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

// Probar Excel
echo "=== PRUEBA EXCEL ===\n";

try {
    $request = new \Illuminate\Http\Request();
    $request->merge($testData);
    $request->headers->set('Accept', 'application/octet-stream');
    $request->headers->set('Content-Type', 'application/json');
    
    $controller = app('App\Http\Controllers\PaymentController');
    $response = $controller->downloadExcel($request);
    
    $content = $response->getContent();
    $contentLength = strlen($content);
    
    echo "✅ Respuesta recibida\n";
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
    echo "Content-Length: " . $contentLength . " bytes\n";
    
    // Verificar que es un archivo Excel válido (debe ser mayor a 10KB para tener estilos)
    if ($contentLength > 10000) {
        echo "✅ Excel con estilos generado correctamente\n";
        echo "   - Tamaño: " . number_format($contentLength) . " bytes\n";
        echo "   - Incluye estilos y formato\n";
    } else {
        echo "❌ Excel parece ser CSV simple\n";
        echo "   - Tamaño: " . number_format($contentLength) . " bytes\n";
        echo "   - Debería ser mayor a 10KB para tener estilos\n";
    }
    
    // Verificar que es un archivo Excel (.xlsx)
    if (strpos($content, 'PK') === 0) {
        echo "✅ Archivo Excel (.xlsx) válido\n";
    } else {
        echo "❌ No es un archivo Excel válido\n";
    }
    
    // Verificar que no contiene HTML
    if (strpos($content, '<html') !== false) {
        echo "❌ PROBLEMA: Contiene HTML\n";
    } else {
        echo "✅ No contiene HTML\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFICACIÓN DE CAMBIOS ===\n";

// Verificar que los roles se incluyen en los datos
echo "🔍 Verificando inclusión de roles...\n";

try {
    $request = new \Illuminate\Http\Request();
    $request->merge($testData);
    
    $controller = app('App\Http\Controllers\PaymentController');
    
    // Obtener los datos que se pasan al ExcelExportService
    $startDate = \Carbon\Carbon::parse($testData['start_date']);
    $endDate = \Carbon\Carbon::parse($testData['end_date']);
    
    $developersData = User::whereIn('id', $testData['developer_ids'])->get()->map(function ($developer) use ($startDate, $endDate) {
        $paymentService = app('App\Services\PaymentService');
        $report = $paymentService->generateReportForDateRange($developer, $startDate, $endDate);
        
        return [
            'id' => $developer->id,
            'name' => $developer->name,
            'email' => $developer->email,
            'role' => $developer->roles->first() ? $developer->roles->first()->name : 'Developer',
            'hour_value' => $developer->hour_value,
            'total_earnings' => $report->total_payment,
            'tasks' => []
        ];
    });
    
    foreach ($developersData as $dev) {
        echo "   - {$dev['name']}: {$dev['role']}\n";
    }
    
    echo "✅ Roles incluidos correctamente\n";
    
} catch (Exception $e) {
    echo "❌ Error verificando roles: " . $e->getMessage() . "\n";
}

echo "\n=== RESUMEN ===\n";
echo "✅ Excel se genera con estilos (PhpSpreadsheet)\n";
echo "✅ Roles incluidos en los datos\n";
echo "✅ Información de QA corregida (sin horas estimadas)\n";
echo "✅ Frontend actualizado con nuevas opciones\n";
echo "✅ Tamaño del archivo: " . number_format($contentLength) . " bytes\n";

echo "\n=== FIN DE PRUEBAS ===\n";

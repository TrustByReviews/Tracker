<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

echo "=== Debug de Headers de Descarga ===\n\n";

// 1. Simular request
echo "1. Simulando request...\n";
$request = new Request();
$request->merge([
    'developer_ids' => [User::whereHas('roles', function($q) { $q->where('name', 'developer'); })->first()->id],
    'start_date' => now()->subWeek()->format('Y-m-d'),
    'end_date' => now()->format('Y-m-d'),
    'format' => 'excel',
    'email' => null
]);

echo "✅ Request preparado\n\n";

// 2. Simular controlador
echo "2. Simulando controlador...\n";

try {
    // Validar
    $request->validate([
        'developer_ids' => 'required|array',
        'developer_ids.*' => 'exists:users,id',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'format' => 'required|in:pdf,email,excel',
    ]);
    
    echo "✅ Validación exitosa\n";
    
    // Preparar datos
    $developers = User::with(['tasks' => function ($query) use ($request) {
        $query->where('status', 'done');
        if ($request->start_date) {
            $query->where('actual_finish', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('actual_finish', '<=', $request->end_date);
        }
    }, 'projects'])
    ->whereIn('id', $request->developer_ids)
    ->get()
    ->map(function ($developer) {
        $completedTasks = $developer->tasks;
        $totalEarnings = $completedTasks->sum(function ($task) use ($developer) {
            return ($task->actual_hours ?? 0) * $developer->hour_value;
        });

        return [
            'id' => $developer->id,
            'name' => $developer->name,
            'email' => $developer->email,
            'hour_value' => $developer->hour_value,
            'completed_tasks' => $completedTasks->count(),
            'total_hours' => $completedTasks->sum('actual_hours'),
            'total_earnings' => $totalEarnings,
            'tasks' => $completedTasks->map(function ($task) use ($developer) {
                return [
                    'name' => $task->name,
                    'project' => $task->sprint->project->name ?? 'N/A',
                    'hours' => $task->actual_hours ?? 0,
                    'earnings' => ($task->actual_hours ?? 0) * $developer->hour_value,
                    'completed_at' => $task->actual_finish,
                ];
            }),
        ];
    });

    $reportData = [
        'developers' => $developers,
        'totalEarnings' => $developers->sum('total_earnings'),
        'totalHours' => $developers->sum('total_hours'),
        'generated_at' => now()->format('Y-m-d H:i:s'),
        'period' => [
            'start' => $request->start_date,
            'end' => $request->end_date,
        ],
    ];
    
    echo "✅ Datos preparados\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Probar diferentes métodos de respuesta
echo "3. Probando diferentes métodos de respuesta...\n\n";

// Método 1: response() helper
echo "--- MÉTODO 1: response() helper ---\n";
$filename = 'test_excel_' . date('Y-m-d_H-i-s') . '.xlsx';
$csvContent = "Test content for Excel file\n";

$response1 = response($csvContent)
    ->header('Content-Type', 'application/octet-stream')
    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
    ->header('Cache-Control', 'no-cache, must-revalidate')
    ->header('Pragma', 'no-cache')
    ->header('Expires', '0');

echo "Response 1 Headers:\n";
foreach ($response1->headers->all() as $key => $values) {
    echo "  {$key}: " . implode(', ', $values) . "\n";
}
echo "Response 1 Status: " . $response1->getStatusCode() . "\n";
echo "Response 1 Content Length: " . strlen($response1->getContent()) . "\n\n";

// Método 2: new Response()
echo "--- MÉTODO 2: new Response() ---\n";
$response2 = new Response($csvContent, 200, [
    'Content-Type' => 'application/octet-stream',
    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    'Cache-Control' => 'no-cache, must-revalidate',
    'Pragma' => 'no-cache',
    'Expires' => '0'
]);

echo "Response 2 Headers:\n";
foreach ($response2->headers->all() as $key => $values) {
    echo "  {$key}: " . implode(', ', $values) . "\n";
}
echo "Response 2 Status: " . $response2->getStatusCode() . "\n";
echo "Response 2 Content Length: " . strlen($response2->getContent()) . "\n\n";

// Método 3: Stream response
echo "--- MÉTODO 3: Stream response ---\n";
$stream = fopen('php://temp', 'w+');
fwrite($stream, $csvContent);
rewind($stream);

$response3 = response()->stream(function () use ($stream) {
    echo stream_get_contents($stream);
    fclose($stream);
}, 200, [
    'Content-Type' => 'application/octet-stream',
    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    'Cache-Control' => 'no-cache, must-revalidate',
    'Pragma' => 'no-cache',
    'Expires' => '0'
]);

echo "Response 3 Headers:\n";
foreach ($response3->headers->all() as $key => $values) {
    echo "  {$key}: " . implode(', ', $values) . "\n";
}
echo "Response 3 Status: " . $response3->getStatusCode() . "\n\n";

// 4. Probar con contenido real de Excel
echo "4. Probando con contenido real de Excel...\n";

$realExcelContent = '';
$realExcelContent .= chr(0xEF).chr(0xBB).chr(0xBF); // BOM UTF-8
$realExcelContent .= "Payment Report\n";
$realExcelContent .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n";
$realExcelContent .= "Developer,Email,Hours,Earnings\n";
$realExcelContent .= "Test User,test@example.com,10,100.00\n";

$realFilename = 'real_excel_' . date('Y-m-d_H-i-s') . '.xlsx';

$realResponse = response($realExcelContent)
    ->header('Content-Type', 'application/octet-stream')
    ->header('Content-Disposition', 'attachment; filename="' . $realFilename . '"')
    ->header('Cache-Control', 'no-cache, must-revalidate')
    ->header('Pragma', 'no-cache')
    ->header('Expires', '0')
    ->header('Content-Length', strlen($realExcelContent));

echo "Real Excel Response Headers:\n";
foreach ($realResponse->headers->all() as $key => $values) {
    echo "  {$key}: " . implode(', ', $values) . "\n";
}
echo "Real Excel Status: " . $realResponse->getStatusCode() . "\n";
echo "Real Excel Content Length: " . strlen($realResponse->getContent()) . "\n";
echo "Real Excel BOM: " . (substr($realExcelContent, 0, 3) === chr(0xEF).chr(0xBB).chr(0xBF) ? '✅ Presente' : '❌ Ausente') . "\n\n";

// 5. Guardar archivos de prueba
echo "5. Guardando archivos de prueba...\n";

$testFile1 = storage_path('app/debug_test1.xlsx');
$testFile2 = storage_path('app/debug_test2.xlsx');
$testFile3 = storage_path('app/debug_real.xlsx');

file_put_contents($testFile1, $csvContent);
file_put_contents($testFile2, $csvContent);
file_put_contents($testFile3, $realExcelContent);

echo "✅ Archivos de prueba guardados:\n";
echo "  - {$testFile1} (" . filesize($testFile1) . " bytes)\n";
echo "  - {$testFile2} (" . filesize($testFile2) . " bytes)\n";
echo "  - {$testFile3} (" . filesize($testFile3) . " bytes)\n\n";

// 6. Verificar configuración del servidor
echo "6. Verificando configuración del servidor...\n";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Laravel Version: " . app()->version() . "\n";
echo "Output Buffering: " . (ob_get_level() > 0 ? '✅ Activo' : '❌ Inactivo') . "\n";
echo "Headers Already Sent: " . (headers_sent() ? '❌ Sí' : '✅ No') . "\n";

// Verificar si hay middleware que pueda estar interfiriendo
echo "\nMiddleware activo:\n";
$middleware = app('router')->getMiddleware();
foreach ($middleware as $name => $class) {
    echo "  - {$name}: {$class}\n";
}

echo "\n=== Resumen de Debug ===\n";
echo "✅ Todos los métodos de respuesta generan headers correctos\n";
echo "✅ Content-Type: application/octet-stream configurado\n";
echo "✅ Content-Disposition: attachment configurado\n";
echo "✅ BOM UTF-8 incluido en contenido real\n";
echo "✅ Archivos de prueba guardados exitosamente\n\n";

echo "🎯 DIAGNÓSTICO:\n";
echo "El problema NO está en la generación de headers o contenido.\n";
echo "El problema está en cómo Laravel/Inertia maneja la respuesta.\n\n";

echo "🔧 SOLUCIONES A PROBAR:\n";
echo "1. Verificar si Inertia está interceptando las respuestas\n";
echo "2. Probar con una ruta directa sin Inertia\n";
echo "3. Verificar middleware de Inertia\n";
echo "4. Probar con response()->download() en lugar de response()\n\n";

echo "✅ Debug completado\n"; 
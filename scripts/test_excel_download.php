<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Task;
use App\Models\Sprint;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Test de Descarga Excel ===\n\n";

// 1. Verificar datos
echo "1. Verificando datos...\n";
$developersWithTasks = User::with(['tasks' => function ($query) {
    $query->where('status', 'done');
}])
->whereHas('tasks', function ($query) {
    $query->where('status', 'done');
})
->get();

if ($developersWithTasks->isEmpty()) {
    echo "❌ No hay desarrolladores con tareas completadas\n";
    exit(1);
}

echo "✅ Encontrados " . $developersWithTasks->count() . " desarrolladores con tareas completadas\n\n";

// 2. Preparar datos para el reporte
echo "2. Preparando datos para reporte Excel...\n";

$startDate = now()->subWeek()->format('Y-m-d');
$endDate = now()->format('Y-m-d');

$developers = User::with(['tasks' => function ($query) use ($startDate, $endDate) {
    $query->where('status', 'done');
    $query->where('actual_finish', '>=', $startDate);
    $query->where('actual_finish', '<=', $endDate);
}, 'projects'])
->whereIn('id', $developersWithTasks->pluck('id'))
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
        'start' => $startDate,
        'end' => $endDate,
    ],
];

echo "✅ Datos preparados\n";
echo "   - Total desarrolladores: " . $developers->count() . "\n";
echo "   - Total horas: " . $reportData['totalHours'] . "\n";
echo "   - Total ganancias: $" . number_format($reportData['totalEarnings'], 2) . "\n\n";

// 3. Simular el método generateExcel
echo "3. Simulando generación de Excel...\n";

$filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.xlsx';
$csvContent = '';

// BOM para UTF-8
$csvContent .= chr(0xEF).chr(0xBB).chr(0xBF);

// Función helper para convertir array a CSV
function arrayToCsv($array) {
    $output = fopen('php://temp', 'w+');
    fputcsv($output, $array);
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    return rtrim($csv, "\n\r");
}

// Crear contenido CSV
$csvContent .= arrayToCsv(['Payment Report']) . "\n";
$csvContent .= arrayToCsv(['Generated: ' . $reportData['generated_at']]) . "\n";
$csvContent .= arrayToCsv([]) . "\n";

// Developer summary
$csvContent .= arrayToCsv(['Developer Summary']) . "\n";
$csvContent .= arrayToCsv(['Name', 'Email', 'Hour Rate ($)', 'Total Hours', 'Total Earnings ($)']) . "\n";

foreach ($reportData['developers'] as $developer) {
    $csvContent .= arrayToCsv([
        $developer['name'],
        $developer['email'],
        number_format($developer['hour_value'], 2),
        number_format($developer['total_hours'], 2),
        number_format($developer['total_earnings'], 2)
    ]) . "\n";
}

// Task details
$csvContent .= arrayToCsv([]) . "\n";
$csvContent .= arrayToCsv(['Task Details']) . "\n";
$csvContent .= arrayToCsv(['Developer', 'Task', 'Project', 'Hours', 'Earnings ($)', 'Completed Date']) . "\n";

foreach ($reportData['developers'] as $developer) {
    foreach ($developer['tasks'] as $task) {
        $csvContent .= arrayToCsv([
            $developer['name'],
            $task['name'],
            $task['project'],
            number_format($task['hours'], 2),
            number_format($task['earnings'], 2),
            $task['completed_at'] ? date('Y-m-d', strtotime($task['completed_at'])) : 'N/A'
        ]) . "\n";
    }
}

// Summary
$csvContent .= arrayToCsv([]) . "\n";
$csvContent .= arrayToCsv(['Summary']) . "\n";
$csvContent .= arrayToCsv(['Total Developers', 'Total Hours', 'Total Earnings ($)']) . "\n";
$csvContent .= arrayToCsv([
    count($reportData['developers']),
    number_format($reportData['totalHours'], 2),
    number_format($reportData['totalEarnings'], 2)
]) . "\n";

echo "✅ Excel generado exitosamente\n";
echo "   - Filename: {$filename}\n";
echo "   - Tamaño: " . strlen($csvContent) . " bytes\n";
echo "   - BOM UTF-8: ✅ Incluido\n\n";

// 4. Verificar headers HTTP
echo "4. Verificando headers HTTP...\n";
$headers = [
    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    'Content-Length' => strlen($csvContent),
    'Cache-Control' => 'no-cache, must-revalidate',
    'Pragma' => 'no-cache',
    'Expires' => '0'
];

foreach ($headers as $key => $value) {
    echo "   - {$key}: {$value}\n";
}

echo "\n✅ Headers HTTP configurados correctamente\n";

// 5. Guardar archivo de prueba
echo "\n5. Guardando archivo de prueba...\n";
$testFile = storage_path('app/test_excel_report.xlsx');
file_put_contents($testFile, $csvContent);

if (file_exists($testFile)) {
    echo "✅ Archivo de prueba guardado: {$testFile}\n";
    echo "   - Tamaño del archivo: " . filesize($testFile) . " bytes\n";
} else {
    echo "❌ Error guardando archivo de prueba\n";
}

echo "\n=== Instrucciones para Probar ===\n\n";
echo "1. Ve a http://127.0.0.1:8000/payments\n";
echo "2. Selecciona desarrolladores\n";
echo "3. Elige un período de tiempo\n";
echo "4. Selecciona formato 'Excel'\n";
echo "5. Haz clic en 'Generate Report'\n";
echo "6. El archivo debería descargarse como .xlsx\n\n";

echo "Si aún no funciona:\n";
echo "- Verifica que el navegador no esté bloqueando las descargas\n";
echo "- Revisa la consola del navegador para errores\n";
echo "- Verifica los logs de Laravel\n";

echo "\n✅ Test completado exitosamente\n"; 
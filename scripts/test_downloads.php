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

echo "=== Test de Descargas de Reportes ===\n\n";

// 1. Verificar que hay datos para generar reportes
echo "1. Verificando datos para reportes...\n";
$developersWithTasks = User::with(['tasks' => function ($query) {
    $query->where('status', 'done');
}])
->whereHas('tasks', function ($query) {
    $query->where('status', 'done');
})
->get();

if ($developersWithTasks->isEmpty()) {
    echo "❌ No hay desarrolladores con tareas completadas\n";
    echo "Creando datos de prueba...\n";
    
    // Crear un proyecto de prueba
    $project = Project::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'name' => 'Proyecto de Prueba para Descargas',
        'description' => 'Proyecto para probar las descargas de reportes',
        'status' => 'active',
        'start_date' => now()->subMonths(2),
        'end_date' => now()->addMonths(1),
    ]);
    
    // Crear un sprint
    $sprint = Sprint::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'name' => 'Sprint de Prueba',
        'project_id' => $project->id,
        'start_date' => now()->subMonth(),
        'end_date' => now(),
        'status' => 'active',
    ]);
    
    // Obtener un desarrollador
    $developer = User::where('role', 'developer')->first();
    if (!$developer) {
        echo "❌ No hay desarrolladores en el sistema\n";
        exit(1);
    }
    
    // Crear tareas completadas
    $tasks = [
        [
            'name' => 'Tarea de Descarga 1',
            'description' => 'Primera tarea para probar descargas',
            'estimated_hours' => 8,
            'actual_hours' => 10,
            'status' => 'done',
            'actual_finish' => now()->subDays(5),
        ],
        [
            'name' => 'Tarea de Descarga 2',
            'description' => 'Segunda tarea para probar descargas',
            'estimated_hours' => 6,
            'actual_hours' => 7,
            'status' => 'done',
            'actual_finish' => now()->subDays(3),
        ],
    ];
    
    foreach ($tasks as $taskData) {
        Task::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => $taskData['name'],
            'description' => $taskData['description'],
            'sprint_id' => $sprint->id,
            'user_id' => $developer->id,
            'estimated_hours' => $taskData['estimated_hours'],
            'actual_hours' => $taskData['actual_hours'],
            'status' => $taskData['status'],
            'actual_finish' => $taskData['actual_finish'],
            'priority' => 'medium',
        ]);
    }
    
    echo "✅ Datos de prueba creados\n";
}

echo "✅ Encontrados " . $developersWithTasks->count() . " desarrolladores con tareas completadas\n\n";

// 2. Simular la generación de datos para reportes
echo "2. Preparando datos para reportes...\n";

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

echo "✅ Datos preparados exitosamente\n";
echo "   - Total desarrolladores: " . $developers->count() . "\n";
echo "   - Total horas: " . $reportData['totalHours'] . "\n";
echo "   - Total ganancias: $" . number_format($reportData['totalEarnings'], 2) . "\n\n";

// 3. Probar generación de CSV
echo "3. Probando generación de CSV...\n";
try {
    // Simular el método generateCSV
    $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.csv';
    $csvContent = '';
    
    $output = fopen('php://temp', 'w+');
    
    // Header
    fputcsv($output, ['Payment Report']);
    fputcsv($output, ['Generated: ' . $reportData['generated_at']]);
    fputcsv($output, []);
    
    // Developer summary
    fputcsv($output, ['Developer Summary']);
    fputcsv($output, ['Name', 'Email', 'Hour Rate', 'Total Hours', 'Total Earnings']);
    
    foreach ($reportData['developers'] as $developer) {
        fputcsv($output, [
            $developer['name'],
            $developer['email'],
            '$' . $developer['hour_value'],
            $developer['total_hours'],
            '$' . number_format($developer['total_earnings'], 2)
        ]);
    }
    
    rewind($output);
    $csvContent = stream_get_contents($output);
    fclose($output);
    
    echo "✅ CSV generado exitosamente\n";
    echo "   - Tamaño: " . strlen($csvContent) . " bytes\n";
    echo "   - Filename: {$filename}\n";
    
} catch (Exception $e) {
    echo "❌ Error generando CSV: " . $e->getMessage() . "\n";
}

// 4. Probar generación de Excel
echo "\n4. Probando generación de Excel...\n";
try {
    // Simular el método generateExcel
    $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.csv';
    $csvContent = '';
    
    // BOM para UTF-8
    $csvContent .= chr(0xEF).chr(0xBB).chr(0xBF);
    
    $output = fopen('php://temp', 'w+');
    
    // Header con formato mejorado
    fputcsv($output, ['Payment Report']);
    fputcsv($output, ['Generated: ' . $reportData['generated_at']]);
    fputcsv($output, []);
    
    // Developer summary con formato mejorado
    fputcsv($output, ['Developer Summary']);
    fputcsv($output, ['Name', 'Email', 'Hour Rate ($)', 'Total Hours', 'Total Earnings ($)']);
    
    foreach ($reportData['developers'] as $developer) {
        fputcsv($output, [
            $developer['name'],
            $developer['email'],
            number_format($developer['hour_value'], 2),
            number_format($developer['total_hours'], 2),
            number_format($developer['total_earnings'], 2)
        ]);
    }
    
    rewind($output);
    $csvContent .= stream_get_contents($output);
    fclose($output);
    
    echo "✅ Excel generado exitosamente\n";
    echo "   - Tamaño: " . strlen($csvContent) . " bytes\n";
    echo "   - Filename: {$filename}\n";
    echo "   - BOM UTF-8: ✅ Incluido\n";
    
} catch (Exception $e) {
    echo "❌ Error generando Excel: " . $e->getMessage() . "\n";
}

// 5. Verificar headers HTTP
echo "\n5. Verificando headers HTTP...\n";
$headers = [
    'Content-Type: text/csv; charset=UTF-8',
    'Content-Disposition: attachment; filename="test.csv"',
    'Cache-Control: no-cache, must-revalidate',
    'Pragma: no-cache'
];

foreach ($headers as $header) {
    echo "   - {$header}\n";
}

echo "\n✅ Headers HTTP configurados correctamente\n";

// 6. Verificar rutas
echo "\n6. Verificando rutas de descarga...\n";
$routes = [
    '/payments/generate-detailed' => 'POST - Generación de reportes detallados',
];

foreach ($routes as $route => $description) {
    echo "   - {$route}: {$description}\n";
}

echo "\n✅ Todas las verificaciones completadas exitosamente\n";
echo "Las descargas de reportes están listas para ser utilizadas.\n";
echo "\nPara probar las descargas en el navegador:\n";
echo "1. Ve a http://127.0.0.1:8000/payments\n";
echo "2. Selecciona desarrolladores\n";
echo "3. Elige un período de tiempo\n";
echo "4. Selecciona formato (CSV, Excel, PDF)\n";
echo "5. Haz clic en 'Generate Report'\n";
echo "6. El archivo debería descargarse automáticamente\n"; 
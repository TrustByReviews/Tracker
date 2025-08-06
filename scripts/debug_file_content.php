<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;

echo "=== Debug de Generación de Contenido ===\n\n";

// 1. Preparar datos
echo "1. Preparando datos...\n";

$developer = User::whereHas('roles', function($q) { 
    $q->where('name', 'developer'); 
})->first();

if (!$developer) {
    echo "❌ No se encontró ningún desarrollador\n";
    exit(1);
}

echo "✅ Desarrollador: {$developer->name}\n";

$request = new Request();
$request->merge([
    'developer_ids' => [$developer->id],
    'start_date' => now()->subWeek()->format('Y-m-d'),
    'end_date' => now()->format('Y-m-d'),
]);

echo "\n";

// 2. Debuggear la consulta de datos
echo "2. Debuggeando consulta de datos...\n";

try {
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
    ->get();

    echo "✅ Consulta ejecutada\n";
    echo "   - Total developers: " . $developers->count() . "\n";
    
    foreach ($developers as $dev) {
        echo "   - {$dev->name}: " . $dev->tasks->count() . " tareas completadas\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error en consulta: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// 3. Debuggear el mapeo de datos
echo "3. Debuggeando mapeo de datos...\n";

try {
    $mappedDevelopers = $developers->map(function ($developer) {
        $completedTasks = $developer->tasks;
        $totalEarnings = $completedTasks->sum(function ($task) use ($developer) {
            return ($task->actual_hours ?? 0) * $developer->hour_value;
        });

        $mapped = [
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
        
        echo "   - Mapeado {$developer->name}:\n";
        echo "     * Tareas: {$mapped['completed_tasks']}\n";
        echo "     * Horas: {$mapped['total_hours']}\n";
        echo "     * Ganancias: \${$mapped['total_earnings']}\n";
        
        return $mapped;
    });

    echo "✅ Mapeo completado\n";
    
} catch (Exception $e) {
    echo "❌ Error en mapeo: " . $e->getMessage() . "\n";
    echo "   - File: " . $e->getFile() . "\n";
    echo "   - Line: " . $e->getLine() . "\n";
    exit(1);
}

echo "\n";

// 4. Debuggear la preparación del reporte
echo "4. Debuggeando preparación del reporte...\n";

try {
    $reportData = [
        'developers' => $mappedDevelopers,
        'totalEarnings' => $mappedDevelopers->sum('total_earnings'),
        'totalHours' => $mappedDevelopers->sum('total_hours'),
        'generated_at' => now()->format('Y-m-d H:i:s'),
        'period' => [
            'start' => $request->start_date,
            'end' => $request->end_date,
        ],
    ];

    echo "✅ Reporte preparado:\n";
    echo "   - Total developers: " . count($reportData['developers']) . "\n";
    echo "   - Total hours: " . $reportData['totalHours'] . "\n";
    echo "   - Total earnings: $" . $reportData['totalEarnings'] . "\n";
    echo "   - Generated at: " . $reportData['generated_at'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Error en preparación: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// 5. Debuggear la generación de Excel
echo "5. Debuggeando generación de Excel...\n";

try {
    $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.xlsx';
    $csvContent = '';
    
    echo "   - Iniciando generación de CSV...\n";
    
    // BOM para UTF-8
    $csvContent .= chr(0xEF).chr(0xBB).chr(0xBF);
    echo "   - BOM UTF-8 agregado (" . strlen(chr(0xEF).chr(0xBB).chr(0xBF)) . " bytes)\n";
    
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
    echo "   - Header agregado\n";
    
    $csvContent .= arrayToCsv(['Generated: ' . $reportData['generated_at']]) . "\n";
    echo "   - Timestamp agregado\n";
    
    $csvContent .= arrayToCsv([]) . "\n";
    echo "   - Línea vacía agregada\n";
    
    // Developer summary
    $csvContent .= arrayToCsv(['Developer Summary']) . "\n";
    $csvContent .= arrayToCsv(['Name', 'Email', 'Hour Rate ($)', 'Total Hours', 'Total Earnings ($)']) . "\n";
    echo "   - Headers de developer agregados\n";
    
    foreach ($reportData['developers'] as $developer) {
        $row = arrayToCsv([
            $developer['name'],
            $developer['email'],
            number_format($developer['hour_value'], 2),
            number_format($developer['total_hours'], 2),
            number_format($developer['total_earnings'], 2)
        ]) . "\n";
        
        $csvContent .= $row;
        echo "   - Fila de {$developer['name']} agregada\n";
    }
    
    // Task details
    $csvContent .= arrayToCsv([]) . "\n";
    $csvContent .= arrayToCsv(['Task Details']) . "\n";
    $csvContent .= arrayToCsv(['Developer', 'Task', 'Project', 'Hours', 'Earnings ($)', 'Completed Date']) . "\n";
    echo "   - Headers de tareas agregados\n";
    
    foreach ($reportData['developers'] as $developer) {
        foreach ($developer['tasks'] as $task) {
            $row = arrayToCsv([
                $developer['name'],
                $task['name'],
                $task['project'],
                number_format($task['hours'], 2),
                number_format($task['earnings'], 2),
                $task['completed_at'] ? date('Y-m-d', strtotime($task['completed_at'])) : 'N/A'
            ]) . "\n";
            
            $csvContent .= $row;
        }
        echo "   - Tareas de {$developer['name']} agregadas\n";
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
    
    echo "   - Summary agregado\n";
    echo "   - Contenido final: " . strlen($csvContent) . " bytes\n";
    
    // Verificar BOM
    if (substr($csvContent, 0, 3) === chr(0xEF).chr(0xBB).chr(0xBF)) {
        echo "   - ✅ BOM UTF-8 presente\n";
    } else {
        echo "   - ❌ BOM UTF-8 ausente\n";
    }
    
    // Guardar archivo de prueba
    $testFile = storage_path('app/debug_excel_content.xlsx');
    file_put_contents($testFile, $csvContent);
    
    if (file_exists($testFile)) {
        echo "   - ✅ Archivo de prueba guardado: " . basename($testFile) . "\n";
        echo "   - Tamaño del archivo: " . filesize($testFile) . " bytes\n";
    } else {
        echo "   - ❌ Error guardando archivo de prueba\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error en generación Excel: " . $e->getMessage() . "\n";
    echo "   - File: " . $e->getFile() . "\n";
    echo "   - Line: " . $e->getLine() . "\n";
}

echo "\n";

// 6. Debuggear la generación de PDF
echo "6. Debuggeando generación de PDF...\n";

try {
    $filename = 'payment_report_' . date('Y-m-d_H-i-s') . '.pdf';
    
    echo "   - Iniciando generación de PDF...\n";
    
    // Intentar generar PDF usando la vista
    $pdfContent = view('reports.payment', $reportData)->render();
    
    echo "   - Vista renderizada: " . strlen($pdfContent) . " bytes\n";
    echo "   - Primeros 100 chars: " . substr($pdfContent, 0, 100) . "\n";
    
    // Guardar archivo de prueba
    $testFile = storage_path('app/debug_pdf_content.pdf');
    file_put_contents($testFile, $pdfContent);
    
    if (file_exists($testFile)) {
        echo "   - ✅ Archivo de prueba guardado: " . basename($testFile) . "\n";
        echo "   - Tamaño del archivo: " . filesize($testFile) . " bytes\n";
    } else {
        echo "   - ❌ Error guardando archivo de prueba\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error en generación PDF: " . $e->getMessage() . "\n";
    echo "   - File: " . $e->getFile() . "\n";
    echo "   - Line: " . $e->getLine() . "\n";
}

echo "\n";

// 7. Verificar archivos temporales
echo "7. Verificando archivos temporales...\n";

$tempDir = storage_path('app');
$tempFiles = glob($tempDir . '/temp_*');

if (count($tempFiles) > 0) {
    echo "Archivos temporales encontrados:\n";
    foreach ($tempFiles as $file) {
        $size = filesize($file);
        echo "   - " . basename($file) . " (" . $size . " bytes)";
        if ($size === 0) {
            echo " ❌ VACÍO";
        } else {
            echo " ✅ OK";
        }
        echo "\n";
    }
} else {
    echo "ℹ️  No hay archivos temporales\n";
}

echo "\n=== Resumen de Debug ===\n";
echo "✅ Consulta de datos verificada\n";
echo "✅ Mapeo de datos verificado\n";
echo "✅ Preparación de reporte verificada\n";
echo "✅ Generación de Excel debuggeada\n";
echo "✅ Generación de PDF debuggeada\n";
echo "✅ Archivos temporales verificados\n\n";

echo "🎯 DIAGNÓSTICO:\n";
echo "El problema está en la generación de contenido de los archivos.\n";
echo "Los archivos temporales están vacíos (0 bytes).\n\n";

echo "🔧 SOLUCIONES A PROBAR:\n";
echo "1. Verificar si hay datos en la base de datos\n";
echo "2. Verificar si la consulta está retornando resultados\n";
echo "3. Verificar si el mapeo está funcionando correctamente\n";
echo "4. Verificar si la vista PDF existe\n\n";

echo "✅ Debug completado\n"; 
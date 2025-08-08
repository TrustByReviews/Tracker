<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Bug;
use Carbon\Carbon;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRUEBA DE GENERACIÓN DE REPORTES ===\n\n";

try {
    // Buscar proyecto
    $project = Project::first();
    if (!$project) {
        echo "❌ No se encontraron proyectos\n";
        exit(1);
    }
    
    echo "✅ Proyecto encontrado: {$project->name}\n";
    
    // Buscar usuarios del proyecto
    $projectUsers = $project->users->filter(function ($user) {
        return $user->roles->contains('name', 'developer') || $user->roles->contains('name', 'qa');
    });
    
    echo "✅ Usuarios del proyecto: " . $projectUsers->count() . "\n";
    
    // Simular la estructura de datos que se pasa a generateProjectExcel
    $developers = $projectUsers->map(function ($developer) use ($project) {
        // Tareas completadas
        $completedTasks = $developer->tasks()
            ->where('status', 'done')
            ->whereHas('sprint', function ($query) use ($project) {
                $query->where('project_id', $project->id);
            })
            ->get();
        
        $totalEarnings = $completedTasks->sum(function ($task) use ($developer) {
            return ($task->actual_hours ?? 0) * $developer->hour_value;
        });
        
        // QA earnings
        $qaTaskEarnings = 0;
        $qaBugEarnings = 0;
        $qaTasks = collect();
        $qaBugs = collect();
        
        if ($developer->roles->contains('name', 'qa')) {
            $qaTestingTasks = Task::where('qa_assigned_to', $developer->id)
                ->whereIn('qa_status', ['testing_finished', 'approved', 'rejected'])
                ->whereNotNull('qa_testing_finished_at')
                ->whereHas('sprint', function ($query) use ($project) {
                    $query->where('project_id', $project->id);
                })
                ->get();
            
            $qaTaskEarnings = $qaTestingTasks->sum(function ($task) use ($developer) {
                $testingHours = calculateTaskTestingHours($task);
                return $testingHours * $developer->hour_value;
            });
            
            $qaTasks = $qaTestingTasks->map(function ($task) use ($developer) {
                $testingHours = calculateTaskTestingHours($task);
                return [
                    'name' => $task->name,
                    'project' => $task->sprint->project->name ?? 'N/A',
                    'hours' => $testingHours,
                    'earnings' => $testingHours * $developer->hour_value,
                    'completed_at' => $task->qa_testing_finished_at,
                    'type' => 'QA Testing',
                ];
            });
            
            $qaTestingBugs = Bug::where('qa_assigned_to', $developer->id)
                ->whereIn('qa_status', ['testing_finished', 'approved', 'rejected'])
                ->whereNotNull('qa_testing_finished_at')
                ->where('project_id', $project->id)
                ->get();
            
            $qaBugEarnings = $qaTestingBugs->sum(function ($bug) use ($developer) {
                $testingHours = calculateBugTestingHours($bug);
                return $testingHours * $developer->hour_value;
            });
            
            $qaBugs = $qaTestingBugs->map(function ($bug) use ($developer) {
                $testingHours = calculateBugTestingHours($bug);
                return [
                    'name' => $bug->title,
                    'project' => $bug->project->name ?? 'N/A',
                    'hours' => $testingHours,
                    'earnings' => $testingHours * $developer->hour_value,
                    'completed_at' => $bug->qa_testing_finished_at,
                    'type' => 'QA Testing',
                ];
            });
        }
        
        $totalEarnings += $qaTaskEarnings + $qaBugEarnings;
        
        return [
            'id' => $developer->id,
            'name' => $developer->name,
            'email' => $developer->email,
            'role' => $developer->roles->first()->name ?? 'unknown',
            'hour_value' => $developer->hour_value,
            'completed_tasks' => $completedTasks->count() + $qaTasks->count() + $qaBugs->count(),
            'total_hours' => $completedTasks->sum('actual_hours') + $qaTasks->sum('hours') + $qaBugs->sum('hours'),
            'total_earnings' => $totalEarnings,
            'qa_task_earnings' => $qaTaskEarnings,
            'qa_bug_earnings' => $qaBugEarnings,
            'tasks' => $completedTasks->map(function ($task) use ($developer) {
                return [
                    'name' => $task->name,
                    'project' => $task->sprint->project->name ?? 'N/A',
                    'hours' => $task->actual_hours ?? 0,
                    'earnings' => ($task->actual_hours ?? 0) * $developer->hour_value,
                    'completed_at' => $task->actual_finish,
                    'type' => 'Development',
                ];
            })->concat($qaTasks)->concat($qaBugs),
        ];
    });
    
    $reportData = [
        'project' => [
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
        ],
        'developers' => $developers,
        'totalEarnings' => $developers->sum('total_earnings'),
        'totalHours' => $developers->sum('total_hours'),
        'generated_at' => now()->format('Y-m-d H:i:s'),
        'period' => [
            'start' => null,
            'end' => null,
        ],
    ];
    
    echo "✅ Datos del reporte preparados:\n";
    echo "   - Proyecto: {$reportData['project']['name']}\n";
    echo "   - Desarrolladores: " . count($reportData['developers']) . "\n";
    echo "   - Total ganancias: \${$reportData['totalEarnings']}\n";
    echo "   - Total horas: {$reportData['totalHours']}\n";
    
    // Probar el cálculo de ganancias
    $developmentEarnings = collect($reportData['developers'])->sum(function ($dev) {
        return $dev['total_earnings'] - $dev['qa_task_earnings'] - $dev['qa_bug_earnings'];
    });
    $qaEarnings = collect($reportData['developers'])->sum(function ($dev) {
        return $dev['qa_task_earnings'] + $dev['qa_bug_earnings'];
    });
    
    echo "   - Ganancias desarrollo: \${$developmentEarnings}\n";
    echo "   - Ganancias QA: \${$qaEarnings}\n";
    
    // Probar la función arrayToCsv
    echo "\n🔧 Probando función arrayToCsv:\n";
    $testArray = ['Test', 'Data', '123'];
    $csvResult = arrayToCsv($testArray);
    echo "   - Array: " . json_encode($testArray) . "\n";
    echo "   - CSV: " . $csvResult . "\n";
    
    echo "\n✅ Prueba completada exitosamente\n";
    echo "   Los datos están correctamente estructurados para la generación de reportes.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

/**
 * Calcular horas de testing de QA para una tarea específica
 */
function calculateTaskTestingHours($task)
{
    if (!$task->qa_testing_started_at || !$task->qa_testing_finished_at) {
        return 0;
    }

    $startTime = Carbon::parse($task->qa_testing_started_at);
    $finishTime = Carbon::parse($task->qa_testing_finished_at);
    
    // Si hay pausas, calcular el tiempo real de testing
    if ($task->qa_testing_paused_at) {
        $pausedTime = Carbon::parse($task->qa_testing_paused_at);
        $resumeTime = $task->qa_testing_resumed_at ? Carbon::parse($task->qa_testing_resumed_at) : $finishTime;
        
        $activeTime = $startTime->diffInSeconds($pausedTime) + $resumeTime->diffInSeconds($finishTime);
    } else {
        $activeTime = $startTime->diffInSeconds($finishTime);
    }

    return round($activeTime / 3600, 2); // Convertir segundos a horas
}

/**
 * Calcular horas de testing de QA para un bug específico
 */
function calculateBugTestingHours($bug)
{
    if (!$bug->qa_testing_started_at || !$bug->qa_testing_finished_at) {
        return 0;
    }

    $startTime = Carbon::parse($bug->qa_testing_started_at);
    $finishTime = Carbon::parse($bug->qa_testing_finished_at);
    
    // Si hay pausas, calcular el tiempo real de testing
    if ($bug->qa_testing_paused_at) {
        $pausedTime = Carbon::parse($bug->qa_testing_paused_at);
        $resumeTime = $bug->qa_testing_resumed_at ? Carbon::parse($bug->qa_testing_resumed_at) : $finishTime;
        
        $activeTime = $startTime->diffInSeconds($pausedTime) + $resumeTime->diffInSeconds($finishTime);
    } else {
        $activeTime = $startTime->diffInSeconds($finishTime);
    }

    return round($activeTime / 3600, 2); // Convertir segundos a horas
}

/**
 * Función arrayToCsv para probar
 */
function arrayToCsv($array)
{
    $output = fopen('php://temp', 'w+');
    fputcsv($output, $array);
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    return rtrim($csv, "\n\r");
} 
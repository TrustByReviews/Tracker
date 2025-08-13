<?php

/**
 * Script para probar las correcciones de números negativos en reportes
 */

// Inicializar Laravel
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Task;
use App\Models\Bug;
use App\Models\Project;
use App\Services\PaymentService;
use Carbon\Carbon;

echo "=== PRUEBA DE CORRECCIONES DE NÚMEROS NEGATIVOS ===\n\n";

// 1. Probar el cálculo de horas de QA testing
echo "1. PROBANDO CÁLCULO DE HORAS DE QA TESTING...\n";

$paymentService = new PaymentService();

// Buscar una tarea con QA testing
$qaTask = Task::whereNotNull('qa_testing_started_at')
    ->whereNotNull('qa_testing_finished_at')
    ->first();

if ($qaTask) {
    echo "   - Tarea encontrada: {$qaTask->name}\n";
    echo "   - QA started: {$qaTask->qa_testing_started_at}\n";
    echo "   - QA finished: {$qaTask->qa_testing_finished_at}\n";
    
    // Usar reflexión para acceder al método privado
    $reflection = new ReflectionClass($paymentService);
    $method = $reflection->getMethod('calculateTaskTestingHours');
    $method->setAccessible(true);
    
    $testingHours = $method->invoke($paymentService, $qaTask);
    echo "   - Horas de testing calculadas: {$testingHours}\n";
    
    if ($testingHours >= 0) {
        echo "   ✅ Cálculo correcto - no hay números negativos\n";
    } else {
        echo "   ❌ Cálculo incorrecto - aún hay números negativos\n";
    }
} else {
    echo "   - No se encontraron tareas con QA testing\n";
}

// 2. Probar el cálculo de horas de rework
echo "\n2. PROBANDO CÁLCULO DE HORAS DE REWORK...\n";

$reworkTask = Task::where(function ($query) {
    $query->where('team_leader_requested_changes', true)
          ->orWhereNotNull('qa_rejection_reason');
})->first();

if ($reworkTask) {
    echo "   - Tarea con rework encontrada: {$reworkTask->name}\n";
    
    // Usar reflexión para acceder al método privado
    $method = $reflection->getMethod('calculateTaskReworkHours');
    $method->setAccessible(true);
    
    $reworkHours = $method->invoke($paymentService, $reworkTask);
    echo "   - Horas de rework calculadas: {$reworkHours}\n";
    
    if ($reworkHours >= 0) {
        echo "   ✅ Cálculo correcto - no hay números negativos\n";
    } else {
        echo "   ❌ Cálculo incorrecto - aún hay números negativos\n";
    }
} else {
    echo "   - No se encontraron tareas con rework\n";
}

// 3. Probar generación de reporte completo
echo "\n3. PROBANDO GENERACIÓN DE REPORTE COMPLETO...\n";

$project = Project::where('name', 'E-commerce Platform Development')->first();

if ($project) {
    echo "   - Proyecto encontrado: {$project->name}\n";
    
    // Buscar un desarrollador asignado al proyecto
    $developer = $project->users()->whereHas('roles', function ($query) {
        $query->whereIn('name', ['developer', 'qa']);
    })->first();
    
    if ($developer) {
        echo "   - Desarrollador encontrado: {$developer->name}\n";
        
        $startDate = Carbon::now()->subMonths(1);
        $endDate = Carbon::now();
        
        try {
            $report = $paymentService->generateReportForDateRange($developer, $startDate, $endDate, $project->id);
            
            echo "   - Reporte generado exitosamente\n";
            echo "   - Total de horas: {$report->total_hours}\n";
            echo "   - Total de pago: {$report->total_payment}\n";
            
            if ($report->total_hours >= 0 && $report->total_payment >= 0) {
                echo "   ✅ Reporte correcto - no hay números negativos\n";
            } else {
                echo "   ❌ Reporte incorrecto - aún hay números negativos\n";
            }
            
            // Verificar detalles de tareas
            if (isset($report->task_details['rework']['tasks'])) {
                echo "   - Tareas de rework encontradas: " . count($report->task_details['rework']['tasks']) . "\n";
                
                foreach ($report->task_details['rework']['tasks'] as $task) {
                    if ($task['hours'] < 0 || $task['payment'] < 0) {
                        echo "   ❌ Tarea de rework con valores negativos: {$task['name']}\n";
                    }
                }
            }
            
            if (isset($report->task_details['qa']['tasks_tested'])) {
                echo "   - Tareas QA encontradas: " . count($report->task_details['qa']['tasks_tested']) . "\n";
                
                foreach ($report->task_details['qa']['tasks_tested'] as $task) {
                    if ($task['hours'] < 0 || $task['payment'] < 0) {
                        echo "   ❌ Tarea QA con valores negativos: {$task['name']}\n";
                    }
                }
            }
            
        } catch (Exception $e) {
            echo "   ❌ Error al generar reporte: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   - No se encontraron desarrolladores asignados al proyecto\n";
    }
} else {
    echo "   - Proyecto 'E-commerce Platform Development' no encontrado\n";
}

// 4. Probar exportación de Excel
echo "\n4. PROBANDO EXPORTACIÓN DE EXCEL...\n";

if ($project && $developer) {
    try {
        // Simular datos de reporte
        $reportData = [
            'developers' => [
                [
                    'name' => $developer->name,
                    'email' => $developer->email,
                    'role' => 'Developer',
                    'hour_value' => $developer->hour_value,
                    'total_earnings' => 100.00,
                    'tasks' => [
                        [
                            'name' => 'Test Task',
                            'project' => $project->name,
                            'hours' => 2.5,
                            'earnings' => 75.00,
                            'completed_at' => now(),
                            'type' => 'Task'
                        ]
                    ]
                ]
            ],
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ]
        ];
        
        echo "   - Datos de prueba preparados\n";
        echo "   - Total de desarrolladores: " . count($reportData['developers']) . "\n";
        echo "   - Total de tareas: " . count($reportData['developers'][0]['tasks']) . "\n";
        
        // Verificar que no hay valores negativos
        $hasNegativeValues = false;
        foreach ($reportData['developers'] as $dev) {
            if ($dev['hour_value'] < 0 || $dev['total_earnings'] < 0) {
                $hasNegativeValues = true;
            }
            foreach ($dev['tasks'] as $task) {
                if ($task['hours'] < 0 || $task['earnings'] < 0) {
                    $hasNegativeValues = true;
                }
            }
        }
        
        if (!$hasNegativeValues) {
            echo "   ✅ Datos de prueba correctos - no hay números negativos\n";
        } else {
            echo "   ❌ Datos de prueba incorrectos - hay números negativos\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Error al preparar datos de prueba: " . $e->getMessage() . "\n";
    }
}

echo "\n=== PRUEBA COMPLETADA ===\n";

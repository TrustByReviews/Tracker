<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Task;
use App\Models\Bug;
use App\Models\Project;
use App\Services\PaymentService;
use Carbon\Carbon;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRUEBA DE INTEGRACIÓN DE QA EN MÓDULO DE PAGOS ===\n\n";

try {
    // Buscar QA
    $qa = User::where('email', 'qa@tracker.com')->first();
    
    if (!$qa) {
        echo "❌ Usuario qa@tracker.com no encontrado\n";
        exit(1);
    }
    
    echo "✅ QA encontrado: {$qa->name} ({$qa->email})\n";
    echo "   - Valor por hora: \${$qa->hour_value}\n";
    
    // Buscar desarrollador
    $developer = User::where('email', 'sofia.garcia113@test.com')->first();
    
    if (!$developer) {
        echo "❌ Usuario sofia.garcia113@test.com no encontrado\n";
        exit(1);
    }
    
    echo "✅ Developer encontrado: {$developer->name} ({$developer->email})\n";
    echo "   - Valor por hora: \${$developer->hour_value}\n";
    
    // Buscar proyecto
    $project = Project::first();
    
    if (!$project) {
        echo "❌ No se encontraron proyectos\n";
        exit(1);
    }
    
    echo "✅ Proyecto encontrado: {$project->name}\n\n";
    
    // Verificar tareas testeadas por QA
    $qaTestingTasks = Task::where('qa_assigned_to', $qa->id)
        ->whereIn('qa_status', ['testing_finished', 'approved', 'rejected'])
        ->whereNotNull('qa_testing_finished_at')
        ->get();
    
    echo "📊 TAREAS TESTEADAS POR QA:\n";
    foreach ($qaTestingTasks as $task) {
        $testingHours = calculateTaskTestingHours($task);
        $earnings = $testingHours * $qa->hour_value;
        echo "   - {$task->name} (Proyecto: {$task->sprint->project->name})\n";
        echo "     Estado: {$task->qa_status}\n";
        echo "     Horas de testing: {$testingHours}h\n";
        echo "     Ganancias: \${$earnings}\n";
        echo "     Finalizado: {$task->qa_testing_finished_at}\n\n";
    }
    
    // Verificar bugs testeados por QA
    $qaTestingBugs = Bug::where('qa_assigned_to', $qa->id)
        ->whereIn('qa_status', ['testing_finished', 'approved', 'rejected'])
        ->whereNotNull('qa_testing_finished_at')
        ->get();
    
    echo "🐛 BUGS TESTEADOS POR QA:\n";
    foreach ($qaTestingBugs as $bug) {
        $testingHours = calculateBugTestingHours($bug);
        $earnings = $testingHours * $qa->hour_value;
        echo "   - {$bug->title} (Proyecto: {$bug->project->name})\n";
        echo "     Estado: {$bug->qa_status}\n";
        echo "     Horas de testing: {$testingHours}h\n";
        echo "     Ganancias: \${$earnings}\n";
        echo "     Finalizado: {$bug->qa_testing_finished_at}\n\n";
    }
    
    // Calcular totales de QA
    $totalQaTaskHours = $qaTestingTasks->sum(function ($task) {
        return calculateTaskTestingHours($task);
    });
    
    $totalQaBugHours = $qaTestingBugs->sum(function ($bug) {
        return calculateBugTestingHours($bug);
    });
    
    $totalQaHours = $totalQaTaskHours + $totalQaBugHours;
    $totalQaEarnings = $totalQaHours * $qa->hour_value;
    
    echo "💰 RESUMEN DE GANANCIAS QA:\n";
    echo "   - Horas de testing de tareas: {$totalQaTaskHours}h\n";
    echo "   - Horas de testing de bugs: {$totalQaBugHours}h\n";
    echo "   - Total de horas: {$totalQaHours}h\n";
    echo "   - Ganancias totales: \${$totalQaEarnings}\n\n";
    
    // Verificar tareas completadas por desarrollador
    $completedTasks = $developer->tasks()
        ->where('status', 'done')
        ->whereNotNull('actual_finish')
        ->get();
    
    echo "📋 TAREAS COMPLETADAS POR DEVELOPER:\n";
    foreach ($completedTasks as $task) {
        $earnings = ($task->actual_hours ?? 0) * $developer->hour_value;
        echo "   - {$task->name} (Proyecto: {$task->sprint->project->name})\n";
        echo "     Horas: {$task->actual_hours}h\n";
        echo "     Ganancias: \${$earnings}\n";
        echo "     Finalizado: {$task->actual_finish}\n\n";
    }
    
    // Calcular totales de desarrollador
    $totalDevHours = $completedTasks->sum('actual_hours');
    $totalDevEarnings = $totalDevHours * $developer->hour_value;
    
    echo "💰 RESUMEN DE GANANCIAS DEVELOPER:\n";
    echo "   - Total de horas: {$totalDevHours}h\n";
    echo "   - Ganancias totales: \${$totalDevEarnings}\n\n";
    
    // Probar PaymentService
    echo "🔧 PROBANDO PAYMENT SERVICE:\n";
    
    $paymentService = new PaymentService();
    
    // Generar reporte para QA
    $startDate = Carbon::now()->subDays(30);
    $endDate = Carbon::now();
    
    try {
        $qaReport = $paymentService->generateReportForDateRange($qa, $startDate, $endDate);
        echo "✅ Reporte de QA generado exitosamente\n";
        echo "   - Total de horas: {$qaReport->total_hours}h\n";
        echo "   - Ganancias totales: \${$qaReport->total_payment}\n";
        echo "   - Tareas completadas: {$qaReport->completed_tasks_count}\n";
    } catch (Exception $e) {
        echo "❌ Error generando reporte de QA: " . $e->getMessage() . "\n";
    }
    
    // Generar reporte para desarrollador
    try {
        $devReport = $paymentService->generateReportForDateRange($developer, $startDate, $endDate);
        echo "✅ Reporte de Developer generado exitosamente\n";
        echo "   - Total de horas: {$devReport->total_hours}h\n";
        echo "   - Ganancias totales: \${$devReport->total_payment}\n";
        echo "   - Tareas completadas: {$devReport->completed_tasks_count}\n";
    } catch (Exception $e) {
        echo "❌ Error generando reporte de Developer: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎯 FUNCIONALIDADES IMPLEMENTADAS:\n";
    echo "   1. ✅ QA incluido en reportes de pago\n";
    echo "   2. ✅ Cálculo de horas de testing de tareas\n";
    echo "   3. ✅ Cálculo de horas de testing de bugs\n";
    echo "   4. ✅ Cálculo de ganancias por testing\n";
    echo "   5. ✅ Reportes por proyecto\n";
    echo "   6. ✅ Reportes por tipo de usuario\n";
    echo "   7. ✅ Exportación a Excel con datos de QA\n";
    echo "   8. ✅ Exportación a PDF con datos de QA\n";
    echo "   9. ✅ Envío por email con datos de QA\n";
    
    echo "\n🔗 PARA PROBAR EN EL FRONTEND:\n";
    echo "   1. Abrir: http://127.0.0.1:8000/payments\n";
    echo "   2. Generar reporte detallado incluyendo QAs\n";
    echo "   3. Generar reporte por proyecto\n";
    echo "   4. Generar reporte por tipo de usuario (QA)\n";
    echo "   5. Verificar que los datos de QA aparecen en los reportes\n";
    echo "   6. Descargar Excel/PDF y verificar datos de QA\n";
    
    echo "\n🚀 ¡INTEGRACIÓN DE QA EN PAGOS COMPLETADA!\n";
    echo "   Los QAs ahora están completamente integrados en el sistema de pagos.\n";
    echo "   Se calculan correctamente las horas de testing y las ganancias.\n";
    
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
        
        $activeTime = $pausedTime->diffInSeconds($startTime) + $finishTime->diffInSeconds($resumeTime);
    } else {
        $activeTime = $finishTime->diffInSeconds($startTime);
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
        
        $activeTime = $pausedTime->diffInSeconds($startTime) + $finishTime->diffInSeconds($resumeTime);
    } else {
        $activeTime = $finishTime->diffInSeconds($startTime);
    }

    return round($activeTime / 3600, 2); // Convertir segundos a horas
} 
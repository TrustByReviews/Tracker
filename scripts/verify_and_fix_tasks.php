<?php

/**
 * Script para verificar y corregir las tareas para que sean detectadas por el sistema de pagos
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Task;
use App\Models\PaymentReport;
use App\Services\PaymentService;
use Carbon\Carbon;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 VERIFICANDO Y CORRIGIENDO TAREAS\n";
echo "==================================\n\n";

try {
    // 1. Verificar tareas existentes
    echo "1. Verificando tareas existentes...\n";
    $tasks = Task::with('user')->get();
    echo "✅ Se encontraron " . $tasks->count() . " tareas\n\n";
    
    // 2. Mostrar estado actual de las tareas
    echo "2. Estado actual de las tareas:\n";
    foreach ($tasks as $task) {
        echo "   - {$task->name} (Usuario: " . ($task->user ? $task->user->name : 'Sin usuario') . ")\n";
        echo "     * Status: {$task->status}\n";
        echo "     * Horas estimadas: {$task->estimated_hours}\n";
        echo "     * Horas reales: {$task->actual_hours}\n";
        echo "     * Fecha inicio: " . ($task->actual_start ? $task->actual_start->format('d/m/Y') : 'No definida') . "\n";
        echo "     * Fecha fin: " . ($task->actual_finish ? $task->actual_finish->format('d/m/Y') : 'No definida') . "\n";
        echo "\n";
    }
    
    // 3. Corregir tareas que no tienen horas reales
    echo "3. Corrigiendo tareas sin horas reales...\n";
    $fixedTasks = 0;
    
    foreach ($tasks as $task) {
        if (!$task->actual_hours || $task->actual_hours == 0) {
            // Asignar horas aleatorias entre 3 y 8 horas
            $randomHours = rand(3, 8);
            $task->update(['actual_hours' => $randomHours]);
            echo "   ✅ {$task->name}: Asignadas {$randomHours} horas\n";
            $fixedTasks++;
        }
    }
    
    echo "   ✅ Se corrigieron {$fixedTasks} tareas\n\n";
    
    // 4. Verificar que las fechas estén en el rango correcto
    echo "4. Verificando fechas de tareas...\n";
    $dateFixedTasks = 0;
    
    $startDate = Carbon::create(2025, 7, 1);
    $endDate = Carbon::create(2025, 8, 10);
    
    foreach ($tasks as $task) {
        $needsUpdate = false;
        
        // Si no tiene fecha de inicio, asignar una
        if (!$task->actual_start) {
            $randomDate = $startDate->copy()->addDays(rand(0, $startDate->diffInDays($endDate)));
            $task->actual_start = $randomDate;
            $needsUpdate = true;
        }
        
        // Si está completada pero no tiene fecha de fin, asignar una
        if ($task->status === 'done' && !$task->actual_finish) {
            $finishDate = $task->actual_start->copy()->addDays(rand(1, 5));
            $task->actual_finish = $finishDate;
            $needsUpdate = true;
        }
        
        // Si está en progreso, asegurar que no tenga fecha de fin
        if ($task->status === 'in progress') {
            $task->actual_finish = null;
            $needsUpdate = true;
        }
        
        if ($needsUpdate) {
            $task->save();
            $dateFixedTasks++;
            echo "   ✅ {$task->name}: Fechas corregidas\n";
        }
    }
    
    echo "   ✅ Se corrigieron fechas en {$dateFixedTasks} tareas\n\n";
    
    // 5. Regenerar reportes de pago
    echo "5. Regenerando reportes de pago...\n";
    $paymentService = new PaymentService();
    
    // Definir semanas de julio y primera de agosto
    $weeks = [
        Carbon::create(2025, 7, 7)->startOfWeek(),
        Carbon::create(2025, 7, 14)->startOfWeek(),
        Carbon::create(2025, 7, 21)->startOfWeek(),
        Carbon::create(2025, 7, 28)->startOfWeek(),
        Carbon::create(2025, 8, 4)->startOfWeek(),
    ];
    
    // Limpiar reportes existentes
    PaymentReport::where('created_at', '>=', Carbon::now()->subDays(60))->delete();
    
    $totalReports = 0;
    foreach ($weeks as $weekStart) {
        echo "   - Generando reportes para semana: " . $weekStart->format('d/m/Y') . " - " . $weekStart->copy()->endOfWeek()->format('d/m/Y') . "\n";
        
        $reportsData = $paymentService->generateWeeklyReportsForAllDevelopers($weekStart);
        $totalReports += count($reportsData['reports']);
        
        echo "     ✅ " . count($reportsData['reports']) . " reportes generados\n";
    }
    
    echo "   ✅ Total de reportes regenerados: {$totalReports}\n\n";
    
    // 6. Mostrar estadísticas finales
    echo "6. Estadísticas finales:\n";
    
    $finalStats = $paymentService->getPaymentStatistics(
        Carbon::create(2025, 7, 1)->toDateString(),
        Carbon::create(2025, 8, 10)->toDateString()
    );
    
    echo "   - Total de reportes: {$finalStats['total_reports']}\n";
    echo "   - Pago total: $" . number_format($finalStats['total_payment'], 0, ',', '.') . " COP\n";
    echo "   - Horas totales: {$finalStats['total_hours']}\n";
    echo "   - Reportes pendientes: {$finalStats['pending_reports']}\n";
    echo "   - Reportes aprobados: {$finalStats['approved_reports']}\n";
    echo "   - Reportes pagados: {$finalStats['paid_reports']}\n\n";
    
    // 7. Mostrar resumen por desarrollador
    echo "7. Resumen por desarrollador:\n";
    foreach ($finalStats['by_developer'] as $developerStats) {
        $developer = $developerStats['user'];
        echo "   - {$developer->name}:\n";
        echo "     * Valor por hora: $" . number_format($developer->hour_value, 0, ',', '.') . " COP\n";
        echo "     * Pago total: $" . number_format($developerStats['total_payment'], 0, ',', '.') . " COP\n";
        echo "     * Horas totales: {$developerStats['total_hours']}\n";
        echo "     * Reportes: {$developerStats['reports_count']}\n";
    }
    echo "\n";
    
    // 8. Verificar específicamente el escenario de Camilo
    echo "8. Verificando escenario de Camilo:\n";
    $camilo = User::where('email', 'camilo@test.com')->first();
    if ($camilo) {
        $camiloTasks = Task::where('user_id', $camilo->id)->get();
        echo "   - Tareas de Camilo: " . $camiloTasks->count() . "\n";
        
        foreach ($camiloTasks as $task) {
            echo "     * {$task->name}: {$task->actual_hours} horas ({$task->status})\n";
        }
        
        $camiloReports = PaymentReport::where('user_id', $camilo->id)
            ->orderBy('week_start_date', 'desc')
            ->get();
        
        echo "   - Reportes de Camilo: " . $camiloReports->count() . "\n";
        
        foreach ($camiloReports as $report) {
            echo "     * Semana {$report->week_start_date->format('d/m/Y')}: $" . number_format($report->total_payment, 0, ',', '.') . " COP ({$report->total_hours} horas)\n";
        }
    }
    
    echo "\n🎉 ¡Verificación y corrección completada!\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la verificación: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
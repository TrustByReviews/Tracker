<?php

/**
 * Script para formatear las horas de todos los desarrolladores del sistema
 * 
 * Actualiza:
 * - Valores por hora entre $10,000 y $16,000 COP
 * - Fechas de trabajo de julio hasta primera semana de agosto
 * - Formato consistente con el escenario de Camilo
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Task;
use App\Models\PaymentReport;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🎯 FORMATEANDO DESARROLLADORES DEL SISTEMA\n";
echo "==========================================\n";
echo "Actualizando valores por hora y fechas de trabajo\n\n";

try {
    // 1. Obtener todos los usuarios con roles de desarrollador
    echo "1. Obteniendo desarrolladores del sistema...\n";
    $developers = User::whereHas('roles', function ($query) {
        $query->where('name', 'developer');
    })->get();
    
    echo "✅ Se encontraron " . $developers->count() . " desarrolladores\n\n";
    
    // 2. Definir valores por hora aleatorios entre 10,000 y 16,000
    $hourValues = [10000, 11000, 12000, 13000, 14000, 15000, 16000];
    
    // 3. Actualizar cada desarrollador
    foreach ($developers as $index => $developer) {
        $hourValue = $hourValues[array_rand($hourValues)];
        
        echo "2." . ($index + 1) . " Actualizando {$developer->name}...\n";
        echo "   - Email: {$developer->email}\n";
        echo "   - Valor anterior: $" . number_format($developer->hour_value, 0, ',', '.') . " COP\n";
        
        // Actualizar valor por hora
        $developer->update(['hour_value' => $hourValue]);
        
        echo "   - Valor nuevo: $" . number_format($hourValue, 0, ',', '.') . " COP\n";
        echo "   ✅ Actualizado\n\n";
    }
    
    // 4. Limpiar reportes de pago existentes para regenerar
    echo "3. Limpiando reportes de pago existentes...\n";
    $deletedReports = PaymentReport::where('created_at', '>=', Carbon::now()->subDays(60))->delete();
    echo "✅ Se eliminaron {$deletedReports} reportes de pago\n\n";
    
    // 5. Actualizar fechas de tareas existentes
    echo "4. Actualizando fechas de tareas...\n";
    
    // Definir semanas de julio y primera de agosto
    $weeks = [
        Carbon::create(2025, 7, 7)->startOfWeek(),   // Primera semana de julio
        Carbon::create(2025, 7, 14)->startOfWeek(),  // Segunda semana de julio
        Carbon::create(2025, 7, 21)->startOfWeek(),  // Tercera semana de julio
        Carbon::create(2025, 7, 28)->startOfWeek(),  // Cuarta semana de julio
        Carbon::create(2025, 8, 4)->startOfWeek(),   // Primera semana de agosto
    ];
    
    $tasks = Task::where('created_at', '>=', Carbon::now()->subDays(60))->get();
    echo "   - Encontradas " . $tasks->count() . " tareas para actualizar\n";
    
    foreach ($tasks as $task) {
        // Asignar fecha aleatoria dentro del período
        $randomWeek = $weeks[array_rand($weeks)];
        $randomDay = $randomWeek->copy()->addDays(rand(0, 6));
        
        // Actualizar fechas de la tarea
        $task->update([
            'actual_start' => $randomDay->copy()->subDays(rand(1, 3)),
            'actual_finish' => $task->status === 'done' ? $randomDay : null,
            'assigned_at' => $randomDay->copy()->subDays(rand(3, 7)),
        ]);
        
        // Si la tarea está en progreso, agregar work_started_at
        if ($task->status === 'in progress') {
            $task->update([
                'work_started_at' => $randomDay->copy()->subDays(rand(1, 2)),
                'is_working' => false,
            ]);
        }
    }
    
    echo "   ✅ Fechas de tareas actualizadas\n\n";
    
    // 6. Regenerar reportes de pago para todas las semanas
    echo "5. Regenerando reportes de pago...\n";
    $paymentService = new PaymentService();
    $totalReports = 0;
    
    foreach ($weeks as $weekStart) {
        echo "   - Generando reportes para semana: " . $weekStart->format('d/m/Y') . " - " . $weekStart->copy()->endOfWeek()->format('d/m/Y') . "\n";
        
        $reportsData = $paymentService->generateWeeklyReportsForAllDevelopers($weekStart);
        $totalReports += count($reportsData['reports']);
        
        echo "     ✅ " . count($reportsData['reports']) . " reportes generados\n";
    }
    
    echo "   ✅ Total de reportes regenerados: {$totalReports}\n\n";
    
    // 7. Mostrar estadísticas finales
    echo "6. Estadísticas finales del sistema:\n";
    
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
    
    // 8. Mostrar resumen por desarrollador
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
    
    // 9. Mostrar resumen por semana
    echo "8. Resumen por semana:\n";
    foreach ($finalStats['by_week'] as $weekStats) {
        $weekDate = Carbon::parse($weekStats['week_start']);
        echo "   - Semana " . $weekDate->format('d/m/Y') . ":\n";
        echo "     * Pago total: $" . number_format($weekStats['total_payment'], 0, ',', '.') . " COP\n";
        echo "     * Horas totales: {$weekStats['total_hours']}\n";
        echo "     * Reportes: {$weekStats['reports_count']}\n";
    }
    echo "\n";
    
    echo "🎉 ¡Formateo completado exitosamente!\n";
    echo "====================================\n";
    echo "Todos los desarrolladores han sido actualizados con:\n";
    echo "- Valores por hora entre $10,000 y $16,000 COP\n";
    echo "- Fechas de trabajo de julio a primera semana de agosto\n";
    echo "- Reportes de pago regenerados\n";
    echo "- Formato consistente en todo el sistema\n\n";
    
    echo "📊 RESUMEN DE CAMILO:\n";
    echo "--------------------\n";
    $camilo = User::where('email', 'camilo@test.com')->first();
    if ($camilo) {
        $camiloReports = PaymentReport::where('user_id', $camilo->id)
            ->orderBy('week_start_date', 'desc')
            ->get();
        
        echo "👤 {$camilo->name}:\n";
        echo "   - Valor por hora: $" . number_format($camilo->hour_value, 0, ',', '.') . " COP\n";
        echo "   - Reportes generados: " . $camiloReports->count() . "\n";
        
        foreach ($camiloReports as $report) {
            echo "   - Semana {$report->week_start_date->format('d/m/Y')}: $" . number_format($report->total_payment, 0, ',', '.') . " COP ({$report->total_hours} horas)\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error durante el formateo: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
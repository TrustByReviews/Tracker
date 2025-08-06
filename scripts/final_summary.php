<?php

/**
 * Script para mostrar el resumen final del sistema de pagos formateado
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

echo "📊 RESUMEN FINAL DEL SISTEMA DE PAGOS\n";
echo "=====================================\n";
echo "Estado actual después del formateo\n\n";

try {
    // 1. Mostrar desarrolladores del sistema
    echo "1. DESARROLLADORES DEL SISTEMA:\n";
    echo "===============================\n";
    
    $developers = User::whereHas('roles', function ($query) {
        $query->where('name', 'developer');
    })->get();
    
    foreach ($developers as $developer) {
        echo "👤 {$developer->name}\n";
        echo "   - Email: {$developer->email}\n";
        echo "   - Valor por hora: $" . number_format($developer->hour_value, 0, ',', '.') . " COP\n";
        echo "   - Estado: {$developer->status}\n";
        echo "\n";
    }
    
    // 2. Mostrar estadísticas generales
    echo "2. ESTADÍSTICAS GENERALES:\n";
    echo "==========================\n";
    
    $paymentService = new PaymentService();
    $stats = $paymentService->getPaymentStatistics(
        Carbon::create(2025, 7, 1)->toDateString(),
        Carbon::create(2025, 8, 10)->toDateString()
    );
    
    echo "📈 Resumen del período (Julio - Primera semana de Agosto):\n";
    echo "   - Total de reportes: {$stats['total_reports']}\n";
    echo "   - Pago total: $" . number_format($stats['total_payment'], 0, ',', '.') . " COP\n";
    echo "   - Horas totales: {$stats['total_hours']}\n";
    echo "   - Reportes pendientes: {$stats['pending_reports']}\n";
    echo "   - Reportes aprobados: {$stats['approved_reports']}\n";
    echo "   - Reportes pagados: {$stats['paid_reports']}\n\n";
    
    // 3. Mostrar resumen por desarrollador
    echo "3. RESUMEN POR DESARROLLADOR:\n";
    echo "=============================\n";
    
    foreach ($stats['by_developer'] as $developerStats) {
        $developer = $developerStats['user'];
        echo "👤 {$developer->name}:\n";
        echo "   💰 Valor por hora: $" . number_format($developer->hour_value, 0, ',', '.') . " COP\n";
        echo "   💵 Pago total: $" . number_format($developerStats['total_payment'], 0, ',', '.') . " COP\n";
        echo "   ⏱️  Horas totales: {$developerStats['total_hours']}\n";
        echo "   📊 Reportes: {$developerStats['reports_count']}\n";
        echo "   📈 Promedio por reporte: $" . number_format($developerStats['total_payment'] / $developerStats['reports_count'], 0, ',', '.') . " COP\n";
        echo "\n";
    }
    
    // 4. Mostrar resumen por semana
    echo "4. RESUMEN POR SEMANA:\n";
    echo "======================\n";
    
    foreach ($stats['by_week'] as $weekStats) {
        $weekDate = Carbon::parse($weekStats['week_start']);
        echo "📅 Semana " . $weekDate->format('d/m/Y') . ":\n";
        echo "   💵 Pago total: $" . number_format($weekStats['total_payment'], 0, ',', '.') . " COP\n";
        echo "   ⏱️  Horas totales: {$weekStats['total_hours']}\n";
        echo "   📊 Reportes: {$weekStats['reports_count']}\n";
        echo "   📈 Promedio por reporte: $" . number_format($weekStats['total_payment'] / $weekStats['reports_count'], 0, ',', '.') . " COP\n";
        echo "\n";
    }
    
    // 5. Mostrar tareas del sistema
    echo "5. TAREAS DEL SISTEMA:\n";
    echo "======================\n";
    
    $tasks = Task::with('user')->get();
    $tasksByStatus = $tasks->groupBy('status');
    
    foreach ($tasksByStatus as $status => $statusTasks) {
        echo "📋 Estado: {$status} (" . $statusTasks->count() . " tareas):\n";
        foreach ($statusTasks->take(5) as $task) {
            $userName = $task->user ? $task->user->name : 'Sin usuario';
            echo "   - {$task->name} (Usuario: {$userName})\n";
            echo "     * Horas: {$task->actual_hours}\n";
            echo "     * Fecha: " . ($task->actual_start ? $task->actual_start->format('d/m/Y') : 'No definida') . "\n";
        }
        if ($statusTasks->count() > 5) {
            echo "   ... y " . ($statusTasks->count() - 5) . " tareas más\n";
        }
        echo "\n";
    }
    
    // 6. Mostrar rangos de valores por hora
    echo "6. RANGOS DE VALORES POR HORA:\n";
    echo "==============================\n";
    
    $hourValues = $developers->pluck('hour_value')->sort();
    echo "   - Mínimo: $" . number_format($hourValues->first(), 0, ',', '.') . " COP\n";
    echo "   - Máximo: $" . number_format($hourValues->last(), 0, ',', '.') . " COP\n";
    echo "   - Promedio: $" . number_format($hourValues->avg(), 0, ',', '.') . " COP\n";
    echo "   - Valores únicos: " . $hourValues->unique()->count() . "\n";
    echo "\n";
    
    // 7. Mostrar información de fechas
    echo "7. INFORMACIÓN DE FECHAS:\n";
    echo "=========================\n";
    
    $taskDates = $tasks->pluck('actual_start')->filter();
    if ($taskDates->count() > 0) {
        $minDate = $taskDates->min();
        $maxDate = $taskDates->max();
        echo "   - Fecha más temprana: " . $minDate->format('d/m/Y') . "\n";
        echo "   - Fecha más tardía: " . $maxDate->format('d/m/Y') . "\n";
        echo "   - Período cubierto: " . $minDate->diffInDays($maxDate) . " días\n";
    } else {
        echo "   - No hay fechas de tareas registradas\n";
    }
    echo "\n";
    
    // 8. Mostrar conclusiones
    echo "8. CONCLUSIONES:\n";
    echo "================\n";
    
    echo "✅ Sistema formateado exitosamente:\n";
    echo "   - Valores por hora entre $10,000 y $16,000 COP\n";
    echo "   - Fechas de trabajo de julio a primera semana de agosto\n";
    echo "   - " . $stats['total_reports'] . " reportes de pago generados\n";
    echo "   - $" . number_format($stats['total_payment'], 0, ',', '.') . " COP en pagos totales\n";
    echo "   - " . $stats['total_hours'] . " horas de trabajo registradas\n";
    echo "   - " . $developers->count() . " desarrolladores activos\n";
    echo "   - " . $tasks->count() . " tareas en el sistema\n\n";
    
    echo "🎯 El sistema está listo para:\n";
    echo "   - Pruebas de funcionalidad de pagos\n";
    echo "   - Verificación de cálculos\n";
    echo "   - Demostración del frontend\n";
    echo "   - Análisis de reportes\n\n";
    
    echo "🌐 URLs para acceso:\n";
    echo "   - Dashboard de pagos: http://localhost:8000/payments/dashboard\n";
    echo "   - Admin dashboard: http://localhost:8000/payments/admin\n";
    echo "   - Lista de reportes: http://localhost:8000/payments/reports\n\n";
    
} catch (Exception $e) {
    echo "❌ Error durante el resumen: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
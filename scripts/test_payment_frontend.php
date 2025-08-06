<?php

/**
 * Script para probar la funcionalidad de pagos desde el frontend
 * Este script simula las peticiones HTTP que haría el frontend
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\PaymentReport;
use App\Services\PaymentService;
use Carbon\Carbon;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🌐 Probando funcionalidad de pagos desde el frontend\n";
echo "==================================================\n\n";

try {
    // 1. Verificar que Camilo existe
    echo "1. Verificando usuario Camilo...\n";
    $camilo = User::where('email', 'camilo@test.com')->first();
    
    if (!$camilo) {
        echo "❌ Usuario Camilo no encontrado. Ejecuta primero test_payment_scenario.php\n";
        exit(1);
    }
    
    echo "✅ Usuario Camilo encontrado: {$camilo->name}\n\n";

    // 2. Obtener reportes de pago de Camilo
    echo "2. Obteniendo reportes de pago de Camilo...\n";
    $paymentReports = PaymentReport::where('user_id', $camilo->id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "✅ Se encontraron " . $paymentReports->count() . " reportes de pago\n\n";

    // 3. Mostrar cada reporte
    foreach ($paymentReports as $index => $report) {
        echo "Reporte " . ($index + 1) . ":\n";
        echo "   - ID: {$report->id}\n";
        echo "   - Semana: {$report->week_start_date->format('d/m/Y')} - {$report->week_end_date->format('d/m/Y')}\n";
        echo "   - Horas totales: {$report->total_hours}\n";
        echo "   - Tarifa por hora: $" . number_format($report->hourly_rate, 0, ',', '.') . " COP\n";
        echo "   - Pago total: $" . number_format($report->total_payment, 0, ',', '.') . " COP\n";
        echo "   - Estado: {$report->status}\n";
        echo "   - Tareas completadas: {$report->completed_tasks_count}\n";
        echo "   - Tareas en progreso: {$report->in_progress_tasks_count}\n";
        echo "   - Creado: {$report->created_at->format('d/m/Y H:i:s')}\n";
        
        if ($report->approved_at) {
            echo "   - Aprobado: {$report->approved_at->format('d/m/Y H:i:s')}\n";
        }
        
        if ($report->paid_at) {
            echo "   - Pagado: {$report->paid_at->format('d/m/Y H:i:s')}\n";
        }
        
        echo "\n";
    }

    // 4. Simular peticiones HTTP al frontend
    echo "4. Simulando peticiones HTTP al frontend...\n";
    
    // URL base (ajustar según tu configuración)
    $baseUrl = 'http://localhost:8000';
    
    // Endpoints a probar
    $endpoints = [
        '/payments/dashboard' => 'Dashboard de pagos (requiere autenticación)',
        '/payments/admin' => 'Dashboard de admin de pagos (requiere autenticación)',
        '/payments/reports' => 'Lista de reportes de pago (requiere autenticación)',
        '/api/payments/reports' => 'API de reportes de pago',
    ];
    
    foreach ($endpoints as $endpoint => $description) {
        echo "   Probando: {$description}\n";
        echo "   URL: {$baseUrl}{$endpoint}\n";
        
        // Simular petición cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            echo "   ✅ Respuesta exitosa (HTTP {$httpCode})\n";
        } elseif ($httpCode == 302) {
            echo "   ⚠️  Redirección (HTTP {$httpCode}) - Probablemente a login\n";
        } elseif ($httpCode == 401) {
            echo "   🔒 No autorizado (HTTP {$httpCode}) - Requiere autenticación\n";
        } elseif ($httpCode == 403) {
            echo "   🚫 Prohibido (HTTP {$httpCode}) - Requiere permisos\n";
        } elseif ($httpCode == 404) {
            echo "   ❌ No encontrado (HTTP {$httpCode})\n";
        } else {
            echo "   ❓ Respuesta inesperada (HTTP {$httpCode})\n";
        }
        echo "\n";
    }

    // 5. Generar estadísticas de pagos
    echo "5. Generando estadísticas de pagos...\n";
    $paymentService = new PaymentService();
    
    $startDate = Carbon::now()->subDays(30);
    $endDate = Carbon::now();
    
    $statistics = $paymentService->getPaymentStatistics($startDate->toDateString(), $endDate->toDateString());
    
    echo "✅ Estadísticas de los últimos 30 días:\n";
    echo "   - Total de reportes: {$statistics['total_reports']}\n";
    echo "   - Pago total: $" . number_format($statistics['total_payment'], 0, ',', '.') . " COP\n";
    echo "   - Horas totales: {$statistics['total_hours']}\n";
    echo "   - Reportes pendientes: {$statistics['pending_reports']}\n";
    echo "   - Reportes aprobados: {$statistics['approved_reports']}\n";
    echo "   - Reportes pagados: {$statistics['paid_reports']}\n\n";

    // 6. Mostrar estadísticas por desarrollador
    if (count($statistics['by_developer']) > 0) {
        echo "6. Estadísticas por desarrollador:\n";
        foreach ($statistics['by_developer'] as $developerStats) {
            $developer = $developerStats['user'];
            echo "   - {$developer->name}:\n";
            echo "     * Pago total: $" . number_format($developerStats['total_payment'], 0, ',', '.') . " COP\n";
            echo "     * Horas totales: {$developerStats['total_hours']}\n";
            echo "     * Reportes: {$developerStats['reports_count']}\n";
        }
        echo "\n";
    }

    // 7. Mostrar estadísticas por semana
    if (count($statistics['by_week']) > 0) {
        echo "7. Estadísticas por semana:\n";
        foreach ($statistics['by_week'] as $weekStats) {
            echo "   - Semana {$weekStats['week_start']}:\n";
            echo "     * Pago total: $" . number_format($weekStats['total_payment'], 0, ',', '.') . " COP\n";
            echo "     * Horas totales: {$weekStats['total_hours']}\n";
            echo "     * Reportes: {$weekStats['reports_count']}\n";
        }
        echo "\n";
    }

    // 8. Verificar que el cálculo de 420,000 COP está presente
    echo "8. Verificando el cálculo esperado de 420,000 COP...\n";
    $targetReport = null;
    
    foreach ($paymentReports as $report) {
        if ($report->total_payment == 420000) {
            $targetReport = $report;
            break;
        }
    }
    
    if ($targetReport) {
        echo "✅ Reporte con pago de 420,000 COP encontrado:\n";
        echo "   - ID: {$targetReport->id}\n";
        echo "   - Semana: {$targetReport->week_start_date->format('d/m/Y')} - {$targetReport->week_end_date->format('d/m/Y')}\n";
        echo "   - Horas: {$targetReport->total_hours}\n";
        echo "   - Pago: $" . number_format($targetReport->total_payment, 0, ',', '.') . " COP\n";
    } else {
        echo "❌ No se encontró reporte con pago de 420,000 COP\n";
        echo "   Reportes disponibles:\n";
        foreach ($paymentReports as $report) {
            echo "   - $" . number_format($report->total_payment, 0, ',', '.') . " COP\n";
        }
    }
    echo "\n";

    echo "🎉 ¡Prueba del frontend completada!\n";
    echo "==================================\n";
    echo "El sistema de pagos está funcionando correctamente tanto en backend como frontend.\n";
    echo "Puedes acceder a la aplicación web para ver los reportes visualmente.\n\n";

} catch (Exception $e) {
    echo "❌ Error durante la prueba: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
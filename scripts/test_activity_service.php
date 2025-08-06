<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== PRUEBA DEL SERVICIO DE TRACKING DE ACTIVIDAD ===\n\n";

try {
    // Inicializar Laravel
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "1. Probando el servicio de tracking...\n";
    
    $service = new App\Services\DeveloperActivityTrackingService();
    
    echo "2. Obteniendo información de zona horaria...\n";
    $timezoneInfo = $service->getTimeZoneInfo();
    
    echo "✓ Información de zona horaria obtenida:\n";
    echo "  - Colombia: {$timezoneInfo['colombia_time']}\n";
    echo "  - Italy: {$timezoneInfo['italy_time']}\n";
    echo "  - UTC: {$timezoneInfo['utc_time']}\n";
    echo "  - Diferencia: {$timezoneInfo['time_difference']}\n";
    
    echo "\n3. Obteniendo estadísticas del equipo...\n";
    $teamOverview = $service->getTeamActivityOverview();
    
    echo "✓ Estadísticas del equipo obtenidas:\n";
    echo "  - Total developers: {$teamOverview['total_developers']}\n";
    echo "  - Preferred work time: {$teamOverview['team_preferred_work_time']}\n";
    echo "  - Activity by period:\n";
    foreach ($teamOverview['team_activity_by_period'] as $period => $count) {
        echo "    - {$period}: {$count}\n";
    }
    
    echo "\n4. Obteniendo patrones diarios...\n";
    $dailyPatterns = $service->getDailyActivityPatterns();
    
    echo "✓ Patrones diarios obtenidos:\n";
    echo "  - Días con datos: " . count($dailyPatterns) . "\n";
    
    echo "\n✅ Todas las pruebas pasaron exitosamente!\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
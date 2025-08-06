<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Bug;
use App\Services\BugTimeTrackingService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Starting Bug Work ===\n\n";

try {
    // Buscar un bug en progreso que no esté trabajando
    $bug = Bug::where('status', 'in progress')
              ->where('is_working', false)
              ->first();
    
    if (!$bug) {
        echo "No hay bugs disponibles para iniciar trabajo\n";
        exit(1);
    }
    
    echo "Bug seleccionado: {$bug->title}\n";
    echo "ID: {$bug->id}\n";
    echo "Status: {$bug->status}\n";
    echo "Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n";
    echo "Total Time: {$bug->total_time_seconds} seconds\n\n";
    
    // Iniciar trabajo
    $bugTimeTrackingService = new BugTimeTrackingService();
    
    echo "Iniciando trabajo...\n";
    $result = $bugTimeTrackingService->startWork($bug);
    
    echo "Resultado: " . $result['message'] . "\n";
    
    // Refrescar el bug
    $bug->refresh();
    
    echo "\nEstado después de iniciar:\n";
    echo "Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n";
    echo "Work Started At: {$bug->work_started_at}\n";
    echo "Total Time: {$bug->total_time_seconds} seconds\n";
    
    echo "\n✅ Bug iniciado correctamente\n";
    echo "Ahora puedes ir al frontend y ver:\n";
    echo "- El contador con segundos actualizándose en tiempo real\n";
    echo "- El botón 'Finalizar' visible (solo cuando está trabajando)\n";
    echo "- El botón 'Pausar' visible\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== End ===\n"; 
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Bug;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Bug Card Updates ===\n\n";

try {
    // Buscar bugs en progreso
    $inProgressBugs = Bug::where('status', 'in progress')->get();
    
    echo "Bugs en progreso encontrados: " . $inProgressBugs->count() . "\n\n";
    
    foreach ($inProgressBugs as $bug) {
        echo "=== Bug: {$bug->title} ===\n";
        echo "ID: {$bug->id}\n";
        echo "Status: {$bug->status}\n";
        echo "Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n";
        echo "Total Time: {$bug->total_time_seconds} seconds\n";
        echo "Work Started At: {$bug->work_started_at}\n";
        
        // Determinar qué botones deberían mostrarse
        echo "\nBotones que deberían mostrarse:\n";
        
        if (!$bug->user_id && $bug->status === 'new') {
            echo "- Auto-asignar\n";
        }
        
        if ($bug->user_id && ($bug->status === 'assigned' || ($bug->status === 'in progress' && !$bug->is_working && $bug->total_time_seconds === 0)) && !$bug->is_working) {
            echo "- Iniciar\n";
        }
        
        if ($bug->is_working) {
            echo "- Pausar\n";
            echo "- Finalizar (NUEVO: solo cuando está trabajando)\n";
        }
        
        if ($bug->status === 'in progress' && !$bug->is_working && $bug->total_time_seconds > 0) {
            echo "- Reanudar\n";
        }
        
        echo "- Ver detalles\n";
        
        echo "\n---\n\n";
    }
    
    // Mostrar resumen de estados
    echo "=== Summary ===\n";
    $statusCounts = Bug::selectRaw('status, count(*) as count')->groupBy('status')->get();
    foreach ($statusCounts as $status) {
        echo "{$status->status}: {$status->count}\n";
    }
    
    $workingBugs = Bug::where('is_working', true)->count();
    echo "\nBugs trabajando actualmente: {$workingBugs}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== End ===\n"; 
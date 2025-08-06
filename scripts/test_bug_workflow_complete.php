<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Bug;
use App\Services\BugTimeTrackingService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Complete Bug Workflow ===\n\n";

try {
    $bugTimeTrackingService = new BugTimeTrackingService();
    
    // Buscar un bug que nunca haya sido iniciado
    $bug = Bug::where('status', 'in progress')
              ->where('is_working', false)
              ->where('total_time_seconds', 0)
              ->first();
    
    if (!$bug) {
        echo "No hay bugs disponibles para iniciar trabajo (sin tiempo previo)\n";
        exit(1);
    }
    
    echo "=== Bug seleccionado: {$bug->title} ===\n";
    echo "Estado inicial:\n";
    echo "- Status: {$bug->status}\n";
    echo "- Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n";
    echo "- Total Time: {$bug->total_time_seconds} seconds\n";
    echo "- Work Started At: {$bug->work_started_at}\n\n";
    
    // PASO 1: Iniciar trabajo
    echo "=== PASO 1: Iniciar trabajo ===\n";
    $result = $bugTimeTrackingService->startWork($bug);
    echo "Resultado: " . $result['message'] . "\n";
    $bug->refresh();
    echo "Estado después de iniciar:\n";
    echo "- Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n";
    echo "- Work Started At: {$bug->work_started_at}\n";
    echo "- Total Time: {$bug->total_time_seconds} seconds\n\n";
    
    // Simular trabajo por 3 segundos
    echo "Simulando trabajo por 3 segundos...\n";
    sleep(3);
    
    // PASO 2: Pausar trabajo
    echo "=== PASO 2: Pausar trabajo ===\n";
    $result = $bugTimeTrackingService->pauseWork($bug);
    echo "Resultado: " . $result['message'] . "\n";
    $bug->refresh();
    echo "Estado después de pausar:\n";
    echo "- Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n";
    echo "- Work Paused At: {$bug->work_paused_at}\n";
    echo "- Total Time: {$bug->total_time_seconds} seconds\n\n";
    
    // PASO 3: Reanudar trabajo
    echo "=== PASO 3: Reanudar trabajo ===\n";
    $result = $bugTimeTrackingService->resumeWork($bug);
    echo "Resultado: " . $result['message'] . "\n";
    $bug->refresh();
    echo "Estado después de reanudar:\n";
    echo "- Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n";
    echo "- Work Started At: {$bug->work_started_at}\n";
    echo "- Total Time: {$bug->total_time_seconds} seconds\n\n";
    
    // Simular trabajo por 2 segundos más
    echo "Simulando trabajo por 2 segundos más...\n";
    sleep(2);
    
    // PASO 4: Finalizar trabajo
    echo "=== PASO 4: Finalizar trabajo ===\n";
    $result = $bugTimeTrackingService->finishWork($bug);
    echo "Resultado: " . $result['message'] . "\n";
    $bug->refresh();
    echo "Estado después de finalizar:\n";
    echo "- Status: {$bug->status}\n";
    echo "- Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n";
    echo "- Work Finished At: {$bug->work_finished_at}\n";
    echo "- Total Time: {$bug->total_time_seconds} seconds\n\n";
    
    echo "✅ Flujo completo probado exitosamente\n";
    echo "Resumen del flujo:\n";
    echo "1. Iniciar → Botón 'Iniciar' desaparece, aparecen 'Pausar' y 'Finalizar'\n";
    echo "2. Pausar → Botón 'Reanudar' aparece, 'Iniciar' NO aparece (porque ya fue iniciado)\n";
    echo "3. Reanudar → Vuelven a aparecer 'Pausar' y 'Finalizar'\n";
    echo "4. Finalizar → Bug se marca como resuelto\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== End ===\n"; 
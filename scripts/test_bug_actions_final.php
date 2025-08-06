<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Bug;
use App\Models\User;
use App\Services\BugTimeTrackingService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Final Test: Bug Actions ===\n\n";

try {
    // Buscar el usuario
    $user = User::where('email', 'carmen.ruiz79@test.com')->first();
    if (!$user) {
        echo "Error: User not found\n";
        exit(1);
    }
    
    echo "User found: {$user->name} ({$user->email})\n\n";
    
    // Buscar los bugs mencionados
    $bugs = Bug::whereIn('title', [
        'Validación de email falla',
        'Error 404 en página de contacto'
    ])->get();
    
    echo "Found " . $bugs->count() . " bugs to test\n\n";
    
    $bugTimeTrackingService = new BugTimeTrackingService();
    
    foreach ($bugs as $bug) {
        echo "=== Testing Bug: {$bug->title} ===\n";
        echo "Status: {$bug->status}\n";
        echo "Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n";
        echo "Total Time: {$bug->total_time_seconds} seconds\n";
        echo "Work Started At: {$bug->work_started_at}\n\n";
        
        // Probar el flujo completo usando el servicio directamente
        echo "1. Testing START work...\n";
        try {
            if (!$bug->is_working) {
                $result = $bugTimeTrackingService->startWork($bug);
                echo "   Success: " . $result['message'] . "\n";
                $bug->refresh();
                echo "   Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n";
            } else {
                echo "   Skipped: Bug is already working\n";
            }
        } catch (Exception $e) {
            echo "   Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n2. Testing PAUSE work...\n";
        try {
            if ($bug->is_working) {
                $result = $bugTimeTrackingService->pauseWork($bug);
                echo "   Success: " . $result['message'] . "\n";
                $bug->refresh();
                echo "   Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n";
                echo "   Total Time: {$bug->total_time_seconds} seconds\n";
            } else {
                echo "   Skipped: Bug is not working\n";
            }
        } catch (Exception $e) {
            echo "   Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n3. Testing RESUME work...\n";
        try {
            if (!$bug->is_working) {
                $result = $bugTimeTrackingService->resumeWork($bug);
                echo "   Success: " . $result['message'] . "\n";
                $bug->refresh();
                echo "   Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n";
            } else {
                echo "   Skipped: Bug is already working\n";
            }
        } catch (Exception $e) {
            echo "   Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n4. Testing FINISH work...\n";
        try {
            $result = $bugTimeTrackingService->finishWork($bug);
            echo "   Success: " . $result['message'] . "\n";
            $bug->refresh();
            echo "   Status: {$bug->status}\n";
            echo "   Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n";
            echo "   Total Time: {$bug->total_time_seconds} seconds\n";
        } catch (Exception $e) {
            echo "   Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n--- End of bug test ---\n\n";
    }
    
    // Mostrar resumen final
    echo "=== Summary ===\n";
    $totalBugs = Bug::count();
    $workingBugs = Bug::where('is_working', true)->count();
    $resolvedBugs = Bug::where('status', 'resolved')->count();
    
    echo "Total bugs in system: {$totalBugs}\n";
    echo "Currently working: {$workingBugs}\n";
    echo "Resolved: {$resolvedBugs}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test completed ===\n"; 
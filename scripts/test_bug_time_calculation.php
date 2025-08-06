<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Bug;
use App\Models\User;
use App\Services\BugTimeTrackingService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Bug Time Calculation ===\n\n";

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
        echo "ID: {$bug->id}\n";
        echo "User ID: {$bug->user_id}\n";
        echo "Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n";
        echo "Work Started At: {$bug->work_started_at}\n";
        echo "Work Paused At: {$bug->work_paused_at}\n";
        echo "Total Time: {$bug->total_time_seconds} seconds\n\n";
        
        // Probar el cálculo de tiempo actual
        $currentTime = $bugTimeTrackingService->getCurrentWorkTime($bug);
        echo "Current work time: {$currentTime} seconds\n";
        
        // Verificar que el tiempo total sea un entero válido
        $totalTime = (int) ($bug->total_time_seconds ?? 0);
        echo "Total time (as integer): {$totalTime} seconds\n";
        
        if ($totalTime < 0) {
            echo "WARNING: Total time is negative! This should be fixed.\n";
        }
        
        // Verificar que work_started_at sea una fecha válida
        if ($bug->work_started_at) {
            $startTime = $bug->work_started_at;
            $now = now();
            
            if ($startTime > $now) {
                echo "WARNING: Work started at ({$startTime}) is in the future!\n";
            } else {
                $diff = $now->diffInSeconds($startTime);
                echo "Time since work started: {$diff} seconds\n";
            }
        }
        
        echo "\n--- End of bug test ---\n\n";
    }
    
    // Probar con un bug que tenga datos problemáticos
    echo "=== Testing with problematic data ===\n";
    
    // Crear un bug de prueba con datos problemáticos
    $testBug = Bug::create([
        'title' => 'Test Bug for Time Calculation',
        'description' => 'Test bug to verify time calculation fixes',
        'importance' => 'medium',
        'bug_type' => 'backend',
        'environment' => 'test',
        'browser_info' => 'test',
        'os_info' => 'test',
        'steps_to_reproduce' => 'test',
        'expected_behavior' => 'test',
        'actual_behavior' => 'test',
        'reproducibility' => 'always',
        'severity' => 'medium',
        'sprint_id' => $bugs->first()->sprint_id,
        'project_id' => $bugs->first()->project_id,
        'user_id' => $user->id,
        'status' => 'new',
        'is_working' => true,
        'work_started_at' => now()->addHours(1), // Fecha futura para probar el manejo
        'total_time_seconds' => -100 // Valor negativo para probar el manejo
    ]);
    
    echo "Created test bug with problematic data:\n";
    echo "Work started at: {$testBug->work_started_at}\n";
    echo "Total time: {$testBug->total_time_seconds}\n";
    
    // Probar el cálculo de tiempo
    $currentTime = $bugTimeTrackingService->getCurrentWorkTime($testBug);
    echo "Current work time: {$currentTime} seconds\n";
    
    // Probar pausar el trabajo
    echo "\nTesting pause work...\n";
    try {
        $result = $bugTimeTrackingService->pauseWork($testBug);
        echo "Pause result: " . ($result['success'] ? 'Success' : 'Failed') . "\n";
        echo "Message: " . $result['message'] . "\n";
        
        $testBug->refresh();
        echo "New total time: {$testBug->total_time_seconds} seconds\n";
        echo "Is working: " . ($testBug->is_working ? 'Yes' : 'No') . "\n";
        
    } catch (Exception $e) {
        echo "Error pausing work: " . $e->getMessage() . "\n";
    }
    
    // Limpiar el bug de prueba
    $testBug->delete();
    echo "\nTest bug deleted.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test completed ===\n"; 
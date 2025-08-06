<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Bug;
use App\Models\User;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Bug Frontend Simulation ===\n\n";

try {
    // Buscar el usuario
    $user = User::where('email', 'carmen.ruiz79@test.com')->first();
    if (!$user) {
        echo "Error: User not found\n";
        exit(1);
    }
    
    echo "User found: {$user->name} ({$user->email})\n";
    
    // Buscar los bugs mencionados
    $bugs = Bug::whereIn('title', [
        'Validación de email falla',
        'Error 404 en página de contacto'
    ])->get();
    
    echo "Found " . $bugs->count() . " bugs to test\n\n";
    
    foreach ($bugs as $bug) {
        echo "=== Testing Bug: {$bug->title} ===\n";
        echo "Status: {$bug->status}\n";
        echo "ID: {$bug->id}\n";
        echo "User ID: {$bug->user_id}\n";
        echo "Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n\n";
        
        // Simular el flujo completo como lo haría el frontend
        
        // 1. Primero, asignar el bug al usuario si no está asignado
        if (!$bug->user_id) {
            echo "1. Self-assigning bug...\n";
            $assignResponse = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("http://127.0.0.1:8000/bugs/{$bug->id}/self-assign");
            
            echo "   Status: " . $assignResponse->status() . "\n";
            echo "   Response: " . $assignResponse->body() . "\n\n";
        }
        
        // 2. Iniciar trabajo
        echo "2. Starting work...\n";
        $startResponse = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("http://127.0.0.1:8000/bugs/{$bug->id}/start-work");
        
        echo "   Status: " . $startResponse->status() . "\n";
        echo "   Response: " . $startResponse->body() . "\n\n";
        
        // Esperar un momento para simular trabajo
        sleep(2);
        
        // 3. Pausar trabajo
        echo "3. Pausing work...\n";
        $pauseResponse = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("http://127.0.0.1:8000/bugs/{$bug->id}/pause-work");
        
        echo "   Status: " . $pauseResponse->status() . "\n";
        echo "   Response: " . $pauseResponse->body() . "\n\n";
        
        // Esperar un momento
        sleep(1);
        
        // 4. Reanudar trabajo
        echo "4. Resuming work...\n";
        $resumeResponse = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("http://127.0.0.1:8000/bugs/{$bug->id}/resume-work");
        
        echo "   Status: " . $resumeResponse->status() . "\n";
        echo "   Response: " . $resumeResponse->body() . "\n\n";
        
        // Esperar un momento
        sleep(2);
        
        // 5. Finalizar trabajo
        echo "5. Finishing work...\n";
        $finishResponse = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("http://127.0.0.1:8000/bugs/{$bug->id}/finish-work", [
            'actual_hours' => 2
        ]);
        
        echo "   Status: " . $finishResponse->status() . "\n";
        echo "   Response: " . $finishResponse->body() . "\n\n";
        
        // 6. Verificar el estado final del bug
        $updatedBug = Bug::find($bug->id);
        echo "6. Final bug status:\n";
        echo "   Status: {$updatedBug->status}\n";
        echo "   Is Working: " . ($updatedBug->is_working ? 'Yes' : 'No') . "\n";
        echo "   Total Time: {$updatedBug->total_time_seconds} seconds\n";
        echo "   Work Started At: {$updatedBug->work_started_at}\n";
        echo "   Work Paused At: {$updatedBug->work_paused_at}\n";
        echo "   Work Finished At: {$updatedBug->work_finished_at}\n\n";
        
        echo "--- End of bug test ---\n\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "=== Test completed ===\n"; 
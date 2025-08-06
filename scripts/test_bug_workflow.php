<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Bug;
use App\Models\User;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Bug Workflow (Start, Pause, Resume, Finish) ===\n\n";

try {
    // Buscar el usuario (usar uno existente)
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
        echo "ID: {$bug->id}\n\n";
        
        // Simular login del usuario
        echo "1. Simulating login...\n";
        
        // Obtener CSRF token
        $response = Http::get('http://127.0.0.1:8000/bugs');
        $csrfToken = null;
        
        if ($response->successful()) {
            // Extraer CSRF token del HTML
            preg_match('/<meta name="csrf-token" content="([^"]+)"/', $response->body(), $matches);
            if (isset($matches[1])) {
                $csrfToken = $matches[1];
                echo "CSRF Token obtained: " . substr($csrfToken, 0, 20) . "...\n";
            }
        }
        
        if (!$csrfToken) {
            echo "Warning: Could not get CSRF token\n";
        }
        
        // Headers para las peticiones
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        
        if ($csrfToken) {
            $headers['X-CSRF-TOKEN'] = $csrfToken;
        }
        
        // 2. Intentar iniciar el bug
        echo "2. Testing START bug...\n";
        $startResponse = Http::withHeaders($headers)
            ->post("http://127.0.0.1:8000/bugs/{$bug->id}/start-work", [
                'user_id' => $user->id
            ]);
        
        echo "   Status: " . $startResponse->status() . "\n";
        echo "   Response: " . $startResponse->body() . "\n\n";
        
        // 3. Intentar pausar el bug
        echo "3. Testing PAUSE bug...\n";
        $pauseResponse = Http::withHeaders($headers)
            ->post("http://127.0.0.1:8000/bugs/{$bug->id}/pause-work", [
                'user_id' => $user->id
            ]);
        
        echo "   Status: " . $pauseResponse->status() . "\n";
        echo "   Response: " . $pauseResponse->body() . "\n\n";
        
        // 4. Intentar reanudar el bug
        echo "4. Testing RESUME bug...\n";
        $resumeResponse = Http::withHeaders($headers)
            ->post("http://127.0.0.1:8000/bugs/{$bug->id}/resume-work", [
                'user_id' => $user->id
            ]);
        
        echo "   Status: " . $resumeResponse->status() . "\n";
        echo "   Response: " . $resumeResponse->body() . "\n\n";
        
        // 5. Intentar finalizar el bug
        echo "5. Testing FINISH bug...\n";
        $finishResponse = Http::withHeaders($headers)
            ->post("http://127.0.0.1:8000/bugs/{$bug->id}/finish-work", [
                'user_id' => $user->id,
                'actual_hours' => 5
            ]);
        
        echo "   Status: " . $finishResponse->status() . "\n";
        echo "   Response: " . $finishResponse->body() . "\n\n";
        
        echo "--- End of bug test ---\n\n";
    }
    
    // También probar con un bug que no existe para ver el manejo de errores
    echo "=== Testing with non-existent bug ===\n";
    $fakeBugId = '00000000-0000-0000-0000-000000000000';
    
    $errorResponse = Http::withHeaders($headers)
        ->post("http://127.0.0.1:8000/bugs/{$fakeBugId}/start-work", [
            'user_id' => $user->id
        ]);
    
    echo "Status: " . $errorResponse->status() . "\n";
    echo "Response: " . $errorResponse->body() . "\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "=== Test completed ===\n"; 
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Task Search API ===\n\n";

try {
    // Get a user to authenticate with
    $user = User::first();
    if (!$user) {
        echo "No users found in the system\n";
        exit(1);
    }
    
    echo "Using user: {$user->name} ({$user->email})\n\n";
    
    // Test search queries
    $searchQueries = [
        'test',
        'feature',
        'bug',
        'frontend',
        'backend',
        'database'
    ];
    
    foreach ($searchQueries as $query) {
        echo "Searching for: '{$query}'\n";
        
        $url = "http://127.0.0.1:8000/api/tasks/search?q=" . urlencode($query);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        // Add authentication headers (simulate logged in user)
        curl_setopt($ch, CURLOPT_COOKIE, "laravel_session=test_session");
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "HTTP Code: {$httpCode}\n";
        
        if ($httpCode === 200) {
            $tasks = json_decode($response, true);
            if (is_array($tasks)) {
                echo "Found " . count($tasks) . " tasks:\n";
                
                foreach ($tasks as $task) {
                    echo "  - {$task['name']} (Status: {$task['status']}, Priority: {$task['priority']})\n";
                }
            } else {
                echo "Response: " . $response . "\n";
            }
        } else {
            echo "Error: " . $response . "\n";
        }
        
        echo "\n";
    }
    
    echo "✅ Task search API test completed\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== End ===\n"; 
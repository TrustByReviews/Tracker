<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Bug;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Bugs Status Check ===\n\n";

try {
    $bugs = Bug::all(['id', 'title', 'status', 'is_working', 'user_id', 'total_time_seconds']);
    
    echo "Total bugs in system: " . $bugs->count() . "\n\n";
    
    foreach ($bugs as $bug) {
        echo "ID: {$bug->id}\n";
        echo "Title: {$bug->title}\n";
        echo "Status: {$bug->status}\n";
        echo "Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n";
        echo "User ID: {$bug->user_id}\n";
        echo "Total Time: {$bug->total_time_seconds} seconds\n";
        echo "---\n";
    }
    
    // Contar por estado
    echo "\n=== Summary by Status ===\n";
    $statusCounts = $bugs->groupBy('status')->map->count();
    foreach ($statusCounts as $status => $count) {
        echo "{$status}: {$count}\n";
    }
    
    // Contar bugs trabajando
    echo "\n=== Working Bugs ===\n";
    $workingBugs = $bugs->where('is_working', true);
    echo "Currently working: " . $workingBugs->count() . "\n";
    
    foreach ($workingBugs as $bug) {
        echo "- {$bug->title} (Status: {$bug->status})\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== End ===\n"; 
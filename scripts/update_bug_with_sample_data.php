<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Bug;
use App\Models\Task;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Update Bug with Sample Data ===\n\n";

try {
    $bug = Bug::first();
    if (!$bug) {
        echo "No bugs found in the system\n";
        exit(1);
    }
    
    echo "Updating bug: {$bug->title} (ID: {$bug->id})\n\n";
    
    // Get a task to relate to this bug
    $relatedTask = Task::first();
    $relatedTaskId = $relatedTask ? $relatedTask->id : null;
    
    $sampleData = [
        'bug_type' => 'frontend',
        'importance' => 'high',
        'severity' => 'critical',
        'environment' => 'production',
        'browser_info' => 'Chrome 120.0.0.0',
        'os_info' => 'Windows 11',
        'steps_to_reproduce' => "1. Navigate to the login page\n2. Enter invalid email format\n3. Click on 'Login' button\n4. Observe that no validation error appears\n5. System accepts invalid email format",
        'expected_behavior' => 'The system should display a validation error message when an invalid email format is entered, preventing the user from proceeding with login.',
        'actual_behavior' => 'The system accepts any input in the email field without validation, allowing users to proceed with invalid email formats.',
        'reproducibility' => 'always',
        'related_task_id' => $relatedTaskId
    ];
    
    $bug->update($sampleData);
    
    echo "✅ Bug updated successfully\n\n";
    echo "Added data:\n";
    echo "- Bug Type: {$sampleData['bug_type']}\n";
    echo "- Importance: {$sampleData['importance']}\n";
    echo "- Severity: {$sampleData['severity']}\n";
    echo "- Environment: {$sampleData['environment']}\n";
    echo "- Browser: {$sampleData['browser_info']}\n";
    echo "- OS: {$sampleData['os_info']}\n";
    echo "- Steps to Reproduce: " . substr($sampleData['steps_to_reproduce'], 0, 50) . "...\n";
    echo "- Expected Behavior: " . substr($sampleData['expected_behavior'], 0, 50) . "...\n";
    echo "- Actual Behavior: " . substr($sampleData['actual_behavior'], 0, 50) . "...\n";
    echo "- Reproducibility: {$sampleData['reproducibility']}\n";
    echo "- Related Task ID: " . ($relatedTaskId ? $relatedTaskId : 'None') . "\n";
    
    echo "\nURL to test: http://127.0.0.1:8000/bugs/{$bug->id}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== End ===\n"; 
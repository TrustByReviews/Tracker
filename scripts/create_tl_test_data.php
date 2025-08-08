<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\Bug;

echo "Creating test data for Team Leader...\n";

// Find users
$tlUser = User::where('email', 'tl@test.com')->first();
$qaUser = User::where('email', 'qa@test.com')->first();
$devUser = User::where('email', 'dev@test.com')->first();

if (!$tlUser || !$qaUser || !$devUser) {
    echo "Required users not found!\n";
    exit(1);
}

// Get projects assigned to TL
$projects = $tlUser->projects;
if ($projects->isEmpty()) {
    echo "No projects assigned to TL!\n";
    exit(1);
}

$project = $projects->first();
$sprint = $project->sprints()->where('start_date', '<=', now())->where('end_date', '>=', now())->first();

if (!$sprint) {
    echo "No active sprint found!\n";
    exit(1);
}

echo "Using project: {$project->name}\n";
echo "Using sprint: {$sprint->name}\n";

// Create tasks in different states
$tasks = [
    [
        'name' => 'Implement user authentication',
        'description' => 'Create login and registration system',
        'status' => 'done',
        'qa_status' => 'approved',
        'story_points' => 8,
        'user_id' => $devUser->id,
        'qa_assigned_to' => $qaUser->id,
        'qa_reviewed_by' => $qaUser->id,
        'qa_reviewed_at' => now()->subHours(2),
        'qa_notes' => 'Authentication system working correctly'
    ],
    [
        'name' => 'Design database schema',
        'description' => 'Create database structure for the application',
        'status' => 'done',
        'qa_status' => 'approved',
        'story_points' => 5,
        'user_id' => $devUser->id,
        'qa_assigned_to' => $qaUser->id,
        'qa_reviewed_by' => $qaUser->id,
        'qa_reviewed_at' => now()->subHours(1),
        'qa_notes' => 'Schema design is optimal'
    ],
    [
        'name' => 'Create API endpoints',
        'description' => 'Implement REST API for the application',
        'status' => 'done',
        'qa_status' => 'rejected',
        'story_points' => 13,
        'user_id' => $devUser->id,
        'qa_assigned_to' => $qaUser->id,
        'qa_reviewed_by' => $qaUser->id,
        'qa_reviewed_at' => now()->subHours(3),
        'qa_rejection_reason' => 'Missing error handling and validation'
    ]
];

foreach ($tasks as $taskData) {
    $task = Task::create(array_merge($taskData, [
        'project_id' => $project->id,
        'sprint_id' => $sprint->id
    ]));
    
    echo "Created task: {$task->name} (QA Status: {$task->qa_status})\n";
}

// Create bugs in different states
$bugs = [
    [
        'title' => 'Login button not responsive',
        'description' => 'The login button does not respond to clicks on mobile devices',
        'status' => 'resolved',
        'qa_status' => 'approved',
        'importance' => 'high',
        'severity' => 'medium',
        'bug_type' => 'ui',
        'user_id' => $devUser->id,
        'qa_assigned_to' => $qaUser->id,
        'qa_reviewed_by' => $qaUser->id,
        'qa_reviewed_at' => now()->subHours(4),
        'qa_notes' => 'Bug fixed successfully'
    ],
    [
        'title' => 'Database connection timeout',
        'description' => 'Users experience timeout when loading large datasets',
        'status' => 'resolved',
        'qa_status' => 'rejected',
        'importance' => 'critical',
        'severity' => 'high',
        'bug_type' => 'performance',
        'user_id' => $devUser->id,
        'qa_assigned_to' => $qaUser->id,
        'qa_reviewed_by' => $qaUser->id,
        'qa_reviewed_at' => now()->subHours(5),
        'qa_rejection_reason' => 'Timeout still occurs under heavy load'
    ]
];

foreach ($bugs as $bugData) {
    $bug = Bug::create(array_merge($bugData, [
        'project_id' => $project->id,
        'sprint_id' => $sprint->id
    ]));
    
    echo "Created bug: {$bug->title} (QA Status: {$bug->qa_status})\n";
}

echo "Test data created successfully!\n";
echo "Team Leader should now see:\n";
echo "- Tasks approved by QA (ready for TL review)\n";
echo "- Tasks rejected by QA (with reasons)\n";
echo "- Bugs approved by QA (ready for TL review)\n";
echo "- Bugs rejected by QA (with reasons)\n";

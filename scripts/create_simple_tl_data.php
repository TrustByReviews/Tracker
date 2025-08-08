<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\Bug;

echo "Creating simple test data for Team Leader...\n";

// Find users
$tlUser = User::where('email', 'tl@test.com')->first();
$devUser = User::where('email', 'dev@test.com')->first();

if (!$tlUser || !$devUser) {
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
$sprint = $project->sprints()->first();

if (!$sprint) {
    echo "No sprint found!\n";
    exit(1);
}

echo "Using project: {$project->name}\n";
echo "Using sprint: {$sprint->name}\n";

// Create basic tasks
$tasks = [
    [
        'name' => 'Implement user authentication',
        'description' => 'Create login and registration system',
        'status' => 'done',
        'story_points' => 8,
        'user_id' => $devUser->id
    ],
    [
        'name' => 'Design database schema',
        'description' => 'Create database structure for the application',
        'status' => 'done',
        'story_points' => 5,
        'user_id' => $devUser->id
    ],
    [
        'name' => 'Create API endpoints',
        'description' => 'Implement REST API for the application',
        'status' => 'in progress',
        'story_points' => 13,
        'user_id' => $devUser->id
    ]
];

foreach ($tasks as $taskData) {
    $task = Task::create(array_merge($taskData, [
        'project_id' => $project->id,
        'sprint_id' => $sprint->id
    ]));
    
    echo "Created task: {$task->name} (Status: {$task->status})\n";
}

// Create basic bugs
$bugs = [
    [
        'title' => 'Login button not responsive',
        'description' => 'The login button does not respond to clicks on mobile devices',
        'status' => 'resolved',
        'importance' => 'high',
        'severity' => 'medium',
        'bug_type' => 'ui',
        'user_id' => $devUser->id
    ],
    [
        'title' => 'Database connection timeout',
        'description' => 'Users experience timeout when loading large datasets',
        'status' => 'in progress',
        'importance' => 'critical',
        'severity' => 'high',
        'bug_type' => 'performance',
        'user_id' => $devUser->id
    ]
];

foreach ($bugs as $bugData) {
    $bug = Bug::create(array_merge($bugData, [
        'project_id' => $project->id,
        'sprint_id' => $sprint->id
    ]));
    
    echo "Created bug: {$bug->title} (Status: {$bug->status})\n";
}

echo "Simple test data created successfully!\n";
echo "Team Leader should now see:\n";
echo "- Projects assigned to them\n";
echo "- Sprints in those projects\n";
echo "- Tasks and bugs in those sprints\n";

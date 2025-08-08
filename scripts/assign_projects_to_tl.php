<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\Sprint;

echo "Assigning projects to Team Leader...\n";

// Find the TL user
$user = User::where('email', 'tl@test.com')->first();
if (!$user) {
    echo "TL user not found!\n";
    exit(1);
}

echo "Found user: {$user->name} (ID: {$user->id})\n";

// Get or create some projects
$projects = [
    [
        'name' => 'E-commerce Platform',
        'description' => 'Development of a modern e-commerce platform',
        'status' => 'active',
        'created_by' => $user->id
    ],
    [
        'name' => 'Mobile App',
        'description' => 'Cross-platform mobile application',
        'status' => 'active',
        'created_by' => $user->id
    ],
    [
        'name' => 'Admin Dashboard',
        'description' => 'Administrative dashboard for internal use',
        'status' => 'active',
        'created_by' => $user->id
    ]
];

foreach ($projects as $projectData) {
    $project = Project::firstOrCreate(
        ['name' => $projectData['name']],
        $projectData
    );
    
    // Assign the project to the TL
    $project->users()->syncWithoutDetaching([$user->id]);
    
    echo "Project '{$project->name}' assigned to TL\n";
    
    // Create some sprints for each project
    $sprints = [
        [
            'name' => 'Sprint 1',
            'goal' => 'Initial setup and core features',
            'start_date' => now()->subDays(30),
            'end_date' => now()->addDays(14),
            'status' => 'active'
        ],
        [
            'name' => 'Sprint 2',
            'goal' => 'Advanced features and optimization',
            'start_date' => now()->addDays(15),
            'end_date' => now()->addDays(29),
            'status' => 'planned'
        ]
    ];
    
    foreach ($sprints as $sprintData) {
        $sprint = Sprint::firstOrCreate(
            [
                'name' => $sprintData['name'],
                'project_id' => $project->id
            ],
            array_merge($sprintData, ['project_id' => $project->id])
        );
        
        echo "  - Sprint '{$sprint->name}' created\n";
    }
}

echo "Projects and sprints assigned successfully!\n";

<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Role;
use App\Models\Permission;

// Lista de permisos necesarios para Team Leader
$permissions = [
    [
        'name' => 'team-leader.dashboard',
        'display_name' => 'Access Team Leader Dashboard',
        'description' => 'Can access team leader dashboard'
    ],
    [
        'name' => 'team-leader.projects',
        'display_name' => 'Access Team Leader Projects',
        'description' => 'Can access team leader projects'
    ],
    [
        'name' => 'team-leader.sprints',
        'display_name' => 'Access Team Leader Sprints',
        'description' => 'Can access team leader sprints'
    ],
    [
        'name' => 'team-leader.notifications',
        'display_name' => 'Access Team Leader Notifications',
        'description' => 'Can access team leader notifications'
    ],
    [
        'name' => 'team-leader.reports',
        'display_name' => 'Access Team Leader Reports',
        'description' => 'Can access team leader reports'
    ],
    [
        'name' => 'team-leader.review.tasks',
        'display_name' => 'Review Tasks',
        'description' => 'Can review tasks completed by QA'
    ],
    [
        'name' => 'team-leader.review.bugs',
        'display_name' => 'Review Bugs',
        'description' => 'Can review bugs completed by QA'
    ],
    [
        'name' => 'team-leader.stats',
        'display_name' => 'View Team Leader Stats',
        'description' => 'Can view team leader statistics'
    ],
    [
        'name' => 'create-sprint-tasks',
        'display_name' => 'Create Tasks in Sprints',
        'description' => 'Can create tasks in sprints'
    ],
    [
        'name' => 'create-sprint-bugs',
        'display_name' => 'Create Bugs in Sprints',
        'description' => 'Can create bugs in sprints'
    ]
];

// Crear los permisos si no existen
foreach ($permissions as $permissionData) {
    $permission = Permission::firstOrCreate(
        ['name' => $permissionData['name']],
        [
            'display_name' => $permissionData['display_name'],
            'description' => $permissionData['description']
        ]
    );
    echo "Permission '{$permission->name}' " . ($permission->wasRecentlyCreated ? 'created' : 'already exists') . "\n";
}

// Asignar los permisos al rol de Team Leader
$teamLeaderRole = Role::where('name', 'team_leader')->first();

if ($teamLeaderRole) {
    foreach ($permissions as $permissionData) {
        $permission = Permission::where('name', $permissionData['name'])->first();
        if (!$teamLeaderRole->permissions->contains($permission->id)) {
            $teamLeaderRole->permissions()->attach($permission->id);
            echo "Permission '{$permission->name}' assigned to Team Leader role\n";
        } else {
            echo "Permission '{$permission->name}' was already assigned to Team Leader role\n";
        }
    }
} else {
    echo "Team Leader role not found!\n";
}

echo "\nDone!\n";

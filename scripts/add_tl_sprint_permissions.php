<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Role;
use App\Models\Permission;

// Crear los nuevos permisos si no existen
$permissions = [
    [
        'name' => 'create-sprint-tasks',
        'display_name' => 'Create Tasks in Sprints',
        'description' => 'Can create new tasks within sprints'
    ],
    [
        'name' => 'assign-sprint-tasks',
        'display_name' => 'Assign Tasks in Sprints',
        'description' => 'Can assign tasks to developers within sprints'
    ],
    [
        'name' => 'create-sprint-bugs',
        'display_name' => 'Create Bugs in Sprints',
        'description' => 'Can create new bugs within sprints'
    ],
    [
        'name' => 'assign-sprint-bugs',
        'display_name' => 'Assign Bugs in Sprints',
        'description' => 'Can assign bugs to developers within sprints'
    ]
];

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

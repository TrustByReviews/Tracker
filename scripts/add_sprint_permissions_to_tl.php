<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Role;
use App\Models\Permission;

echo "Adding sprint permissions to Team Leader role...\n";

$teamLeaderRole = Role::where('name', 'team_leader')->first();

if (!$teamLeaderRole) {
    echo "Error: Team Leader role not found!\n";
    exit(1);
}

// Lista de permisos necesarios para gestionar sprints
$permissions = [
    'sprints.create' => 'Can create sprints',
    'sprints.edit' => 'Can edit sprints',
    'sprints.view' => 'Can view sprints',
    'sprints.delete' => 'Can delete sprints'
];

foreach ($permissions as $name => $description) {
    $permission = Permission::firstOrCreate(
        ['name' => $name],
        ['description' => $description]
    );
    
    echo "Permission '{$name}' " . ($permission->wasRecentlyCreated ? 'created' : 'already exists') . "\n";
    
    if (!$teamLeaderRole->permissions->contains($permission->id)) {
        $teamLeaderRole->permissions()->attach($permission->id);
        echo "Permission '{$name}' assigned to Team Leader role\n";
    } else {
        echo "Permission '{$name}' was already assigned to Team Leader role\n";
    }
}

echo "\nDone!\n";

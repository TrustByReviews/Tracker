<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Role;

echo "Fixing Team Leader roles...\n";

// Find the TL user
$user = User::where('email', 'tl@test.com')->first();
if (!$user) {
    echo "TL user not found!\n";
    exit(1);
}

echo "Found user: {$user->name} (ID: {$user->id})\n";

// Find the team_leader role
$teamLeaderRole = Role::where('value', 'team_leader')->first();
if (!$teamLeaderRole) {
    echo "Team Leader role not found!\n";
    exit(1);
}

echo "Found role: {$teamLeaderRole->name} (ID: {$teamLeaderRole->id})\n";

// Check current roles
$currentRoles = $user->roles;
echo "Current roles count: " . $currentRoles->count() . "\n";

// Assign the role
$user->roles()->sync([$teamLeaderRole->id]);

// Verify the assignment
$user->refresh();
$updatedRoles = $user->roles;
echo "Updated roles count: " . $updatedRoles->count() . "\n";

foreach ($updatedRoles as $role) {
    echo "- Role: {$role->name} (value: {$role->value})\n";
}

echo "Team Leader role assigned successfully!\n";

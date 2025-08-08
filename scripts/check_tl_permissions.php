<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

echo "=== CHECKING TEAM LEADER PERMISSIONS ===\n";

// Find the TL user and role
$user = User::where('email', 'tl@test.com')->first();
$teamLeaderRole = Role::where('value', 'team_leader')->first();

if (!$user || !$teamLeaderRole) {
    echo "❌ User or role not found!\n";
    exit(1);
}

echo "✅ Found user: {$user->name}\n";
echo "✅ Found role: {$teamLeaderRole->name}\n";

// Check if permission exists
$dashboardPermission = Permission::where('name', 'team-leader.dashboard')->first();
if (!$dashboardPermission) {
    echo "❌ Permission 'team-leader.dashboard' not found!\n";
    echo "🔧 Creating permission...\n";
    
    $dashboardPermission = Permission::create([
        'name' => 'team-leader.dashboard',
        'display_name' => 'Team Leader Dashboard',
        'description' => 'Access to Team Leader dashboard',
        'module' => 'team-leader',
        'is_active' => true
    ]);
    echo "✅ Permission created!\n";
} else {
    echo "✅ Permission 'team-leader.dashboard' exists\n";
}

// Check if role has the permission
$hasPermission = $teamLeaderRole->hasPermission('team-leader.dashboard');
echo "🔐 Role has permission: " . ($hasPermission ? 'YES' : 'NO') . "\n";

if (!$hasPermission) {
    echo "🔧 Assigning permission to role...\n";
    $teamLeaderRole->permissions()->attach($dashboardPermission->id);
    echo "✅ Permission assigned!\n";
}

// Check user permissions
$userHasPermission = $user->hasPermission('team-leader.dashboard');
echo "👤 User has permission: " . ($userHasPermission ? 'YES' : 'NO') . "\n";

// Create other necessary permissions for Team Leader
$tlPermissions = [
    'team-leader.projects',
    'team-leader.sprints',
    'team-leader.notifications',
    'team-leader.reports',
    'team-leader.settings',
    'team-leader.review.tasks',
    'team-leader.review.bugs',
    'team-leader.review.stats'
];

echo "\n=== CREATING TEAM LEADER PERMISSIONS ===\n";

foreach ($tlPermissions as $permissionName) {
    $permission = Permission::where('name', $permissionName)->first();
    if (!$permission) {
        echo "🔧 Creating permission '{$permissionName}'...\n";
        $permission = Permission::create([
            'name' => $permissionName,
            'display_name' => ucwords(str_replace(['-', '.'], [' ', ' '], $permissionName)),
            'description' => 'Access to ' . str_replace(['-', '.'], [' ', ' '], $permissionName),
            'module' => 'team-leader',
            'is_active' => true
        ]);
        echo "✅ Permission created!\n";
    } else {
        echo "✅ Permission '{$permissionName}' exists\n";
    }
    
    // Assign to role
    if (!$teamLeaderRole->hasPermission($permissionName)) {
        echo "🔧 Assigning '{$permissionName}' to role...\n";
        $teamLeaderRole->permissions()->attach($permission->id);
        echo "✅ Permission assigned!\n";
    }
}

echo "\n=== FINAL CHECK ===\n";
$user->refresh();
$finalCheck = $user->hasPermission('team-leader.dashboard');
echo "🎯 User can access team-leader.dashboard: " . ($finalCheck ? 'YES' : 'NO') . "\n";

if ($finalCheck) {
    echo "✅ Team Leader should now be able to access the dashboard!\n";
} else {
    echo "❌ Still having issues with permissions\n";
}

echo "\n=== END CHECK ===\n";

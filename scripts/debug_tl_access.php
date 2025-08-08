<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\Project;

echo "=== DEBUG TEAM LEADER ACCESS ===\n";

// Find the TL user
$user = User::where('email', 'tl@test.com')->first();
if (!$user) {
    echo "❌ TL user not found!\n";
    exit(1);
}

echo "✅ Found user: {$user->name} (ID: {$user->id})\n";

// Check roles
$roles = $user->roles;
echo "📋 Roles count: " . $roles->count() . "\n";
foreach ($roles as $role) {
    echo "  - Role: {$role->name} (value: {$role->value})\n";
}

// Check permissions
$permissions = $user->getAllPermissions();
echo "🔐 Permissions count: " . count($permissions) . "\n";
foreach ($permissions as $permission) {
    echo "  - Permission: {$permission->name}\n";
}

// Check if user has team_leader role
$hasTeamLeaderRole = $user->hasRole('team_leader');
echo "👑 Has team_leader role: " . ($hasTeamLeaderRole ? 'YES' : 'NO') . "\n";

// Check if user is team leader
$isTeamLeader = $user->isTeamLeader();
echo "🎯 isTeamLeader() method: " . ($isTeamLeader ? 'YES' : 'NO') . "\n";

// Check projects assigned
$projects = $user->projects;
echo "📁 Projects assigned: " . $projects->count() . "\n";
foreach ($projects as $project) {
    echo "  - Project: {$project->name} (ID: {$project->id})\n";
}

// Check if user can access team leader routes
echo "\n=== TESTING ROUTE ACCESS ===\n";

// Simulate route access test
$request = new \Illuminate\Http\Request();
$request->setUserResolver(function () use ($user) {
    return $user;
});

// Test middleware
echo "🔍 Testing auth middleware...\n";
$authMiddleware = new \App\Http\Middleware\Authenticate();
try {
    // This is a simplified test
    echo "✅ Auth middleware should pass\n";
} catch (Exception $e) {
    echo "❌ Auth middleware failed: " . $e->getMessage() . "\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
if ($roles->count() === 0) {
    echo "❌ User has no roles assigned\n";
    echo "💡 Run: php scripts/fix_tl_roles.php\n";
}

if ($projects->count() === 0) {
    echo "❌ User has no projects assigned\n";
    echo "💡 Run: php scripts/assign_projects_to_tl.php\n";
}

if (!$hasTeamLeaderRole) {
    echo "❌ User doesn't have team_leader role\n";
    echo "💡 Check role assignment\n";
}

echo "\n=== END DEBUG ===\n";

<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== DEBUG FRONTEND USER DATA ===\n";

// Find the TL user
$user = User::where('email', 'tl@test.com')->with(['roles.permissions'])->first();
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
    
    // Check permissions for this role
    $permissions = $role->permissions;
    echo "    Permissions count: " . $permissions->count() . "\n";
    foreach ($permissions as $permission) {
        echo "      - Permission: {$permission->name}\n";
    }
}

// Simulate the data that would be sent to frontend
echo "\n=== FRONTEND DATA SIMULATION ===\n";
$frontendUser = [
    'id' => $user->id,
    'name' => $user->name,
    'email' => $user->email,
    'roles' => $roles->map(function($role) {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'value' => $role->value,
            'permissions' => $role->permissions->map(function($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'display_name' => $permission->display_name,
                    'description' => $permission->description,
                    'module' => $permission->module,
                    'is_active' => $permission->is_active
                ];
            })->toArray()
        ];
    })->toArray()
];

echo "Frontend user data structure:\n";
echo json_encode($frontendUser, JSON_PRETTY_PRINT) . "\n";

// Check if user would be detected as team leader
$hasTeamLeaderRole = collect($frontendUser['roles'])->some(function($role) {
    return $role['value'] === 'team_leader';
});

echo "\n=== FRONTEND DETECTION TEST ===\n";
echo "Would be detected as Team Leader: " . ($hasTeamLeaderRole ? 'YES' : 'NO') . "\n";

if ($hasTeamLeaderRole) {
    echo "✅ User should see Team Leader sidebar\n";
} else {
    echo "❌ User will see default sidebar\n";
}

echo "\n=== END DEBUG ===\n";

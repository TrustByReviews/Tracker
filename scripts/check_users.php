<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Role;

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking existing users...\n\n";

try {
    $users = User::with('roles')->get();
    
    echo "Total users: " . $users->count() . "\n\n";
    
    foreach ($users as $user) {
        echo "User: {$user->name} ({$user->email})\n";
        echo "  Roles: ";
        $roles = $user->roles->pluck('name')->toArray();
        echo implode(', ', $roles) ?: 'No roles';
        echo "\n";
        echo "  Status: {$user->status}\n";
        echo "  Projects: " . $user->projects()->count() . "\n\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 
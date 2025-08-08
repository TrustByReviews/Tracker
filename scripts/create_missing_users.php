<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Creating missing test users...\n\n";

try {
    // Obtener roles existentes
    $roles = [
        'admin' => Role::where('value', 'admin')->first(),
        'team_leader' => Role::where('value', 'team_leader')->first(),
        'developer' => Role::where('value', 'developer')->first(),
        'qa' => Role::where('value', 'qa')->first(),
    ];

    // Crear usuarios faltantes
    $usersToCreate = [
        'admin' => [
            'name' => 'Admin User',
            'email' => 'admin@tracker.com',
            'password' => 'password',
            'nickname' => 'admin',
            'role' => 'admin',
        ],
        'team_leader' => [
            'name' => 'Team Leader',
            'email' => 'teamleader@tracker.com',
            'password' => 'password',
            'nickname' => 'team_leader',
            'role' => 'team_leader',
        ],
        'developer' => [
            'name' => 'Developer',
            'email' => 'developer@tracker.com',
            'password' => 'password',
            'nickname' => 'developer',
            'role' => 'developer',
        ],
    ];

    foreach ($usersToCreate as $key => $userData) {
        $existingUser = User::where('email', $userData['email'])->first();
        
        if (!$existingUser) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'nickname' => $userData['nickname'],
                'hour_value' => 25,
                'work_time' => 'full',
                'status' => 'active',
            ]);
            echo "Created user: {$user->name}\n";
        } else {
            $user = $existingUser;
            echo "User already exists: {$user->name}\n";
        }

        // Asignar rol si no lo tiene
        $role = $roles[$userData['role']];
        if (!$user->roles()->where('role_id', $role->id)->exists()) {
            $user->roles()->attach($role->id);
            echo "  - Assigned role: {$role->name}\n";
        }

        // Asignar a todos los proyectos si no está asignado
        $projects = Project::all();
        foreach ($projects as $project) {
            if (!$user->projects()->where('project_id', $project->id)->exists()) {
                $user->projects()->attach($project->id);
                echo "  - Assigned to project: {$project->name}\n";
            }
        }
    }

    echo "\n✅ Missing users created successfully!\n\n";
    echo "Test Users:\n";
    echo "- Admin: admin@tracker.com / password\n";
    echo "- Team Leader: teamleader@tracker.com / password\n";
    echo "- Developer: developer@tracker.com / password\n";
    echo "- QA: qa@tracker.com / password (already exists)\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;

echo "=== CREANDO USUARIOS DE PRUEBA ===\n\n";

// Crear roles si no existen
$roles = [
    'admin' => 'Administrator',
    'team_leader' => 'Team Leader', 
    'developer' => 'Developer',
    'qa' => 'QA Tester'
];

foreach ($roles as $name => $displayName) {
    $role = Role::firstOrCreate(['name' => $name], [
        'display_name' => $displayName,
        'description' => $displayName . ' role'
    ]);
    echo "✅ Rol '{$name}' creado/verificado\n";
}

// Crear usuarios de prueba
$testUsers = [
    [
        'name' => 'Admin User',
        'email' => 'admin@test.com',
        'password' => 'password',
        'role' => 'admin'
    ],
    [
        'name' => 'Team Leader 1',
        'email' => 'tl1@test.com', 
        'password' => 'password',
        'role' => 'team_leader'
    ],
    [
        'name' => 'Team Leader 2',
        'email' => 'tl2@test.com',
        'password' => 'password', 
        'role' => 'team_leader'
    ],
    [
        'name' => 'Developer 1',
        'email' => 'dev1@test.com',
        'password' => 'password',
        'role' => 'developer'
    ],
    [
        'name' => 'Developer 2', 
        'email' => 'dev2@test.com',
        'password' => 'password',
        'role' => 'developer'
    ],
    [
        'name' => 'Developer 3',
        'email' => 'dev3@test.com', 
        'password' => 'password',
        'role' => 'developer'
    ],
    [
        'name' => 'QA Tester 1',
        'email' => 'qa1@test.com',
        'password' => 'password',
        'role' => 'qa'
    ],
    [
        'name' => 'QA Tester 2',
        'email' => 'qa2@test.com',
        'password' => 'password',
        'role' => 'qa'
    ]
];

foreach ($testUsers as $userData) {
    $user = User::firstOrCreate(['email' => $userData['email']], [
        'name' => $userData['name'],
        'email' => $userData['email'],
        'password' => Hash::make($userData['password']),
        'email_verified_at' => now()
    ]);
    
    // Asignar rol
    $role = Role::where('name', $userData['role'])->first();
    if ($role && !$user->roles()->where('name', $userData['role'])->exists()) {
        $user->roles()->attach($role->id);
    }
    
    echo "✅ Usuario '{$userData['name']}' creado/verificado ({$userData['email']})\n";
}

// Obtener proyectos existentes
$projects = Project::all();
if ($projects->count() > 0) {
    echo "\n=== ASIGNANDO USUARIOS A PROYECTOS ===\n";
    
    foreach ($projects as $project) {
        // Asignar team leaders y developers a proyectos
        $teamLeaders = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['team_leader', 'admin']);
        })->get();
        
        $developers = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['developer']);
        })->get();
        
        foreach ($teamLeaders as $user) {
            if (!$project->users()->where('user_id', $user->id)->exists()) {
                $project->users()->attach($user->id);
                echo "✅ {$user->name} asignado al proyecto '{$project->name}'\n";
            }
        }
        
        foreach ($developers as $user) {
            if (!$project->users()->where('user_id', $user->id)->exists()) {
                $project->users()->attach($user->id);
                echo "✅ {$user->name} asignado al proyecto '{$project->name}'\n";
            }
        }
    }
} else {
    echo "\n⚠️  No hay proyectos disponibles para asignar usuarios\n";
}

echo "\n=== RESUMEN ===\n";
echo "Total usuarios creados: " . User::count() . "\n";
echo "Total proyectos: " . Project::count() . "\n";
echo "Total roles: " . Role::count() . "\n";

echo "\n=== CREDENCIALES DE ACCESO ===\n";
echo "Admin: admin@test.com / password\n";
echo "Team Leader 1: tl1@test.com / password\n";
echo "Team Leader 2: tl2@test.com / password\n";
echo "Developer 1: dev1@test.com / password\n";
echo "Developer 2: dev2@test.com / password\n";
echo "Developer 3: dev3@test.com / password\n";
echo "QA 1: qa1@test.com / password\n";
echo "QA 2: qa2@test.com / password\n";

echo "\n✅ Usuarios de prueba creados exitosamente!\n";
echo "Ahora puedes probar la funcionalidad de asignación de bugs.\n"; 
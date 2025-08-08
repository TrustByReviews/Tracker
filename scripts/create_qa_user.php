<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Creating QA user...\n";

try {
    // Crear usuario QA
    $qaUser = User::create([
        'name' => 'QA Tester',
        'email' => 'qa@tracker.com',
        'password' => Hash::make('password'),
        'nickname' => 'qa_tester',
        'hour_value' => 25,
        'work_time' => 'full',
        'status' => 'active',
    ]);

    echo "QA user created: {$qaUser->name} ({$qaUser->email})\n";

    // Obtener el rol de QA
    $qaRole = Role::where('value', 'qa')->first();
    
    if (!$qaRole) {
        echo "QA role not found. Creating it...\n";
        $qaRole = Role::create([
            'name' => 'qa',
            'value' => 'qa'
        ]);
    }

    // Asignar rol de QA al usuario
    $qaUser->roles()->attach($qaRole->id);
    echo "QA role assigned to user\n";

    // Asignar a todos los proyectos existentes
    $projects = Project::all();
    foreach ($projects as $project) {
        $qaUser->projects()->attach($project->id);
        echo "Assigned to project: {$project->name}\n";
    }

    echo "\nQA user setup completed successfully!\n";
    echo "Email: qa@tracker.com\n";
    echo "Password: password\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 
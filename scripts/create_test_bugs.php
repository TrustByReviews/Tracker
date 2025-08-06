<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Bug;
use App\Models\User;
use App\Models\Sprint;
use App\Models\Project;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Creating Test Bugs ===\n\n";

try {
    // Buscar usuario y proyecto/sprint para crear los bugs
    $user = User::where('email', 'carmen.ruiz79@test.com')->first();
    if (!$user) {
        echo "Error: User not found\n";
        exit(1);
    }
    
    $project = Project::first();
    $sprint = Sprint::first();
    
    if (!$project || !$sprint) {
        echo "Error: No project or sprint found\n";
        exit(1);
    }
    
    echo "User: {$user->name}\n";
    echo "Project: {$project->name}\n";
    echo "Sprint: {$sprint->name}\n\n";
    
    // Lista de bugs de prueba
    $testBugs = [
        [
            'title' => 'Validación de email falla',
            'description' => 'La validación de email no funciona correctamente con ciertos formatos.',
            'long_description' => 'La validación de email no funciona correctamente con ciertos formatos.\n\nEste es un bug que requiere atención inmediata. Se ha reportado por múltiples usuarios y afecta la funcionalidad principal del sistema.',
            'importance' => 'critical',
            'bug_type' => 'database',
            'environment' => 'development',
            'browser_info' => 'Firefox 121.0',
            'os_info' => 'macOS 14.0',
            'steps_to_reproduce' => '1. Intentar registrar con email inválido\n2. Observar que no se valida correctamente',
            'expected_behavior' => 'El sistema debería funcionar correctamente sin errores.',
            'actual_behavior' => 'La validación de email no funciona correctamente con ciertos formatos.',
            'reproducibility' => 'always',
            'severity' => 'high',
            'estimated_hours' => 7,
            'estimated_minutes' => 52,
            'tags' => 'database,development,critical'
        ],
        [
            'title' => 'Error 404 en página de contacto',
            'description' => 'El usuario reporta que al hacer clic en el botón de contacto aparece un error 404.',
            'long_description' => 'El usuario reporta que al hacer clic en el botón de contacto aparece un error 404.\n\nEste error afecta la experiencia del usuario y puede causar pérdida de clientes potenciales.',
            'importance' => 'high',
            'bug_type' => 'backend',
            'environment' => 'staging',
            'browser_info' => 'Chrome 120.0',
            'os_info' => 'Windows 11',
            'steps_to_reproduce' => '1. Navegar a la página principal\n2. Hacer clic en "Contacto"\n3. Observar error 404',
            'expected_behavior' => 'Debería mostrar la página de contacto.',
            'actual_behavior' => 'Muestra error 404.',
            'reproducibility' => 'always',
            'severity' => 'high',
            'estimated_hours' => 8,
            'estimated_minutes' => 0,
            'tags' => 'backend,staging,high'
        ],
        [
            'title' => 'Botón de login no responde',
            'description' => 'El botón de login no responde cuando se hace clic en él.',
            'long_description' => 'El botón de login no responde cuando se hace clic en él.\n\nEste es un problema crítico que impide que los usuarios accedan al sistema.',
            'importance' => 'critical',
            'bug_type' => 'frontend',
            'environment' => 'production',
            'browser_info' => 'Safari 17.0',
            'os_info' => 'iOS 17.0',
            'steps_to_reproduce' => '1. Ir a la página de login\n2. Ingresar credenciales\n3. Hacer clic en "Login"\n4. Observar que no responde',
            'expected_behavior' => 'Debería procesar el login y redirigir al dashboard.',
            'actual_behavior' => 'El botón no responde al clic.',
            'reproducibility' => 'always',
            'severity' => 'critical',
            'estimated_hours' => 4,
            'estimated_minutes' => 30,
            'tags' => 'frontend,production,critical'
        ],
        [
            'title' => 'Datos no se guardan en formulario',
            'description' => 'Los datos ingresados en el formulario no se guardan correctamente.',
            'long_description' => 'Los datos ingresados en el formulario no se guardan correctamente.\n\nEste problema afecta la funcionalidad principal de recolección de datos.',
            'importance' => 'high',
            'bug_type' => 'backend',
            'environment' => 'development',
            'browser_info' => 'Edge 120.0',
            'os_info' => 'Windows 10',
            'steps_to_reproduce' => '1. Llenar el formulario\n2. Hacer clic en "Guardar"\n3. Verificar que no se guardó',
            'expected_behavior' => 'Los datos deberían guardarse en la base de datos.',
            'actual_behavior' => 'Los datos no se guardan.',
            'reproducibility' => 'sometimes',
            'severity' => 'high',
            'estimated_hours' => 6,
            'estimated_minutes' => 0,
            'tags' => 'backend,development,high'
        ]
    ];
    
    $createdCount = 0;
    
    foreach ($testBugs as $bugData) {
        // Calcular priority score
        $importanceScore = match($bugData['importance']) {
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 1,
        };
        
        $severityScore = match($bugData['severity']) {
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 1,
        };
        
        $reproducibilityScore = match($bugData['reproducibility']) {
            'always' => 4,
            'sometimes' => 3,
            'rarely' => 2,
            'unable' => 1,
            default => 2,
        };
        
        $priorityScore = ($importanceScore * 3) + ($severityScore * 2) + $reproducibilityScore;
        
        // Crear el bug
        $bug = Bug::create([
            'title' => $bugData['title'],
            'description' => $bugData['description'],
            'long_description' => $bugData['long_description'],
            'importance' => $bugData['importance'],
            'bug_type' => $bugData['bug_type'],
            'environment' => $bugData['environment'],
            'browser_info' => $bugData['browser_info'],
            'os_info' => $bugData['os_info'],
            'steps_to_reproduce' => $bugData['steps_to_reproduce'],
            'expected_behavior' => $bugData['expected_behavior'],
            'actual_behavior' => $bugData['actual_behavior'],
            'reproducibility' => $bugData['reproducibility'],
            'severity' => $bugData['severity'],
            'sprint_id' => $sprint->id,
            'project_id' => $project->id,
            'user_id' => $user->id,
            'estimated_hours' => $bugData['estimated_hours'],
            'estimated_minutes' => $bugData['estimated_minutes'],
            'tags' => $bugData['tags'],
            'priority_score' => $priorityScore,
            'status' => 'in progress',
            'is_working' => false, // No trabajando inicialmente
            'total_time_seconds' => 0
        ]);
        
        echo "Created bug: {$bug->title} (ID: {$bug->id})\n";
        echo "  Status: {$bug->status}\n";
        echo "  Is Working: " . ($bug->is_working ? 'Yes' : 'No') . "\n";
        echo "  Priority Score: {$bug->priority_score}\n";
        echo "---\n";
        
        $createdCount++;
    }
    
    echo "\n=== Summary ===\n";
    echo "Created {$createdCount} test bugs\n";
    echo "All bugs are in 'in progress' status and ready for testing\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== End ===\n"; 
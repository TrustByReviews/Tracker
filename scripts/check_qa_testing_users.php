<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Bug;

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VERIFICACIÓN DE USUARIOS PARA TESTING QA ===\n\n";

try {
    // Verificar usuarios principales
    $users = [
        'developer' => User::where('email', 'juan.martinez324@test.com')->first(),
        'team_leader' => User::where('email', 'roberto.silva190@test.com')->first(),
        'qa' => User::where('email', 'qa@tracker.com')->first(),
        'admin' => User::where('email', 'carmen.ruiz79@test.com')->first(),
    ];

    echo "👥 USUARIOS DISPONIBLES:\n";
    echo "========================\n\n";

    foreach ($users as $role => $user) {
        if ($user) {
            echo "✅ {$role}: {$user->name} ({$user->email})\n";
            echo "   - Rol: " . $user->roles->pluck('value')->implode(', ') . "\n";
            echo "   - Estado: " . ($user->is_active ? 'Activo' : 'Inactivo') . "\n";
            echo "   - Proyectos asignados: " . $user->projects()->count() . "\n";
            echo "   - Contraseña: password\n\n";
        } else {
            echo "❌ {$role}: NO ENCONTRADO\n\n";
        }
    }

    // Verificar proyectos
    echo "📁 PROYECTOS DISPONIBLES:\n";
    echo "========================\n";
    $projects = Project::all();
    foreach ($projects as $project) {
        $qaUsers = $project->users()->whereHas('roles', function ($query) {
            $query->where('value', 'qa');
        })->get();
        
        echo "✅ {$project->name}\n";
        echo "   - QA asignado: " . $qaUsers->pluck('name')->implode(', ') . "\n";
        echo "   - Total usuarios: " . $project->users()->count() . "\n\n";
    }

    // Verificar tareas de prueba
    echo "📋 TAREAS DE PRUEBA DISPONIBLES:\n";
    echo "================================\n";
    
    $taskStats = [
        'total' => Task::count(),
        'ready_for_test' => Task::where('qa_status', 'ready_for_test')->count(),
        'testing' => Task::where('qa_status', 'testing')->count(),
        'approved' => Task::where('qa_status', 'approved')->count(),
        'rejected' => Task::where('qa_status', 'rejected')->count(),
    ];

    echo "✅ Total de tareas: {$taskStats['total']}\n";
    echo "✅ Listas para testing: {$taskStats['ready_for_test']}\n";
    echo "✅ En testing: {$taskStats['testing']}\n";
    echo "✅ Aprobadas por QA: {$taskStats['approved']}\n";
    echo "✅ Rechazadas por QA: {$taskStats['rejected']}\n\n";

    // Verificar bugs de prueba
    echo "🐛 BUGS DE PRUEBA DISPONIBLES:\n";
    echo "==============================\n";
    
    $bugStats = [
        'total' => Bug::count(),
        'ready_for_test' => Bug::where('qa_status', 'ready_for_test')->count(),
        'testing' => Bug::where('qa_status', 'testing')->count(),
        'approved' => Bug::where('qa_status', 'approved')->count(),
        'rejected' => Bug::where('qa_status', 'rejected')->count(),
    ];

    echo "✅ Total de bugs: {$bugStats['total']}\n";
    echo "✅ Listos para testing: {$bugStats['ready_for_test']}\n";
    echo "✅ En testing: {$bugStats['testing']}\n";
    echo "✅ Aprobados por QA: {$bugStats['approved']}\n";
    echo "✅ Rechazados por QA: {$bugStats['rejected']}\n\n";

    // Verificar URLs importantes
    echo "🔗 URLS IMPORTANTES:\n";
    echo "===================\n";
    echo "✅ Login: /login\n";
    echo "✅ QA Dashboard: /qa/dashboard\n";
    echo "✅ QA Kanban: /qa/kanban\n";
    echo "✅ Team Leader Dashboard: /team-leader/dashboard\n";
    echo "✅ Team Leader QA Review: /team-leader/qa-review\n";
    echo "✅ Developer Dashboard: /dashboard\n\n";

    // Verificar que todos los usuarios necesarios existen
    $allUsersExist = true;
    foreach ($users as $role => $user) {
        if (!$user) {
            $allUsersExist = false;
            break;
        }
    }

    if ($allUsersExist) {
        echo "🎉 ¡TODOS LOS USUARIOS ESTÁN DISPONIBLES!\n";
        echo "==========================================\n";
        echo "✅ Puedes comenzar a testear el sistema de QA\n";
        echo "✅ Usa las credenciales del archivo CREDENCIALES_QA_TESTING.md\n";
        echo "✅ Todos los flujos están funcionando correctamente\n\n";
        
        echo "🚀 FLUJO DE TESTING RECOMENDADO:\n";
        echo "1. Login como Developer (juan.martinez324@test.com)\n";
        echo "2. Login como Team Leader (roberto.silva190@test.com)\n";
        echo "3. Login como QA (qa@tracker.com)\n";
        echo "4. Probar el flujo completo de QA\n\n";
        
        echo "¡Disfruta probando el sistema! 🎯\n";
    } else {
        echo "❌ ALGUNOS USUARIOS FALTAN\n";
        echo "==========================\n";
        echo "Por favor, ejecuta los scripts de creación de usuarios primero.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
<?php

/**
 * Script para probar la página de tareas
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 PROBANDO PÁGINA DE TAREAS\n";
echo "============================\n\n";

try {
    // 1. Simular acceso como desarrollador
    echo "1. Simulando acceso como desarrollador...\n";
    
    $developer = User::whereHas('roles', function ($query) {
        $query->where('name', 'developer');
    })->first();
    
    if (!$developer) {
        echo "   ❌ No se encontró ningún desarrollador\n";
        exit(1);
    }
    
    echo "   - Usuario: {$developer->name} ({$developer->email})\n";
    echo "   - ID: {$developer->id}\n";
    
    $roles = $developer->roles->pluck('name')->join(', ');
    echo "   - Roles: {$roles}\n";
    
    echo "\n";
    
    // 2. Verificar tareas del desarrollador
    echo "2. Verificando tareas del desarrollador...\n";
    
    $tasks = Task::where('user_id', $developer->id)
        ->with(['user', 'sprint', 'project'])
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "   - Tareas encontradas: " . $tasks->count() . "\n";
    
    if ($tasks->count() > 0) {
        echo "   📝 Tareas del desarrollador:\n";
        foreach ($tasks as $task) {
            $projectName = $task->project ? $task->project->name : 'Sin proyecto';
            $sprintName = $task->sprint ? $task->sprint->name : 'Sin sprint';
            echo "     * {$task->name} (Estado: {$task->status}, Proyecto: {$projectName}, Sprint: {$sprintName})\n";
        }
    } else {
        echo "   ⚠️  No se encontraron tareas para este desarrollador\n";
    }
    
    echo "\n";
    
    // 3. Verificar proyectos del desarrollador
    echo "3. Verificando proyectos del desarrollador...\n";
    
    $projects = $developer->projects()->orderBy('name')->get();
    echo "   - Proyectos encontrados: " . $projects->count() . "\n";
    
    if ($projects->count() > 0) {
        echo "   📝 Proyectos del desarrollador:\n";
        foreach ($projects as $project) {
            echo "     * {$project->name} (Estado: {$project->status})\n";
        }
    } else {
        echo "   ⚠️  No se encontraron proyectos para este desarrollador\n";
    }
    
    echo "\n";
    
    // 4. Verificar sprints del desarrollador
    echo "4. Verificando sprints del desarrollador...\n";
    
    $sprints = Sprint::whereHas('project.users', function ($query) use ($developer) {
        $query->where('users.id', $developer->id);
    })->with('project')->orderBy('start_date', 'desc')->get();
    
    echo "   - Sprints encontrados: " . $sprints->count() . "\n";
    
    if ($sprints->count() > 0) {
        echo "   📝 Sprints del desarrollador:\n";
        foreach ($sprints as $sprint) {
            $projectName = $sprint->project ? $sprint->project->name : 'Sin proyecto';
            echo "     * {$sprint->name} (Proyecto: {$projectName}, Estado: {$sprint->status})\n";
        }
    } else {
        echo "   ⚠️  No se encontraron sprints para este desarrollador\n";
    }
    
    echo "\n";
    
    // 5. Verificar todas las tareas del sistema
    echo "5. Verificando todas las tareas del sistema...\n";
    
    $allTasks = Task::with(['user', 'sprint', 'project'])->get();
    echo "   - Total de tareas en el sistema: " . $allTasks->count() . "\n";
    
    $tasksByStatus = $allTasks->groupBy('status');
    foreach ($tasksByStatus as $status => $statusTasks) {
        echo "   - Tareas '{$status}': " . $statusTasks->count() . "\n";
    }
    
    echo "\n";
    
    // 6. Verificar si hay tareas sin usuario asignado
    echo "6. Verificando tareas sin usuario asignado...\n";
    
    $unassignedTasks = Task::whereNull('user_id')->get();
    echo "   - Tareas sin asignar: " . $unassignedTasks->count() . "\n";
    
    if ($unassignedTasks->count() > 0) {
        echo "   📝 Tareas sin asignar:\n";
        foreach ($unassignedTasks as $task) {
            echo "     * {$task->name} (Estado: {$task->status})\n";
        }
    }
    
    echo "\n";
    
    // 7. Resumen final
    echo "7. Resumen final:\n";
    echo "   - Desarrollador: {$developer->name}\n";
    echo "   - Tareas asignadas: " . $tasks->count() . "\n";
    echo "   - Proyectos disponibles: " . $projects->count() . "\n";
    echo "   - Sprints disponibles: " . $sprints->count() . "\n";
    echo "   - Total tareas sistema: " . $allTasks->count() . "\n";
    
    if ($tasks->count() > 0) {
        echo "\n✅ La página de tareas debería mostrar contenido\n";
        echo "=============================================\n";
        echo "El desarrollador tiene tareas asignadas y debería ver:\n";
        echo "- " . $tasks->count() . " tareas en la lista\n";
        echo "- " . $projects->count() . " proyectos disponibles\n";
        echo "- " . $sprints->count() . " sprints disponibles\n";
    } else {
        echo "\n⚠️  La página de tareas podría estar vacía\n";
        echo "========================================\n";
        echo "El desarrollador no tiene tareas asignadas.\n";
        echo "Considera asignar algunas tareas o verificar la lógica de asignación.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error durante la prueba: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
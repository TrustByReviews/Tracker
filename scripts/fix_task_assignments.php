<?php

/**
 * Script para diagnosticar y corregir asignaciones de tareas
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

echo "🔍 DIAGNÓSTICO DE ASIGNACIONES DE TAREAS\n";
echo "========================================\n\n";

try {
    // 1. Verificar usuarios disponibles
    echo "1. Verificando usuarios disponibles...\n";
    $users = User::with('roles')->get();
    echo "   - Total de usuarios: " . $users->count() . "\n";
    
    foreach ($users as $user) {
        $roles = $user->roles->pluck('name')->join(', ');
        echo "   - {$user->name} ({$user->email}) - Roles: {$roles}\n";
    }
    
    echo "\n";
    
    // 2. Verificar tareas sin asignar
    echo "2. Verificando tareas sin asignar...\n";
    $unassignedTasks = Task::whereNull('user_id')->get();
    echo "   - Tareas sin asignar: " . $unassignedTasks->count() . "\n";
    
    if ($unassignedTasks->count() > 0) {
        echo "   📝 Tareas sin asignar:\n";
        foreach ($unassignedTasks as $task) {
            echo "     * {$task->name} (Proyecto: " . ($task->project ? $task->project->name : 'Sin proyecto') . ")\n";
        }
    }
    
    echo "\n";
    
    // 3. Verificar tareas asignadas a usuarios inexistentes
    echo "3. Verificando tareas con usuarios inexistentes...\n";
    $tasksWithInvalidUsers = Task::whereNotNull('user_id')
        ->whereNotIn('user_id', $users->pluck('id'))
        ->get();
    
    echo "   - Tareas con usuarios inexistentes: " . $tasksWithInvalidUsers->count() . "\n";
    
    if ($tasksWithInvalidUsers->count() > 0) {
        echo "   📝 Tareas con usuarios inválidos:\n";
        foreach ($tasksWithInvalidUsers as $task) {
            echo "     * {$task->name} (user_id: {$task->user_id})\n";
        }
    }
    
    echo "\n";
    
    // 4. Verificar tareas por usuario
    echo "4. Verificando tareas por usuario...\n";
    foreach ($users as $user) {
        $userTasks = Task::where('user_id', $user->id)->count();
        echo "   - {$user->name}: {$userTasks} tareas\n";
    }
    
    echo "\n";
    
    // 5. Corregir asignaciones problemáticas
    echo "5. Corrigiendo asignaciones problemáticas...\n";
    
    // Obtener el primer desarrollador disponible
    $developer = User::whereHas('roles', function ($query) {
        $query->where('name', 'developer');
    })->first();
    
    if (!$developer) {
        echo "   ❌ No se encontró ningún desarrollador\n";
        exit(1);
    }
    
    echo "   - Desarrollador para asignaciones: {$developer->name}\n";
    
    // Asignar tareas sin asignar al desarrollador
    $assignedCount = 0;
    if ($unassignedTasks->count() > 0) {
        Task::whereNull('user_id')->update(['user_id' => $developer->id]);
        $assignedCount = $unassignedTasks->count();
        echo "   ✅ Asignadas {$assignedCount} tareas sin asignar a {$developer->name}\n";
    }
    
    // Corregir tareas con usuarios inexistentes
    $fixedCount = 0;
    if ($tasksWithInvalidUsers->count() > 0) {
        Task::whereNotIn('user_id', $users->pluck('id'))->update(['user_id' => $developer->id]);
        $fixedCount = $tasksWithInvalidUsers->count();
        echo "   ✅ Corregidas {$fixedCount} tareas con usuarios inexistentes\n";
    }
    
    echo "\n";
    
    // 6. Verificar proyectos y sprints
    echo "6. Verificando proyectos y sprints...\n";
    $projects = Project::count();
    $sprints = Sprint::count();
    echo "   - Proyectos: {$projects}\n";
    echo "   - Sprints: {$sprints}\n";
    
    // Verificar tareas sin proyecto o sprint
    $tasksWithoutProject = Task::whereNull('project_id')->count();
    $tasksWithoutSprint = Task::whereNull('sprint_id')->count();
    
    echo "   - Tareas sin proyecto: {$tasksWithoutProject}\n";
    echo "   - Tareas sin sprint: {$tasksWithoutSprint}\n";
    
    if ($tasksWithoutProject > 0 || $tasksWithoutSprint > 0) {
        echo "   📝 Corrigiendo tareas sin proyecto o sprint...\n";
        
        $defaultProject = Project::first();
        $defaultSprint = Sprint::first();
        
        if ($defaultProject) {
            Task::whereNull('project_id')->update(['project_id' => $defaultProject->id]);
            echo "   ✅ Asignado proyecto por defecto a tareas sin proyecto\n";
        }
        
        if ($defaultSprint) {
            Task::whereNull('sprint_id')->update(['sprint_id' => $defaultSprint->id]);
            echo "   ✅ Asignado sprint por defecto a tareas sin sprint\n";
        }
    }
    
    echo "\n";
    
    // 7. Estadísticas finales
    echo "7. Estadísticas finales:\n";
    
    $totalTasks = Task::count();
    $assignedTasks = Task::whereNotNull('user_id')->count();
    $unassignedTasks = Task::whereNull('user_id')->count();
    
    echo "   - Total de tareas: {$totalTasks}\n";
    echo "   - Tareas asignadas: {$assignedTasks}\n";
    echo "   - Tareas sin asignar: {$unassignedTasks}\n";
    
    // Verificar tareas por estado
    $todoTasks = Task::where('status', 'to do')->count();
    $inProgressTasks = Task::where('status', 'in progress')->count();
    $doneTasks = Task::where('status', 'done')->count();
    
    echo "   - Tareas 'to do': {$todoTasks}\n";
    echo "   - Tareas 'in progress': {$inProgressTasks}\n";
    echo "   - Tareas 'done': {$doneTasks}\n";
    
    echo "\n🎉 ¡Diagnóstico completado!\n";
    echo "===========================\n";
    echo "Ahora la página de tareas debería mostrar:\n";
    echo "- Tareas correctamente asignadas\n";
    echo "- Proyectos y sprints asociados\n";
    echo "- Estados de tareas válidos\n";
    echo "- Usuarios con permisos correctos\n";
    
} catch (Exception $e) {
    echo "❌ Error durante el diagnóstico: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Sprint;
use App\Models\Task;
use App\Models\Bug;
use App\Models\Project;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Sprint Filters ===\n\n";

try {
    // Obtener todos los sprints con sus relaciones
    $sprints = Sprint::with(['tasks', 'bugs', 'project'])->get();
    
    echo "Total sprints found: " . $sprints->count() . "\n\n";
    
    foreach ($sprints as $sprint) {
        echo "Sprint: {$sprint->name}\n";
        echo "  Project: {$sprint->project->name}\n";
        echo "  Tasks: " . $sprint->tasks->count() . "\n";
        echo "  Bugs: " . $sprint->bugs->count() . "\n";
        echo "  Total items: " . ($sprint->tasks->count() + $sprint->bugs->count()) . "\n";
        echo "\n";
    }
    
    // Probar filtro por tipo de elemento - solo tareas
    echo "=== Testing 'tasks' filter ===\n";
    $sprintsWithTasks = Sprint::whereHas('tasks')->with(['tasks', 'bugs', 'project'])->get();
    echo "Sprints with tasks: " . $sprintsWithTasks->count() . "\n";
    
    foreach ($sprintsWithTasks as $sprint) {
        echo "  - {$sprint->name}: {$sprint->tasks->count()} tasks, {$sprint->bugs->count()} bugs\n";
    }
    
    // Probar filtro por tipo de elemento - solo bugs
    echo "\n=== Testing 'bugs' filter ===\n";
    $sprintsWithBugs = Sprint::whereHas('bugs')->with(['tasks', 'bugs', 'project'])->get();
    echo "Sprints with bugs: " . $sprintsWithBugs->count() . "\n";
    
    foreach ($sprintsWithBugs as $sprint) {
        echo "  - {$sprint->name}: {$sprint->tasks->count()} tasks, {$sprint->bugs->count()} bugs\n";
    }
    
    // Probar filtro por tipo de elemento - todos
    echo "\n=== Testing 'all' filter ===\n";
    $allSprints = Sprint::with(['tasks', 'bugs', 'project'])->get();
    echo "All sprints: " . $allSprints->count() . "\n";
    
    // Mostrar estadísticas generales
    echo "\n=== General Statistics ===\n";
    $totalTasks = Task::count();
    $totalBugs = Bug::count();
    $totalSprints = Sprint::count();
    
    echo "Total tasks in system: {$totalTasks}\n";
    echo "Total bugs in system: {$totalBugs}\n";
    echo "Total sprints in system: {$totalSprints}\n";
    
    // Mostrar sprints con más elementos
    echo "\n=== Sprints with most items ===\n";
    $sprintsWithItemCount = Sprint::with(['tasks', 'bugs'])
        ->get()
        ->map(function ($sprint) {
            $sprint->total_items = $sprint->tasks->count() + $sprint->bugs->count();
            return $sprint;
        })
        ->sortByDesc('total_items');
    
    foreach ($sprintsWithItemCount->take(5) as $sprint) {
        echo "  {$sprint->name}: {$sprint->total_items} items ({$sprint->tasks->count()} tasks, {$sprint->bugs->count()} bugs)\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test completed ===\n"; 
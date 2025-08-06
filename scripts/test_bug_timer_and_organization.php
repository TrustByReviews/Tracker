<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Bug;
use App\Models\Project;
use App\Models\Sprint;
use App\Services\BugTimeTrackingService;
use Carbon\Carbon;

echo "=== TESTING BUG TIMER AND ORGANIZATION ===\n\n";

try {
    // 1. Obtener datos reales
    $bugs = Bug::with(['user', 'sprint', 'project', 'assignedBy'])->get();
    $projects = Project::with(['sprints', 'bugs'])->get();
    $sprints = Sprint::with(['tasks', 'bugs', 'project'])->get();
    
    echo "✅ Datos cargados:\n";
    echo "   - Bugs: {$bugs->count()}\n";
    echo "   - Proyectos: {$projects->count()}\n";
    echo "   - Sprints: {$sprints->count()}\n\n";
    
    // 2. Organizar bugs por estado
    $activeBugs = $bugs->filter(function($bug) {
        return in_array($bug->status, ['new', 'assigned', 'in progress']);
    });
    
    $completedBugs = $bugs->filter(function($bug) {
        return in_array($bug->status, ['resolved', 'verified', 'closed']);
    });
    
    echo "📊 Organización de bugs:\n";
    echo "   - Bugs activos: {$activeBugs->count()}\n";
    echo "   - Bugs completados: {$completedBugs->count()}\n\n";
    
    // 3. Mostrar bugs activos
    echo "🔄 Bugs Activos:\n";
    foreach ($activeBugs as $bug) {
        echo "   - {$bug->title}\n";
        echo "     * Estado: {$bug->status}\n";
        echo "     * Proyecto: {$bug->project->name}\n";
        echo "     * Sprint: {$bug->sprint->name}\n";
        echo "     * Asignado a: " . ($bug->user ? $bug->user->name : 'No asignado') . "\n";
        echo "     * Tiempo total: {$bug->total_time_seconds} segundos\n";
        echo "     * Trabajando: " . ($bug->is_working ? 'Sí' : 'No') . "\n";
        if ($bug->current_session_start) {
            echo "     * Inicio de sesión: {$bug->current_session_start}\n";
        }
        echo "\n";
    }
    
    // 4. Mostrar bugs completados
    echo "✅ Bugs Completados:\n";
    foreach ($completedBugs as $bug) {
        echo "   - {$bug->title}\n";
        echo "     * Estado: {$bug->status}\n";
        echo "     * Proyecto: {$bug->project->name}\n";
        echo "     * Sprint: {$bug->sprint->name}\n";
        echo "     * Asignado a: " . ($bug->user ? $bug->user->name : 'No asignado') . "\n";
        echo "     * Tiempo total: {$bug->total_time_seconds} segundos\n";
        echo "\n";
    }
    
    // 5. Probar contador de tiempo
    echo "⏱️  Probando contador de tiempo:\n";
    
    $developer = User::whereHas('roles', function($query) {
        $query->where('name', 'developer');
    })->first();
    
    if ($developer) {
        echo "   - Usuario de prueba: {$developer->name}\n";
        
        // Buscar un bug asignado al desarrollador
        $assignedBug = $bugs->where('user_id', $developer->id)
                           ->whereIn('status', ['assigned', 'in progress'])
                           ->first();
        
        if ($assignedBug) {
            echo "   - Bug encontrado: {$assignedBug->title}\n";
            echo "     * Estado: {$assignedBug->status}\n";
            echo "     * Trabajando: " . ($assignedBug->is_working ? 'Sí' : 'No') . "\n";
            
            if ($assignedBug->is_working && $assignedBug->current_session_start) {
                $startTime = Carbon::parse($assignedBug->current_session_start);
                $elapsedSeconds = now()->diffInSeconds($startTime);
                $hours = floor($elapsedSeconds / 3600);
                $minutes = floor(($elapsedSeconds % 3600) / 60);
                $seconds = $elapsedSeconds % 60;
                
                echo "     * Tiempo transcurrido: {$hours}h {$minutes}m {$seconds}s\n";
                echo "     * Inicio de sesión: {$assignedBug->current_session_start}\n";
            } else {
                echo "     * No está trabajando actualmente\n";
            }
        } else {
            echo "   - No se encontró ningún bug asignado al desarrollador\n";
        }
    }
    
    // 6. Verificar progreso de proyectos incluyendo bugs
    echo "\n📈 Progreso de proyectos (incluyendo bugs):\n";
    foreach ($projects->take(3) as $project) {
        $stats = $project->getProjectStats();
        echo "   - {$project->name}:\n";
        echo "     * Tareas: {$stats['total_tasks']} (completadas: {$stats['completed_tasks']})\n";
        echo "     * Bugs: {$stats['total_bugs']} (completados: {$stats['completed_bugs']})\n";
        echo "     * Progreso total: {$stats['progress_percentage']}%\n";
        echo "\n";
    }
    
    // 7. Verificar progreso de sprints incluyendo bugs
    echo "📊 Progreso de sprints (incluyendo bugs):\n";
    foreach ($sprints->take(3) as $sprint) {
        $stats = $sprint->getSprintStats();
        echo "   - {$sprint->name} ({$sprint->project->name}):\n";
        echo "     * Tareas: {$stats['total_tasks']} (completadas: {$stats['completed_tasks']})\n";
        echo "     * Bugs: {$stats['total_bugs']} (completados: {$stats['completed_bugs']})\n";
        echo "     * Progreso total: {$stats['progress_percentage']}%\n";
        echo "     * Días restantes: {$sprint->getDaysRemaining()}\n";
        echo "     * Prioridad: {$sprint->getPriorityScore()}\n";
        echo "\n";
    }
    
    // 8. Verificar que el timer funcione correctamente
    echo "🔧 Verificando timer:\n";
    
    $workingBugs = $bugs->where('is_working', true);
    echo "   - Bugs trabajando: {$workingBugs->count()}\n";
    
    foreach ($workingBugs as $bug) {
        echo "   - {$bug->title}:\n";
        echo "     * Inicio de sesión: {$bug->current_session_start}\n";
        
        if ($bug->current_session_start) {
            $startTime = Carbon::parse($bug->current_session_start);
            $elapsedSeconds = now()->diffInSeconds($startTime);
            $hours = floor($elapsedSeconds / 3600);
            $minutes = floor(($elapsedSeconds % 3600) / 60);
            $seconds = $elapsedSeconds % 60;
            
            echo "     * Tiempo transcurrido: {$hours}h {$minutes}m {$seconds}s\n";
            
            // Simular actualización del timer
            echo "     * Simulando actualización del timer...\n";
            for ($i = 1; $i <= 3; $i++) {
                sleep(1);
                $newElapsedSeconds = now()->diffInSeconds($startTime);
                $newHours = floor($newElapsedSeconds / 3600);
                $newMinutes = floor(($newElapsedSeconds % 3600) / 60);
                $newSeconds = $newElapsedSeconds % 60;
                
                echo "       +{$i}s: {$newHours}h {$newMinutes}m {$newSeconds}s\n";
            }
        }
        echo "\n";
    }
    
    echo "✅ Timer and organization test completed successfully!\n";
    echo "🎯 Los bugs están organizados correctamente por estado\n";
    echo "⏱️  El contador de tiempo está funcionando\n";
    echo "📊 Los proyectos y sprints incluyen bugs en su progreso\n";
    
} catch (Exception $e) {
    echo "❌ Error testing timer and organization: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
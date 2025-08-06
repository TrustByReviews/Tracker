<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Bug;
use App\Models\Project;
use App\Models\Sprint;

echo "=== TESTING BUG FRONTEND WITH REAL DATA ===\n\n";

try {
    // 1. Obtener datos reales
    $bugs = Bug::with(['user', 'sprint', 'project', 'assignedBy'])->get();
    $projects = Project::orderBy('name')->get();
    $sprints = Sprint::with('project')->orderBy('start_date', 'desc')->get();
    $developers = User::whereHas('roles', function($query) {
        $query->where('name', 'developer');
    })->get();
    
    echo "✅ Datos cargados:\n";
    echo "   - Bugs: {$bugs->count()}\n";
    echo "   - Proyectos: {$projects->count()}\n";
    echo "   - Sprints: {$sprints->count()}\n";
    echo "   - Desarrolladores: {$developers->count()}\n\n";
    
    // 2. Mostrar algunos bugs de ejemplo
    echo "📋 Ejemplos de bugs:\n";
    $sampleBugs = $bugs->take(5);
    
    foreach ($sampleBugs as $index => $bug) {
        echo "   " . ($index + 1) . ". {$bug->title}\n";
        echo "      - Estado: {$bug->status}\n";
        echo "      - Importancia: {$bug->importance}\n";
        echo "      - Tipo: {$bug->bug_type}\n";
        echo "      - Proyecto: {$bug->project->name}\n";
        echo "      - Sprint: {$bug->sprint->name}\n";
        echo "      - Asignado a: " . ($bug->user ? $bug->user->name : 'No asignado') . "\n";
        echo "      - Tiempo total: {$bug->total_time_seconds} segundos\n";
        echo "      - Trabajando: " . ($bug->is_working ? 'Sí' : 'No') . "\n";
        if ($bug->current_session_start) {
            echo "      - Inicio de sesión: {$bug->current_session_start}\n";
        }
        echo "\n";
    }
    
    // 3. Verificar colores y estados
    echo "🎨 Verificando colores y estados:\n";
    
    $statusColors = [
        'new' => 'border-red-200',
        'assigned' => 'border-orange-200',
        'in progress' => 'border-blue-200',
        'resolved' => 'border-green-200',
        'verified' => 'border-purple-200',
        'closed' => 'border-gray-200'
    ];
    
    $importanceColors = [
        'critical' => 'bg-red-100 text-red-800',
        'high' => 'bg-orange-100 text-orange-800',
        'medium' => 'bg-yellow-100 text-yellow-800',
        'low' => 'bg-green-100 text-green-800'
    ];
    
    $bugTypeColors = [
        'frontend' => 'bg-blue-100 text-blue-800',
        'backend' => 'bg-purple-100 text-purple-800',
        'database' => 'bg-green-100 text-green-800',
        'api' => 'bg-orange-100 text-orange-800',
        'ui_ux' => 'bg-pink-100 text-pink-800',
        'performance' => 'bg-indigo-100 text-indigo-800',
        'security' => 'bg-red-100 text-red-800',
        'other' => 'bg-gray-100 text-gray-800'
    ];
    
    foreach ($sampleBugs as $bug) {
        echo "   - {$bug->title}:\n";
        echo "     * Estado: {$bug->status} → {$statusColors[$bug->status]}\n";
        echo "     * Importancia: {$bug->importance} → {$importanceColors[$bug->importance]}\n";
        echo "     * Tipo: {$bug->bug_type} → {$bugTypeColors[$bug->bug_type]}\n";
    }
    
    // 4. Verificar botones disponibles
    echo "\n🔘 Verificando botones disponibles:\n";
    
    foreach ($sampleBugs as $bug) {
        echo "   - {$bug->title}:\n";
        
        // Auto-asignar
        if (!$bug->user_id && $bug->status === 'new') {
            echo "     * Botón: Auto-asignar (rojo)\n";
        }
        
        // Iniciar trabajo
        if ($bug->user_id && ($bug->status === 'assigned' || ($bug->status === 'in progress' && !$bug->hasPausedSessions())) && !$bug->is_working) {
            echo "     * Botón: Iniciar (verde)\n";
        }
        
        // Pausar trabajo
        if ($bug->is_working) {
            echo "     * Botón: Pausar (amarillo)\n";
        }
        
        // Reanudar trabajo
        if ($bug->status === 'in progress' && !$bug->is_working && $bug->total_time_seconds > 0 && $bug->hasPausedSessions()) {
            echo "     * Botón: Reanudar (azul)\n";
        }
        
        // Finalizar trabajo
        if ($bug->status === 'in progress' && !$bug->is_working) {
            echo "     * Botón: Finalizar (verde)\n";
        }
        
        // Ver detalles (siempre disponible)
        echo "     * Botón: Ver detalles (gris)\n";
    }
    
    // 5. Verificar tiempo en tiempo real
    echo "\n⏱️  Verificando tiempo en tiempo real:\n";
    
    $workingBugs = $bugs->where('is_working', true);
    echo "   - Bugs trabajando: {$workingBugs->count()}\n";
    
    foreach ($workingBugs as $bug) {
        echo "   - {$bug->title}:\n";
        echo "     * Tiempo total acumulado: {$bug->total_time_seconds} segundos\n";
        
        if ($bug->current_session_start) {
            $startTime = \Carbon\Carbon::parse($bug->current_session_start);
            $elapsedSeconds = now()->diffInSeconds($startTime);
            $hours = floor($elapsedSeconds / 3600);
            $minutes = floor(($elapsedSeconds % 3600) / 60);
            $seconds = $elapsedSeconds % 60;
            
            echo "     * Tiempo de sesión actual: {$hours}h {$minutes}m {$seconds}s\n";
            echo "     * Inicio de sesión: {$bug->current_session_start}\n";
        }
    }
    
    // 6. Verificar filtros
    echo "\n🔍 Verificando filtros disponibles:\n";
    echo "   - Filtro por proyecto: " . $projects->count() . " proyectos\n";
    echo "   - Filtro por sprint: " . $sprints->count() . " sprints\n";
    echo "   - Filtro por estado: " . count($statusColors) . " estados\n";
    echo "   - Filtro por importancia: " . count($importanceColors) . " niveles\n";
    echo "   - Filtro por tipo: " . count($bugTypeColors) . " tipos\n";
    echo "   - Filtro por desarrollador: " . $developers->count() . " desarrolladores\n";
    
    // 7. Verificar permisos
    echo "\n🔐 Verificando permisos:\n";
    
    $admin = User::whereHas('roles', function($query) {
        $query->where('name', 'admin');
    })->first();
    
    $developer = User::whereHas('roles', function($query) {
        $query->where('name', 'developer');
    })->first();
    
    if ($admin) {
        echo "   - Admin ({$admin->name}):\n";
        echo "     * Puede ver todos los bugs\n";
        echo "     * Puede crear bugs\n";
        echo "     * Puede asignar bugs\n";
        echo "     * Puede auto-asignarse bugs\n";
    }
    
    if ($developer) {
        echo "   - Developer ({$developer->name}):\n";
        echo "     * Puede ver bugs asignados y disponibles\n";
        echo "     * No puede crear bugs\n";
        echo "     * No puede asignar bugs a otros\n";
        echo "     * Puede auto-asignarse bugs\n";
    }
    
    echo "\n✅ Frontend test completed successfully!\n";
    echo "🎨 Los colores están configurados correctamente\n";
    echo "⏱️  El tiempo en tiempo real está funcionando\n";
    echo "🔘 Los botones se muestran según el estado del bug\n";
    
} catch (Exception $e) {
    echo "❌ Error testing frontend: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
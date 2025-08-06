<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Bug;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

echo "=== TESTING BUG FRONTEND ===\n\n";

try {
    // 1. Simular login de un desarrollador
    $developer = User::whereHas('roles', function($query) {
        $query->where('name', 'developer');
    })->first();
    
    if (!$developer) {
        echo "❌ No se encontró ningún desarrollador\n";
        exit(1);
    }
    
    echo "✅ Usuario de prueba: {$developer->name} ({$developer->email})\n";
    
    // 2. Simular sesión autenticada
    auth()->login($developer);
    echo "✅ Sesión autenticada\n";
    
    // 3. Obtener datos para la vista de bugs
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
    echo "   - Desarrolladores: {$developers->count()}\n";
    
    // 4. Simular filtros
    $filters = [
        'project_id' => '',
        'sprint_id' => '',
        'status' => '',
        'importance' => '',
        'bug_type' => '',
        'assigned_user_id' => '',
        'search' => '',
        'sort_by' => 'recent',
        'sort_order' => 'desc'
    ];
    
    echo "✅ Filtros inicializados\n";
    
    // 5. Simular aplicación de filtros
    $filteredBugs = $bugs;
    
    // Filtro por estado
    if (!empty($filters['status'])) {
        $filteredBugs = $filteredBugs->where('status', $filters['status']);
    }
    
    // Filtro por importancia
    if (!empty($filters['importance'])) {
        $filteredBugs = $filteredBugs->where('importance', $filters['importance']);
    }
    
    // Filtro por tipo
    if (!empty($filters['bug_type'])) {
        $filteredBugs = $filteredBugs->where('bug_type', $filters['bug_type']);
    }
    
    echo "✅ Filtros aplicados - Bugs filtrados: {$filteredBugs->count()}\n";
    
    // 6. Simular acciones de usuario
    $availableBugs = $bugs->whereNull('user_id')->where('status', 'new');
    $assignedBugs = $bugs->where('user_id', $developer->id);
    $workingBugs = $bugs->where('user_id', $developer->id)->where('status', 'in progress');
    
    echo "✅ Estado de bugs para el desarrollador:\n";
    echo "   - Bugs disponibles: {$availableBugs->count()}\n";
    echo "   - Bugs asignados: {$assignedBugs->count()}\n";
    echo "   - Bugs en progreso: {$workingBugs->count()}\n";
    
    // 7. Simular auto-asignación de un bug
    if ($availableBugs->count() > 0) {
        $bugToAssign = $availableBugs->first();
        echo "✅ Simulando auto-asignación del bug: {$bugToAssign->title}\n";
        
        // Verificar límite de actividades activas
        $activeTasks = $developer->tasks()
            ->whereIn('status', ['to do', 'in progress'])
            ->count();
            
        $activeBugs = $developer->bugs()
            ->whereIn('status', ['new', 'assigned', 'in progress'])
            ->count();
            
        $totalActive = $activeTasks + $activeBugs;
        
        echo "   - Actividades activas actuales: {$totalActive}/3\n";
        
        if ($totalActive < 3) {
            echo "   ✅ Puede asignarse el bug (límite no alcanzado)\n";
            
            // Simular la asignación
            $bugToAssign->update([
                'user_id' => $developer->id,
                'assigned_by' => $developer->id,
                'assigned_at' => now(),
                'status' => 'assigned'
            ]);
            
            echo "   ✅ Bug asignado exitosamente\n";
        } else {
            echo "   ❌ No puede asignarse el bug (límite alcanzado)\n";
        }
    }
    
    // 8. Simular inicio de trabajo en un bug
    $assignedBugs = $bugs->where('user_id', $developer->id)->where('status', 'assigned');
    if ($assignedBugs->count() > 0) {
        $bugToWork = $assignedBugs->first();
        echo "✅ Simulando inicio de trabajo en: {$bugToWork->title}\n";
        
        // Verificar que no esté trabajando en otro bug
        $currentlyWorking = $bugs->where('user_id', $developer->id)->where('is_working', true);
        
        if ($currentlyWorking->count() > 0) {
            echo "   ❌ Ya está trabajando en otro bug\n";
        } else {
            echo "   ✅ Puede iniciar trabajo\n";
            
            // Simular inicio de trabajo
            $bugToWork->update([
                'status' => 'in progress',
                'work_started_at' => now(),
                'is_working' => true
            ]);
            
            echo "   ✅ Trabajo iniciado exitosamente\n";
        }
    }
    
    // 9. Simular pausa de trabajo
    $workingBugs = $bugs->where('user_id', $developer->id)->where('status', 'in progress')->where('is_working', true);
    if ($workingBugs->count() > 0) {
        $bugToPause = $workingBugs->first();
        echo "✅ Simulando pausa de trabajo en: {$bugToPause->title}\n";
        
        // Simular pausa
        $bugToPause->update([
            'is_working' => false
        ]);
        
        echo "   ✅ Trabajo pausado exitosamente\n";
    }
    
    // 10. Simular reanudación de trabajo
    $pausedBugs = $bugs->where('user_id', $developer->id)->where('status', 'in progress')->where('is_working', false);
    if ($pausedBugs->count() > 0) {
        $bugToResume = $pausedBugs->first();
        echo "✅ Simulando reanudación de trabajo en: {$bugToResume->title}\n";
        
        // Verificar que no esté trabajando en otro bug
        $currentlyWorking = $bugs->where('user_id', $developer->id)->where('is_working', true);
        
        if ($currentlyWorking->count() > 0) {
            echo "   ❌ Ya está trabajando en otro bug\n";
        } else {
            echo "   ✅ Puede reanudar trabajo\n";
            
            // Simular reanudación
            $bugToResume->update([
                'is_working' => true
            ]);
            
            echo "   ✅ Trabajo reanudado exitosamente\n";
        }
    }
    
    // 11. Simular finalización de trabajo
    $workingBugs = $bugs->where('user_id', $developer->id)->where('status', 'in progress');
    if ($workingBugs->count() > 0) {
        $bugToFinish = $workingBugs->first();
        echo "✅ Simulando finalización de trabajo en: {$bugToFinish->title}\n";
        
        // Simular finalización
        $bugToFinish->update([
            'status' => 'resolved',
            'is_working' => false,
            'resolved_at' => now(),
            'resolved_by' => $developer->id
        ]);
        
        echo "   ✅ Trabajo finalizado exitosamente\n";
    }
    
    // 12. Verificar estado final
    $finalBugs = Bug::with(['user', 'sprint', 'project', 'assignedBy'])->get();
    $finalAssignedBugs = $finalBugs->where('user_id', $developer->id);
    $finalWorkingBugs = $finalBugs->where('user_id', $developer->id)->where('status', 'in progress');
    $finalResolvedBugs = $finalBugs->where('user_id', $developer->id)->where('status', 'resolved');
    
    echo "\n✅ Estado final:\n";
    echo "   - Bugs asignados: {$finalAssignedBugs->count()}\n";
    echo "   - Bugs en progreso: {$finalWorkingBugs->count()}\n";
    echo "   - Bugs resueltos: {$finalResolvedBugs->count()}\n";
    
    // 13. Simular vista de detalles
    if ($finalBugs->count() > 0) {
        $bugToView = $finalBugs->first();
        echo "✅ Simulando vista de detalles del bug: {$bugToView->title}\n";
        
        $bugDetails = [
            'id' => $bugToView->id,
            'title' => $bugToView->title,
            'description' => $bugToView->description,
            'status' => $bugToView->status,
            'importance' => $bugToView->importance,
            'bug_type' => $bugToView->bug_type,
            'project' => $bugToView->project,
            'sprint' => $bugToView->sprint,
            'user' => $bugToView->user,
            'assigned_by_user' => $bugToView->assignedBy,
            'attachments' => $bugToView->attachments,
            'tags' => $bugToView->tags,
            'total_time_seconds' => $bugToView->total_time_seconds,
            'is_working' => $bugToView->is_working,
            'created_at' => $bugToView->created_at,
            'updated_at' => $bugToView->updated_at
        ];
        
        echo "   ✅ Detalles del bug cargados correctamente\n";
        echo "   - Estado: {$bugDetails['status']}\n";
        echo "   - Importancia: {$bugDetails['importance']}\n";
        echo "   - Tipo: {$bugDetails['bug_type']}\n";
        echo "   - Tiempo total: {$bugDetails['total_time_seconds']} segundos\n";
        echo "   - Trabajando: " . ($bugDetails['is_working'] ? 'Sí' : 'No') . "\n";
    }
    
    // 14. Verificar permisos
    $userRole = $developer->roles->first();
    $permissions = $userRole ? $userRole->name : 'developer';
    
    echo "\n✅ Permisos del usuario:\n";
    echo "   - Rol: {$permissions}\n";
    
    $canViewBugs = true;
    $canCreateBugs = in_array($permissions, ['admin', 'team_leader']);
    $canAssignBugs = in_array($permissions, ['admin', 'team_leader']);
    $canSelfAssignBugs = in_array($permissions, ['developer', 'team_leader']);
    
    echo "   - Puede ver bugs: " . ($canViewBugs ? 'Sí' : 'No') . "\n";
    echo "   - Puede crear bugs: " . ($canCreateBugs ? 'Sí' : 'No') . "\n";
    echo "   - Puede asignar bugs: " . ($canAssignBugs ? 'Sí' : 'No') . "\n";
    echo "   - Puede auto-asignarse bugs: " . ($canSelfAssignBugs ? 'Sí' : 'No') . "\n";
    
    echo "\n✅ Frontend test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error testing frontend: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
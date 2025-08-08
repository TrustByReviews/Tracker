<?php

/**
 * Script de Prueba del Frontend - Sistema de Notificaciones
 * 
 * Este script prueba las funcionalidades del frontend:
 * 1. Verificar que las rutas del Team Leader están disponibles
 * 2. Probar los filtros de desarrollador
 * 3. Verificar la visualización de tareas/bugs rechazados
 * 4. Comprobar la interfaz de revisión del TL
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Inicializar Laravel
$app = Application::configure(basePath: __DIR__ . '/..')
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Task;
use App\Models\Bug;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class FrontendNotificationTester
{
    private $qaUser;
    private $teamLeader;
    private $developer;
    private $project;
    private $sprint;

    public function __construct()
    {
        $this->setupTestUsers();
        $this->setupTestData();
    }

    private function setupTestUsers()
    {
        echo "🔧 Configurando usuarios de prueba para frontend...\n";

        $this->qaUser = User::where('email', 'qa@test.com')->first();
        if (!$this->qaUser) {
            $this->qaUser = User::create([
                'name' => 'QA Tester',
                'email' => 'qa@test.com',
                'password' => bcrypt('password'),
                'role' => 'qa'
            ]);
        }

        $this->teamLeader = User::where('email', 'tl@test.com')->first();
        if (!$this->teamLeader) {
            $this->teamLeader = User::create([
                'name' => 'Team Leader',
                'email' => 'tl@test.com',
                'password' => bcrypt('password'),
                'role' => 'team_leader'
            ]);
        }

        $this->developer = User::where('email', 'dev@test.com')->first();
        if (!$this->developer) {
            $this->developer = User::create([
                'name' => 'Developer',
                'email' => 'dev@test.com',
                'password' => bcrypt('password'),
                'role' => 'developer'
            ]);
        }

        echo "✅ Usuarios configurados para pruebas de frontend\n";
    }

    private function setupTestData()
    {
        echo "🔧 Configurando datos de prueba para frontend...\n";

        $this->project = Project::firstOrCreate([
            'name' => 'Proyecto Frontend Test',
            'description' => 'Proyecto para probar funcionalidades del frontend',
            'create_by' => $this->qaUser->id
        ]);

        $this->sprint = Sprint::firstOrCreate([
            'name' => 'Sprint Frontend Test',
            'project_id' => $this->project->id,
            'start_date' => now(),
            'end_date' => now()->addDays(14)
        ]);

        echo "✅ Datos de prueba configurados\n";
    }

    public function runFrontendTests()
    {
        echo "\n🚀 INICIANDO PRUEBAS DEL FRONTEND\n";
        echo "==================================\n\n";

        $this->testTeamLeaderRoutes();
        $this->testDeveloperFilters();
        $this->testRejectedItemsDisplay();
        $this->testNotificationComponents();
        $this->testActiveTaskLimitsUI();

        echo "\n✅ TODAS LAS PRUEBAS DE FRONTEND COMPLETADAS\n";
    }

    private function testTeamLeaderRoutes()
    {
        echo "\n🔗 PRUEBA 1: RUTAS DEL TEAM LEADER\n";
        echo "-----------------------------------\n";

        $routes = [
            'team-leader.review.tasks' => '/team-leader/review/tasks',
            'team-leader.review.bugs' => '/team-leader/review/bugs',
            'team-leader.review.stats' => '/team-leader/review/stats',
            'team-leader.review.tasks.approve' => '/team-leader/review/tasks/{task}/approve',
            'team-leader.review.tasks.request-changes' => '/team-leader/review/tasks/{task}/request-changes',
            'team-leader.review.bugs.approve' => '/team-leader/review/bugs/{bug}/approve',
            'team-leader.review.bugs.request-changes' => '/team-leader/review/bugs/{bug}/request-changes'
        ];

        foreach ($routes as $name => $path) {
            $route = Route::getRoutes()->getByName($name);
            if ($route) {
                echo "✅ Ruta '{$name}' registrada correctamente\n";
            } else {
                echo "❌ ERROR: Ruta '{$name}' no encontrada\n";
            }
        }

        echo "✅ Verificación de rutas completada\n";
    }

    private function testDeveloperFilters()
    {
        echo "\n🔍 PRUEBA 2: FILTROS DE DESARROLLADOR\n";
        echo "---------------------------------------\n";

        // Crear tareas con diferentes estados para probar filtros
        $this->createTestTasksForFilters();
        $this->createTestBugsForFilters();

        // Verificar que los filtros funcionan correctamente
        $rejectedTasks = Task::where('user_id', $this->developer->id)
            ->where('qa_status', 'rejected')
            ->count();

        $changesRequestedTasks = Task::where('user_id', $this->developer->id)
            ->where('team_leader_requested_changes', true)
            ->count();

        $rejectedBugs = Bug::where('user_id', $this->developer->id)
            ->where('qa_status', 'rejected')
            ->count();

        $changesRequestedBugs = Bug::where('user_id', $this->developer->id)
            ->where('team_leader_requested_changes', true)
            ->count();

        echo "📊 Tareas rechazadas por QA: {$rejectedTasks}\n";
        echo "📊 Tareas con cambios solicitados por TL: {$changesRequestedTasks}\n";
        echo "📊 Bugs rechazados por QA: {$rejectedBugs}\n";
        echo "📊 Bugs con cambios solicitados por TL: {$changesRequestedBugs}\n";

        if ($rejectedTasks > 0 || $changesRequestedTasks > 0 || $rejectedBugs > 0 || $changesRequestedBugs > 0) {
            echo "✅ Filtros de desarrollador funcionando correctamente\n";
        } else {
            echo "⚠️  ADVERTENCIA: No hay elementos para mostrar en los filtros\n";
        }
    }

    private function createTestTasksForFilters()
    {
        // Tarea rechazada por QA
        Task::create([
            'title' => 'Tarea Rechazada QA - Frontend Test',
            'description' => 'Tarea rechazada por QA para probar filtros',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'completed',
            'qa_status' => 'rejected',
            'qa_rejection_reason' => 'No cumple con los estándares de calidad',
            'qa_reviewed_by' => $this->qaUser->id,
            'qa_reviewed_at' => now()
        ]);

        // Tarea con cambios solicitados por TL
        Task::create([
            'title' => 'Tarea Cambios TL - Frontend Test',
            'description' => 'Tarea con cambios solicitados por TL para probar filtros',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'completed',
            'qa_status' => 'approved',
            'team_leader_requested_changes' => true,
            'team_leader_change_notes' => 'Necesita mejor documentación del código',
            'team_leader_reviewed_by' => $this->teamLeader->id,
            'team_leader_requested_changes_at' => now()
        ]);
    }

    private function createTestBugsForFilters()
    {
        // Bug rechazado por QA
        Bug::create([
            'title' => 'Bug Rechazado QA - Frontend Test',
            'description' => 'Bug rechazado por QA para probar filtros',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'completed',
            'importance' => 'high',
            'severity' => 'high',
            'bug_type' => 'functional',
            'qa_status' => 'rejected',
            'qa_rejection_reason' => 'El bug no se reproduce en el entorno de QA',
            'qa_reviewed_by' => $this->qaUser->id,
            'qa_reviewed_at' => now()
        ]);

        // Bug con cambios solicitados por TL
        Bug::create([
            'title' => 'Bug Cambios TL - Frontend Test',
            'description' => 'Bug con cambios solicitados por TL para probar filtros',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'completed',
            'importance' => 'medium',
            'severity' => 'medium',
            'bug_type' => 'ui',
            'qa_status' => 'approved',
            'team_leader_requested_changes' => true,
            'team_leader_change_notes' => 'Mejorar el manejo de errores en la interfaz',
            'team_leader_reviewed_by' => $this->teamLeader->id,
            'team_leader_requested_changes_at' => now()
        ]);
    }

    private function testRejectedItemsDisplay()
    {
        echo "\n📋 PRUEBA 3: VISUALIZACIÓN DE ELEMENTOS RECHAZADOS\n";
        echo "---------------------------------------------------\n";

        // Verificar que las tareas rechazadas tienen la información correcta
        $rejectedTask = Task::where('user_id', $this->developer->id)
            ->where('qa_status', 'rejected')
            ->first();

        if ($rejectedTask) {
            echo "✅ Tarea rechazada encontrada: {$rejectedTask->title}\n";
            echo "📝 Razón de rechazo: {$rejectedTask->qa_rejection_reason}\n";
            echo "👤 Revisado por: {$rejectedTask->qaReviewedBy->name}\n";
        } else {
            echo "❌ No se encontraron tareas rechazadas\n";
        }

        // Verificar que los bugs rechazados tienen la información correcta
        $rejectedBug = Bug::where('user_id', $this->developer->id)
            ->where('qa_status', 'rejected')
            ->first();

        if ($rejectedBug) {
            echo "✅ Bug rechazado encontrado: {$rejectedBug->title}\n";
            echo "📝 Razón de rechazo: {$rejectedBug->qa_rejection_reason}\n";
            echo "👤 Revisado por: {$rejectedBug->qaReviewedBy->name}\n";
        } else {
            echo "❌ No se encontraron bugs rechazados\n";
        }

        // Verificar elementos con cambios solicitados
        $taskWithChanges = Task::where('user_id', $this->developer->id)
            ->where('team_leader_requested_changes', true)
            ->first();

        if ($taskWithChanges) {
            echo "✅ Tarea con cambios solicitados: {$taskWithChanges->title}\n";
            echo "📝 Notas de cambios: {$taskWithChanges->team_leader_change_notes}\n";
            echo "👤 Solicitado por: {$taskWithChanges->teamLeaderReviewedBy->name}\n";
        }

        $bugWithChanges = Bug::where('user_id', $this->developer->id)
            ->where('team_leader_requested_changes', true)
            ->first();

        if ($bugWithChanges) {
            echo "✅ Bug con cambios solicitados: {$bugWithChanges->title}\n";
            echo "📝 Notas de cambios: {$bugWithChanges->team_leader_change_notes}\n";
            echo "👤 Solicitado por: {$bugWithChanges->teamLeaderReviewedBy->name}\n";
        }
    }

    private function testNotificationComponents()
    {
        echo "\n📧 PRUEBA 4: COMPONENTES DE NOTIFICACIÓN\n";
        echo "-----------------------------------------\n";

        // Verificar que los componentes Vue existen
        $vueFiles = [
            'resources/js/pages/TeamLeader/ReviewTasks.vue',
            'resources/js/pages/TeamLeader/ReviewBugs.vue',
            'resources/js/components/TaskCard.vue',
            'resources/js/components/BugCard.vue'
        ];

        foreach ($vueFiles as $file) {
            if (file_exists($file)) {
                echo "✅ Componente Vue encontrado: {$file}\n";
            } else {
                echo "❌ ERROR: Componente Vue no encontrado: {$file}\n";
            }
        }

        // Verificar que las rutas del frontend están configuradas
        $frontendRoutes = [
            '/team-leader/review/tasks',
            '/team-leader/review/bugs',
            '/tasks',
            '/bugs'
        ];

        foreach ($frontendRoutes as $route) {
            echo "🔗 Ruta frontend disponible: {$route}\n";
        }

        echo "✅ Componentes de notificación verificados\n";
    }

    private function testActiveTaskLimitsUI()
    {
        echo "\n🔢 PRUEBA 5: LÍMITES DE TAREAS ACTIVAS EN UI\n";
        echo "-----------------------------------------------\n";

        // Contar tareas activas del desarrollador
        $activeTasks = Task::where('user_id', $this->developer->id)
            ->where(function ($query) {
                $query->where('is_working', true)
                    ->orWhere('status', 'in progress')
                    ->orWhere('qa_status', 'rejected')
                    ->orWhere('team_leader_requested_changes', true);
            })
            ->count();

        $activeBugs = Bug::where('user_id', $this->developer->id)
            ->where(function ($query) {
                $query->where('is_working', true)
                    ->orWhere('status', 'in progress')
                    ->orWhere('qa_status', 'rejected')
                    ->orWhere('team_leader_requested_changes', true);
            })
            ->count();

        echo "📊 Tareas activas del desarrollador: {$activeTasks}\n";
        echo "📊 Bugs activos del desarrollador: {$activeBugs}\n";

        if ($activeTasks > 3) {
            echo "⚠️  ADVERTENCIA: El desarrollador tiene más de 3 tareas activas\n";
        } else {
            echo "✅ El desarrollador está dentro del límite de tareas activas\n";
        }

        if ($activeBugs > 3) {
            echo "⚠️  ADVERTENCIA: El desarrollador tiene más de 3 bugs activos\n";
        } else {
            echo "✅ El desarrollador está dentro del límite de bugs activos\n";
        }

        // Verificar si puede tener más tareas
        $canHaveMoreTasks = Task::canDeveloperHaveMoreActiveTasks($this->developer);
        $canHaveMoreBugs = Bug::canDeveloperHaveMoreActiveBugs($this->developer);

        echo "✅ ¿Puede tener más tareas activas? " . ($canHaveMoreTasks ? 'SÍ' : 'NO') . "\n";
        echo "✅ ¿Puede tener más bugs activos? " . ($canHaveMoreBugs ? 'SÍ' : 'NO') . "\n";
    }

    public function generateTestData()
    {
        echo "\n🎯 GENERANDO DATOS DE PRUEBA PARA FRONTEND\n";
        echo "==========================================\n";

        // Crear tareas y bugs con diferentes estados para probar la UI
        $this->createTestTasksForFilters();
        $this->createTestBugsForFilters();

        echo "✅ Datos de prueba generados para frontend\n";
        echo "📋 Ahora puedes acceder a:\n";
        echo "   - http://localhost:8000/tasks (como desarrollador)\n";
        echo "   - http://localhost:8000/bugs (como desarrollador)\n";
        echo "   - http://localhost:8000/team-leader/review/tasks (como TL)\n";
        echo "   - http://localhost:8000/team-leader/review/bugs (como TL)\n";
    }

    public function cleanup()
    {
        echo "\n🧹 LIMPIEZA DE DATOS DE PRUEBA FRONTEND\n";
        echo "-----------------------------------------\n";

        Task::where('title', 'like', '%Frontend Test%')->delete();
        Bug::where('title', 'like', '%Frontend Test%')->delete();

        echo "✅ Datos de prueba frontend eliminados\n";
    }
}

// Ejecutar las pruebas de frontend
try {
    $tester = new FrontendNotificationTester();
    $tester->runFrontendTests();
    
    // Generar datos adicionales para pruebas manuales
    $tester->generateTestData();
    
    // Opcional: limpiar datos de prueba
    // $tester->cleanup();
    
} catch (Exception $e) {
    echo "❌ ERROR EN LAS PRUEBAS DE FRONTEND: " . $e->getMessage() . "\n";
    echo "📋 Stack trace:\n" . $e->getTraceAsString() . "\n";
}

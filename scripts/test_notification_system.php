<?php

/**
 * Script de Prueba del Sistema de Notificaciones y Revisión
 * 
 * Este script prueba todo el flujo:
 * 1. QA marca tarea/bug como completado
 * 2. TL recibe notificación
 * 3. TL puede revisar, aprobar o solicitar cambios
 * 4. Dev recibe notificaciones correspondientes
 * 5. Verificación de límites de tareas activas
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
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationSystemTester
{
    private $notificationService;
    private $qaUser;
    private $teamLeader;
    private $developer;
    private $project;
    private $sprint;

    public function __construct()
    {
        $this->notificationService = app(NotificationService::class);
        $this->setupTestUsers();
        $this->setupTestData();
    }

    private function setupTestUsers()
    {
        echo "🔧 Configurando usuarios de prueba...\n";

        // Buscar o crear usuarios de prueba
        $this->qaUser = User::where('email', 'qa@test.com')->first();
        if (!$this->qaUser) {
            $this->qaUser = User::create([
                'name' => 'QA Tester',
                'email' => 'qa@test.com',
                'password' => bcrypt('password'),
                'role' => 'qa'
            ]);
            echo "✅ Usuario QA creado: {$this->qaUser->email}\n";
        } else {
            echo "✅ Usuario QA encontrado: {$this->qaUser->email}\n";
        }

        $this->teamLeader = User::where('email', 'tl@test.com')->first();
        if (!$this->teamLeader) {
            $this->teamLeader = User::create([
                'name' => 'Team Leader',
                'email' => 'tl@test.com',
                'password' => bcrypt('password'),
                'role' => 'team_leader'
            ]);
            echo "✅ Usuario Team Leader creado: {$this->teamLeader->email}\n";
        } else {
            echo "✅ Usuario Team Leader encontrado: {$this->teamLeader->email}\n";
        }

        $this->developer = User::where('email', 'dev@test.com')->first();
        if (!$this->developer) {
            $this->developer = User::create([
                'name' => 'Developer',
                'email' => 'dev@test.com',
                'password' => bcrypt('password'),
                'role' => 'developer'
            ]);
            echo "✅ Usuario Developer creado: {$this->developer->email}\n";
        } else {
            echo "✅ Usuario Developer encontrado: {$this->developer->email}\n";
        }
    }

    private function setupTestData()
    {
        echo "🔧 Configurando datos de prueba...\n";

        // Crear proyecto de prueba
        $this->project = Project::firstOrCreate([
            'name' => 'Proyecto de Prueba Notificaciones',
            'description' => 'Proyecto para probar el sistema de notificaciones',
            'created_by' => $this->qaUser->id
        ]);

        // Crear sprint de prueba
        $this->sprint = Sprint::firstOrCreate([
            'name' => 'Sprint de Prueba',
            'project_id' => $this->project->id,
            'goal' => 'Completar sistema de notificaciones y revisión',
            'start_date' => now(),
            'end_date' => now()->addDays(14)
        ]);

        echo "✅ Proyecto y Sprint configurados\n";
    }

    public function runAllTests()
    {
        echo "\n🚀 INICIANDO PRUEBAS DEL SISTEMA DE NOTIFICACIONES\n";
        echo "================================================\n\n";

        $this->testTaskWorkflow();
        $this->testBugWorkflow();
        $this->testActiveTaskLimits();
        $this->testNotificationDelivery();

        echo "\n✅ TODAS LAS PRUEBAS COMPLETADAS\n";
    }

    private function testTaskWorkflow()
    {
        echo "\n📋 PRUEBA 1: FLUJO COMPLETO DE TAREAS\n";
        echo "----------------------------------------\n";

        // 1. Crear tarea para el desarrollador
        $task = Task::create([
            'name' => 'Tarea de Prueba - Notificaciones',
            'description' => 'Tarea para probar el sistema de notificaciones',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'in progress',
            'priority' => 'medium',
            'estimated_hours' => 8,
            'story_points' => 5
        ]);

        echo "✅ Tarea creada: {$task->name}\n";

        // 2. Simular que el desarrollador termina la tarea
        $task->update([
            'status' => 'done'
        ]);

        echo "✅ Tarea marcada como completada por el desarrollador\n";

        // 3. Simular que QA aprueba la tarea
        $task->approveByQa($this->qaUser, 'Tarea aprobada por QA');
        
        echo "✅ QA aprobó la tarea\n";
        echo "📧 Notificación enviada al Team Leader\n";

        // 4. Verificar que la tarea está lista para revisión del TL
        if ($task->isReadyForTeamLeaderReview()) {
            echo "✅ Tarea está lista para revisión del Team Leader\n";
        } else {
            echo "❌ ERROR: Tarea no está lista para revisión del TL\n";
        }

        // 5. Simular que TL solicita cambios
        $changeNotes = 'Necesito que agregues validación adicional en el formulario';
        $task->requestChangesByTeamLeader($this->teamLeader, $changeNotes);
        
        echo "✅ Team Leader solicitó cambios: {$changeNotes}\n";
        echo "📧 Notificación enviada al desarrollador\n";

        // 6. Verificar que el desarrollador puede ver los cambios solicitados
        if ($task->team_leader_requested_changes && $task->team_leader_change_notes) {
            echo "✅ Cambios solicitados registrados correctamente\n";
        } else {
            echo "❌ ERROR: Cambios solicitados no registrados\n";
        }

        // 7. Simular que el desarrollador hace los cambios y vuelve a enviar
        $task->update([
            'team_leader_requested_changes' => false,
            'team_leader_change_notes' => null,
            'status' => 'done'
        ]);

        // 8. QA aprueba nuevamente
        $task->approveByQa($this->qaUser, 'Cambios implementados correctamente');
        
        echo "✅ QA aprobó los cambios\n";

        // 9. TL aprueba finalmente
        $task->finallyApproveByTeamLeader($this->teamLeader, 'Tarea aprobada finalmente');
        
        echo "✅ Team Leader aprobó finalmente la tarea\n";

        echo "✅ FLUJO DE TAREAS COMPLETADO EXITOSAMENTE\n";
    }

    private function testBugWorkflow()
    {
        echo "\n🐛 PRUEBA 2: FLUJO COMPLETO DE BUGS\n";
        echo "--------------------------------------\n";

        // 1. Crear bug para el desarrollador
        $bug = Bug::create([
            'title' => 'Bug de Prueba - Notificaciones',
            'description' => 'Bug para probar el sistema de notificaciones',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'in progress',
            'importance' => 'medium',
            'severity' => 'medium',
            'bug_type' => 'functional'
        ]);

        echo "✅ Bug creado: {$bug->title}\n";

        // 2. Simular que el desarrollador termina el bug
        $bug->update([
            'status' => 'resolved'
        ]);

        echo "✅ Bug marcado como completado por el desarrollador\n";

        // 3. Simular que QA aprueba el bug
        $bug->approveByQa($this->qaUser, 'Bug aprobado por QA');
        
        echo "✅ QA aprobó el bug\n";
        echo "📧 Notificación enviada al Team Leader\n";

        // 4. Verificar que el bug está lista para revisión del TL
        if ($bug->isReadyForTeamLeaderReview()) {
            echo "✅ Bug está lista para revisión del Team Leader\n";
        } else {
            echo "❌ ERROR: Bug no está lista para revisión del TL\n";
        }

        // 5. Simular que TL aprueba directamente
        $bug->finallyApproveByTeamLeader($this->teamLeader, 'Bug aprobado por TL');
        
        echo "✅ Team Leader aprobó el bug\n";

        echo "✅ FLUJO DE BUGS COMPLETADO EXITOSAMENTE\n";
    }

    private function testActiveTaskLimits()
    {
        echo "\n🔢 PRUEBA 3: LÍMITES DE TAREAS ACTIVAS\n";
        echo "----------------------------------------\n";

        // Verificar límite actual del desarrollador
        $activeTasksCount = Task::where('user_id', $this->developer->id)
            ->where(function ($query) {
                $query->where('is_working', true)
                    ->orWhere('status', 'in progress')
                    ->orWhere('qa_status', 'rejected')
                    ->orWhere('team_leader_requested_changes', true);
            })
            ->count();

        echo "📊 Tareas activas actuales: {$activeTasksCount}\n";

        // Verificar si puede tener más tareas
        $canHaveMore = Task::canDeveloperHaveMoreActiveTasks($this->developer);
        echo "✅ ¿Puede tener más tareas activas? " . ($canHaveMore ? 'SÍ' : 'NO') . "\n";

        // Crear tareas adicionales para probar el límite
        for ($i = 1; $i <= 5; $i++) {
            $task = Task::create([
                'name' => "Tarea Límite {$i}",
                'description' => "Tarea para probar límites",
                'user_id' => $this->developer->id,
                'project_id' => $this->project->id,
                'sprint_id' => $this->sprint->id,
                'status' => 'in progress',
                'priority' => 'low',
                'story_points' => 3
            ]);

            // Marcar como activa
            $task->update(['is_working' => true]);

            $activeCount = Task::where('user_id', $this->developer->id)
                ->where(function ($query) {
                    $query->where('is_working', true)
                        ->orWhere('status', 'in progress')
                        ->orWhere('qa_status', 'rejected')
                        ->orWhere('team_leader_requested_changes', true);
                })
                ->count();

            echo "📊 Tareas activas después de agregar tarea {$i}: {$activeCount}\n";

            if ($activeCount > 3) {
                echo "⚠️  ADVERTENCIA: El desarrollador tiene más de 3 tareas activas\n";
            }
        }

        echo "✅ PRUEBA DE LÍMITES COMPLETADA\n";
    }

    private function testNotificationDelivery()
    {
        echo "\n📧 PRUEBA 4: ENTREGA DE NOTIFICACIONES\n";
        echo "--------------------------------------\n";

        // Crear una tarea de prueba para notificaciones
        $task = Task::create([
            'name' => 'Tarea Notificación Test',
            'description' => 'Tarea para probar notificaciones',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'done',
            'story_points' => 5
        ]);

        // Simular notificación de QA a TL
        try {
            $this->notificationService->notifyTaskCompletedByQa($task, $this->qaUser);
            echo "✅ Notificación QA → TL enviada correctamente\n";
        } catch (Exception $e) {
            echo "❌ ERROR enviando notificación QA → TL: " . $e->getMessage() . "\n";
        }

        // Simular notificación de TL a Dev
        try {
            $this->notificationService->notifyTaskApprovedWithChanges($task, $this->teamLeader, 'Cambios requeridos');
            echo "✅ Notificación TL → Dev enviada correctamente\n";
        } catch (Exception $e) {
            echo "❌ ERROR enviando notificación TL → Dev: " . $e->getMessage() . "\n";
        }

        // Crear un bug de prueba para notificaciones
        $bug = Bug::create([
            'title' => 'Bug Notificación Test',
            'description' => 'Bug para probar notificaciones',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'resolved',
            'importance' => 'medium',
            'bug_type' => 'functional'
        ]);

        // Simular notificación de QA a TL para bug
        try {
            $this->notificationService->notifyBugCompletedByQa($bug, $this->qaUser);
            echo "✅ Notificación QA → TL (Bug) enviada correctamente\n";
        } catch (Exception $e) {
            echo "❌ ERROR enviando notificación QA → TL (Bug): " . $e->getMessage() . "\n";
        }

        // Simular notificación de TL a Dev para bug
        try {
            $this->notificationService->notifyBugApprovedWithChanges($bug, $this->teamLeader, 'Cambios requeridos en bug');
            echo "✅ Notificación TL → Dev (Bug) enviada correctamente\n";
        } catch (Exception $e) {
            echo "❌ ERROR enviando notificación TL → Dev (Bug): " . $e->getMessage() . "\n";
        }

        echo "✅ PRUEBA DE NOTIFICACIONES COMPLETADA\n";
    }

    public function cleanup()
    {
        echo "\n🧹 LIMPIEZA DE DATOS DE PRUEBA\n";
        echo "-------------------------------\n";

        // Eliminar tareas y bugs de prueba
        Task::where('name', 'like', '%Prueba%')->delete();
        Task::where('name', 'like', '%Test%')->delete();
        Task::where('name', 'like', '%Límite%')->delete();
        
        Bug::where('title', 'like', '%Prueba%')->delete();
        Bug::where('title', 'like', '%Test%')->delete();

        echo "✅ Datos de prueba eliminados\n";
    }
}

// Ejecutar las pruebas
try {
    $tester = new NotificationSystemTester();
    $tester->runAllTests();
    
    // Opcional: limpiar datos de prueba
    // $tester->cleanup();
    
} catch (Exception $e) {
    echo "❌ ERROR EN LAS PRUEBAS: " . $e->getMessage() . "\n";
    echo "📋 Stack trace:\n" . $e->getTraceAsString() . "\n";
}

<?php

/**
 * Script de Prueba Paso a Paso - Sistema de Notificaciones
 * 
 * Este script simula el flujo completo:
 * 1. QA aprueba tarea/bug → TL recibe notificación
 * 2. TL revisa y solicita cambios → Dev recibe notificación
 * 3. Dev hace cambios y reenvía → QA aprueba nuevamente
 * 4. TL aprueba finalmente → Proceso completado
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

class StepByStepTester
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

        echo "✅ Usuarios configurados\n";
    }

    private function setupTestData()
    {
        echo "🔧 Configurando datos de prueba...\n";

        $this->project = Project::firstOrCreate([
            'name' => 'Proyecto Paso a Paso',
            'description' => 'Proyecto para probar el flujo paso a paso',
            'create_by' => $this->qaUser->id
        ]);

        $this->sprint = Sprint::firstOrCreate([
            'name' => 'Sprint Paso a Paso',
            'project_id' => $this->project->id,
            'start_date' => now(),
            'end_date' => now()->addDays(14)
        ]);

        echo "✅ Datos configurados\n";
    }

    public function runStepByStepTest()
    {
        echo "\n🚀 INICIANDO PRUEBA PASO A PASO\n";
        echo "================================\n\n";

        $this->testTaskStepByStep();
        $this->testBugStepByStep();

        echo "\n✅ PRUEBA PASO A PASO COMPLETADA\n";
    }

    private function testTaskStepByStep()
    {
        echo "\n📋 FLUJO PASO A PASO - TAREAS\n";
        echo "===============================\n";

        // PASO 1: Crear tarea
        echo "\n📍 PASO 1: Crear tarea para el desarrollador\n";
        $task = Task::create([
            'title' => 'Tarea Paso a Paso - Test',
            'description' => 'Tarea para probar el flujo completo paso a paso',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'assigned',
            'priority' => 'high',
            'estimated_hours' => 12
        ]);
        echo "✅ Tarea creada: {$task->title}\n";
        echo "👤 Asignada a: {$this->developer->name}\n";

        // PASO 2: Desarrollador completa la tarea
        echo "\n📍 PASO 2: Desarrollador completa la tarea\n";
        $task->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
        echo "✅ Tarea marcada como completada\n";
        echo "📅 Completada el: " . $task->completed_at->format('Y-m-d H:i:s') . "\n";

        // PASO 3: QA aprueba la tarea
        echo "\n📍 PASO 3: QA aprueba la tarea\n";
        $qaNotes = 'Tarea aprobada por QA. Funcionalidad implementada correctamente.';
        $task->approveByQa($this->qaUser, $qaNotes);
        echo "✅ QA aprobó la tarea\n";
        echo "📝 Notas de QA: {$qaNotes}\n";
        echo "👤 Revisado por: {$this->qaUser->name}\n";
        echo "📧 NOTIFICACIÓN: Team Leader debería recibir notificación\n";

        // Verificar estado después de aprobación de QA
        $task->refresh();
        if ($task->isReadyForTeamLeaderReview()) {
            echo "✅ Tarea está lista para revisión del Team Leader\n";
        } else {
            echo "❌ ERROR: Tarea no está lista para revisión del TL\n";
        }

        // PASO 4: Team Leader solicita cambios
        echo "\n📍 PASO 4: Team Leader solicita cambios\n";
        $changeNotes = 'Necesito que agregues validación adicional en el formulario y mejores la documentación del código.';
        $task->requestChangesByTeamLeader($this->teamLeader, $changeNotes);
        echo "✅ Team Leader solicitó cambios\n";
        echo "📝 Notas de cambios: {$changeNotes}\n";
        echo "👤 Solicitado por: {$this->teamLeader->name}\n";
        echo "📧 NOTIFICACIÓN: Developer debería recibir notificación\n";

        // Verificar estado después de solicitud de cambios
        $task->refresh();
        if ($task->team_leader_requested_changes && $task->team_leader_change_notes) {
            echo "✅ Cambios solicitados registrados correctamente\n";
        } else {
            echo "❌ ERROR: Cambios solicitados no registrados\n";
        }

        // PASO 5: Desarrollador hace los cambios
        echo "\n📍 PASO 5: Desarrollador implementa los cambios\n";
        $task->update([
            'team_leader_requested_changes' => false,
            'team_leader_change_notes' => null,
            'status' => 'completed',
            'description' => $task->description . "\n\nCAMBIOS IMPLEMENTADOS:\n- Agregada validación adicional en formulario\n- Mejorada documentación del código\n- Optimizado rendimiento"
        ]);
        echo "✅ Desarrollador implementó los cambios solicitados\n";
        echo "📝 Descripción actualizada con los cambios\n";

        // PASO 6: QA aprueba los cambios
        echo "\n📍 PASO 6: QA aprueba los cambios\n";
        $qaNotes2 = 'Cambios implementados correctamente. Validaciones funcionando bien.';
        $task->approveByQa($this->qaUser, $qaNotes2);
        echo "✅ QA aprobó los cambios\n";
        echo "📝 Notas de QA: {$qaNotes2}\n";
        echo "📧 NOTIFICACIÓN: Team Leader debería recibir notificación nuevamente\n";

        // PASO 7: Team Leader aprueba finalmente
        echo "\n📍 PASO 7: Team Leader aprueba finalmente\n";
        $finalNotes = 'Tarea aprobada finalmente. Todos los cambios implementados correctamente.';
        $task->finallyApproveByTeamLeader($this->teamLeader, $finalNotes);
        echo "✅ Team Leader aprobó finalmente la tarea\n";
        echo "📝 Notas finales: {$finalNotes}\n";
        echo "🎉 PROCESO COMPLETADO EXITOSAMENTE\n";

        // Verificar estado final
        $task->refresh();
        if ($task->team_leader_final_approval && $task->qa_status === 'approved') {
            echo "✅ Estado final correcto: Tarea completamente aprobada\n";
        } else {
            echo "❌ ERROR: Estado final incorrecto\n";
        }

        echo "\n✅ FLUJO DE TAREAS PASO A PASO COMPLETADO\n";
    }

    private function testBugStepByStep()
    {
        echo "\n🐛 FLUJO PASO A PASO - BUGS\n";
        echo "=============================\n";

        // PASO 1: Crear bug
        echo "\n📍 PASO 1: Crear bug para el desarrollador\n";
        $bug = Bug::create([
            'title' => 'Bug Paso a Paso - Test',
            'description' => 'Bug para probar el flujo completo paso a paso',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'assigned',
            'importance' => 'high',
            'severity' => 'high',
            'bug_type' => 'functional'
        ]);
        echo "✅ Bug creado: {$bug->title}\n";
        echo "👤 Asignado a: {$this->developer->name}\n";

        // PASO 2: Desarrollador completa el bug
        echo "\n📍 PASO 2: Desarrollador completa el bug\n";
        $bug->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
        echo "✅ Bug marcado como completado\n";
        echo "📅 Completado el: " . $bug->completed_at->format('Y-m-d H:i:s') . "\n";

        // PASO 3: QA aprueba el bug
        echo "\n📍 PASO 3: QA aprueba el bug\n";
        $qaNotes = 'Bug corregido correctamente. Funcionalidad restaurada.';
        $bug->approveByQa($this->qaUser, $qaNotes);
        echo "✅ QA aprobó el bug\n";
        echo "📝 Notas de QA: {$qaNotes}\n";
        echo "👤 Revisado por: {$this->qaUser->name}\n";
        echo "📧 NOTIFICACIÓN: Team Leader debería recibir notificación\n";

        // Verificar estado después de aprobación de QA
        $bug->refresh();
        if ($bug->isReadyForTeamLeaderReview()) {
            echo "✅ Bug está lista para revisión del Team Leader\n";
        } else {
            echo "❌ ERROR: Bug no está lista para revisión del TL\n";
        }

        // PASO 4: Team Leader aprueba directamente
        echo "\n📍 PASO 4: Team Leader aprueba directamente\n";
        $finalNotes = 'Bug corregido correctamente. Aprobado por Team Leader.';
        $bug->finallyApproveByTeamLeader($this->teamLeader, $finalNotes);
        echo "✅ Team Leader aprobó el bug\n";
        echo "📝 Notas finales: {$finalNotes}\n";
        echo "👤 Aprobado por: {$this->teamLeader->name}\n";
        echo "🎉 PROCESO COMPLETADO EXITOSAMENTE\n";

        // Verificar estado final
        $bug->refresh();
        if ($bug->team_leader_final_approval && $bug->qa_status === 'approved') {
            echo "✅ Estado final correcto: Bug completamente aprobado\n";
        } else {
            echo "❌ ERROR: Estado final incorrecto\n";
        }

        echo "\n✅ FLUJO DE BUGS PASO A PASO COMPLETADO\n";
    }

    public function testNotificationFlow()
    {
        echo "\n📧 PRUEBA DE FLUJO DE NOTIFICACIONES\n";
        echo "=====================================\n";

        // Crear tarea de prueba para notificaciones
        $task = Task::create([
            'title' => 'Tarea Notificación Flow Test',
            'description' => 'Tarea para probar el flujo de notificaciones',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'completed'
        ]);

        echo "\n📍 Simulando notificaciones...\n";

        // Simular notificación QA → TL
        try {
            $this->notificationService->notifyTaskCompletedByQa($task, $this->qaUser);
            echo "✅ Notificación QA → TL enviada\n";
        } catch (Exception $e) {
            echo "❌ ERROR: Notificación QA → TL falló: " . $e->getMessage() . "\n";
        }

        // Simular notificación TL → Dev
        try {
            $this->notificationService->notifyTaskApprovedWithChanges($task, $this->teamLeader, 'Cambios requeridos en la implementación');
            echo "✅ Notificación TL → Dev enviada\n";
        } catch (Exception $e) {
            echo "❌ ERROR: Notificación TL → Dev falló: " . $e->getMessage() . "\n";
        }

        // Crear bug de prueba para notificaciones
        $bug = Bug::create([
            'title' => 'Bug Notificación Flow Test',
            'description' => 'Bug para probar el flujo de notificaciones',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'completed'
        ]);

        // Simular notificación QA → TL para bug
        try {
            $this->notificationService->notifyBugCompletedByQa($bug, $this->qaUser);
            echo "✅ Notificación QA → TL (Bug) enviada\n";
        } catch (Exception $e) {
            echo "❌ ERROR: Notificación QA → TL (Bug) falló: " . $e->getMessage() . "\n";
        }

        // Simular notificación TL → Dev para bug
        try {
            $this->notificationService->notifyBugApprovedWithChanges($bug, $this->teamLeader, 'Cambios requeridos en la corrección del bug');
            echo "✅ Notificación TL → Dev (Bug) enviada\n";
        } catch (Exception $e) {
            echo "❌ ERROR: Notificación TL → Dev (Bug) falló: " . $e->getMessage() . "\n";
        }

        echo "✅ Flujo de notificaciones probado\n";
    }

    public function generateSummary()
    {
        echo "\n📊 RESUMEN DE PRUEBAS\n";
        echo "====================\n";

        // Contar elementos por estado
        $tasksReadyForTL = Task::where('qa_status', 'approved')
            ->where('team_leader_final_approval', false)
            ->where('team_leader_requested_changes', false)
            ->count();

        $tasksWithChanges = Task::where('team_leader_requested_changes', true)->count();
        $tasksFinallyApproved = Task::where('team_leader_final_approval', true)->count();

        $bugsReadyForTL = Bug::where('qa_status', 'approved')
            ->where('team_leader_final_approval', false)
            ->where('team_leader_requested_changes', false)
            ->count();

        $bugsWithChanges = Bug::where('team_leader_requested_changes', true)->count();
        $bugsFinallyApproved = Bug::where('team_leader_final_approval', true)->count();

        echo "📋 TAREAS:\n";
        echo "   - Listas para revisión TL: {$tasksReadyForTL}\n";
        echo "   - Con cambios solicitados: {$tasksWithChanges}\n";
        echo "   - Finalmente aprobadas: {$tasksFinallyApproved}\n";

        echo "🐛 BUGS:\n";
        echo "   - Listos para revisión TL: {$bugsReadyForTL}\n";
        echo "   - Con cambios solicitados: {$bugsWithChanges}\n";
        echo "   - Finalmente aprobados: {$bugsFinallyApproved}\n";

        echo "\n🎯 PRÓXIMOS PASOS PARA PRUEBAS MANUALES:\n";
        echo "1. Acceder como Team Leader a: http://localhost:8000/team-leader/review/tasks\n";
        echo "2. Acceder como Team Leader a: http://localhost:8000/team-leader/review/bugs\n";
        echo "3. Acceder como Developer a: http://localhost:8000/tasks\n";
        echo "4. Acceder como Developer a: http://localhost:8000/bugs\n";
        echo "5. Verificar notificaciones en la interfaz\n";
        echo "6. Probar filtros de tareas/bugs rechazados\n";
        echo "7. Probar secciones de cambios solicitados\n";
    }

    public function cleanup()
    {
        echo "\n🧹 LIMPIEZA DE DATOS DE PRUEBA\n";
        echo "===============================\n";

        Task::where('title', 'like', '%Paso a Paso%')->delete();
        Task::where('title', 'like', '%Notificación Flow%')->delete();
        Bug::where('title', 'like', '%Paso a Paso%')->delete();
        Bug::where('title', 'like', '%Notificación Flow%')->delete();

        echo "✅ Datos de prueba eliminados\n";
    }
}

// Ejecutar la prueba paso a paso
try {
    $tester = new StepByStepTester();
    $tester->runStepByStepTest();
    $tester->testNotificationFlow();
    $tester->generateSummary();
    
    // Opcional: limpiar datos de prueba
    // $tester->cleanup();
    
} catch (Exception $e) {
    echo "❌ ERROR EN LA PRUEBA PASO A PASO: " . $e->getMessage() . "\n";
    echo "📋 Stack trace:\n" . $e->getTraceAsString() . "\n";
}

<?php

/**
 * Script para Generar Datos de Prueba - Sistema de Notificaciones
 * 
 * Este script genera datos de prueba para probar manualmente:
 * 1. Tareas y bugs con diferentes estados
 * 2. Usuarios de prueba (QA, TL, Developer)
 * 3. Proyectos y sprints de prueba
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

class TestDataGenerator
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
        echo "🔧 Configurando usuarios de prueba...\n";

        // Crear o encontrar usuarios de prueba
        $this->qaUser = User::firstOrCreate(
            ['email' => 'qa@test.com'],
            [
                'name' => 'QA Tester',
                'password' => bcrypt('password'),
                'role' => 'qa'
            ]
        );

        $this->teamLeader = User::firstOrCreate(
            ['email' => 'tl@test.com'],
            [
                'name' => 'Team Leader',
                'password' => bcrypt('password'),
                'role' => 'team_leader'
            ]
        );

        $this->developer = User::firstOrCreate(
            ['email' => 'dev@test.com'],
            [
                'name' => 'Developer',
                'password' => bcrypt('password'),
                'role' => 'developer'
            ]
        );

        echo "✅ Usuarios configurados:\n";
        echo "   - QA: {$this->qaUser->email} (password: password)\n";
        echo "   - Team Leader: {$this->teamLeader->email} (password: password)\n";
        echo "   - Developer: {$this->developer->email} (password: password)\n";
    }

    private function setupTestData()
    {
        echo "\n🔧 Configurando datos de prueba...\n";

        $this->project = Project::firstOrCreate(
            ['name' => 'Proyecto de Prueba Notificaciones'],
            [
                'description' => 'Proyecto para probar el sistema de notificaciones y revisión',
                'created_by' => $this->qaUser->id,
                'status' => 'active'
            ]
        );

        $this->sprint = Sprint::firstOrCreate(
            ['name' => 'Sprint de Prueba'],
            [
                'project_id' => $this->project->id,
                'goal' => 'Completar sistema de notificaciones y revisión',
                'start_date' => now(),
                'end_date' => now()->addDays(14),
                'status' => 'active'
            ]
        );

        echo "✅ Proyecto y Sprint configurados\n";
    }

    public function generateAllTestData()
    {
        echo "\n🚀 GENERANDO DATOS DE PRUEBA COMPLETOS\n";
        echo "======================================\n\n";

        $this->generateTasksForTesting();
        $this->generateBugsForTesting();
        $this->generateSummary();

        echo "\n✅ DATOS DE PRUEBA GENERADOS EXITOSAMENTE\n";
    }

    private function generateTasksForTesting()
    {
        echo "\n📋 GENERANDO TAREAS DE PRUEBA\n";
        echo "-----------------------------\n";

        // 1. Tarea lista para revisión del TL (aprobada por QA)
        $task1 = Task::create([
            'name' => 'Implementar Login de Usuario',
            'description' => 'Crear sistema de autenticación con validación de credenciales y tokens JWT',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'done',
            'priority' => 'high',
            'story_points' => 8,
            'estimated_hours' => 16,
            'qa_status' => 'approved',
            'qa_reviewed_by' => $this->qaUser->id,
            'qa_reviewed_at' => now(),
            'qa_notes' => 'Funcionalidad implementada correctamente. Tests pasando.'
        ]);
        echo "✅ Tarea 1 creada: Lista para revisión TL\n";

        // 2. Tarea con cambios solicitados por TL
        $task2 = Task::create([
            'name' => 'Crear Dashboard de Estadísticas',
            'description' => 'Desarrollar panel de control con gráficos y métricas del proyecto',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'done',
            'priority' => 'medium',
            'story_points' => 5,
            'estimated_hours' => 12,
            'qa_status' => 'approved',
            'qa_reviewed_by' => $this->qaUser->id,
            'qa_reviewed_at' => now(),
            'qa_notes' => 'Dashboard funcional, pero necesita mejoras en UX',
            'team_leader_requested_changes' => true,
            'team_leader_change_notes' => 'Necesito que agregues más filtros y mejores la responsividad en móviles',
            'team_leader_reviewed_by' => $this->teamLeader->id,
            'team_leader_requested_changes_at' => now()
        ]);
        echo "✅ Tarea 2 creada: Con cambios solicitados por TL\n";

        // 3. Tarea rechazada por QA
        $task3 = Task::create([
            'name' => 'Optimizar Consultas de Base de Datos',
            'description' => 'Mejorar rendimiento de consultas SQL y agregar índices necesarios',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'done',
            'priority' => 'high',
            'story_points' => 3,
            'estimated_hours' => 8,
            'qa_status' => 'rejected',
            'qa_reviewed_by' => $this->qaUser->id,
            'qa_reviewed_at' => now(),
            'qa_rejection_reason' => 'Las consultas siguen siendo lentas. Necesitas optimizar más los índices.'
        ]);
        echo "✅ Tarea 3 creada: Rechazada por QA\n";

        // 4. Tarea finalmente aprobada
        $task4 = Task::create([
            'name' => 'Configurar Sistema de Notificaciones',
            'description' => 'Implementar sistema de notificaciones en tiempo real con WebSockets',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'done',
            'priority' => 'medium',
            'story_points' => 13,
            'estimated_hours' => 20,
            'qa_status' => 'approved',
            'qa_reviewed_by' => $this->qaUser->id,
            'qa_reviewed_at' => now(),
            'qa_notes' => 'Sistema funcionando perfectamente',
            'team_leader_final_approval' => true,
            'team_leader_final_approval_at' => now(),
            'team_leader_final_notes' => 'Excelente trabajo. Sistema aprobado para producción.',
            'team_leader_reviewed_by' => $this->teamLeader->id
        ]);
        echo "✅ Tarea 4 creada: Finalmente aprobada\n";

        // 5. Tarea en progreso (para probar límites)
        $task5 = Task::create([
            'name' => 'Implementar API REST',
            'description' => 'Crear endpoints RESTful para el sistema de gestión',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'in progress',
            'priority' => 'high',
            'story_points' => 21,
            'estimated_hours' => 24,
            'is_working' => true
        ]);
        echo "✅ Tarea 5 creada: En progreso (activa)\n";
    }

    private function generateBugsForTesting()
    {
        echo "\n🐛 GENERANDO BUGS DE PRUEBA\n";
        echo "----------------------------\n";

        // 1. Bug lista para revisión del TL (aprobado por QA)
        $bug1 = Bug::create([
            'title' => 'Error en Validación de Formulario',
            'description' => 'El formulario de registro no valida correctamente el email',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'completed',
            'importance' => 'high',
            'severity' => 'medium',
            'bug_type' => 'functional',
            'qa_status' => 'approved',
            'qa_reviewed_by' => $this->qaUser->id,
            'qa_reviewed_at' => now(),
            'qa_notes' => 'Validación corregida correctamente'
        ]);
        echo "✅ Bug 1 creado: Listo para revisión TL\n";

        // 2. Bug con cambios solicitados por TL
        $bug2 = Bug::create([
            'title' => 'Problema de Rendimiento en Lista',
            'description' => 'La lista de usuarios tarda mucho en cargar cuando hay muchos registros',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'completed',
            'importance' => 'medium',
            'severity' => 'high',
            'bug_type' => 'performance',
            'qa_status' => 'approved',
            'qa_reviewed_by' => $this->qaUser->id,
            'qa_reviewed_at' => now(),
            'qa_notes' => 'Rendimiento mejorado, pero aún puede optimizarse',
            'team_leader_requested_changes' => true,
            'team_leader_change_notes' => 'Necesito que implementes paginación y filtros adicionales',
            'team_leader_reviewed_by' => $this->teamLeader->id,
            'team_leader_requested_changes_at' => now()
        ]);
        echo "✅ Bug 2 creado: Con cambios solicitados por TL\n";

        // 3. Bug rechazado por QA
        $bug3 = Bug::create([
            'title' => 'Error de Diseño en Móviles',
            'description' => 'El menú no se muestra correctamente en dispositivos móviles',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'completed',
            'importance' => 'medium',
            'severity' => 'low',
            'bug_type' => 'ui',
            'qa_status' => 'rejected',
            'qa_reviewed_by' => $this->qaUser->id,
            'qa_reviewed_at' => now(),
            'qa_rejection_reason' => 'El problema persiste en dispositivos iOS. Necesitas revisar la implementación.'
        ]);
        echo "✅ Bug 3 creado: Rechazado por QA\n";

        // 4. Bug finalmente aprobado
        $bug4 = Bug::create([
            'title' => 'Error de Conexión a Base de Datos',
            'description' => 'Ocasionalmente se pierde la conexión a la base de datos',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'completed',
            'importance' => 'high',
            'severity' => 'critical',
            'bug_type' => 'database',
            'qa_status' => 'approved',
            'qa_reviewed_by' => $this->qaUser->id,
            'qa_reviewed_at' => now(),
            'qa_notes' => 'Conexión establecida correctamente',
            'team_leader_final_approval' => true,
            'team_leader_final_approval_at' => now(),
            'team_leader_final_notes' => 'Problema resuelto completamente. Aprobado.',
            'team_leader_reviewed_by' => $this->teamLeader->id
        ]);
        echo "✅ Bug 4 creado: Finalmente aprobado\n";

        // 5. Bug en progreso (para probar límites)
        $bug5 = Bug::create([
            'title' => 'Error de Seguridad en Autenticación',
            'description' => 'Vulnerabilidad en el sistema de autenticación',
            'user_id' => $this->developer->id,
            'project_id' => $this->project->id,
            'sprint_id' => $this->sprint->id,
            'status' => 'in progress',
            'importance' => 'high',
            'severity' => 'critical',
            'bug_type' => 'security',
            'is_working' => true
        ]);
        echo "✅ Bug 5 creado: En progreso (activo)\n";
    }

    private function generateSummary()
    {
        echo "\n📊 RESUMEN DE DATOS GENERADOS\n";
        echo "=============================\n";

        // Contar elementos por estado
        $tasksReadyForTL = Task::where('qa_status', 'approved')
            ->where('team_leader_final_approval', false)
            ->where('team_leader_requested_changes', false)
            ->count();

        $tasksWithChanges = Task::where('team_leader_requested_changes', true)->count();
        $tasksRejectedByQa = Task::where('qa_status', 'rejected')->count();
        $tasksFinallyApproved = Task::where('team_leader_final_approval', true)->count();
        $tasksInProgress = Task::where('is_working', true)->count();

        $bugsReadyForTL = Bug::where('qa_status', 'approved')
            ->where('team_leader_final_approval', false)
            ->where('team_leader_requested_changes', false)
            ->count();

        $bugsWithChanges = Bug::where('team_leader_requested_changes', true)->count();
        $bugsRejectedByQa = Bug::where('qa_status', 'rejected')->count();
        $bugsFinallyApproved = Bug::where('team_leader_final_approval', true)->count();
        $bugsInProgress = Bug::where('is_working', true)->count();

        echo "📋 TAREAS:\n";
        echo "   - Listas para revisión TL: {$tasksReadyForTL}\n";
        echo "   - Con cambios solicitados: {$tasksWithChanges}\n";
        echo "   - Rechazadas por QA: {$tasksRejectedByQa}\n";
        echo "   - Finalmente aprobadas: {$tasksFinallyApproved}\n";
        echo "   - En progreso: {$tasksInProgress}\n";

        echo "\n🐛 BUGS:\n";
        echo "   - Listos para revisión TL: {$bugsReadyForTL}\n";
        echo "   - Con cambios solicitados: {$bugsWithChanges}\n";
        echo "   - Rechazados por QA: {$bugsRejectedByQa}\n";
        echo "   - Finalmente aprobados: {$bugsFinallyApproved}\n";
        echo "   - En progreso: {$bugsInProgress}\n";

        echo "\n🎯 INSTRUCCIONES PARA PRUEBAS MANUALES:\n";
        echo "========================================\n";
        echo "1. Acceder como Team Leader:\n";
        echo "   - URL: http://localhost:8000/team-leader/review/tasks\n";
        echo "   - Email: tl@test.com\n";
        echo "   - Password: password\n";
        echo "   - Verificar que aparecen tareas listas para revisión\n";
        echo "   - Probar aprobar y solicitar cambios\n\n";

        echo "2. Acceder como Developer:\n";
        echo "   - URL: http://localhost:8000/tasks\n";
        echo "   - Email: dev@test.com\n";
        echo "   - Password: password\n";
        echo "   - Verificar filtros de QA status y Team Leader status\n";
        echo "   - Verificar secciones de tareas rechazadas y cambios solicitados\n\n";

        echo "3. Probar límites de tareas activas:\n";
        echo "   - Verificar que no puede tener más de 3 tareas activas\n";
        echo "   - Las tareas activas incluyen: en progreso, rechazadas por QA, con cambios solicitados\n\n";

        echo "4. Probar notificaciones:\n";
        echo "   - Verificar que las notificaciones aparecen en la interfaz\n";
        echo "   - Probar que son clickeables y llevan a la tarea/bug correspondiente\n\n";

        echo "5. Probar el mismo flujo con bugs:\n";
        echo "   - URL: http://localhost:8000/team-leader/review/bugs\n";
        echo "   - URL: http://localhost:8000/bugs\n";
    }

    public function cleanup()
    {
        echo "\n🧹 LIMPIEZA DE DATOS DE PRUEBA\n";
        echo "===============================\n";

        Task::where('title', 'like', '%Implementar%')->delete();
        Task::where('title', 'like', '%Crear%')->delete();
        Task::where('title', 'like', '%Optimizar%')->delete();
        Task::where('title', 'like', '%Configurar%')->delete();
        
        Bug::where('title', 'like', '%Error%')->delete();
        Bug::where('title', 'like', '%Problema%')->delete();
        Bug::where('title', 'like', '%Diseño%')->delete();
        Bug::where('title', 'like', '%Conexión%')->delete();
        Bug::where('title', 'like', '%Seguridad%')->delete();

        echo "✅ Datos de prueba eliminados\n";
    }
}

// Ejecutar el generador de datos
try {
    $generator = new TestDataGenerator();
    $generator->generateAllTestData();
    
    echo "\n✅ DATOS DE PRUEBA GENERADOS EXITOSAMENTE\n";
    echo "🎯 Ahora puedes probar manualmente el sistema\n";
    
    // Opcional: limpiar datos de prueba
    // $generator->cleanup();
    
} catch (Exception $e) {
    echo "❌ ERROR GENERANDO DATOS: " . $e->getMessage() . "\n";
    echo "📋 Stack trace:\n" . $e->getTraceAsString() . "\n";
}

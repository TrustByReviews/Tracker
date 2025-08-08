<?php

/**
 * Script de Prueba Simple - Sistema de Notificaciones
 * 
 * Este script prueba las funcionalidades básicas del sistema
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

echo "🚀 INICIANDO PRUEBA SIMPLE DEL SISTEMA DE NOTIFICACIONES\n";
echo "========================================================\n\n";

try {
    // 1. Verificar que los modelos existen
    echo "✅ Verificando modelos...\n";
    echo "   - Task model: " . (class_exists(Task::class) ? 'OK' : 'ERROR') . "\n";
    echo "   - Bug model: " . (class_exists(Bug::class) ? 'OK' : 'ERROR') . "\n";
    echo "   - User model: " . (class_exists(User::class) ? 'OK' : 'ERROR') . "\n";
    echo "   - Project model: " . (class_exists(Project::class) ? 'OK' : 'ERROR') . "\n";
    echo "   - Sprint model: " . (class_exists(Sprint::class) ? 'OK' : 'ERROR') . "\n";
    echo "   - NotificationService: " . (class_exists(NotificationService::class) ? 'OK' : 'ERROR') . "\n";

    // 2. Verificar que las rutas están registradas
    echo "\n✅ Verificando rutas...\n";
    $routes = [
        'team-leader.review.tasks',
        'team-leader.review.bugs',
        'team-leader.review.stats'
    ];

    foreach ($routes as $routeName) {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName($routeName);
        echo "   - {$routeName}: " . ($route ? 'OK' : 'ERROR') . "\n";
    }

    // 3. Verificar métodos en los modelos
    echo "\n✅ Verificando métodos de modelos...\n";
    
    // Verificar métodos en Task
    $taskMethods = ['isReadyForTeamLeaderReview', 'finallyApproveByTeamLeader', 'requestChangesByTeamLeader', 'canDeveloperHaveMoreActiveTasks'];
    foreach ($taskMethods as $method) {
        echo "   - Task::{$method}: " . (method_exists(Task::class, $method) ? 'OK' : 'ERROR') . "\n";
    }

    // Verificar métodos en Bug
    $bugMethods = ['isReadyForTeamLeaderReview', 'finallyApproveByTeamLeader', 'requestChangesByTeamLeader', 'canDeveloperHaveMoreActiveBugs'];
    foreach ($bugMethods as $method) {
        echo "   - Bug::{$method}: " . (method_exists(Bug::class, $method) ? 'OK' : 'ERROR') . "\n";
    }

    // 4. Verificar métodos en NotificationService
    echo "\n✅ Verificando métodos de NotificationService...\n";
    $notificationMethods = [
        'notifyTaskCompletedByQa',
        'notifyBugCompletedByQa', 
        'notifyTaskApprovedWithChanges',
        'notifyBugApprovedWithChanges'
    ];
    
    foreach ($notificationMethods as $method) {
        echo "   - NotificationService::{$method}: " . (method_exists(NotificationService::class, $method) ? 'OK' : 'ERROR') . "\n";
    }

    // 5. Verificar archivos Vue
    echo "\n✅ Verificando componentes Vue...\n";
    $vueFiles = [
        'resources/js/pages/TeamLeader/ReviewTasks.vue',
        'resources/js/pages/TeamLeader/ReviewBugs.vue',
        'resources/js/components/TaskCard.vue',
        'resources/js/components/BugCard.vue'
    ];

    foreach ($vueFiles as $file) {
        echo "   - {$file}: " . (file_exists($file) ? 'OK' : 'ERROR') . "\n";
    }

    // 6. Verificar campos en la base de datos
    echo "\n✅ Verificando campos de base de datos...\n";
    
    // Verificar campos en tasks
    $taskFields = [
        'team_leader_final_approval',
        'team_leader_final_approval_at',
        'team_leader_final_notes',
        'team_leader_requested_changes',
        'team_leader_requested_changes_at',
        'team_leader_change_notes',
        'team_leader_reviewed_by'
    ];

    $taskTable = \Illuminate\Support\Facades\Schema::getColumnListing('tasks');
    foreach ($taskFields as $field) {
        echo "   - tasks.{$field}: " . (in_array($field, $taskTable) ? 'OK' : 'ERROR') . "\n";
    }

    // Verificar campos en bugs
    $bugFields = [
        'team_leader_final_approval',
        'team_leader_final_approval_at',
        'team_leader_final_notes',
        'team_leader_requested_changes',
        'team_leader_requested_changes_at',
        'team_leader_change_notes',
        'team_leader_reviewed_by'
    ];

    $bugTable = \Illuminate\Support\Facades\Schema::getColumnListing('bugs');
    foreach ($bugFields as $field) {
        echo "   - bugs.{$field}: " . (in_array($field, $bugTable) ? 'OK' : 'ERROR') . "\n";
    }

    echo "\n✅ PRUEBA SIMPLE COMPLETADA EXITOSAMENTE\n";
    echo "🎯 El sistema está listo para pruebas manuales\n";
    echo "\n📋 URLs para probar:\n";
    echo "   - Team Leader Review Tasks: http://localhost:8000/team-leader/review/tasks\n";
    echo "   - Team Leader Review Bugs: http://localhost:8000/team-leader/review/bugs\n";
    echo "   - Developer Tasks: http://localhost:8000/tasks\n";
    echo "   - Developer Bugs: http://localhost:8000/bugs\n";

} catch (Exception $e) {
    echo "❌ ERROR EN LA PRUEBA: " . $e->getMessage() . "\n";
    echo "📋 Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
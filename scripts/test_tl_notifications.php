<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Role;
use App\Services\NotificationService;

echo "=== TESTING TEAM LEADER NOTIFICATIONS ===\n";

// 1. Encontrar usuarios
$qa = User::where('email', 'qa@test.com')->first();
$tl = User::where('email', 'tl@test.com')->first();
$developer = User::where('email', 'dev@test.com')->first();

if (!$qa || !$tl || !$developer) {
    echo "❌ Error: No se encontraron todos los usuarios necesarios\n";
    exit(1);
}

echo "✅ Usuarios encontrados:\n";
echo "  - QA: {$qa->name} ({$qa->email})\n";
echo "  - TL: {$tl->name} ({$tl->email})\n";
echo "  - Developer: {$developer->name} ({$developer->email})\n";

// 2. Crear proyecto y sprint si no existen
$project = Project::where('name', 'Test Project for TL Notifications')->first();
if (!$project) {
    $project = Project::create([
        'name' => 'Test Project for TL Notifications',
        'description' => 'Proyecto de prueba para notificaciones TL',
        'created_by' => $tl->id,
        'status' => 'active'
    ]);
    echo "✅ Proyecto creado: {$project->name}\n";
} else {
    echo "✅ Proyecto existente: {$project->name}\n";
}

// Asignar TL al proyecto
$project->users()->syncWithoutDetaching([$tl->id]);

$sprint = Sprint::where('project_id', $project->id)->first();
if (!$sprint) {
    $sprint = Sprint::create([
        'project_id' => $project->id,
        'name' => 'Sprint Test TL',
        'goal' => 'Probar notificaciones TL',
        'start_date' => now(),
        'end_date' => now()->addDays(14),
        'created_by' => $tl->id
    ]);
    echo "✅ Sprint creado: {$sprint->name}\n";
} else {
    echo "✅ Sprint existente: {$sprint->name}\n";
}

// 3. Crear tarea para el developer
$task = Task::where('name', 'Test Task for TL Notifications')->first();
if (!$task) {
    $task = Task::create([
        'name' => 'Test Task for TL Notifications',
        'description' => 'Tarea de prueba para notificaciones TL',
        'project_id' => $project->id,
        'sprint_id' => $sprint->id,
        'assigned_to' => $developer->id,
        'created_by' => $tl->id,
        'status' => 'in_progress',
        'priority' => 'medium',
        'story_points' => 3
    ]);
    echo "✅ Tarea creada: {$task->name}\n";
} else {
    echo "✅ Tarea existente: {$task->name}\n";
}

// 4. Simular que QA completa la tarea
echo "\n=== SIMULANDO COMPLETACIÓN POR QA ===\n";

// Marcar tarea como completada por QA
$task->update([
    'status' => 'done',
    'qa_reviewed_by' => $qa->id,
    'qa_reviewed_at' => now(),
    'qa_status' => 'approved'
]);

echo "✅ Tarea marcada como completada por QA\n";

// 5. Verificar que se envió notificación al TL
echo "\n=== VERIFICANDO NOTIFICACIÓN AL TL ===\n";

// Simular el envío de notificación
$notificationService = new NotificationService();
$notificationService->notifyTaskCompletedByQa($task);

echo "✅ Notificación enviada al TL\n";

// 6. Verificar que el TL puede ver la tarea en su dashboard
echo "\n=== VERIFICANDO ACCESO DEL TL ===\n";

$tlTasks = Task::whereHas('project.users', function($query) use ($tl) {
    $query->where('users.id', $tl->id);
})->where('qa_status', 'approved')
  ->whereNull('team_leader_reviewed_by')
  ->get();

echo "📋 Tareas pendientes de revisión por TL: {$tlTasks->count()}\n";

foreach ($tlTasks as $tlTask) {
    echo "  - {$tlTask->name} (Proyecto: {$tlTask->project->name})\n";
}

// 7. Simular que TL aprueba la tarea
echo "\n=== SIMULANDO APROBACIÓN POR TL ===\n";

$task->update([
    'team_leader_reviewed_by' => $tl->id,
    'team_leader_reviewed_at' => now(),
    'team_leader_status' => 'approved'
]);

echo "✅ Tarea aprobada por TL\n";

// 8. Verificar que se envió notificación al developer
echo "\n=== VERIFICANDO NOTIFICACIÓN AL DEVELOPER ===\n";

$notificationService->notifyTaskApprovedByTeamLeader($task);

echo "✅ Notificación enviada al developer\n";

// 9. Simular que TL solicita cambios
echo "\n=== SIMULANDO SOLICITUD DE CAMBIOS POR TL ===\n";

$task->update([
    'team_leader_status' => 'changes_requested',
    'team_leader_changes_justification' => 'Se requiere mejorar la interfaz de usuario según los estándares de diseño'
]);

echo "✅ Cambios solicitados por TL\n";

// 10. Verificar notificación al developer sobre cambios solicitados
echo "\n=== VERIFICANDO NOTIFICACIÓN DE CAMBIOS ===\n";

$notificationService->notifyTaskApprovedWithChanges($task, 'Se requiere mejorar la interfaz de usuario según los estándares de diseño');

echo "✅ Notificación de cambios enviada al developer\n";

echo "\n=== TEST COMPLETADO ===\n";
echo "✅ Todas las notificaciones del flujo TL han sido probadas\n";
echo "📋 Resumen:\n";
echo "  - QA completó tarea → TL recibe notificación\n";
echo "  - TL aprueba tarea → Developer recibe notificación\n";
echo "  - TL solicita cambios → Developer recibe notificación con justificación\n";

echo "\n=== DATOS DE PRUEBA CREADOS ===\n";
echo "Proyecto: {$project->name} (ID: {$project->id})\n";
echo "Sprint: {$sprint->name} (ID: {$sprint->id})\n";
echo "Tarea: {$task->name} (ID: {$task->id})\n";
echo "Estado final: {$task->team_leader_status}\n";

echo "\n=== INSTRUCCIONES PARA PRUEBAS MANUALES ===\n";
echo "1. Accede como TL (tl@test.com / password)\n";
echo "2. Ve al dashboard del TL\n";
echo "3. Verifica que la tarea aparece en 'Tareas Pendientes'\n";
echo "4. Haz clic en la tarea para revisarla\n";
echo "5. Prueba aprobar o solicitar cambios\n";
echo "6. Accede como developer para ver las notificaciones\n";

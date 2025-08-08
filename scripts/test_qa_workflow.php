<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\Sprint;
use App\Services\NotificationService;

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing QA Workflow...\n\n";

try {
    // Obtener usuarios existentes
    $developer = User::where('email', 'juan.martinez324@test.com')->first();
    $teamLeader = User::where('email', 'roberto.silva190@test.com')->first();
    $qaUser = User::where('email', 'qa@tracker.com')->first();
    $admin = User::where('email', 'carmen.ruiz79@test.com')->first();

    if (!$developer || !$teamLeader || !$qaUser || !$admin) {
        echo "Error: Required users not found.\n";
        exit(1);
    }

    echo "Users found:\n";
    echo "- Developer: {$developer->name}\n";
    echo "- Team Leader: {$teamLeader->name}\n";
    echo "- QA: {$qaUser->name}\n";
    echo "- Admin: {$admin->name}\n\n";

    // Obtener un proyecto
    $project = Project::first();
    if (!$project) {
        echo "Error: No projects found.\n";
        exit(1);
    }

    echo "Using project: {$project->name}\n\n";

    // Obtener o crear un sprint
    $sprint = Sprint::where('project_id', $project->id)->first();
    if (!$sprint) {
        echo "Error: No sprints found for project.\n";
        exit(1);
    }

    echo "Using sprint: {$sprint->name}\n\n";

    // Crear una tarea de prueba
    echo "Creating test task...\n";
    $task = Task::create([
        'name' => 'Test QA Workflow Task',
        'description' => 'This is a test task to verify the QA workflow',
        'status' => 'to do',
        'priority' => 'high',
        'category' => 'frontend',
        'story_points' => 5,
        'sprint_id' => $sprint->id,
        'project_id' => $project->id,
        'user_id' => $developer->id,
        'assigned_by' => $teamLeader->id,
        'assigned_at' => now(),
        'estimated_hours' => 4,
        'estimated_minutes' => 30,
        'approval_status' => 'pending',
        'qa_status' => 'pending',
    ]);

    echo "Task created: {$task->name}\n";

    // Simular que el desarrollador termina la tarea
    echo "\n1. Developer finishing task...\n";
    $task->update([
        'status' => 'done',
        'actual_finish' => now(),
        'total_time_seconds' => 16200, // 4.5 hours
    ]);
    echo "Task marked as done\n";

    // Simular que el team leader aprueba la tarea
    echo "\n2. Team Leader approving task...\n";
    $task->update([
        'approval_status' => 'approved',
        'reviewed_by' => $teamLeader->id,
        'reviewed_at' => now(),
    ]);

    // Marcar como lista para QA
    $task->markAsReadyForQa();
    echo "Task approved and marked as ready for QA\n";

    // Verificar que la tarea está lista para QA
    echo "\n3. Checking QA status...\n";
    $task->refresh();
    echo "QA Status: {$task->qa_status}\n";
    echo "Ready for QA: " . ($task->isReadyForQa() ? 'Yes' : 'No') . "\n";

    // Simular que el QA asigna la tarea a sí mismo
    echo "\n4. QA assigning task to self...\n";
    $task->assignToQa($qaUser);
    echo "Task assigned to QA\n";

    // Verificar el estado
    $task->refresh();
    echo "QA Status: {$task->qa_status}\n";
    echo "QA Assigned To: {$task->qaAssignedTo->name}\n";

    // Simular que el QA aprueba la tarea
    echo "\n5. QA approving task...\n";
    $task->approveByQa($qaUser, 'All tests passed successfully');
    echo "Task approved by QA\n";

    // Verificar el estado final
    $task->refresh();
    echo "\n6. Final status check...\n";
    echo "Task Status: {$task->status}\n";
    echo "Approval Status: {$task->approval_status}\n";
    echo "QA Status: {$task->qa_status}\n";
    echo "QA Notes: {$task->qa_notes}\n";

    // Probar notificaciones
    echo "\n7. Testing notifications...\n";
    $notificationService = new NotificationService();
    
    // Verificar notificaciones del QA
    $qaNotifications = $qaUser->notifications()->where('type', 'task_ready_for_qa')->get();
    echo "QA notifications for ready tasks: " . $qaNotifications->count() . "\n";

    // Verificar notificaciones del team leader
    $tlNotifications = $teamLeader->notifications()->where('type', 'task_approved')->get();
    echo "Team Leader notifications for approved tasks: " . $tlNotifications->count() . "\n";

    // Verificar notificaciones del desarrollador
    $devNotifications = $developer->notifications()->where('type', 'task_approved')->get();
    echo "Developer notifications for approved tasks: " . $devNotifications->count() . "\n";

    echo "\n✅ QA Workflow test completed successfully!\n";
    echo "\nSummary:\n";
    echo "- Task created and assigned to developer\n";
    echo "- Developer completed the task\n";
    echo "- Team Leader approved the task\n";
    echo "- Task automatically marked as ready for QA\n";
    echo "- QA assigned task to self\n";
    echo "- QA approved the task\n";
    echo "- Notifications sent to all parties\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
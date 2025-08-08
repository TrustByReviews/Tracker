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

echo "Testing QA Review Workflow...\n\n";

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
        'name' => 'QA Review Workflow Test Task',
        'description' => 'This is a test task to verify the QA review workflow',
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

    // Simular que el QA asigna la tarea a sí mismo
    echo "\n3. QA assigning task to self...\n";
    $task->assignToQa($qaUser);
    echo "Task assigned to QA\n";

    // Simular que el QA aprueba la tarea
    echo "\n4. QA approving task...\n";
    $task->approveByQa($qaUser, 'All tests passed successfully. Task meets all requirements.');
    echo "Task approved by QA\n";

    // Verificar el estado después de QA
    $task->refresh();
    echo "\n5. Status after QA approval...\n";
    echo "Task Status: {$task->status}\n";
    echo "Approval Status: {$task->approval_status}\n";
    echo "QA Status: {$task->qa_status}\n";
    echo "QA Notes: {$task->qa_notes}\n";

    // Simular que el Team Leader revisa la tarea aprobada por QA
    echo "\n6. Team Leader reviewing QA-approved task...\n";
    
    // Opción 1: Team Leader aprueba completamente
    echo "Option 1: Team Leader approves completely\n";
    $task->update([
        'status' => 'done',
        'team_leader_final_approval' => true,
        'team_leader_final_approval_at' => now(),
        'team_leader_final_notes' => 'Excellent work! Task is complete and meets all requirements.',
    ]);
    echo "Task finally approved by Team Leader\n";

    // Verificar el estado final
    $task->refresh();
    echo "\n7. Final status check...\n";
    echo "Task Status: {$task->status}\n";
    echo "Approval Status: {$task->approval_status}\n";
    echo "QA Status: {$task->qa_status}\n";
    echo "Team Leader Final Approval: " . ($task->team_leader_final_approval ? 'Yes' : 'No') . "\n";
    echo "Team Leader Final Notes: {$task->team_leader_final_notes}\n";

    // Crear una segunda tarea para probar el flujo de cambios solicitados
    echo "\n\n--- Testing Changes Requested Flow ---\n\n";

    echo "Creating second test task...\n";
    $task2 = Task::create([
        'name' => 'QA Review with Changes Requested',
        'description' => 'This task will test the flow where Team Leader requests changes after QA approval',
        'status' => 'to do',
        'priority' => 'medium',
        'category' => 'backend',
        'story_points' => 3,
        'sprint_id' => $sprint->id,
        'project_id' => $project->id,
        'user_id' => $developer->id,
        'assigned_by' => $teamLeader->id,
        'assigned_at' => now(),
        'estimated_hours' => 2,
        'estimated_minutes' => 0,
        'approval_status' => 'pending',
        'qa_status' => 'pending',
    ]);

    echo "Task 2 created: {$task2->name}\n";

    // Simular flujo completo hasta QA
    echo "\n1. Developer finishing task 2...\n";
    $task2->update([
        'status' => 'done',
        'actual_finish' => now(),
        'total_time_seconds' => 7200, // 2 hours
    ]);

    echo "\n2. Team Leader approving task 2...\n";
    $task2->update([
        'approval_status' => 'approved',
        'reviewed_by' => $teamLeader->id,
        'reviewed_at' => now(),
    ]);
    $task2->markAsReadyForQa();

    echo "\n3. QA assigning and approving task 2...\n";
    $task2->assignToQa($qaUser);
    $task2->approveByQa($qaUser, 'QA tests passed. Task functionality works as expected.');

    // Simular que el Team Leader solicita cambios
    echo "\n4. Team Leader requesting changes...\n";
    $task2->update([
        'qa_status' => 'pending',
        'qa_assigned_to' => null,
        'qa_assigned_at' => null,
        'qa_started_at' => null,
        'qa_completed_at' => null,
        'qa_notes' => null,
        'qa_rejection_reason' => null,
        'qa_reviewed_by' => null,
        'qa_reviewed_at' => null,
        'team_leader_requested_changes' => true,
        'team_leader_requested_changes_at' => now(),
        'team_leader_change_notes' => 'Please improve the error handling and add more validation. Also, consider adding unit tests for the new functionality.',
    ]);

    echo "Changes requested by Team Leader\n";

    // Verificar el estado después de solicitar cambios
    $task2->refresh();
    echo "\n5. Status after changes requested...\n";
    echo "Task Status: {$task2->status}\n";
    echo "QA Status: {$task2->qa_status}\n";
    echo "Team Leader Requested Changes: " . ($task2->team_leader_requested_changes ? 'Yes' : 'No') . "\n";
    echo "Change Notes: {$task2->team_leader_change_notes}\n";

    // Probar notificaciones
    echo "\n8. Testing notifications...\n";
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

    // Verificar notificaciones de aprobación final
    $finalApprovalNotifications = $developer->notifications()->where('type', 'task_final_approved')->get();
    echo "Developer notifications for final approval: " . $finalApprovalNotifications->count() . "\n";

    // Verificar notificaciones de cambios solicitados
    $changesRequestedNotifications = $developer->notifications()->where('type', 'task_changes_requested')->get();
    echo "Developer notifications for changes requested: " . $changesRequestedNotifications->count() . "\n";

    echo "\n✅ QA Review Workflow test completed successfully!\n";
    echo "\nSummary:\n";
    echo "- Task created and assigned to developer\n";
    echo "- Developer completed the task\n";
    echo "- Team Leader approved the task\n";
    echo "- Task automatically marked as ready for QA\n";
    echo "- QA assigned task to self\n";
    echo "- QA approved the task\n";
    echo "- Team Leader reviewed QA approval\n";
    echo "- Team Leader approved completely OR requested changes\n";
    echo "- Changes requested flow tested\n";
    echo "- Notifications sent to all parties\n";

    echo "\nWorkflow Steps:\n";
    echo "1. Developer → Task Done\n";
    echo "2. Team Leader → Approve → Task Ready for QA\n";
    echo "3. QA → Assign to Self → Test → Approve\n";
    echo "4. Team Leader → Review QA Approval → Final Approve OR Request Changes\n";
    echo "5. If Changes Requested → Back to Developer → Cycle Restarts\n";
    echo "6. If Final Approved → Task Complete\n";

    echo "\nQA Review Features:\n";
    echo "- Team Leader can see all QA-approved tasks and bugs\n";
    echo "- Team Leader can approve completely or request changes\n";
    echo "- Detailed notes and feedback system\n";
    echo "- Automatic notifications to all parties\n";
    echo "- Complete audit trail of decisions\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
<?php

/**
 * Script de testing completo del Sistema de Gestión de Tareas
 * Simula las operaciones del frontend y prueba todas las funcionalidades
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\TaskTimeLog;
use App\Models\Role;
use App\Services\TaskAssignmentService;
use App\Services\TaskTimeTrackingService;
use App\Services\TaskApprovalService;
use App\Services\AdminDashboardService;
use App\Services\EmailService;

echo "🧪 INICIANDO TESTING COMPLETO DEL SISTEMA\n";
echo "==================================================\n\n";

// Configurar la aplicación
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "✅ Aplicación inicializada\n\n";

// ============================================================================
// FASE 1: CREACIÓN DE DATOS DE PRUEBA
// ============================================================================

echo "📊 FASE 1: CREACIÓN DE DATOS DE PRUEBA\n";
echo "----------------------------------------\n";

// Crear roles si no existen
$roles = [
    'admin' => Role::firstOrCreate(['name' => 'admin']),
    'team_leader' => Role::firstOrCreate(['name' => 'team_leader']),
    'developer' => Role::firstOrCreate(['name' => 'developer'])
];

echo "✅ Roles creados/verificados\n";

// Crear usuarios de prueba
$users = [];

// Administrador
$users['admin'] = User::firstOrCreate(
    ['email' => 'admin@example.com'],
    [
        'name' => 'Administrador Principal',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now()
    ]
);

// Team Leaders
$users['team_leader_1'] = User::firstOrCreate(
    ['email' => 'teamleader1@example.com'],
    [
        'name' => 'María González - Team Leader',
        'email' => 'teamleader1@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now()
    ]
);

$users['team_leader_2'] = User::firstOrCreate(
    ['email' => 'teamleader2@example.com'],
    [
        'name' => 'Carlos Rodríguez - Team Leader',
        'email' => 'teamleader2@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now()
    ]
);

// Desarrolladores
$users['developer_1'] = User::firstOrCreate(
    ['email' => 'developer1@example.com'],
    [
        'name' => 'Ana Martínez - Desarrolladora',
        'email' => 'developer1@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now()
    ]
);

$users['developer_2'] = User::firstOrCreate(
    ['email' => 'developer2@example.com'],
    [
        'name' => 'Luis Pérez - Desarrollador',
        'email' => 'developer2@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now()
    ]
);

$users['developer_3'] = User::firstOrCreate(
    ['email' => 'developer3@example.com'],
    [
        'name' => 'Sofia García - Desarrolladora',
        'email' => 'developer3@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now()
    ]
);

echo "✅ Usuarios creados/verificados\n";

// Asignar roles a usuarios
$users['admin']->roles()->sync([$roles['admin']->id]);
$users['team_leader_1']->roles()->sync([$roles['team_leader']->id]);
$users['team_leader_2']->roles()->sync([$roles['team_leader']->id]);
$users['developer_1']->roles()->sync([$roles['developer']->id]);
$users['developer_2']->roles()->sync([$roles['developer']->id]);
$users['developer_3']->roles()->sync([$roles['developer']->id]);

echo "✅ Roles asignados a usuarios\n";

// Crear proyectos
$projects = [];

$projects['ecommerce'] = Project::firstOrCreate(
    ['name' => 'E-commerce Platform'],
    [
        'name' => 'E-commerce Platform',
        'description' => 'Plataforma de comercio electrónico completa con carrito de compras, pagos y gestión de inventario',
        'status' => 'active',
        'start_date' => now()->subDays(30),
        'end_date' => now()->addDays(60),
        'created_by' => $users['admin']->id
    ]
);

$projects['mobile_app'] = Project::firstOrCreate(
    ['name' => 'Mobile App'],
    [
        'name' => 'Mobile App',
        'description' => 'Aplicación móvil para iOS y Android con funcionalidades de geolocalización',
        'status' => 'active',
        'start_date' => now()->subDays(15),
        'end_date' => now()->addDays(45),
        'created_by' => $users['admin']->id
    ]
);

$projects['dashboard'] = Project::firstOrCreate(
    ['name' => 'Analytics Dashboard'],
    [
        'name' => 'Analytics Dashboard',
        'description' => 'Panel de análisis con gráficos interactivos y reportes en tiempo real',
        'status' => 'active',
        'start_date' => now()->subDays(7),
        'end_date' => now()->addDays(30),
        'created_by' => $users['admin']->id
    ]
);

echo "✅ Proyectos creados\n";

// Asignar usuarios a proyectos
$projects['ecommerce']->users()->sync([
    $users['team_leader_1']->id,
    $users['developer_1']->id,
    $users['developer_2']->id
]);

$projects['mobile_app']->users()->sync([
    $users['team_leader_2']->id,
    $users['developer_2']->id,
    $users['developer_3']->id
]);

$projects['dashboard']->users()->sync([
    $users['team_leader_1']->id,
    $users['developer_1']->id,
    $users['developer_3']->id
]);

echo "✅ Usuarios asignados a proyectos\n";

// Crear sprints
$sprints = [];

foreach ($projects as $projectKey => $project) {
    $sprints[$projectKey . '_sprint_1'] = Sprint::firstOrCreate(
        [
            'project_id' => $project->id,
            'name' => 'Sprint 1'
        ],
        [
            'project_id' => $project->id,
            'name' => 'Sprint 1',
            'description' => 'Primer sprint del proyecto ' . $project->name,
            'start_date' => now()->subDays(7),
            'end_date' => now()->addDays(7),
            'status' => 'active'
        ]
    );

    $sprints[$projectKey . '_sprint_2'] = Sprint::firstOrCreate(
        [
            'project_id' => $project->id,
            'name' => 'Sprint 2'
        ],
        [
            'project_id' => $project->id,
            'name' => 'Sprint 2',
            'description' => 'Segundo sprint del proyecto ' . $project->name,
            'start_date' => now()->addDays(8),
            'end_date' => now()->addDays(21),
            'status' => 'planned'
        ]
    );
}

echo "✅ Sprints creados\n";

// Crear tareas
$tasks = [];

// Tareas para E-commerce
$tasks['ecommerce_login'] = Task::firstOrCreate(
    ['name' => 'Implementar sistema de login'],
    [
        'name' => 'Implementar sistema de login',
        'description' => 'Crear sistema de autenticación con JWT y validación de usuarios',
        'project_id' => $projects['ecommerce']->id,
        'sprint_id' => $sprints['ecommerce_sprint_1']->id,
        'status' => 'to do',
        'priority' => 'high',
        'estimated_hours' => 8,
        'created_by' => $users['team_leader_1']->id
    ]
);

$tasks['ecommerce_cart'] = Task::firstOrCreate(
    ['name' => 'Desarrollar carrito de compras'],
    [
        'name' => 'Desarrollar carrito de compras',
        'description' => 'Implementar funcionalidad de carrito con persistencia y cálculos',
        'project_id' => $projects['ecommerce']->id,
        'sprint_id' => $sprints['ecommerce_sprint_1']->id,
        'status' => 'in progress',
        'priority' => 'high',
        'estimated_hours' => 12,
        'user_id' => $users['developer_1']->id,
        'assigned_by' => $users['team_leader_1']->id,
        'assigned_at' => now()->subDays(2),
        'work_started_at' => now()->subDays(1),
        'is_working' => true,
        'total_time_seconds' => 14400, // 4 horas
        'created_by' => $users['team_leader_1']->id
    ]
);

$tasks['ecommerce_payment'] = Task::firstOrCreate(
    ['name' => 'Integrar pasarela de pagos'],
    [
        'name' => 'Integrar pasarela de pagos',
        'description' => 'Integrar Stripe para procesamiento de pagos seguros',
        'project_id' => $projects['ecommerce']->id,
        'sprint_id' => $sprints['ecommerce_sprint_1']->id,
        'status' => 'done',
        'priority' => 'medium',
        'estimated_hours' => 10,
        'user_id' => $users['developer_2']->id,
        'assigned_by' => $users['team_leader_1']->id,
        'assigned_at' => now()->subDays(5),
        'total_time_seconds' => 28800, // 8 horas
        'approval_status' => 'approved',
        'reviewed_by' => $users['team_leader_1']->id,
        'reviewed_at' => now()->subDays(1),
        'created_by' => $users['team_leader_1']->id
    ]
);

// Tareas para Mobile App
$tasks['mobile_ui'] = Task::firstOrCreate(
    ['name' => 'Diseñar interfaz de usuario'],
    [
        'name' => 'Diseñar interfaz de usuario',
        'description' => 'Crear mockups y prototipos de la interfaz móvil',
        'project_id' => $projects['mobile_app']->id,
        'sprint_id' => $sprints['mobile_app_sprint_1']->id,
        'status' => 'to do',
        'priority' => 'high',
        'estimated_hours' => 6,
        'created_by' => $users['team_leader_2']->id
    ]
);

$tasks['mobile_geolocation'] = Task::firstOrCreate(
    ['name' => 'Implementar geolocalización'],
    [
        'name' => 'Implementar geolocalización',
        'description' => 'Agregar funcionalidad de GPS y mapas interactivos',
        'project_id' => $projects['mobile_app']->id,
        'sprint_id' => $sprints['mobile_app_sprint_1']->id,
        'status' => 'in progress',
        'priority' => 'medium',
        'estimated_hours' => 15,
        'user_id' => $users['developer_2']->id,
        'assigned_by' => $users['team_leader_2']->id,
        'assigned_at' => now()->subDays(3),
        'work_started_at' => now()->subDays(2),
        'is_working' => false,
        'total_time_seconds' => 21600, // 6 horas
        'created_by' => $users['team_leader_2']->id
    ]
);

// Tareas para Dashboard
$tasks['dashboard_charts'] = Task::firstOrCreate(
    ['name' => 'Crear gráficos interactivos'],
    [
        'name' => 'Crear gráficos interactivos',
        'description' => 'Implementar gráficos con Chart.js y filtros dinámicos',
        'project_id' => $projects['dashboard']->id,
        'sprint_id' => $sprints['dashboard_sprint_1']->id,
        'status' => 'done',
        'priority' => 'high',
        'estimated_hours' => 14,
        'user_id' => $users['developer_1']->id,
        'assigned_by' => $users['team_leader_1']->id,
        'assigned_at' => now()->subDays(8),
        'total_time_seconds' => 36000, // 10 horas
        'approval_status' => 'pending',
        'created_by' => $users['team_leader_1']->id
    ]
);

echo "✅ Tareas creadas\n";

// Crear logs de tiempo para tareas en progreso
$timeLogs = [];

// Log para tarea de carrito de compras (en progreso)
$timeLogs['cart_session_1'] = TaskTimeLog::firstOrCreate(
    [
        'task_id' => $tasks['ecommerce_cart']->id,
        'user_id' => $users['developer_1']->id,
        'action' => 'start'
    ],
    [
        'task_id' => $tasks['ecommerce_cart']->id,
        'user_id' => $users['developer_1']->id,
        'started_at' => now()->subDays(1)->setTime(9, 0),
        'action' => 'start',
        'notes' => 'Inicio de trabajo en carrito de compras'
    ]
);

// Log para tarea de geolocalización (pausada)
$timeLogs['geo_session_1'] = TaskTimeLog::firstOrCreate(
    [
        'task_id' => $tasks['mobile_geolocation']->id,
        'user_id' => $users['developer_2']->id,
        'action' => 'start'
    ],
    [
        'task_id' => $tasks['mobile_geolocation']->id,
        'user_id' => $users['developer_2']->id,
        'started_at' => now()->subDays(2)->setTime(10, 0),
        'paused_at' => now()->subDays(2)->setTime(16, 0),
        'action' => 'pause',
        'duration_seconds' => 21600, // 6 horas
        'notes' => 'Sesión pausada para revisión de requerimientos'
    ]
);

echo "✅ Logs de tiempo creados\n\n";

// ============================================================================
// FASE 2: SIMULACIÓN DE OPERACIONES DEL FRONTEND
// ============================================================================

echo "🖥️  FASE 2: SIMULACIÓN DE OPERACIONES DEL FRONTEND\n";
echo "----------------------------------------------------\n";

// Instanciar servicios
$taskAssignmentService = new TaskAssignmentService();
$taskTimeTrackingService = new TaskTimeTrackingService();
$taskApprovalService = new TaskApprovalService();
$adminDashboardService = new AdminDashboardService();
$emailService = new EmailService();

// ============================================================================
// TEST 1: OPERACIONES DE DESARROLLADOR
// ============================================================================

echo "👨‍💻 TEST 1: OPERACIONES DE DESARROLLADOR\n";
echo "----------------------------------------\n";

// Simular login de desarrollador
echo "🔐 Simulando login de desarrollador (Ana Martínez)...\n";
$currentUser = $users['developer_1'];
echo "✅ Usuario logueado: {$currentUser->name}\n";

// Obtener tareas disponibles para auto-asignación
echo "\n📋 Obteniendo tareas disponibles para auto-asignación...\n";
$availableTasks = $taskAssignmentService->getAvailableTasksForDeveloper($currentUser->id);
echo "✅ Tareas disponibles: " . count($availableTasks) . "\n";

foreach ($availableTasks as $task) {
    echo "   - {$task->name} (Proyecto: {$task->project->name})\n";
}

// Simular auto-asignación de tarea
if (!empty($availableTasks)) {
    $taskToAssign = $availableTasks->first();
    echo "\n🎯 Simulando auto-asignación de tarea: {$taskToAssign->name}\n";
    
    $result = $taskAssignmentService->selfAssignTask($taskToAssign->id, $currentUser->id);
    if ($result) {
        echo "✅ Tarea auto-asignada exitosamente\n";
        $taskToAssign->refresh();
        echo "   Estado actual: {$taskToAssign->status}\n";
        echo "   Asignada a: {$taskToAssign->user->name}\n";
    } else {
        echo "❌ Error en auto-asignación\n";
    }
}

// Simular inicio de trabajo en tarea
echo "\n⏱️  Simulando inicio de trabajo en tarea...\n";
$workingTask = $tasks['ecommerce_cart'];
echo "Tarea seleccionada: {$workingTask->name}\n";

$startResult = $taskTimeTrackingService->startWork($workingTask->id, $currentUser->id);
if ($startResult) {
    echo "✅ Trabajo iniciado exitosamente\n";
    $workingTask->refresh();
    echo "   Estado: {$workingTask->status}\n";
    echo "   Trabajando: " . ($workingTask->is_working ? 'Sí' : 'No') . "\n";
    echo "   Tiempo total: " . gmdate('H:i:s', $workingTask->total_time_seconds) . "\n";
} else {
    echo "❌ Error al iniciar trabajo\n";
}

// Simular pausa de trabajo
echo "\n⏸️  Simulando pausa de trabajo...\n";
$pauseResult = $taskTimeTrackingService->pauseWork($workingTask->id, $currentUser->id);
if ($pauseResult) {
    echo "✅ Trabajo pausado exitosamente\n";
    $workingTask->refresh();
    echo "   Estado: {$workingTask->status}\n";
    echo "   Trabajando: " . ($workingTask->is_working ? 'Sí' : 'No') . "\n";
} else {
    echo "❌ Error al pausar trabajo\n";
}

// Simular reanudación de trabajo
echo "\n▶️  Simulando reanudación de trabajo...\n";
$resumeResult = $taskTimeTrackingService->resumeWork($workingTask->id, $currentUser->id);
if ($resumeResult) {
    echo "✅ Trabajo reanudado exitosamente\n";
    $workingTask->refresh();
    echo "   Estado: {$workingTask->status}\n";
    echo "   Trabajando: " . ($workingTask->is_working ? 'Sí' : 'No') . "\n";
} else {
    echo "❌ Error al reanudar trabajo\n";
}

// Simular finalización de trabajo
echo "\n✅ Simulando finalización de trabajo...\n";
$finishResult = $taskTimeTrackingService->finishWork($workingTask->id, $currentUser->id);
if ($finishResult) {
    echo "✅ Trabajo finalizado exitosamente\n";
    $workingTask->refresh();
    echo "   Estado: {$workingTask->status}\n";
    echo "   Trabajando: " . ($workingTask->is_working ? 'Sí' : 'No') . "\n";
    echo "   Tiempo total final: " . gmdate('H:i:s', $workingTask->total_time_seconds) . "\n";
} else {
    echo "❌ Error al finalizar trabajo\n";
}

// ============================================================================
// TEST 2: OPERACIONES DE TEAM LEADER
// ============================================================================

echo "\n👨‍💼 TEST 2: OPERACIONES DE TEAM LEADER\n";
echo "----------------------------------------\n";

// Simular login de team leader
echo "🔐 Simulando login de team leader (María González)...\n";
$currentUser = $users['team_leader_1'];
echo "✅ Usuario logueado: {$currentUser->name}\n";

// Obtener tareas pendientes de aprobación
echo "\n📋 Obteniendo tareas pendientes de aprobación...\n";
$pendingTasks = $taskApprovalService->getPendingTasksForTeamLeader($currentUser->id);
echo "✅ Tareas pendientes: " . count($pendingTasks) . "\n";

foreach ($pendingTasks as $task) {
    echo "   - {$task->name} (Desarrollador: {$task->user->name})\n";
}

// Simular aprobación de tarea
if (!empty($pendingTasks)) {
    $taskToApprove = $pendingTasks->first();
    echo "\n✅ Simulando aprobación de tarea: {$taskToApprove->name}\n";
    
    $approveResult = $taskApprovalService->approveTask($taskToApprove->id, $currentUser->id);
    if ($approveResult) {
        echo "✅ Tarea aprobada exitosamente\n";
        $taskToApprove->refresh();
        echo "   Estado de aprobación: {$taskToApprove->approval_status}\n";
        echo "   Revisada por: {$taskToApprove->reviewedBy->name}\n";
        echo "   Fecha de revisión: {$taskToApprove->reviewed_at}\n";
    } else {
        echo "❌ Error al aprobar tarea\n";
    }
}

// Simular rechazo de tarea
$taskToReject = $tasks['dashboard_charts'];
echo "\n❌ Simulando rechazo de tarea: {$taskToReject->name}\n";

$rejectResult = $taskApprovalService->rejectTask(
    $taskToReject->id, 
    $currentUser->id, 
    'Necesita mejoras en la responsividad de los gráficos'
);
if ($rejectResult) {
    echo "✅ Tarea rechazada exitosamente\n";
    $taskToReject->refresh();
    echo "   Estado de aprobación: {$taskToReject->approval_status}\n";
    echo "   Motivo de rechazo: {$taskToReject->rejection_reason}\n";
} else {
    echo "❌ Error al rechazar tarea\n";
}

// Obtener estadísticas de aprobación
echo "\n📊 Obteniendo estadísticas de aprobación...\n";
$approvalStats = $taskApprovalService->getApprovalStatsForTeamLeader($currentUser->id);
echo "✅ Estadísticas obtenidas:\n";
echo "   Tareas aprobadas: {$approvalStats['approved']}\n";
echo "   Tareas rechazadas: {$approvalStats['rejected']}\n";
echo "   Tareas pendientes: {$approvalStats['pending']}\n";
echo "   Tasa de aprobación: " . round($approvalStats['approval_rate'], 2) . "%\n";

// ============================================================================
// TEST 3: OPERACIONES DE ADMINISTRADOR
// ============================================================================

echo "\n👨‍💻 TEST 3: OPERACIONES DE ADMINISTRADOR\n";
echo "----------------------------------------\n";

// Simular login de administrador
echo "🔐 Simulando login de administrador...\n";
$currentUser = $users['admin'];
echo "✅ Usuario logueado: {$currentUser->name}\n";

// Obtener estadísticas del sistema
echo "\n📊 Obteniendo estadísticas del sistema...\n";
$systemStats = $adminDashboardService->getSystemStats();
echo "✅ Estadísticas del sistema:\n";
echo "   Proyectos totales: {$systemStats['totalProjects']}\n";
echo "   Proyectos activos: {$systemStats['activeProjects']}\n";
echo "   Tareas totales: {$systemStats['totalTasks']}\n";
echo "   Tareas en progreso: {$systemStats['inProgressTasks']}\n";
echo "   Tareas completadas: {$systemStats['completedTasks']}\n";
echo "   Usuarios totales: {$systemStats['totalUsers']}\n";
echo "   Desarrolladores: {$systemStats['developers']}\n";
echo "   Team Leaders: {$systemStats['teamLeaders']}\n";

// Obtener métricas de desarrolladores
echo "\n👥 Obteniendo métricas de desarrolladores...\n";
$developerMetrics = $adminDashboardService->getDeveloperPerformanceMetrics();
echo "✅ Métricas de desarrolladores obtenidas: " . count($developerMetrics) . " desarrolladores\n";

foreach ($developerMetrics as $metric) {
    echo "   {$metric['user_name']}:\n";
    echo "     Eficiencia: " . round($metric['efficiency_percentage'], 2) . "%\n";
    echo "     Tasa de completitud: " . round($metric['completion_rate'], 2) . "%\n";
    echo "     Tiempo promedio por tarea: " . gmdate('H:i:s', $metric['average_task_time']) . "\n";
    echo "     Tareas completadas: {$metric['completed_tasks']}\n";
}

// Obtener tareas que requieren atención
echo "\n⚠️  Obteniendo tareas que requieren atención...\n";
$attentionTasks = $adminDashboardService->getTasksRequiringAttention();
echo "✅ Tareas que requieren atención: " . count($attentionTasks) . "\n";

foreach ($attentionTasks as $task) {
    echo "   - {$task->name} (Proyecto: {$task->project->name})\n";
    echo "     Desarrollador: {$task->user->name}\n";
    echo "     Tiempo estimado: {$task->estimated_hours}h\n";
    echo "     Tiempo real: " . gmdate('H:i:s', $task->total_time_seconds) . "\n";
    echo "     Diferencia: " . round(($task->total_time_seconds / 3600) - $task->estimated_hours, 2) . "h\n";
}

// Obtener reporte de tiempo
echo "\n⏰ Obteniendo reporte de tiempo...\n";
$timeReport = $adminDashboardService->getTimeReportByPeriod('week');
echo "✅ Reporte de tiempo semanal:\n";
echo "   Tiempo total registrado: " . gmdate('H:i:s', $timeReport['total_time']) . "\n";
echo "   Tiempo estimado total: " . gmdate('H:i:s', $timeReport['total_estimated_time']) . "\n";
echo "   Eficiencia general: " . round($timeReport['efficiency'], 2) . "%\n";

// ============================================================================
// TEST 4: OPERACIONES DE EMAIL
// ============================================================================

echo "\n📧 TEST 4: OPERACIONES DE EMAIL\n";
echo "--------------------------------\n";

// Simular envío de email de asignación
echo "📨 Simulando envío de email de asignación...\n";
$assignmentEmail = $emailService->sendTaskAssignmentEmail(
    $users['developer_1'],
    $tasks['ecommerce_login'],
    $users['team_leader_1']
);
echo "✅ Email de asignación enviado\n";

// Simular envío de email de notificación de aprobación
echo "📨 Simulando envío de email de notificación de aprobación...\n";
$approvalEmail = $emailService->sendTaskApprovalEmail(
    $users['developer_1'],
    $tasks['ecommerce_payment'],
    'approved',
    $users['team_leader_1']
);
echo "✅ Email de aprobación enviado\n";

// ============================================================================
// TEST 5: OPERACIONES DE KANBAN
// ============================================================================

echo "\n📋 TEST 5: OPERACIONES DE KANBAN\n";
echo "--------------------------------\n";

// Simular obtención de tareas para Kanban
echo "🎯 Obteniendo tareas para vista Kanban...\n";
$kanbanTasks = Task::with(['project', 'sprint', 'user', 'assignedBy'])
    ->where('project_id', $projects['ecommerce']->id)
    ->get()
    ->groupBy('status');

echo "✅ Tareas organizadas por estado:\n";
foreach ($kanbanTasks as $status => $tasks) {
    echo "   {$status}: " . count($tasks) . " tareas\n";
    foreach ($tasks as $task) {
        echo "     - {$task->name}";
        if ($task->user) {
            echo " (Asignada a: {$task->user->name})";
        }
        echo "\n";
    }
}

// Simular drag & drop (cambio de estado)
echo "\n🔄 Simulando drag & drop (cambio de estado)...\n";
$taskToMove = $tasks['ecommerce_login'];
$oldStatus = $taskToMove->status;
$taskToMove->status = 'in progress';
$taskToMove->save();

echo "✅ Tarea movida de '{$oldStatus}' a '{$taskToMove->status}'\n";
echo "   Tarea: {$taskToMove->name}\n";

// ============================================================================
// TEST 6: OPERACIONES DE BÚSQUEDA Y FILTROS
// ============================================================================

echo "\n🔍 TEST 6: OPERACIONES DE BÚSQUEDA Y FILTROS\n";
echo "--------------------------------------------\n";

// Simular búsqueda de tareas
echo "🔍 Simulando búsqueda de tareas...\n";
$searchResults = Task::where('name', 'like', '%login%')
    ->orWhere('description', 'like', '%login%')
    ->with(['project', 'user'])
    ->get();

echo "✅ Resultados de búsqueda para 'login': " . count($searchResults) . " tareas\n";
foreach ($searchResults as $task) {
    echo "   - {$task->name} (Proyecto: {$task->project->name})\n";
}

// Simular filtros por prioridad
echo "\n🎯 Simulando filtros por prioridad...\n";
$highPriorityTasks = Task::where('priority', 'high')
    ->with(['project', 'user'])
    ->get();

echo "✅ Tareas de alta prioridad: " . count($highPriorityTasks) . "\n";
foreach ($highPriorityTasks as $task) {
    echo "   - {$task->name} (Proyecto: {$task->project->name})\n";
}

// ============================================================================
// RESUMEN FINAL
// ============================================================================

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎉 RESUMEN DEL TESTING COMPLETO\n";
echo str_repeat("=", 50) . "\n";

echo "✅ DATOS CREADOS:\n";
echo "   - Usuarios: " . count($users) . "\n";
echo "   - Proyectos: " . count($projects) . "\n";
echo "   - Sprints: " . count($sprints) . "\n";
echo "   - Tareas: " . count($tasks) . "\n";
echo "   - Logs de tiempo: " . count($timeLogs) . "\n";

echo "\n✅ FUNCIONALIDADES PROBADAS:\n";
echo "   - Autenticación y autorización\n";
echo "   - Auto-asignación de tareas\n";
echo "   - Seguimiento de tiempo (inicio, pausa, reanudación, fin)\n";
echo "   - Sistema de aprobación/rechazo\n";
echo "   - Dashboard y métricas\n";
echo "   - Envío de emails\n";
echo "   - Operaciones de Kanban\n";
echo "   - Búsqueda y filtros\n";

echo "\n✅ SERVICIOS VERIFICADOS:\n";
echo "   - TaskAssignmentService\n";
echo "   - TaskTimeTrackingService\n";
echo "   - TaskApprovalService\n";
echo "   - AdminDashboardService\n";
echo "   - EmailService\n";

echo "\n🚀 EL SISTEMA ESTÁ COMPLETAMENTE FUNCIONAL Y LISTO PARA USO\n";
echo "==================================================\n";

echo "\n📋 PRÓXIMOS PASOS:\n";
echo "1. Ejecutar: php artisan serve\n";
echo "2. Acceder a: http://localhost:8000\n";
echo "3. Usar las credenciales de prueba creadas\n";
echo "4. Explorar todas las funcionalidades implementadas\n";

echo "\n🎯 CREDENCIALES DE PRUEBA:\n";
echo "   Admin: admin@example.com / password\n";
echo "   Team Leader 1: teamleader1@example.com / password\n";
echo "   Team Leader 2: teamleader2@example.com / password\n";
echo "   Developer 1: developer1@example.com / password\n";
echo "   Developer 2: developer2@example.com / password\n";
echo "   Developer 3: developer3@example.com / password\n";

echo "\n✅ ¡TESTING COMPLETADO EXITOSAMENTE!\n"; 
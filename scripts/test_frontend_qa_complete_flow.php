<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Task;
use App\Models\Bug;
use App\Models\Project;
use App\Models\Sprint;
use App\Services\NotificationService;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRUEBA DEL FLUJO COMPLETO QA DESDE FRONTEND ===\n\n";

try {
    // Buscar usuarios necesarios
    $qa = User::where('email', 'qa@tracker.com')->first();
    $developer = User::whereHas('roles', function ($query) {
        $query->where('value', 'developer');
    })->where('status', 'active')->first();
    $teamLeader = User::whereHas('roles', function ($query) {
        $query->where('value', 'team_leader');
    })->first();
    
    if (!$qa) {
        echo "❌ Usuario qa@tracker.com no encontrado\n";
        exit(1);
    }
    
    if (!$developer) {
        echo "❌ No se encontró ningún desarrollador activo\n";
        exit(1);
    }
    
    if (!$teamLeader) {
        echo "❌ No se encontró ningún team leader\n";
        exit(1);
    }
    
    echo "✅ Usuarios encontrados:\n";
    echo "   - QA: {$qa->name} ({$qa->email})\n";
    echo "   - Developer: {$developer->name} ({$developer->email})\n";
    echo "   - Team Leader: {$teamLeader->name} ({$teamLeader->email})\n";
    
    // Verificar estado actual
    $totalTasks = Task::where('qa_status', 'ready_for_test')->count();
    $totalBugs = Bug::where('qa_status', 'ready_for_test')->count();
    $totalNotifications = $qa->notifications()->where('read', false)->count();
    
    echo "\n📊 ESTADO ACTUAL:\n";
    echo "   - Tareas listas para testing: {$totalTasks}\n";
    echo "   - Bugs listos para testing: {$totalBugs}\n";
    echo "   - Notificaciones no leídas: {$totalNotifications}\n";
    
    if ($totalTasks === 0 && $totalBugs === 0) {
        echo "\n⚠️  No hay items para testing. Creando items de prueba...\n";
        
        // Buscar proyecto asignado al QA
        $project = $qa->projects()->first();
        if (!$project) {
            echo "❌ El QA no tiene proyectos asignados\n";
            exit(1);
        }
        
        $sprint = $project->sprints()->first();
        if (!$sprint) {
            echo "❌ No hay sprints en el proyecto\n";
            exit(1);
        }
        
        // Crear una tarea de prueba
        $task = Task::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => 'Prueba de Testing QA',
            'description' => 'Tarea creada para probar el flujo de testing del QA',
            'priority' => 'high',
            'status' => 'done',
            'qa_status' => 'ready_for_test',
            'project_id' => $project->id,
            'sprint_id' => $sprint->id,
            'user_id' => $developer->id,
            'assigned_to' => $developer->id,
            'qa_assigned_to' => $qa->id,
            'estimated_hours' => 4,
            'actual_hours' => 5,
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subHours(2)
        ]);
        
        // Crear un bug de prueba
        $bug = Bug::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'title' => 'Bug de Prueba QA',
            'description' => 'Bug creado para probar el flujo de testing del QA',
            'importance' => 'medium',
            'status' => 'resolved',
            'qa_status' => 'ready_for_test',
            'project_id' => $project->id,
            'sprint_id' => $sprint->id,
            'user_id' => $developer->id,
            'assigned_to' => $developer->id,
            'qa_assigned_to' => $qa->id,
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subHours(1)
        ]);
        
        // Crear notificaciones
        $notificationService = new NotificationService();
        $notificationService->notifyTaskReadyForQa($task);
        $notificationService->notifyBugReadyForQa($bug);
        
        echo "   ✅ Tarea de prueba creada: {$task->name}\n";
        echo "   ✅ Bug de prueba creado: {$bug->title}\n";
        echo "   ✅ Notificaciones creadas\n";
        
        $totalTasks = 1;
        $totalBugs = 1;
        $totalNotifications = $qa->notifications()->where('read', false)->count();
    }
    
    echo "\n🎯 FLUJO DE TESTING A PROBAR:\n";
    echo "   1. QA inicia sesión en http://127.0.0.1:8000\n";
    echo "   2. QA ve notificaciones en la campana (esquina superior derecha)\n";
    echo "   3. QA hace click en 'Finished Items' en el sidebar\n";
    echo "   4. QA ve {$totalTasks} tareas y {$totalBugs} bugs listos para testing\n";
    echo "   5. QA hace click en 'Iniciar Testing' en una tarea\n";
    echo "   6. QA ve el botón cambiar a 'Pausar Testing' y 'Finalizar Testing'\n";
    echo "   7. QA hace click en 'Pausar Testing'\n";
    echo "   8. QA ve el botón cambiar a 'Reanudar Testing'\n";
    echo "   9. QA hace click en 'Reanudar Testing'\n";
    echo "   10. QA hace click en 'Finalizar Testing'\n";
    echo "   11. QA ve el botón cambiar a 'Aprobar' o 'Rechazar'\n";
    echo "   12. QA hace click en 'Aprobar'\n";
    echo "   13. Team Leader recibe notificación\n";
    
    echo "\n🔗 URLs PARA TESTING:\n";
    echo "   - Login QA: http://127.0.0.1:8000/login\n";
    echo "   - Dashboard QA: http://127.0.0.1:8000/dashboard\n";
    echo "   - Finished Items: http://127.0.0.1:8000/qa/finished-items\n";
    echo "   - Notifications: Campana en esquina superior derecha\n";
    
    echo "\n👤 CREDENCIALES:\n";
    echo "   - QA: qa@tracker.com / password\n";
    echo "   - Developer: {$developer->email} / password\n";
    echo "   - Team Leader: {$teamLeader->email} / password\n";
    
    echo "\n📋 PASOS PARA PROBAR:\n";
    echo "   1. Abrir http://127.0.0.1:8000/login\n";
    echo "   2. Iniciar sesión con qa@tracker.com / password\n";
    echo "   3. Verificar que aparezca la campana de notificaciones en la esquina superior derecha\n";
    echo "   4. Hacer click en la campana para ver las notificaciones\n";
    echo "   5. Hacer click en 'Finished Items' en el sidebar\n";
    echo "   6. Verificar que aparezcan los items con estado 'Listo para Testing'\n";
    echo "   7. Hacer click en 'Iniciar Testing' en una tarea\n";
    echo "   8. Verificar que el estado cambie a 'En Testing'\n";
    echo "   9. Verificar que aparezcan los botones 'Pausar Testing' y 'Finalizar Testing'\n";
    echo "   10. Hacer click en 'Pausar Testing'\n";
    echo "   11. Verificar que aparezca el botón 'Reanudar Testing'\n";
    echo "   12. Hacer click en 'Reanudar Testing'\n";
    echo "   13. Hacer click en 'Finalizar Testing'\n";
    echo "   14. Verificar que aparezcan los botones 'Aprobar' y 'Rechazar'\n";
    echo "   15. Hacer click en 'Aprobar'\n";
    echo "   16. Verificar que el Team Leader reciba la notificación\n";
    
    echo "\n✅ VERIFICACIONES ESPERADAS:\n";
    echo "   ✅ QA NO debe ver botones de desarrollo (iniciar/reanudar/finalizar tareas)\n";
    echo "   ✅ QA SÍ debe ver botones de testing (iniciar/pausar/reanudar/finalizar testing)\n";
    echo "   ✅ Los botones deben cambiar según el estado del item\n";
    echo "   ✅ Las notificaciones deben aparecer en tiempo real\n";
    echo "   ✅ El Team Leader debe recibir notificaciones cuando QA aprueba\n";
    echo "   ✅ Los filtros deben funcionar correctamente\n";
    echo "   ✅ Los tabs (Todos/Tareas/Bugs) deben funcionar\n";
    echo "   ✅ Las estadísticas deben actualizarse en tiempo real\n";
    
    echo "\n🚀 ¡SISTEMA LISTO PARA TESTING FRONTEND!\n";
    echo "   El QA puede ahora probar completamente el cronómetro de testing\n";
    echo "   y todo el flujo de trabajo desde la interfaz web.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 
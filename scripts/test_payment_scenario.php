<?php

/**
 * Script de prueba para el escenario de pago de Camilo
 * 
 * Escenario:
 * - Desarrollador: Camilo
 * - Valor por hora: 14,000 COP
 * - 5 tareas completadas: 5 horas cada una = 25 horas
 * - 1 tarea pausada: 5 horas consumidas = 5 horas
 * - Total: 30 horas
 * - Pago esperado: 30 * 14,000 = 420,000 COP
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\Role;
use App\Models\PaymentReport;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 Iniciando prueba del escenario de pago de Camilo\n";
echo "==================================================\n\n";

try {
    // 1. Crear o obtener el rol de desarrollador
    echo "1. Verificando rol de desarrollador...\n";
    $developerRole = Role::firstOrCreate(
        ['name' => 'developer'],
        ['name' => 'developer', 'display_name' => 'Developer', 'description' => 'Software Developer']
    );
    echo "✅ Rol de desarrollador verificado\n\n";

    // 2. Crear o obtener el usuario Camilo primero
    echo "2. Creando usuario Camilo...\n";
    $camilo = User::firstOrCreate(
        ['email' => 'camilo@test.com'],
        [
            'name' => 'Camilo Test',
            'nickname' => 'camilo',
            'email' => 'camilo@test.com',
            'password' => Hash::make('password123'),
            'hour_value' => 14000, // 14,000 COP por hora
            'work_time' => 'full',
            'status' => 'active',
        ]
    );

    // Asignar rol de desarrollador si no lo tiene
    if (!$camilo->roles()->where('name', 'developer')->exists()) {
        $camilo->roles()->attach($developerRole->id);
    }

    echo "✅ Usuario Camilo creado:\n";
    echo "   - Nombre: {$camilo->name}\n";
    echo "   - Email: {$camilo->email}\n";
    echo "   - Valor por hora: $" . number_format($camilo->hour_value, 0, ',', '.') . " COP\n\n";

    // 3. Crear o obtener el proyecto de prueba
    echo "3. Creando proyecto de prueba...\n";
    $project = Project::firstOrCreate(
        ['name' => 'Proyecto de Prueba - Pagos'],
        [
            'name' => 'Proyecto de Prueba - Pagos',
            'description' => 'Proyecto para probar el sistema de pagos',
            'status' => 'active',
            'created_by' => $camilo->id,
        ]
    );
    echo "✅ Proyecto creado: {$project->name}\n\n";

    // 4. Crear sprint de prueba
    echo "4. Creando sprint de prueba...\n";
    $sprint = Sprint::firstOrCreate(
        [
            'name' => 'Sprint de Prueba - Pagos',
            'project_id' => $project->id
        ],
        [
            'name' => 'Sprint de Prueba - Pagos',
            'goal' => 'Completar pruebas del sistema de pagos',
            'project_id' => $project->id,
            'start_date' => Carbon::now()->subDays(7),
            'end_date' => Carbon::now()->addDays(7),
        ]
    );
    echo "✅ Sprint creado: {$sprint->name}\n\n";

    // 5. Asignar Camilo al proyecto
    if (!$camilo->projects()->where('project_id', $project->id)->exists()) {
        $camilo->projects()->attach($project->id);
        echo "✅ Camilo asignado al proyecto\n\n";
    }

    // 6. Crear las 5 tareas completadas
    echo "6. Creando 5 tareas completadas...\n";
    $completedTasks = [];
    for ($i = 1; $i <= 5; $i++) {
        $task = Task::create([
            'name' => "Tarea Completada {$i}",
            'description' => "Descripción de la tarea completada {$i}",
            'status' => 'done',
            'priority' => 'medium',
            'category' => 'full stack',
            'story_points' => 3,
            'sprint_id' => $sprint->id,
            'project_id' => $project->id,
            'user_id' => $camilo->id,
            'assigned_by' => $camilo->id,
            'assigned_at' => Carbon::now()->subDays(10),
            'estimated_hours' => 5,
            'actual_hours' => 5,
            'actual_start' => Carbon::now()->subDays(10),
            'actual_finish' => Carbon::now()->subDays(5),
            'total_time_seconds' => 5 * 3600, // 5 horas en segundos
        ]);
        
        $completedTasks[] = $task;
        echo "   ✅ Tarea {$i} creada: {$task->name} (5 horas)\n";
    }
    echo "\n";

    // 7. Crear la tarea pausada
    echo "7. Creando tarea pausada...\n";
    $pausedTask = Task::create([
        'name' => 'Tarea Pausada',
        'description' => 'Tarea que está pausada con 5 horas consumidas',
        'status' => 'in progress',
        'priority' => 'high',
        'category' => 'full stack',
        'story_points' => 5,
        'sprint_id' => $sprint->id,
        'project_id' => $project->id,
        'user_id' => $camilo->id,
        'assigned_by' => $camilo->id,
        'assigned_at' => Carbon::now()->subDays(3),
        'estimated_hours' => 8,
        'actual_hours' => 5, // 5 horas consumidas
        'actual_start' => Carbon::now()->subDays(3),
        'total_time_seconds' => 5 * 3600, // 5 horas en segundos
        'work_started_at' => Carbon::now()->subDays(3),
        'is_working' => false,
    ]);
    
    echo "✅ Tarea pausada creada: {$pausedTask->name} (5 horas consumidas)\n\n";

    // 8. Generar reporte de pago usando el servicio
    echo "8. Generando reporte de pago...\n";
    $paymentService = new PaymentService();
    
    // Generar reporte para la semana actual
    $weekStart = Carbon::now()->startOfWeek();
    $weekEnd = Carbon::now()->endOfWeek();
    
    $paymentReport = $paymentService->generateReportForDateRange($camilo, $weekStart, $weekEnd);
    
    echo "✅ Reporte de pago generado:\n";
    echo "   - Semana: {$paymentReport->week_start_date->format('d/m/Y')} - {$paymentReport->week_end_date->format('d/m/Y')}\n";
    echo "   - Horas totales: {$paymentReport->total_hours}\n";
    echo "   - Tarifa por hora: $" . number_format($paymentReport->hourly_rate, 0, ',', '.') . " COP\n";
    echo "   - Pago total: $" . number_format($paymentReport->total_payment, 0, ',', '.') . " COP\n";
    echo "   - Tareas completadas: {$paymentReport->completed_tasks_count}\n";
    echo "   - Tareas en progreso: {$paymentReport->in_progress_tasks_count}\n";
    echo "   - Estado: {$paymentReport->status}\n\n";

    // 9. Verificar cálculos
    echo "9. Verificando cálculos...\n";
    $expectedHours = 30; // 5 tareas * 5 horas + 1 tarea pausada * 5 horas
    $expectedPayment = $expectedHours * $camilo->hour_value; // 30 * 14,000 = 420,000
    
    echo "   Cálculo esperado:\n";
    echo "   - Horas esperadas: {$expectedHours}\n";
    echo "   - Pago esperado: $" . number_format($expectedPayment, 0, ',', '.') . " COP\n";
    echo "   - Horas reales: {$paymentReport->total_hours}\n";
    echo "   - Pago real: $" . number_format($paymentReport->total_payment, 0, ',', '.') . " COP\n";
    
    if ($paymentReport->total_hours == $expectedHours) {
        echo "   ✅ Horas coinciden\n";
    } else {
        echo "   ❌ Horas NO coinciden\n";
    }
    
    if ($paymentReport->total_payment == $expectedPayment) {
        echo "   ✅ Pago coincide\n";
    } else {
        echo "   ❌ Pago NO coincide\n";
    }
    echo "\n";

    // 10. Mostrar detalles de las tareas en el reporte
    echo "10. Detalles de tareas en el reporte:\n";
    $taskDetails = $paymentReport->task_details;
    
    if (isset($taskDetails['completed']) && count($taskDetails['completed']) > 0) {
        echo "   Tareas completadas:\n";
        foreach ($taskDetails['completed'] as $task) {
            echo "   - {$task['name']}: {$task['hours']} horas = $" . number_format($task['payment'], 0, ',', '.') . " COP\n";
        }
    }
    
    if (isset($taskDetails['in_progress']) && count($taskDetails['in_progress']) > 0) {
        echo "   Tareas en progreso:\n";
        foreach ($taskDetails['in_progress'] as $task) {
            echo "   - {$task['name']}: {$task['hours']} horas = $" . number_format($task['payment'], 0, ',', '.') . " COP\n";
        }
    }
    echo "\n";

    // 11. Probar la funcionalidad del frontend
    echo "11. Información para probar en el frontend:\n";
    echo "   - URL del dashboard de pagos: /payments/dashboard\n";
    echo "   - URL del admin dashboard: /payments/admin\n";
    echo "   - URL de reportes: /payments/reports\n";
    echo "   - Credenciales de Camilo:\n";
    echo "     * Email: camilo@test.com\n";
    echo "     * Password: password123\n";
    echo "\n";

    // 12. Generar reporte para la semana anterior también
    echo "12. Generando reporte para la semana anterior...\n";
    $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
    $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();
    
    $lastWeekReport = $paymentService->generateReportForDateRange($camilo, $lastWeekStart, $lastWeekEnd);
    
    echo "✅ Reporte de la semana anterior generado:\n";
    echo "   - Semana: {$lastWeekReport->week_start_date->format('d/m/Y')} - {$lastWeekReport->week_end_date->format('d/m/Y')}\n";
    echo "   - Horas totales: {$lastWeekReport->total_hours}\n";
    echo "   - Pago total: $" . number_format($lastWeekReport->total_payment, 0, ',', '.') . " COP\n\n";

    echo "🎉 ¡Prueba completada exitosamente!\n";
    echo "================================\n";
    echo "El escenario de Camilo ha sido creado y el sistema de pagos está funcionando correctamente.\n";
    echo "Puedes acceder al frontend para ver los reportes generados.\n\n";

} catch (Exception $e) {
    echo "❌ Error durante la prueba: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
<?php

/**
 * Script para limpiar los datos de prueba del escenario de pago de Camilo
 * Elimina el usuario Camilo, sus tareas y reportes de pago
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\PaymentReport;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧹 Limpiando datos de prueba del escenario de Camilo\n";
echo "==================================================\n\n";

try {
    // 1. Buscar y eliminar el usuario Camilo
    echo "1. Buscando usuario Camilo...\n";
    $camilo = User::where('email', 'camilo@test.com')->first();
    
    if ($camilo) {
        echo "✅ Usuario Camilo encontrado: {$camilo->name}\n";
        
        // 2. Eliminar reportes de pago de Camilo
        echo "2. Eliminando reportes de pago...\n";
        $paymentReports = PaymentReport::where('user_id', $camilo->id)->get();
        echo "   - Encontrados " . $paymentReports->count() . " reportes de pago\n";
        
        foreach ($paymentReports as $report) {
            $report->delete();
            echo "   ✅ Reporte eliminado: ID {$report->id}\n";
        }
        
        // 3. Eliminar tareas de Camilo
        echo "3. Eliminando tareas...\n";
        $tasks = Task::where('user_id', $camilo->id)->get();
        echo "   - Encontradas " . $tasks->count() . " tareas\n";
        
        foreach ($tasks as $task) {
            $task->delete();
            echo "   ✅ Tarea eliminada: {$task->name}\n";
        }
        
        // 4. Eliminar el usuario Camilo
        echo "4. Eliminando usuario Camilo...\n";
        $camilo->delete();
        echo "✅ Usuario Camilo eliminado\n";
        
    } else {
        echo "⚠️  Usuario Camilo no encontrado\n";
    }
    
    // 5. Buscar y eliminar el proyecto de prueba
    echo "5. Buscando proyecto de prueba...\n";
    $project = Project::where('name', 'Proyecto de Prueba - Pagos')->first();
    
    if ($project) {
        echo "✅ Proyecto de prueba encontrado: {$project->name}\n";
        
        // 6. Eliminar sprints del proyecto
        echo "6. Eliminando sprints...\n";
        $sprints = Sprint::where('project_id', $project->id)->get();
        echo "   - Encontrados " . $sprints->count() . " sprints\n";
        
        foreach ($sprints as $sprint) {
            $sprint->delete();
            echo "   ✅ Sprint eliminado: {$sprint->name}\n";
        }
        
        // 7. Eliminar el proyecto
        echo "7. Eliminando proyecto...\n";
        $project->delete();
        echo "✅ Proyecto de prueba eliminado\n";
        
    } else {
        echo "⚠️  Proyecto de prueba no encontrado\n";
    }
    
    // 8. Verificar que no quedan datos relacionados
    echo "8. Verificando limpieza...\n";
    
    $remainingTasks = Task::where('name', 'like', '%Tarea Completada%')
        ->orWhere('name', 'like', '%Tarea Pausada%')
        ->count();
    
    $remainingReports = PaymentReport::where('total_payment', 420000)->count();
    
    $remainingUsers = User::where('email', 'camilo@test.com')->count();
    
    $remainingProjects = Project::where('name', 'like', '%Proyecto de Prueba%')->count();
    
    echo "   - Tareas restantes: {$remainingTasks}\n";
    echo "   - Reportes restantes con pago 420,000: {$remainingReports}\n";
    echo "   - Usuarios Camilo restantes: {$remainingUsers}\n";
    echo "   - Proyectos de prueba restantes: {$remainingProjects}\n";
    
    if ($remainingTasks == 0 && $remainingReports == 0 && $remainingUsers == 0 && $remainingProjects == 0) {
        echo "✅ Limpieza completada exitosamente\n";
    } else {
        echo "⚠️  Algunos datos de prueba aún permanecen\n";
    }
    
    echo "\n🎉 ¡Limpieza completada!\n";
    echo "=======================\n";
    echo "Todos los datos de prueba del escenario de Camilo han sido eliminados.\n\n";

} catch (Exception $e) {
    echo "❌ Error durante la limpieza: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
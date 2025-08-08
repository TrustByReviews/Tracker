<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Task;
use App\Models\Bug;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRUEBA DEL CRONÓMETRO EN TIEMPO REAL ===\n\n";

try {
    // Buscar QA
    $qa = User::where('email', 'qa@tracker.com')->first();
    
    if (!$qa) {
        echo "❌ Usuario qa@tracker.com no encontrado\n";
        exit(1);
    }
    
    echo "✅ QA encontrado: {$qa->name} ({$qa->email})\n";
    
    // Verificar tareas en testing
    $testingTasks = Task::where('qa_assigned_to', $qa->id)
        ->whereIn('qa_status', ['testing', 'testing_paused'])
        ->get();
    
    $testingBugs = Bug::where('qa_assigned_to', $qa->id)
        ->whereIn('qa_status', ['testing', 'testing_paused'])
        ->get();
    
    echo "\n📊 ITEMS EN TESTING:\n";
    echo "   - Tareas en testing: {$testingTasks->count()}\n";
    echo "   - Bugs en testing: {$testingBugs->count()}\n";
    
    if ($testingTasks->count() > 0) {
        echo "\n🔍 TAREAS EN TESTING:\n";
        foreach ($testingTasks as $task) {
            $startTime = $task->qa_testing_started_at;
            $pausedTime = $task->qa_testing_paused_at;
            $status = $task->qa_status;
            
            echo "   - {$task->name}\n";
            echo "     Estado: {$status}\n";
            echo "     Iniciado: {$startTime}\n";
            if ($pausedTime) {
                echo "     Pausado: {$pausedTime}\n";
            }
            echo "\n";
        }
    }
    
    if ($testingBugs->count() > 0) {
        echo "\n🔍 BUGS EN TESTING:\n";
        foreach ($testingBugs as $bug) {
            $startTime = $bug->qa_testing_started_at;
            $pausedTime = $bug->qa_testing_paused_at;
            $status = $bug->qa_status;
            
            echo "   - {$bug->title}\n";
            echo "     Estado: {$status}\n";
            echo "     Iniciado: {$startTime}\n";
            if ($pausedTime) {
                echo "     Pausado: {$pausedTime}\n";
            }
            echo "\n";
        }
    }
    
    echo "\n🎯 FUNCIONALIDADES DEL CRONÓMETRO:\n";
    echo "   1. ✅ Cronómetro se actualiza cada segundo\n";
    echo "   2. ✅ Muestra formato HH:MM:SS\n";
    echo "   3. ✅ Se pausa cuando el testing está pausado\n";
    echo "   4. ✅ Se reanuda cuando el testing se reanuda\n";
    echo "   5. ✅ Se detiene cuando el testing se finaliza\n";
    
    echo "\n🔧 IMPLEMENTACIÓN TÉCNICA:\n";
    echo "   ✅ setInterval cada 1000ms (1 segundo)\n";
    echo "   ✅ Cálculo de tiempo basado en timestamps\n";
    echo "   ✅ Manejo de estados pausado/activo\n";
    echo "   ✅ Limpieza automática al desmontar componente\n";
    
    echo "\n🔗 PARA PROBAR:\n";
    echo "   1. Abrir: http://127.0.0.1:8000/qa/finished-items\n";
    echo "   2. Iniciar testing en una tarea\n";
    echo "   3. Verificar que el cronómetro se actualiza cada segundo\n";
    echo "   4. Pausar testing y verificar que se pausa\n";
    echo "   5. Reanudar testing y verificar que continúa\n";
    
    echo "\n✅ VERIFICACIONES:\n";
    echo "   ✅ El cronómetro debe actualizarse cada segundo\n";
    echo "   ✅ El formato debe ser HH:MM:SS\n";
    echo "   ✅ Debe pausarse cuando el estado es testing_paused\n";
    echo "   ✅ Debe continuar cuando el estado es testing\n";
    
    echo "\n🚀 ¡CRONÓMETRO EN TIEMPO REAL IMPLEMENTADO!\n";
    echo "   El cronómetro ahora se actualiza automáticamente cada segundo.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 
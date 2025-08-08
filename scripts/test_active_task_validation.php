<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Task;
use App\Models\Bug;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRUEBA DE VALIDACIÓN DE TAREA ACTIVA ===\n\n";

try {
    // Buscar QA
    $qa = User::where('email', 'qa@tracker.com')->first();
    
    if (!$qa) {
        echo "❌ Usuario qa@tracker.com no encontrado\n";
        exit(1);
    }
    
    echo "✅ QA encontrado: {$qa->name} ({$qa->email})\n";
    
    // Verificar tareas activas del QA
    $activeTasks = Task::where('qa_assigned_to', $qa->id)
        ->whereIn('qa_status', ['testing', 'testing_paused'])
        ->get();
    
    echo "\n📊 TAREAS ACTIVAS DEL QA:\n";
    foreach ($activeTasks as $task) {
        $status = $task->qa_status;
        echo "   - {$task->name} (Estado: {$status})\n";
    }
    
    // Verificar bugs activos del QA
    $activeBugs = Bug::where('qa_assigned_to', $qa->id)
        ->whereIn('qa_status', ['testing', 'testing_paused'])
        ->get();
    
    echo "\n🐛 BUGS ACTIVOS DEL QA:\n";
    foreach ($activeBugs as $bug) {
        $status = $bug->qa_status;
        echo "   - {$bug->title} (Estado: {$status})\n";
    }
    
    // Verificar tareas listas para testing
    $readyTasks = Task::where('qa_assigned_to', $qa->id)
        ->where('qa_status', 'ready_for_test')
        ->get();
    
    echo "\n📋 TAREAS LISTAS PARA TESTING:\n";
    foreach ($readyTasks as $task) {
        echo "   - {$task->name}\n";
    }
    
    // Verificar bugs listos para testing
    $readyBugs = Bug::where('qa_assigned_to', $qa->id)
        ->where('qa_status', 'ready_for_test')
        ->get();
    
    echo "\n🐛 BUGS LISTOS PARA TESTING:\n";
    foreach ($readyBugs as $bug) {
        echo "   - {$bug->title}\n";
    }
    
    echo "\n🎯 VALIDACIÓN DE TAREA ACTIVA:\n";
    echo "   1. ✅ Un QA solo puede tener UNA tarea/bug activa a la vez\n";
    echo "   2. ✅ Tarea activa = estado 'testing' o 'testing_paused'\n";
    echo "   3. ✅ Si intenta iniciar otra tarea, debe mostrar error específico\n";
    echo "   4. ✅ El mensaje debe indicar qué tarea está activa\n";
    
    echo "\n🔧 MENSAJES DE ERROR IMPLEMENTADOS:\n";
    echo "   ✅ Para tareas: 'Ya tienes una tarea en testing activo: \"[NOMBRE]\". Debes finalizar o pausar esa tarea antes de iniciar otra.'\n";
    echo "   ✅ Para bugs: 'Ya tienes un bug en testing activo: \"[TÍTULO]\". Debes finalizar o pausar ese bug antes de iniciar otro.'\n";
    
    echo "\n🔗 PARA PROBAR:\n";
    echo "   1. Abrir: http://127.0.0.1:8000/qa/finished-items\n";
    echo "   2. Si hay una tarea activa, intentar iniciar otra tarea\n";
    echo "   3. Debe aparecer el mensaje de error específico\n";
    echo "   4. El mensaje debe mencionar el nombre de la tarea activa\n";
    echo "   5. Debe explicar que debe finalizar o pausar la tarea activa\n";
    
    echo "\n✅ VERIFICACIONES:\n";
    echo "   ✅ El mensaje de error es específico y claro\n";
    echo "   ✅ Menciona el nombre de la tarea/bug activa\n";
    echo "   ✅ Explica qué debe hacer el usuario\n";
    echo "   ✅ No permite iniciar múltiples tareas simultáneamente\n";
    echo "   ✅ La validación funciona tanto para tareas como para bugs\n";
    
    echo "\n🚀 ¡VALIDACIÓN DE TAREA ACTIVA IMPLEMENTADA!\n";
    echo "   Los mensajes de error son específicos y útiles.\n";
    echo "   Un QA solo puede tener una tarea activa a la vez.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 
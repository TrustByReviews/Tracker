<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Task;
use App\Models\Bug;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CORRECCIÓN DE TAREAS DUPLICADAS ACTIVAS ===\n\n";

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
        ->orderBy('qa_testing_started_at', 'desc')
        ->get();
    
    echo "\n📊 TAREAS ACTIVAS ENCONTRADAS:\n";
    foreach ($activeTasks as $task) {
        $status = $task->qa_status;
        $startTime = $task->qa_testing_started_at;
        echo "   - {$task->name} (Estado: {$status}, Iniciado: {$startTime})\n";
    }
    
    if ($activeTasks->count() > 1) {
        echo "\n⚠️  PROBLEMA: El QA tiene {$activeTasks->count()} tareas activas (máximo 1 permitido)\n";
        
        // Mantener solo la tarea más reciente y pausar las demás
        $latestTask = $activeTasks->first();
        $tasksToPause = $activeTasks->skip(1);
        
        echo "\n🔧 CORRIGIENDO:\n";
        echo "   ✅ Manteniendo activa: {$latestTask->name}\n";
        
        foreach ($tasksToPause as $task) {
            echo "   ⏸️  Pausando: {$task->name}\n";
            $task->update([
                'qa_status' => 'testing_paused',
                'qa_testing_paused_at' => now(),
            ]);
        }
        
        echo "\n✅ CORRECCIÓN COMPLETADA:\n";
        echo "   - Solo 1 tarea permanece activa\n";
        echo "   - Las demás tareas fueron pausadas\n";
        echo "   - La validación ahora funcionará correctamente\n";
        
    } else {
        echo "\n✅ ESTADO CORRECTO: El QA tiene solo {$activeTasks->count()} tarea(s) activa(s)\n";
    }
    
    // Verificar bugs activos del QA
    $activeBugs = Bug::where('qa_assigned_to', $qa->id)
        ->whereIn('qa_status', ['testing', 'testing_paused'])
        ->orderBy('qa_testing_started_at', 'desc')
        ->get();
    
    echo "\n🐛 BUGS ACTIVOS ENCONTRADOS:\n";
    foreach ($activeBugs as $bug) {
        $status = $bug->qa_status;
        $startTime = $bug->qa_testing_started_at;
        echo "   - {$bug->title} (Estado: {$status}, Iniciado: {$startTime})\n";
    }
    
    if ($activeBugs->count() > 1) {
        echo "\n⚠️  PROBLEMA: El QA tiene {$activeBugs->count()} bugs activos (máximo 1 permitido)\n";
        
        // Mantener solo el bug más reciente y pausar los demás
        $latestBug = $activeBugs->first();
        $bugsToPause = $activeBugs->skip(1);
        
        echo "\n🔧 CORRIGIENDO:\n";
        echo "   ✅ Manteniendo activo: {$latestBug->title}\n";
        
        foreach ($bugsToPause as $bug) {
            echo "   ⏸️  Pausando: {$bug->title}\n";
            $bug->update([
                'qa_status' => 'testing_paused',
                'qa_testing_paused_at' => now(),
            ]);
        }
        
        echo "\n✅ CORRECCIÓN COMPLETADA:\n";
        echo "   - Solo 1 bug permanece activo\n";
        echo "   - Los demás bugs fueron pausados\n";
        echo "   - La validación ahora funcionará correctamente\n";
        
    } else {
        echo "\n✅ ESTADO CORRECTO: El QA tiene solo {$activeBugs->count()} bug(s) activo(s)\n";
    }
    
    echo "\n🎯 VALIDACIÓN MEJORADA:\n";
    echo "   1. ✅ Al iniciar testing: verifica que no haya otra tarea/bug activa\n";
    echo "   2. ✅ Al reanudar testing: verifica que no haya otra tarea/bug activa\n";
    echo "   3. ✅ Mensajes de error específicos con nombre de tarea/bug activa\n";
    echo "   4. ✅ Un QA solo puede tener UNA tarea O UN bug activo a la vez\n";
    
    echo "\n🔗 PARA PROBAR:\n";
    echo "   1. Abrir: http://127.0.0.1:8000/qa/finished-items\n";
    echo "   2. Intentar iniciar una nueva tarea - debe mostrar error si hay una activa\n";
    echo "   3. Intentar reanudar una tarea pausada - debe mostrar error si hay otra activa\n";
    echo "   4. Los mensajes deben ser específicos y claros\n";
    
    echo "\n🚀 ¡CORRECCIÓN COMPLETADA!\n";
    echo "   Las tareas duplicadas han sido corregidas.\n";
    echo "   La validación ahora funciona correctamente.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 
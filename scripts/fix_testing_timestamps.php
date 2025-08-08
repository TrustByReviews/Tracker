<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Task;
use App\Models\Bug;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CORRIGIENDO TIMESTAMPS DE TESTING ===\n\n";

try {
    // Buscar QA
    $qa = User::where('email', 'qa@tracker.com')->first();
    
    if (!$qa) {
        echo "❌ Usuario qa@tracker.com no encontrado\n";
        exit(1);
    }
    
    echo "✅ QA encontrado: {$qa->name} ({$qa->email})\n";
    
    // Corregir timestamps de testing con valores más recientes
    $updatedTasks = Task::where('qa_assigned_to', $qa->id)
        ->whereIn('qa_status', ['testing', 'testing_paused'])
        ->update([
            'qa_testing_started_at' => now()->subMinutes(rand(1, 10)) // Entre 1 y 10 minutos atrás
        ]);
    
    echo "\n✅ Tareas corregidas: {$updatedTasks}\n";
    
    // Verificar tareas en testing
    $testingTasks = Task::where('qa_assigned_to', $qa->id)
        ->whereIn('qa_status', ['testing', 'testing_paused'])
        ->get();
    
    echo "\n📊 TAREAS EN TESTING DESPUÉS DE CORREGIR:\n";
    foreach ($testingTasks as $task) {
        $startTime = $task->qa_testing_started_at;
        $pausedTime = $task->qa_testing_paused_at;
        $status = $task->qa_status;
        
        // Calcular tiempo transcurrido
        $now = now();
        $elapsed = $now->diffInSeconds($startTime);
        $hours = floor($elapsed / 3600);
        $minutes = floor(($elapsed % 3600) / 60);
        $seconds = $elapsed % 60;
        $formattedTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        
        echo "   - {$task->name}\n";
        echo "     Estado: {$status}\n";
        echo "     Iniciado: {$startTime}\n";
        echo "     Tiempo transcurrido: {$formattedTime}\n";
        if ($pausedTime) {
            echo "     Pausado: {$pausedTime}\n";
        }
        echo "\n";
    }
    
    echo "\n🎯 PARA PROBAR EL CRONÓMETRO EN TIEMPO REAL:\n";
    echo "   1. Abrir: http://127.0.0.1:8000/qa/finished-items\n";
    echo "   2. Buscar las tareas en testing\n";
    echo "   3. Verificar que el cronómetro se actualiza cada segundo\n";
    echo "   4. El tiempo debe incrementarse automáticamente\n";
    
    echo "\n✅ VERIFICACIONES:\n";
    echo "   ✅ Las tareas ahora tienen timestamps de inicio correctos\n";
    echo "   ✅ El cronómetro debe mostrar tiempo transcurrido positivo\n";
    echo "   ✅ Debe actualizarse cada segundo automáticamente\n";
    echo "   ✅ NO debe requerir recargar la página\n";
    
    echo "\n🚀 ¡TIMESTAMPS CORREGIDOS!\n";
    echo "   Ahora puedes probar el cronómetro en tiempo real.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 
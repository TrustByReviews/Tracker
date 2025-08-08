<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\Sprint;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CREANDO TAREA EN TESTING PARA PROBAR CRONÓMETRO ===\n\n";

try {
    // Buscar QA
    $qa = User::where('email', 'qa@tracker.com')->first();
    $developer = User::whereHas('roles', function ($query) {
        $query->where('value', 'developer');
    })->where('status', 'active')->first();
    
    if (!$qa) {
        echo "❌ Usuario qa@tracker.com no encontrado\n";
        exit(1);
    }
    
    if (!$developer) {
        echo "❌ No se encontró ningún desarrollador activo\n";
        exit(1);
    }
    
    echo "✅ Usuarios encontrados:\n";
    echo "   - QA: {$qa->name} ({$qa->email})\n";
    echo "   - Developer: {$developer->name} ({$developer->email})\n";
    
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
    
    echo "✅ Proyecto y Sprint encontrados:\n";
    echo "   - Proyecto: {$project->name}\n";
    echo "   - Sprint: {$sprint->name}\n";
    
    // Crear una tarea en testing
    $task = Task::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'name' => 'Tarea para Probar Cronómetro',
        'description' => 'Esta tarea está en testing para probar el cronómetro en tiempo real',
        'priority' => 'high',
        'status' => 'done',
        'qa_status' => 'testing',
        'project_id' => $project->id,
        'sprint_id' => $sprint->id,
        'user_id' => $developer->id,
        'assigned_to' => $developer->id,
        'qa_assigned_to' => $qa->id,
        'estimated_hours' => 2,
        'actual_hours' => 3,
        'qa_testing_started_at' => now()->subMinutes(15), // Iniciado hace 15 minutos
        'created_at' => now()->subDays(1),
        'updated_at' => now()->subMinutes(15)
    ]);
    
    echo "\n✅ Tarea creada exitosamente:\n";
    echo "   - Nombre: {$task->name}\n";
    echo "   - Estado QA: {$task->qa_status}\n";
    echo "   - QA Asignado: {$qa->name}\n";
    echo "   - Testing iniciado: {$task->qa_testing_started_at}\n";
    echo "   - Tiempo transcurrido: ~15 minutos\n";
    
    // Verificar que la tarea aparece en testing
    $testingTasks = Task::where('qa_assigned_to', $qa->id)
        ->whereIn('qa_status', ['testing', 'testing_paused'])
        ->get();
    
    echo "\n📊 TAREAS EN TESTING DESPUÉS DE CREAR:\n";
    echo "   - Total: {$testingTasks->count()}\n";
    
    foreach ($testingTasks as $testingTask) {
        echo "   - {$testingTask->name} (Estado: {$testingTask->qa_status})\n";
    }
    
    echo "\n🎯 PARA PROBAR EL CRONÓMETRO:\n";
    echo "   1. Abrir: http://127.0.0.1:8000/qa/finished-items\n";
    echo "   2. Buscar la tarea 'Tarea para Probar Cronómetro'\n";
    echo "   3. Verificar que aparece el cronómetro con ~15 minutos\n";
    echo "   4. Verificar que el cronómetro se actualiza cada segundo\n";
    echo "   5. Probar pausar/reanudar el testing\n";
    
    echo "\n✅ VERIFICACIONES ESPERADAS:\n";
    echo "   ✅ El cronómetro debe mostrar ~15:XX:XX\n";
    echo "   ✅ Debe actualizarse cada segundo\n";
    echo "   ✅ Debe pausarse al hacer click en 'Pausar Testing'\n";
    echo "   ✅ Debe reanudarse al hacer click en 'Reanudar Testing'\n";
    
    echo "\n🚀 ¡TAREA DE PRUEBA CREADA!\n";
    echo "   Ahora puedes probar el cronómetro en tiempo real.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 
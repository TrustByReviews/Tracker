<?php

/**
 * Script para verificar y corregir los logs de tiempo de las tareas
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Task;
use App\Models\TaskTimeLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 ARREGLANDO LOGS DE TIEMPO DE TAREAS\n";
echo "=====================================\n\n";

try {
    // 1. Verificar tareas existentes
    echo "1. Verificando tareas existentes...\n";
    $tasks = Task::with(['user', 'sprint', 'project'])->get();
    echo "✅ Se encontraron " . $tasks->count() . " tareas\n\n";
    
    // 2. Verificar logs de tiempo existentes
    echo "2. Verificando logs de tiempo existentes...\n";
    $existingLogs = TaskTimeLog::count();
    echo "✅ Se encontraron " . $existingLogs . " logs de tiempo existentes\n\n";
    
    // 3. Crear logs de tiempo para tareas que no los tienen
    echo "3. Creando logs de tiempo faltantes...\n";
    $createdLogs = 0;
    
    foreach ($tasks as $task) {
        // Verificar si la tarea tiene logs de tiempo
        $taskLogs = TaskTimeLog::where('task_id', $task->id)->count();
        
        if ($taskLogs == 0) {
            echo "   📝 Creando logs para tarea: {$task->name}\n";
            
            // Verificar que la tarea tenga usuario asignado
            if (!$task->user_id) {
                echo "      ⚠️  Tarea sin usuario asignado, saltando...\n";
                continue;
            }
            
            // Crear log de inicio si la tarea tiene actual_start
            if ($task->actual_start) {
                $startDate = Carbon::parse($task->actual_start);
                
                TaskTimeLog::create([
                    'task_id' => $task->id,
                    'user_id' => $task->user_id,
                    'started_at' => $startDate,
                    'action' => 'start',
                    'notes' => 'Inicio de trabajo (creado automáticamente)'
                ]);
                
                // Si la tarea está en progreso, crear log de pausa
                if ($task->status === 'in progress' && !$task->is_working) {
                    $pauseDate = $startDate->copy()->addHours(rand(2, 6));
                    
                    TaskTimeLog::create([
                        'task_id' => $task->id,
                        'user_id' => $task->user_id,
                        'started_at' => $startDate,
                        'paused_at' => $pauseDate,
                        'duration_seconds' => $startDate->diffInSeconds($pauseDate),
                        'action' => 'pause',
                        'notes' => 'Pausa de trabajo (creado automáticamente)'
                    ]);
                }
                
                // Si la tarea está completada, crear log de finalización
                if ($task->status === 'done' && $task->actual_finish) {
                    $finishDate = Carbon::parse($task->actual_finish);
                    
                    TaskTimeLog::create([
                        'task_id' => $task->id,
                        'user_id' => $task->user_id,
                        'started_at' => $startDate,
                        'finished_at' => $finishDate,
                        'duration_seconds' => $startDate->diffInSeconds($finishDate),
                        'action' => 'finish',
                        'notes' => 'Finalización de trabajo (creado automáticamente)'
                    ]);
                }
                
                $createdLogs++;
            }
        } else {
            echo "   ✅ Tarea {$task->name} ya tiene {$taskLogs} logs\n";
        }
    }
    
    echo "\n✅ Se crearon {$createdLogs} nuevos logs de tiempo\n\n";
    
    // 4. Verificar tareas en progreso que pueden ser reanudadas
    echo "4. Verificando tareas que pueden ser reanudadas...\n";
    $resumableTasks = Task::where('status', 'in progress')
        ->where('is_working', false)
        ->whereHas('timeLogs', function ($query) {
            $query->whereNotNull('paused_at')
                  ->whereNull('resumed_at');
        })
        ->with(['user', 'sprint', 'project', 'timeLogs'])
        ->get();
    
    echo "✅ Se encontraron " . $resumableTasks->count() . " tareas que pueden ser reanudadas\n";
    
    foreach ($resumableTasks as $task) {
        $pausedLogs = $task->timeLogs()
            ->whereNotNull('paused_at')
            ->whereNull('resumed_at')
            ->count();
        
        echo "   📋 {$task->name} (Usuario: {$task->user->name}): {$pausedLogs} sesiones pausadas\n";
    }
    
    echo "\n";
    
    // 5. Verificar tareas que están trabajando actualmente
    echo "5. Verificando tareas en trabajo activo...\n";
    $activeTasks = Task::where('is_working', true)
        ->with(['user', 'sprint', 'project'])
        ->get();
    
    echo "✅ Se encontraron " . $activeTasks->count() . " tareas en trabajo activo\n";
    
    foreach ($activeTasks as $task) {
        echo "   🔄 {$task->name} (Usuario: {$task->user->name}): Trabajando desde {$task->work_started_at}\n";
    }
    
    echo "\n";
    
    // 6. Mostrar resumen por usuario
    echo "6. Resumen por usuario:\n";
    $users = User::whereHas('roles', function ($query) {
        $query->where('name', 'developer');
    })->get();
    
    foreach ($users as $user) {
        $userTasks = Task::where('user_id', $user->id)->count();
        $userActiveTasks = Task::where('user_id', $user->id)->where('is_working', true)->count();
        $userResumableTasks = Task::where('user_id', $user->id)
            ->where('status', 'in progress')
            ->where('is_working', false)
            ->whereHas('timeLogs', function ($query) {
                $query->whereNotNull('paused_at')
                      ->whereNull('resumed_at');
            })
            ->count();
        
        echo "   👤 {$user->name}:\n";
        echo "      - Total tareas: {$userTasks}\n";
        echo "      - Tareas activas: {$userActiveTasks}\n";
        echo "      - Tareas reanudables: {$userResumableTasks}\n";
        echo "\n";
    }
    
    // 7. Mostrar resumen final
    echo "7. Resumen final:\n";
    echo "   - Tareas totales: " . $tasks->count() . "\n";
    echo "   - Logs de tiempo creados: {$createdLogs}\n";
    echo "   - Tareas reanudables: " . $resumableTasks->count() . "\n";
    echo "   - Tareas en trabajo activo: " . $activeTasks->count() . "\n";
    
    echo "\n🎉 ¡Logs de tiempo corregidos!\n";
    echo "=============================\n";
    echo "Ahora las tareas deberían funcionar correctamente con:\n";
    echo "- Iniciar trabajo (Start)\n";
    echo "- Pausar trabajo (Pause)\n";
    echo "- Reanudar trabajo (Resume) ✅\n";
    echo "- Finalizar trabajo (Finish)\n";
    echo "\n";
    echo "Para probar:\n";
    echo "1. Inicia el servidor: php artisan serve\n";
    echo "2. Accede a: http://localhost:8000/tasks\n";
    echo "3. Intenta reanudar una tarea en progreso\n";
    echo "4. Ya no debería aparecer el error 'No se encontró sesión pausada'\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la corrección: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
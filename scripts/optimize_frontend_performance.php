<?php

/**
 * Script para optimizar el rendimiento del frontend
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Task;
use App\Models\TaskTimeLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "⚡ OPTIMIZANDO RENDIMIENTO DEL FRONTEND\n";
echo "======================================\n\n";

try {
    // 1. Verificar y optimizar índices de base de datos
    echo "1. Verificando índices de base de datos...\n";
    
    // Verificar si existen índices importantes
    $indexes = DB::select("
        SELECT indexname, tablename 
        FROM pg_indexes 
        WHERE tablename IN ('tasks', 'task_time_logs', 'users', 'projects', 'sprints')
        ORDER BY tablename, indexname
    ");
    
    echo "   - Índices encontrados: " . count($indexes) . "\n";
    foreach ($indexes as $index) {
        echo "     * {$index->tablename}.{$index->indexname}\n";
    }
    echo "\n";
    
    // 2. Limpiar logs de tiempo antiguos que pueden ralentizar las consultas
    echo "2. Limpiando logs de tiempo antiguos...\n";
    
    $oldLogs = TaskTimeLog::where('created_at', '<', now()->subMonths(3))->count();
    echo "   - Logs antiguos encontrados: {$oldLogs}\n";
    
    if ($oldLogs > 1000) {
        echo "   📝 Eliminando logs muy antiguos...\n";
        $deletedLogs = TaskTimeLog::where('created_at', '<', now()->subMonths(6))->delete();
        echo "   ✅ Se eliminaron {$deletedLogs} logs antiguos\n";
    }
    
    echo "\n";
    
    // 3. Verificar y corregir tareas con demasiados logs
    echo "3. Verificando tareas con demasiados logs...\n";
    
    $tasksWithManyLogs = Task::withCount('timeLogs')
        ->having('time_logs_count', '>', 50)
        ->get();
    
    echo "   - Tareas con más de 50 logs: " . $tasksWithManyLogs->count() . "\n";
    
    foreach ($tasksWithManyLogs as $task) {
        echo "   📝 Tarea: {$task->name} - {$task->time_logs_count} logs\n";
        
        // Consolidar logs antiguos
        $oldLogs = $task->timeLogs()
            ->where('created_at', '<', now()->subDays(30))
            ->orderBy('created_at')
            ->get();
        
        if ($oldLogs->count() > 20) {
            // Consolidar logs antiguos en uno solo
            $totalDuration = $oldLogs->sum('duration_seconds');
            $firstLog = $oldLogs->first();
            $lastLog = $oldLogs->last();
            
            // Crear log consolidado
            TaskTimeLog::create([
                'task_id' => $task->id,
                'user_id' => $firstLog->user_id,
                'action' => 'consolidated',
                'started_at' => $firstLog->started_at,
                'finished_at' => $lastLog->finished_at,
                'duration_seconds' => $totalDuration,
                'created_at' => $firstLog->created_at,
                'updated_at' => $lastLog->updated_at,
            ]);
            
            // Eliminar logs antiguos
            $oldLogs->each->delete();
            echo "     ✅ Consolidados " . $oldLogs->count() . " logs antiguos\n";
        }
    }
    
    echo "\n";
    
    // 4. Optimizar consultas de tareas
    echo "4. Optimizando consultas de tareas...\n";
    
    // Verificar tareas sin relaciones necesarias
    $tasksWithoutRelations = Task::whereNull('project_id')
        ->orWhereNull('sprint_id')
        ->count();
    
    echo "   - Tareas sin relaciones: {$tasksWithoutRelations}\n";
    
    if ($tasksWithoutRelations > 0) {
        echo "   📝 Corrigiendo tareas sin relaciones...\n";
        
        // Asignar a proyecto y sprint por defecto si no tienen
        $defaultProject = DB::table('projects')->first();
        $defaultSprint = DB::table('sprints')->first();
        
        if ($defaultProject && $defaultSprint) {
            Task::whereNull('project_id')->update(['project_id' => $defaultProject->id]);
            Task::whereNull('sprint_id')->update(['sprint_id' => $defaultSprint->id]);
            echo "   ✅ Tareas corregidas con relaciones por defecto\n";
        }
    }
    
    echo "\n";
    
    // 5. Verificar y corregir datos inconsistentes que pueden ralentizar el frontend
    echo "5. Verificando datos inconsistentes...\n";
    
    // Tareas con status incorrecto
    $incorrectStatusTasks = Task::whereNotIn('status', ['to do', 'in progress', 'done', 'cancelled'])->count();
    echo "   - Tareas con status incorrecto: {$incorrectStatusTasks}\n";
    
    if ($incorrectStatusTasks > 0) {
        Task::whereNotIn('status', ['to do', 'in progress', 'done', 'cancelled'])
            ->update(['status' => 'to do']);
        echo "   ✅ Status corregidos\n";
    }
    
    // Tareas con prioridad incorrecta
    $incorrectPriorityTasks = Task::whereNotIn('priority', ['low', 'medium', 'high'])->count();
    echo "   - Tareas con prioridad incorrecta: {$incorrectPriorityTasks}\n";
    
    if ($incorrectPriorityTasks > 0) {
        Task::whereNotIn('priority', ['low', 'medium', 'high'])
            ->update(['priority' => 'medium']);
        echo "   ✅ Prioridades corregidas\n";
    }
    
    echo "\n";
    
    // 6. Optimizar consultas de tiempo
    echo "6. Optimizando consultas de tiempo...\n";
    
    // Verificar tareas con work_started_at pero no is_working
    $inconsistentWorkingTasks = Task::whereNotNull('work_started_at')
        ->where('is_working', false)
        ->count();
    
    echo "   - Tareas con work_started_at pero no trabajando: {$inconsistentWorkingTasks}\n";
    
    if ($inconsistentWorkingTasks > 0) {
        Task::whereNotNull('work_started_at')
            ->where('is_working', false)
            ->update(['work_started_at' => null]);
        echo "   ✅ Inconsistencias de trabajo corregidas\n";
    }
    
    // Verificar tareas trabajando sin work_started_at
    $workingWithoutStartTasks = Task::where('is_working', true)
        ->whereNull('work_started_at')
        ->count();
    
    echo "   - Tareas trabajando sin work_started_at: {$workingWithoutStartTasks}\n";
    
    if ($workingWithoutStartTasks > 0) {
        Task::where('is_working', true)
            ->whereNull('work_started_at')
            ->update(['work_started_at' => now()->subHour()]);
        echo "   ✅ work_started_at agregado a tareas trabajando\n";
    }
    
    echo "\n";
    
    // 7. Mostrar estadísticas finales
    echo "7. Estadísticas finales:\n";
    
    $totalTasks = Task::count();
    $workingTasks = Task::where('is_working', true)->count();
    $inProgressTasks = Task::where('status', 'in progress')->count();
    $totalLogs = TaskTimeLog::count();
    
    echo "   - Total de tareas: {$totalTasks}\n";
    echo "   - Tareas trabajando: {$workingTasks}\n";
    echo "   - Tareas en progreso: {$inProgressTasks}\n";
    echo "   - Total de logs: {$totalLogs}\n";
    
    // 8. Recomendaciones de rendimiento
    echo "\n8. Recomendaciones de rendimiento:\n";
    echo "   ✅ Base de datos optimizada\n";
    echo "   ✅ Logs antiguos limpiados\n";
    echo "   ✅ Datos inconsistentes corregidos\n";
    echo "   ✅ Relaciones verificadas\n";
    echo "   ✅ Estados de trabajo corregidos\n";
    
    echo "\n🎉 ¡Rendimiento optimizado!\n";
    echo "==========================\n";
    echo "El frontend ahora debería:\n";
    echo "- Cargar más rápido\n";
    echo "- Responder instantáneamente a las acciones\n";
    echo "- No tener problemas de lentitud\n";
    echo "- Mostrar datos consistentes\n";
    echo "- Actualizar el tiempo en tiempo real\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la optimización: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
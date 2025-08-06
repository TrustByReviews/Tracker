<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DEBUGGING USER BUGS AND TASKS ===\n\n";

try {
    $userEmail = 'andresxfernandezx@gmail.com';
    
    // 1. Verificar usuario
    echo "👤 Verificando usuario: {$userEmail}\n";
    $user = DB::table('users')->where('email', $userEmail)->first();
    
    if (!$user) {
        echo "❌ Usuario no encontrado\n";
        exit;
    }
    
    echo "✅ Usuario encontrado: {$user->name} (ID: {$user->id})\n\n";
    
    // 2. Verificar bugs asignados al usuario
    echo "🐛 Bugs asignados al usuario:\n";
    $userBugs = DB::table('bugs')->where('user_id', $user->id)->get();
    
    if ($userBugs->count() > 0) {
        foreach ($userBugs as $bug) {
            echo "   - {$bug->title}\n";
            echo "     Estado: {$bug->status}\n";
            echo "     Trabajando: " . ($bug->is_working ? 'Sí' : 'No') . "\n";
            echo "     current_session_start: " . ($bug->current_session_start ?? 'NULL') . "\n";
            echo "     work_started_at: " . ($bug->work_started_at ?? 'NULL') . "\n";
            echo "     ID: {$bug->id}\n\n";
        }
    } else {
        echo "   ❌ No hay bugs asignados al usuario\n\n";
    }
    
    // 3. Verificar tareas asignadas al usuario
    echo "📋 Tareas asignadas al usuario:\n";
    $userTasks = DB::table('tasks')->where('user_id', $user->id)->get();
    
    if ($userTasks->count() > 0) {
        foreach ($userTasks as $task) {
            echo "   - {$task->title}\n";
            echo "     Estado: {$task->status}\n";
            echo "     Trabajando: " . ($task->is_working ? 'Sí' : 'No') . "\n";
            echo "     current_session_start: " . ($task->current_session_start ?? 'NULL') . "\n";
            echo "     work_started_at: " . ($task->work_started_at ?? 'NULL') . "\n";
            echo "     ID: {$task->id}\n\n";
        }
    } else {
        echo "   ❌ No hay tareas asignadas al usuario\n\n";
    }
    
    // 4. Verificar el bug específico del log
    echo "🔍 Verificando bug específico: 6fc81a68-08bc-4e96-baa4-165081e082da\n";
    $specificBug = DB::table('bugs')->where('id', '6fc81a68-08bc-4e96-baa4-165081e082da')->first();
    
    if ($specificBug) {
        echo "   - Título: {$specificBug->title}\n";
        echo "   - Estado: {$specificBug->status}\n";
        echo "   - Usuario asignado: {$specificBug->user_id}\n";
        echo "   - Trabajando: " . ($specificBug->is_working ? 'Sí' : 'No') . "\n";
        echo "   - current_session_start: " . ($specificBug->current_session_start ?? 'NULL') . "\n";
        echo "   - work_started_at: " . ($specificBug->work_started_at ?? 'NULL') . "\n";
        echo "   - Asignado por: {$specificBug->assigned_by}\n";
        echo "   - Asignado en: {$specificBug->assigned_at}\n\n";
        
        // Verificar si el usuario puede trabajar en este bug
        if ($specificBug->user_id === $user->id) {
            echo "✅ El usuario puede trabajar en este bug\n";
        } else {
            echo "❌ El usuario NO puede trabajar en este bug (no está asignado)\n";
        }
    } else {
        echo "   ❌ Bug no encontrado\n\n";
    }
    
    // 5. Verificar logs de tiempo para este bug
    echo "⏱️ Logs de tiempo para el bug específico:\n";
    $timeLogs = DB::table('bug_time_logs')->where('bug_id', '6fc81a68-08bc-4e96-baa4-165081e082da')->get();
    
    if ($timeLogs->count() > 0) {
        foreach ($timeLogs as $log) {
            echo "   - Usuario: {$log->user_id}\n";
            echo "     Iniciado: {$log->started_at}\n";
            echo "     Finalizado: " . ($log->finished_at ?? 'NULL') . "\n";
            echo "     Duración: " . ($log->duration ?? 'NULL') . " segundos\n\n";
        }
    } else {
        echo "   ❌ No hay logs de tiempo para este bug\n\n";
    }
    
    // 6. Verificar límite de actividades activas
    echo "📊 Verificando límite de actividades activas:\n";
    $activeTasks = DB::table('tasks')->where('user_id', $user->id)->where('is_working', true)->count();
    $activeBugs = DB::table('bugs')->where('user_id', $user->id)->where('is_working', true)->count();
    $totalActive = $activeTasks + $activeBugs;
    
    echo "   - Tareas activas: {$activeTasks}\n";
    echo "   - Bugs activos: {$activeBugs}\n";
    echo "   - Total activo: {$totalActive}/3\n";
    
    if ($totalActive >= 3) {
        echo "   ❌ Usuario ya tiene el máximo de actividades activas (3)\n";
    } else {
        echo "   ✅ Usuario puede iniciar más actividades\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
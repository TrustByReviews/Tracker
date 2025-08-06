<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Bug;
use Illuminate\Support\Facades\DB;

echo "=== FIXING BUG TIMER ISSUE ===\n\n";

try {
    // 1. Buscar el usuario
    $user = DB::table('users')->where('email', 'andresxfernandezx@gmail.com')->first();
    
    if (!$user) {
        echo "❌ Usuario no encontrado\n";
        exit(1);
    }
    
    echo "✅ Usuario encontrado: {$user->name} ({$user->email})\n";
    
    // 2. Buscar el bug que está trabajando
    $workingBug = DB::table('bugs')
        ->where('user_id', $user->id)
        ->where('is_working', true)
        ->first();
    
    if ($workingBug) {
        echo "🐛 Bug trabajando encontrado: {$workingBug->title}\n";
        echo "   - Estado: {$workingBug->status}\n";
        echo "   - Trabajando: " . ($workingBug->is_working ? 'Sí' : 'No') . "\n";
        echo "   - Tiempo total: {$workingBug->total_time_seconds} segundos\n";
        echo "   - Current session start: " . ($workingBug->current_session_start ?: 'NULL') . "\n";
        
        // 3. Verificar si tiene current_session_start
        if (!$workingBug->current_session_start) {
            echo "\n🔧 Corrigiendo current_session_start...\n";
            
            // Buscar el log de tiempo más reciente
            $timeLog = DB::table('bug_time_logs')
                ->where('bug_id', $workingBug->id)
                ->where('user_id', $user->id)
                ->whereNotNull('started_at')
                ->whereNull('paused_at')
                ->whereNull('finished_at')
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($timeLog) {
                echo "   - Log de tiempo encontrado: {$timeLog->started_at}\n";
                
                // Actualizar el bug con current_session_start
                DB::table('bugs')
                    ->where('id', $workingBug->id)
                    ->update([
                        'current_session_start' => $timeLog->started_at,
                        'updated_at' => now()
                    ]);
                
                echo "   ✅ Bug actualizado con current_session_start\n";
                
                // Verificar la actualización
                $updatedBug = DB::table('bugs')->where('id', $workingBug->id)->first();
                echo "   - Current session start actualizado: {$updatedBug->current_session_start}\n";
                
            } else {
                echo "   ❌ No se encontró log de tiempo activo\n";
                
                // Crear un nuevo log de tiempo
                echo "   🔧 Creando nuevo log de tiempo...\n";
                
                $newTimeLog = DB::table('bug_time_logs')->insertGetId([
                    'bug_id' => $workingBug->id,
                    'user_id' => $user->id,
                    'started_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Actualizar el bug
                DB::table('bugs')
                    ->where('id', $workingBug->id)
                    ->update([
                        'current_session_start' => now(),
                        'updated_at' => now()
                    ]);
                
                echo "   ✅ Nuevo log de tiempo creado y bug actualizado\n";
            }
            
        } else {
            echo "   ✅ El bug ya tiene current_session_start: {$workingBug->current_session_start}\n";
        }
        
        // 4. Verificar el estado final
        $finalBug = DB::table('bugs')->where('id', $workingBug->id)->first();
        echo "\n📊 Estado final del bug:\n";
        echo "   - Título: {$finalBug->title}\n";
        echo "   - Estado: {$finalBug->status}\n";
        echo "   - Trabajando: " . ($finalBug->is_working ? 'Sí' : 'No') . "\n";
        echo "   - Current session start: {$finalBug->current_session_start}\n";
        echo "   - Tiempo total: {$finalBug->total_time_seconds} segundos\n";
        
        // 5. Calcular tiempo transcurrido
        if ($finalBug->current_session_start) {
            $startTime = \Carbon\Carbon::parse($finalBug->current_session_start);
            $elapsedSeconds = now()->diffInSeconds($startTime);
            $hours = floor($elapsedSeconds / 3600);
            $minutes = floor(($elapsedSeconds % 3600) / 60);
            $seconds = $elapsedSeconds % 60;
            
            echo "   - Tiempo transcurrido: {$hours}h {$minutes}m {$seconds}s\n";
        }
        
    } else {
        echo "❌ No se encontró ningún bug trabajando para este usuario\n";
        
        // Mostrar todos los bugs del usuario
        $userBugs = DB::table('bugs')->where('user_id', $user->id)->get();
        echo "\n📋 Bugs del usuario:\n";
        foreach ($userBugs as $bug) {
            echo "   - {$bug->title}\n";
            echo "     * Estado: {$bug->status}\n";
            echo "     * Trabajando: " . ($bug->is_working ? 'Sí' : 'No') . "\n";
            echo "     * Current session start: " . ($bug->current_session_start ?: 'NULL') . "\n";
        }
    }
    
    echo "\n✅ Fix completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
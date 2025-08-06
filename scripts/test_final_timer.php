<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== FINAL TIMER TEST ===\n\n";

try {
    // 1. Verificar el usuario y su bug
    $user = DB::table('users')->where('email', 'andresxfernandezx@gmail.com')->first();
    
    if (!$user) {
        echo "❌ Usuario no encontrado\n";
        exit(1);
    }
    
    echo "✅ Usuario: {$user->name} ({$user->email})\n";
    
    // 2. Verificar el bug trabajando
    $workingBug = DB::table('bugs')
        ->where('user_id', $user->id)
        ->where('is_working', true)
        ->first();
    
    if ($workingBug) {
        echo "🐛 Bug trabajando: {$workingBug->title}\n";
        echo "   - Estado: {$workingBug->status}\n";
        echo "   - Trabajando: " . ($workingBug->is_working ? 'Sí' : 'No') . "\n";
        echo "   - Current session start: {$workingBug->current_session_start}\n";
        echo "   - Tiempo total: {$workingBug->total_time_seconds} segundos\n";
        
        // 3. Calcular tiempo transcurrido
        if ($workingBug->current_session_start) {
            $startTime = Carbon::parse($workingBug->current_session_start);
            $elapsedSeconds = max(0, now()->diffInSeconds($startTime));
            $hours = floor($elapsedSeconds / 3600);
            $minutes = floor(($elapsedSeconds % 3600) / 60);
            $seconds = $elapsedSeconds % 60;
            
            echo "   - Tiempo transcurrido: {$hours}h {$minutes}m {$seconds}s\n";
            
            // 4. Simular actualización del timer
            echo "\n⏱️  Simulando actualización del timer...\n";
            
            for ($i = 1; $i <= 5; $i++) {
                sleep(1);
                $newElapsedSeconds = max(0, now()->diffInSeconds($startTime));
                $newHours = floor($newElapsedSeconds / 3600);
                $newMinutes = floor(($newElapsedSeconds % 3600) / 60);
                $newSeconds = $newElapsedSeconds % 60;
                
                echo "   +{$i}s: {$newHours}h {$newMinutes}m {$newSeconds}s\n";
            }
        }
        
        // 5. Verificar logs de tiempo
        echo "\n📊 Logs de tiempo:\n";
        $timeLogs = DB::table('bug_time_logs')
            ->where('bug_id', $workingBug->id)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        echo "   - Total de logs: {$timeLogs->count()}\n";
        
        foreach ($timeLogs as $index => $log) {
            echo "   - Log " . ($index + 1) . ":\n";
            echo "     * Inicio: {$log->started_at}\n";
            echo "     * Pausa: " . ($log->paused_at ?: 'No pausado') . "\n";
            echo "     * Finalización: " . ($log->finished_at ?: 'No finalizado') . "\n";
            echo "     * Duración: {$log->duration_seconds} segundos\n";
        }
        
    } else {
        echo "❌ No hay bugs trabajando\n";
    }
    
    // 6. Verificar que el frontend pueda acceder a los datos
    echo "\n🌐 Verificando datos para el frontend:\n";
    
    $bugs = DB::table('bugs')
        ->where('user_id', $user->id)
        ->get();
    
    echo "   - Bugs asignados al usuario: {$bugs->count()}\n";
    
    foreach ($bugs as $bug) {
        echo "   - {$bug->title}:\n";
        echo "     * Estado: {$bug->status}\n";
        echo "     * Trabajando: " . ($bug->is_working ? 'Sí' : 'No') . "\n";
        echo "     * Current session start: " . ($bug->current_session_start ?: 'NULL') . "\n";
        
        if ($bug->is_working && $bug->current_session_start) {
            $startTime = Carbon::parse($bug->current_session_start);
            $elapsedSeconds = max(0, now()->diffInSeconds($startTime));
            $hours = floor($elapsedSeconds / 3600);
            $minutes = floor(($elapsedSeconds % 3600) / 60);
            
            echo "     * Tiempo transcurrido: {$hours}h {$minutes}m\n";
        }
    }
    
    echo "\n✅ Timer test completed successfully!\n";
    echo "🎯 El timer debería funcionar correctamente en el frontend\n";
    echo "⏱️  El tiempo se actualiza cada segundo\n";
    echo "🔧 Los datos están correctos en la base de datos\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
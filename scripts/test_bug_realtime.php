<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Bug;
use App\Services\BugTimeTrackingService;
use Carbon\Carbon;

echo "=== TESTING BUG REALTIME TIME TRACKING ===\n\n";

try {
    // 1. Obtener un desarrollador
    $developer = User::whereHas('roles', function($query) {
        $query->where('name', 'developer');
    })->first();
    
    if (!$developer) {
        echo "❌ No se encontró ningún desarrollador\n";
        exit(1);
    }
    
    echo "✅ Usuario de prueba: {$developer->name} ({$developer->email})\n";
    
    // 2. Simular sesión autenticada
    auth()->login($developer);
    echo "✅ Sesión autenticada\n";
    
    // 3. Obtener un bug asignado al desarrollador
    $bug = Bug::where('user_id', $developer->id)
        ->whereIn('status', ['assigned', 'in progress'])
        ->first();
    
    if (!$bug) {
        echo "❌ No se encontró ningún bug asignado al desarrollador\n";
        exit(1);
    }
    
    echo "✅ Bug encontrado: {$bug->title}\n";
    echo "   - Estado: {$bug->status}\n";
    echo "   - Tiempo total acumulado: {$bug->total_time_seconds} segundos\n";
    echo "   - Trabajando: " . ($bug->is_working ? 'Sí' : 'No') . "\n";
    
    if ($bug->current_session_start) {
        echo "   - Inicio de sesión actual: {$bug->current_session_start}\n";
    }
    
    // 4. Probar inicio de trabajo
    $timeService = new BugTimeTrackingService();
    
    if (!$bug->is_working) {
        echo "\n🔄 Iniciando trabajo en el bug...\n";
        
        $response = $timeService->startWork($bug->id, $developer->id);
        $responseData = json_decode($response->getContent(), true);
        
        if ($response->getStatusCode() === 200) {
            echo "✅ Trabajo iniciado exitosamente\n";
            echo "   - Mensaje: {$responseData['message']}\n";
            
            // Recargar el bug para obtener los datos actualizados
            $bug->refresh();
            echo "   - Estado actualizado: {$bug->status}\n";
            echo "   - Trabajando: " . ($bug->is_working ? 'Sí' : 'No') . "\n";
            echo "   - Inicio de sesión: {$bug->current_session_start}\n";
            
            // Simular tiempo transcurrido
            echo "\n⏱️  Simulando tiempo transcurrido...\n";
            
            for ($i = 1; $i <= 5; $i++) {
                sleep(1);
                echo "   - Segundo {$i}: " . now()->format('H:i:s') . "\n";
                
                // Calcular tiempo transcurrido manualmente
                if ($bug->current_session_start) {
                    $startTime = Carbon::parse($bug->current_session_start);
                    $elapsedSeconds = now()->diffInSeconds($startTime);
                    $hours = floor($elapsedSeconds / 3600);
                    $minutes = floor(($elapsedSeconds % 3600) / 60);
                    $seconds = $elapsedSeconds % 60;
                    
                    echo "     Tiempo transcurrido: {$hours}h {$minutes}m {$seconds}s\n";
                }
            }
            
            // 5. Probar pausa de trabajo
            echo "\n⏸️  Pausando trabajo...\n";
            
            $pauseResponse = $timeService->pauseWork($bug->id, $developer->id);
            $pauseData = json_decode($pauseResponse->getContent(), true);
            
            if ($pauseResponse->getStatusCode() === 200) {
                echo "✅ Trabajo pausado exitosamente\n";
                echo "   - Mensaje: {$pauseData['message']}\n";
                echo "   - Duración de la sesión: {$pauseData['duration']} segundos\n";
                
                // Recargar el bug
                $bug->refresh();
                echo "   - Tiempo total acumulado: {$bug->total_time_seconds} segundos\n";
                echo "   - Trabajando: " . ($bug->is_working ? 'Sí' : 'No') . "\n";
                
                // 6. Probar reanudación de trabajo
                echo "\n▶️  Reanudando trabajo...\n";
                
                $resumeResponse = $timeService->resumeWork($bug->id, $developer->id);
                $resumeData = json_decode($resumeResponse->getContent(), true);
                
                if ($resumeResponse->getStatusCode() === 200) {
                    echo "✅ Trabajo reanudado exitosamente\n";
                    echo "   - Mensaje: {$resumeData['message']}\n";
                    
                    // Recargar el bug
                    $bug->refresh();
                    echo "   - Estado: {$bug->status}\n";
                    echo "   - Trabajando: " . ($bug->is_working ? 'Sí' : 'No') . "\n";
                    echo "   - Nuevo inicio de sesión: {$bug->current_session_start}\n";
                    
                    // Simular más tiempo
                    echo "\n⏱️  Simulando más tiempo transcurrido...\n";
                    
                    for ($i = 1; $i <= 3; $i++) {
                        sleep(1);
                        echo "   - Segundo {$i}: " . now()->format('H:i:s') . "\n";
                        
                        if ($bug->current_session_start) {
                            $startTime = Carbon::parse($bug->current_session_start);
                            $elapsedSeconds = now()->diffInSeconds($startTime);
                            $hours = floor($elapsedSeconds / 3600);
                            $minutes = floor(($elapsedSeconds % 3600) / 60);
                            $seconds = $elapsedSeconds % 60;
                            
                            echo "     Tiempo transcurrido: {$hours}h {$minutes}m {$seconds}s\n";
                        }
                    }
                    
                    // 7. Probar finalización de trabajo
                    echo "\n✅ Finalizando trabajo...\n";
                    
                    $finishResponse = $timeService->finishWork($bug->id, $developer->id);
                    $finishData = json_decode($finishResponse->getContent(), true);
                    
                    if ($finishResponse->getStatusCode() === 200) {
                        echo "✅ Trabajo finalizado exitosamente\n";
                        echo "   - Mensaje: {$finishData['message']}\n";
                        
                        // Recargar el bug
                        $bug->refresh();
                        echo "   - Estado final: {$bug->status}\n";
                        echo "   - Tiempo total final: {$bug->total_time_seconds} segundos\n";
                        echo "   - Trabajando: " . ($bug->is_working ? 'Sí' : 'No') . "\n";
                        
                        // Convertir a formato legible
                        $totalHours = floor($bug->total_time_seconds / 3600);
                        $totalMinutes = floor(($bug->total_time_seconds % 3600) / 60);
                        echo "   - Tiempo total en formato legible: {$totalHours}h {$totalMinutes}m\n";
                        
                    } else {
                        echo "❌ Error al finalizar trabajo: {$finishData['error']}\n";
                    }
                    
                } else {
                    echo "❌ Error al reanudar trabajo: {$resumeData['error']}\n";
                }
                
            } else {
                echo "❌ Error al pausar trabajo: {$pauseData['error']}\n";
            }
            
        } else {
            echo "❌ Error al iniciar trabajo: {$responseData['error']}\n";
        }
        
    } else {
        echo "ℹ️  El bug ya está siendo trabajado\n";
        echo "   - Inicio de sesión: {$bug->current_session_start}\n";
        
        // Calcular tiempo transcurrido
        if ($bug->current_session_start) {
            $startTime = Carbon::parse($bug->current_session_start);
            $elapsedSeconds = now()->diffInSeconds($startTime);
            $hours = floor($elapsedSeconds / 3600);
            $minutes = floor(($elapsedSeconds % 3600) / 60);
            $seconds = $elapsedSeconds % 60;
            
            echo "   - Tiempo transcurrido: {$hours}h {$minutes}m {$seconds}s\n";
        }
    }
    
    // 8. Verificar logs de tiempo
    echo "\n📊 Verificando logs de tiempo...\n";
    
    $timeLogs = $bug->timeLogs()->orderBy('created_at', 'desc')->get();
    echo "   - Total de logs: {$timeLogs->count()}\n";
    
    foreach ($timeLogs as $index => $log) {
        echo "   - Log " . ($index + 1) . ":\n";
        echo "     * Inicio: {$log->started_at}\n";
        echo "     * Pausa: " . ($log->paused_at ?: 'No pausado') . "\n";
        echo "     * Finalización: " . ($log->finished_at ?: 'No finalizado') . "\n";
        echo "     * Duración: {$log->duration_seconds} segundos\n";
    }
    
    echo "\n✅ Realtime time tracking test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error testing realtime time tracking: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Bug;
use App\Services\BugTimeTrackingService;
use Carbon\Carbon;

echo "=== DEBUGGING BUG TIMER FOR andresxfernandezx@gmail.com ===\n\n";

try {
    // 1. Buscar el usuario específico
    $user = User::where('email', 'andresxfernandezx@gmail.com')->first();
    
    if (!$user) {
        echo "❌ Usuario andresxfernandezx@gmail.com no encontrado\n";
        exit(1);
    }
    
    echo "✅ Usuario encontrado: {$user->name} ({$user->email})\n";
    echo "   - ID: {$user->id}\n";
    echo "   - Roles: ";
    foreach ($user->roles as $role) {
        echo $role->name . " ";
    }
    echo "\n\n";
    
    // 2. Simular sesión autenticada
    auth()->login($user);
    echo "✅ Sesión autenticada\n\n";
    
    // 3. Buscar bugs asignados al usuario
    $assignedBugs = Bug::where('user_id', $user->id)->get();
    echo "📋 Bugs asignados al usuario: {$assignedBugs->count()}\n";
    
    foreach ($assignedBugs as $bug) {
        echo "   - {$bug->title}\n";
        echo "     * Estado: {$bug->status}\n";
        echo "     * Trabajando: " . ($bug->is_working ? 'Sí' : 'No') . "\n";
        echo "     * Tiempo total: {$bug->total_time_seconds} segundos\n";
        if ($bug->current_session_start) {
            echo "     * Inicio de sesión: {$bug->current_session_start}\n";
        }
        echo "\n";
    }
    
    // 4. Buscar bugs que pueden ser iniciados
    $startableBugs = Bug::where('user_id', $user->id)
        ->whereIn('status', ['assigned', 'in progress'])
        ->where('is_working', false)
        ->get();
    
    echo "🚀 Bugs que pueden ser iniciados: {$startableBugs->count()}\n";
    
    if ($startableBugs->count() > 0) {
        $testBug = $startableBugs->first();
        echo "   - Bug de prueba: {$testBug->title}\n";
        echo "     * Estado: {$testBug->status}\n";
        echo "     * Trabajando: " . ($testBug->is_working ? 'Sí' : 'No') . "\n";
        echo "     * Tiempo total: {$testBug->total_time_seconds} segundos\n";
        
        // 5. Probar inicio de trabajo
        echo "\n🔄 Probando inicio de trabajo...\n";
        
        $timeService = new BugTimeTrackingService();
        $response = $timeService->startWork($testBug->id, $user->id);
        $responseData = json_decode($response->getContent(), true);
        
        echo "   - Código de respuesta: {$response->getStatusCode()}\n";
        echo "   - Mensaje: " . ($responseData['message'] ?? 'No message') . "\n";
        
        if ($response->getStatusCode() === 200) {
            // Recargar el bug
            $testBug->refresh();
            echo "   ✅ Trabajo iniciado exitosamente\n";
            echo "   - Estado actualizado: {$testBug->status}\n";
            echo "   - Trabajando: " . ($testBug->is_working ? 'Sí' : 'No') . "\n";
            echo "   - Inicio de sesión: {$testBug->current_session_start}\n";
            
            // 6. Simular tiempo transcurrido
            echo "\n⏱️  Simulando tiempo transcurrido...\n";
            
            for ($i = 1; $i <= 5; $i++) {
                sleep(1);
                echo "   - Segundo {$i}: " . now()->format('H:i:s') . "\n";
                
                if ($testBug->current_session_start) {
                    $startTime = Carbon::parse($testBug->current_session_start);
                    $elapsedSeconds = now()->diffInSeconds($startTime);
                    $hours = floor($elapsedSeconds / 3600);
                    $minutes = floor(($elapsedSeconds % 3600) / 60);
                    $seconds = $elapsedSeconds % 60;
                    
                    echo "     Tiempo transcurrido: {$hours}h {$minutes}m {$seconds}s\n";
                }
            }
            
            // 7. Verificar logs de tiempo
            echo "\n📊 Verificando logs de tiempo...\n";
            
            $timeLogs = $testBug->timeLogs()->orderBy('created_at', 'desc')->get();
            echo "   - Total de logs: {$timeLogs->count()}\n";
            
            foreach ($timeLogs as $index => $log) {
                echo "   - Log " . ($index + 1) . ":\n";
                echo "     * Inicio: {$log->started_at}\n";
                echo "     * Pausa: " . ($log->paused_at ?: 'No pausado') . "\n";
                echo "     * Finalización: " . ($log->finished_at ?: 'No finalizado') . "\n";
                echo "     * Duración: {$log->duration_seconds} segundos\n";
            }
            
        } else {
            echo "   ❌ Error al iniciar trabajo: " . ($responseData['error'] ?? 'Unknown error') . "\n";
        }
        
    } else {
        echo "   - No hay bugs que puedan ser iniciados\n";
    }
    
    // 8. Verificar bugs que ya están trabajando
    $workingBugs = Bug::where('user_id', $user->id)
        ->where('is_working', true)
        ->get();
    
    echo "\n🔧 Bugs que ya están trabajando: {$workingBugs->count()}\n";
    
    foreach ($workingBugs as $bug) {
        echo "   - {$bug->title}\n";
        echo "     * Estado: {$bug->status}\n";
        echo "     * Inicio de sesión: {$bug->current_session_start}\n";
        
        if ($bug->current_session_start) {
            $startTime = Carbon::parse($bug->current_session_start);
            $elapsedSeconds = now()->diffInSeconds($startTime);
            $hours = floor($elapsedSeconds / 3600);
            $minutes = floor(($elapsedSeconds % 3600) / 60);
            $seconds = $elapsedSeconds % 60;
            
            echo "     * Tiempo transcurrido: {$hours}h {$minutes}m {$seconds}s\n";
        }
    }
    
    echo "\n✅ Debug completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error debugging: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use App\Models\Bug;
use Carbon\Carbon;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUGGING ERROR DE REPORTES ===\n\n";

try {
    echo "🔍 PASO 1: Verificar conexión a base de datos\n";
    
    // Verificar conexión
    $connection = \DB::connection();
    $connection->getPdo();
    echo "✅ Conexión a base de datos exitosa\n";
    
    echo "\n🔍 PASO 2: Verificar datos necesarios\n";
    
    // Verificar proyectos
    $projects = Project::count();
    echo "   - Proyectos en BD: {$projects}\n";
    
    // Verificar usuarios
    $users = User::count();
    echo "   - Usuarios en BD: {$users}\n";
    
    // Verificar tareas
    $tasks = Task::count();
    echo "   - Tareas en BD: {$tasks}\n";
    
    // Verificar bugs
    $bugs = Bug::count();
    echo "   - Bugs en BD: {$bugs}\n";
    
    echo "\n🔍 PASO 3: Verificar rutas disponibles\n";
    
    // Obtener todas las rutas
    $routes = \Route::getRoutes();
    $paymentRoutes = [];
    
    foreach ($routes as $route) {
        $uri = $route->uri();
        if (strpos($uri, 'payment') !== false || strpos($uri, 'download') !== false) {
            $methods = $route->methods();
            $paymentRoutes[] = [
                'uri' => $uri,
                'methods' => $methods,
                'name' => $route->getName()
            ];
        }
    }
    
    echo "   - Rutas relacionadas con pagos encontradas:\n";
    foreach ($paymentRoutes as $route) {
        echo "     * " . implode('|', $route['methods']) . " /{$route['uri']}";
        if ($route['name']) {
            echo " (name: {$route['name']})";
        }
        echo "\n";
    }
    
    echo "\n🔍 PASO 4: Simular request HTTP\n";
    
    // Crear request simulado
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'developer_ids' => [User::first()->id],
        'start_date' => '2025-01-01',
        'end_date' => '2025-12-31',
        'format' => 'excel'
    ]);
    
    echo "   - Request simulado creado con datos:\n";
    echo "     * developer_ids: " . json_encode($request->developer_ids) . "\n";
    echo "     * start_date: {$request->start_date}\n";
    echo "     * end_date: {$request->end_date}\n";
    echo "     * format: {$request->format}\n";
    
    echo "\n🔍 PASO 5: Probar método downloadExcel\n";
    
    // Crear instancia del controlador
    $controller = new \App\Http\Controllers\PaymentController(new \App\Services\PaymentService());
    
    try {
        echo "   - Llamando al método downloadExcel...\n";
        $response = $controller->downloadExcel($request);
        
        echo "   ✅ Respuesta recibida:\n";
        echo "     * Status: " . $response->getStatusCode() . "\n";
        echo "     * Content-Type: " . $response->headers->get('Content-Type') . "\n";
        echo "     * Content-Length: " . strlen($response->getContent()) . " bytes\n";
        echo "     * Headers: " . json_encode($response->headers->all()) . "\n";
        
        // Verificar si hay errores en el contenido
        $content = $response->getContent();
        if (strpos($content, 'error') !== false || strpos($content, 'exception') !== false) {
            echo "   ⚠️  ADVERTENCIA: El contenido contiene palabras de error\n";
            echo "     * Contenido: " . substr($content, 0, 200) . "...\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Error en downloadExcel: " . $e->getMessage() . "\n";
        echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
    echo "\n🔍 PASO 6: Verificar middleware y permisos\n";
    
    // Verificar si el usuario tiene permisos
    $user = User::first();
    if ($user) {
        echo "   - Usuario de prueba: {$user->name} ({$user->email})\n";
        echo "   - Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
        echo "   - Permisos directos: " . $user->permissions->pluck('name')->implode(', ') . "\n";
    }
    
    echo "\n🔍 PASO 7: Verificar configuración de sesión\n";
    
    // Verificar configuración de sesión
    echo "   - Session driver: " . config('session.driver') . "\n";
    echo "   - Session lifetime: " . config('session.lifetime') . " minutos\n";
    echo "   - CSRF protection: " . (config('session.encrypt') ? 'enabled' : 'disabled') . "\n";
    
    echo "\n🔍 PASO 8: Verificar logs de Laravel\n";
    
    // Verificar si hay logs recientes
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logSize = filesize($logFile);
        echo "   - Log file existe, tamaño: " . number_format($logSize) . " bytes\n";
        
        if ($logSize > 0) {
            $lastLines = file($logFile);
            $recentLines = array_slice($lastLines, -10);
            echo "   - Últimas 10 líneas del log:\n";
            foreach ($recentLines as $line) {
                echo "     " . trim($line) . "\n";
            }
        }
    } else {
        echo "   - Log file no existe\n";
    }
    
    echo "\n✅ Debugging completado\n";
    echo "   Revisa los resultados arriba para identificar el problema.\n";
    
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 
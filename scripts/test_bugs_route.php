<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

echo "=== TESTING BUGS ROUTE ===\n\n";

try {
    // 1. Verificar que la ruta existe
    echo "🔍 Verificando rutas...\n";
    
    $routes = Route::getRoutes();
    $bugsRoute = null;
    
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'bugs') && $route->methods()[0] === 'GET') {
            $bugsRoute = $route;
            break;
        }
    }
    
    if ($bugsRoute) {
        echo "✅ Ruta de bugs encontrada: {$bugsRoute->uri()}\n";
        echo "   - Método: " . implode(', ', $bugsRoute->methods()) . "\n";
        echo "   - Controlador: " . $bugsRoute->getActionName() . "\n";
    } else {
        echo "❌ Ruta de bugs no encontrada\n";
    }
    
    // 2. Verificar que hay bugs en la base de datos
    echo "\n📊 Verificando bugs en la base de datos...\n";
    
    $bugsCount = DB::table('bugs')->count();
    echo "   - Total de bugs: {$bugsCount}\n";
    
    if ($bugsCount > 0) {
        $bugs = DB::table('bugs')->limit(3)->get();
        echo "   - Primeros 3 bugs:\n";
        foreach ($bugs as $bug) {
            echo "     * {$bug->title} (Estado: {$bug->status})\n";
        }
    }
    
    // 3. Verificar que el usuario existe
    echo "\n👤 Verificando usuario...\n";
    
    $user = DB::table('users')->where('email', 'andresxfernandezx@gmail.com')->first();
    
    if ($user) {
        echo "✅ Usuario encontrado: {$user->name}\n";
        echo "   - ID: {$user->id}\n";
        echo "   - Email: {$user->email}\n";
        
        // Verificar roles
        $roles = DB::table('role_user')->where('user_id', $user->id)->get();
        echo "   - Roles: ";
        foreach ($roles as $role) {
            $roleName = DB::table('roles')->where('id', $role->role_id)->value('name');
            echo $roleName . " ";
        }
        echo "\n";
        
        // Verificar bugs asignados
        $userBugs = DB::table('bugs')->where('user_id', $user->id)->get();
        echo "   - Bugs asignados: {$userBugs->count()}\n";
        
    } else {
        echo "❌ Usuario no encontrado\n";
    }
    
    // 4. Simular una petición HTTP
    echo "\n🌐 Simulando petición HTTP...\n";
    
    // Crear una petición simulada
    $request = \Illuminate\Http\Request::create('/bugs', 'GET');
    $request->setLaravelSession(app('session.store'));
    
    // Autenticar al usuario
    auth()->loginUsingId($user->id);
    
    echo "✅ Usuario autenticado para la simulación\n";
    
    // 5. Verificar que el controlador puede manejar la petición
    echo "\n🎯 Verificando controlador...\n";
    
    try {
        $controller = new \App\Http\Controllers\BugController(
            new \App\Services\BugTimeTrackingService(),
            new \App\Services\BugAssignmentService()
        );
        
        echo "✅ Controlador instanciado correctamente\n";
        
        // Verificar que el método index existe
        if (method_exists($controller, 'index')) {
            echo "✅ Método index existe\n";
        } else {
            echo "❌ Método index no existe\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error al instanciar controlador: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Test completed!\n";
    echo "🎯 La ruta debería funcionar correctamente\n";
    echo "🔧 Si sigue apareciendo pantalla negra, verifica:\n";
    echo "   1. Que el servidor esté ejecutándose (php artisan serve)\n";
    echo "   2. Que no haya errores de JavaScript en la consola\n";
    echo "   3. Que el usuario esté autenticado correctamente\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
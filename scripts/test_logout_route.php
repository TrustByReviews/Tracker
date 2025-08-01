<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 PROBANDO RUTA DE LOGOUT\n";
echo "==========================\n\n";

try {
    // 1. Verificar que la ruta existe
    echo "🔗 Verificando ruta de logout...\n";
    
    $router = app('router');
    $routes = $router->getRoutes();
    
    $logoutRoute = null;
    foreach ($routes as $route) {
        if ($route->getName() === 'logout') {
            $logoutRoute = $route;
            break;
        }
    }
    
    if ($logoutRoute) {
        echo "   ✅ Ruta logout encontrada\n";
        echo "   ✅ Método: " . implode('|', $logoutRoute->methods()) . "\n";
        echo "   ✅ URI: {$logoutRoute->uri()}\n";
        echo "   ✅ Middleware: " . implode(', ', $logoutRoute->middleware()) . "\n";
    } else {
        echo "   ❌ Ruta logout NO encontrada\n";
    }
    
    echo "\n";
    
    // 2. Verificar que el controlador existe
    echo "🎮 Verificando controlador de logout...\n";
    
    if ($logoutRoute) {
        $controller = $logoutRoute->getController();
        $method = $logoutRoute->getActionMethod();
        
        if ($controller) {
            echo "   ✅ Controlador: " . get_class($controller) . "\n";
            echo "   ✅ Método: {$method}\n";
        } else {
            echo "   ❌ Controlador no encontrado\n";
        }
    }
    
    echo "\n";
    
    // 3. Verificar configuración de autenticación
    echo "🔐 Verificando configuración de autenticación...\n";
    
    $authConfig = config('auth');
    echo "   ✅ Driver por defecto: {$authConfig['defaults']['guard']}\n";
    echo "   ✅ Provider por defecto: {$authConfig['defaults']['passwords']}\n";
    
    if (isset($authConfig['guards']['web'])) {
        echo "   ✅ Guard web configurado\n";
        echo "   ✅ Provider: {$authConfig['guards']['web']['provider']}\n";
    }
    
    echo "\n";
    
    // 4. Verificar que el usuario puede hacer logout
    echo "👤 Verificando capacidad de logout...\n";
    
    $developer = \App\Models\User::whereHas('roles', function ($query) {
        $query->where('name', 'developer');
    })->first();
    
    if ($developer) {
        echo "   ✅ Usuario de prueba: {$developer->name} ({$developer->email})\n";
        echo "   ✅ ID: {$developer->id}\n";
        echo "   ✅ Estado: {$developer->status}\n";
    } else {
        echo "   ❌ No se encontró usuario de prueba\n";
    }
    
    echo "\n";
    
    // 5. Instrucciones para probar manualmente
    echo "🚀 INSTRUCCIONES PARA PROBAR MANUALMENTE:\n";
    echo "========================================\n\n";
    
    echo "1. Inicia el servidor: php artisan serve\n";
    echo "2. Ve a: http://localhost:8000\n";
    echo "3. Login como: developer1@example.com / password\n";
    echo "4. Abre la consola del navegador (F12)\n";
    echo "5. Ve a la pestaña Console\n";
    echo "6. Busca el botón de usuario en la esquina inferior izquierda\n";
    echo "7. Haz clic en el botón y verifica los logs en la consola\n";
    echo "8. Si no hay logs, el problema está en el CSS o en la interacción\n";
    echo "9. Si hay logs pero no se despliega, el problema está en el dropdown\n\n";
    
    echo "🔧 SOLUCIONES ALTERNATIVAS:\n";
    echo "1. Intenta hacer clic en diferentes partes del botón\n";
    echo "2. Verifica que no haya elementos superpuestos\n";
    echo "3. Intenta hacer clic derecho en el botón\n";
    echo "4. Verifica que el botón tenga el cursor pointer\n";
    echo "5. Intenta usar la tecla Tab para navegar al botón\n";
    
    echo "\n✅ PRUEBA COMPLETADA\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 
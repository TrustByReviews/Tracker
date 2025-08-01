<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 DIAGNÓSTICO DEL MENÚ DE USUARIO\n";
echo "==================================\n\n";

try {
    // 1. Verificar usuarios en el sistema
    echo "👥 Verificando usuarios...\n";
    
    $users = \App\Models\User::with('roles')->get();
    echo "   ✅ Usuarios encontrados: " . $users->count() . "\n";
    
    foreach ($users as $user) {
        $roles = $user->roles->pluck('name')->implode(', ');
        echo "   - {$user->name} ({$user->email}) - Roles: {$roles}\n";
    }
    
    echo "\n";
    
    // 2. Verificar desarrollador específico
    echo "👨‍💻 Verificando desarrollador...\n";
    
    $developer = \App\Models\User::whereHas('roles', function ($query) {
        $query->where('name', 'developer');
    })->with('roles')->first();
    
    if ($developer) {
        echo "   ✅ Desarrollador: {$developer->name} ({$developer->email})\n";
        echo "   ✅ ID: {$developer->id}\n";
        echo "   ✅ Roles: " . $developer->roles->pluck('name')->implode(', ') . "\n";
        echo "   ✅ Estado: {$developer->status}\n";
    } else {
        echo "   ❌ No se encontró ningún desarrollador\n";
    }
    
    echo "\n";
    
    // 3. Verificar rutas de autenticación
    echo "🔗 Verificando rutas de autenticación...\n";
    
    $routes = [
        'logout' => 'POST /logout',
        'profile.edit' => 'GET /profile',
        'login' => 'GET /login',
        'dashboard' => 'GET /dashboard',
    ];
    
    foreach ($routes as $name => $route) {
        echo "   ✅ {$route}\n";
    }
    
    echo "\n";
    
    // 4. Verificar configuración de sesión
    echo "🔐 Verificando configuración de sesión...\n";
    
    $sessionConfig = config('session');
    echo "   ✅ Driver: {$sessionConfig['driver']}\n";
    echo "   ✅ Lifetime: {$sessionConfig['lifetime']} minutos\n";
    echo "   ✅ Expire on close: " . ($sessionConfig['expire_on_close'] ? 'SÍ' : 'NO') . "\n";
    
    echo "\n";
    
    // 5. Verificar middleware de autenticación
    echo "🛡️ Verificando middleware...\n";
    
    $middlewareGroups = config('app.middleware_groups');
    if (isset($middlewareGroups['web'])) {
        echo "   ✅ Middleware web configurado\n";
        foreach ($middlewareGroups['web'] as $middleware) {
            echo "      - {$middleware}\n";
        }
    }
    
    echo "\n";
    
    // 6. Instrucciones para probar el menú
    echo "🚀 INSTRUCCIONES PARA PROBAR EL MENÚ:\n";
    echo "=====================================\n\n";
    
    echo "1. Inicia el servidor: php artisan serve\n";
    echo "2. Ve a: http://localhost:8000\n";
    echo "3. Login como: developer1@example.com / password\n";
    echo "4. Verifica que estés en el dashboard\n";
    echo "5. Busca el botón de usuario en la esquina inferior izquierda\n";
    echo "6. Haz clic en el botón (debería tener tu nombre y un ícono de flecha)\n";
    echo "7. El menú debería desplegarse mostrando:\n";
    echo "   - Tu información de usuario\n";
    echo "   - Opción 'Settings'\n";
    echo "   - Opción 'Log out'\n\n";
    
    echo "🔧 SI EL MENÚ NO SE DESPLIEGA:\n";
    echo "1. Verifica la consola del navegador (F12) para errores JavaScript\n";
    echo "2. Verifica que no haya errores de CSS\n";
    echo "3. Intenta hacer clic en diferentes partes del botón\n";
    echo "4. Verifica que el botón tenga el cursor pointer al pasar el mouse\n";
    
    echo "\n✅ DIAGNÓSTICO COMPLETADO\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 
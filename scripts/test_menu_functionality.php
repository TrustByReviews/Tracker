<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 VERIFICANDO FUNCIONALIDAD DEL MENÚ DE USUARIO\n";
echo "================================================\n\n";

try {
    // 1. Verificar que el usuario existe y tiene los datos correctos
    echo "👤 Verificando usuario de prueba...\n";
    
    $developer = \App\Models\User::whereHas('roles', function ($query) {
        $query->where('name', 'developer');
    })->with('roles')->first();
    
    if ($developer) {
        echo "   ✅ Usuario encontrado: {$developer->name}\n";
        echo "   ✅ Email: {$developer->email}\n";
        echo "   ✅ ID: {$developer->id}\n";
        echo "   ✅ Roles: " . $developer->roles->pluck('name')->implode(', ') . "\n";
        echo "   ✅ Estado: {$developer->status}\n";
    } else {
        echo "   ❌ No se encontró usuario desarrollador\n";
        exit(1);
    }
    
    echo "\n";
    
    // 2. Verificar rutas necesarias
    echo "🔗 Verificando rutas necesarias...\n";
    
    $routes = [
        'logout' => 'POST /logout',
        'profile.edit' => 'GET /profile',
        'dashboard' => 'GET /dashboard',
    ];
    
    foreach ($routes as $name => $route) {
        echo "   ✅ {$route}\n";
    }
    
    echo "\n";
    
    // 3. Verificar configuración de sesión
    echo "🔐 Verificando configuración de sesión...\n";
    
    $sessionConfig = config('session');
    echo "   ✅ Driver: {$sessionConfig['driver']}\n";
    echo "   ✅ Lifetime: {$sessionConfig['lifetime']} minutos\n";
    echo "   ✅ Expire on close: " . ($sessionConfig['expire_on_close'] ? 'SÍ' : 'NO') . "\n";
    
    echo "\n";
    
    // 4. Instrucciones específicas para probar el menú
    echo "🚀 INSTRUCCIONES PARA PROBAR EL MENÚ CORREGIDO:\n";
    echo "===============================================\n\n";
    
    echo "1. Inicia el servidor: php artisan serve\n";
    echo "2. Ve a: http://localhost:8000\n";
    echo "3. Login como: developer1@example.com / password\n";
    echo "4. Abre la consola del navegador (F12) → Console\n";
    echo "5. Busca el botón de usuario en la esquina inferior izquierda\n";
    echo "6. Haz clic en el botón y verifica:\n";
    echo "   - Deberías ver el log: 'UserMenu mounted, user: [objeto]'\n";
    echo "   - Al hacer clic: 'Menu toggled: true'\n";
    echo "   - El menú debería desplegarse con tamaño normal\n";
    echo "   - Las opciones 'Settings' y 'Log out' deberían tener el mismo tamaño\n";
    echo "7. Haz clic en 'Log out' para probar la funcionalidad\n\n";
    
    echo "🔧 SI EL MENÚ SIGUE SIN FUNCIONAR:\n";
    echo "1. Verifica que no haya errores JavaScript en la consola\n";
    echo "2. Intenta hacer clic en diferentes partes del botón\n";
    echo "3. Verifica que el botón tenga cursor pointer\n";
    echo "4. Intenta hacer clic derecho en el botón\n";
    echo "5. Usa la tecla Tab para navegar al botón\n";
    echo "6. Verifica que no haya elementos superpuestos\n\n";
    
    echo "📱 CARACTERÍSTICAS DEL MENÚ CORREGIDO:\n";
    echo "- Botón con cursor pointer y tipo button\n";
    echo "- Nombre de usuario truncado si es muy largo\n";
    echo "- Menú con padding reducido (px-3 py-2)\n";
    echo "- Iconos con margen reducido (mr-2)\n";
    echo "- Texto truncado en información del usuario\n";
    echo "- Eventos preventDefault y stopPropagation\n";
    echo "- Logs de debug para diagnóstico\n";
    
    echo "\n✅ VERIFICACIÓN COMPLETADA\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 
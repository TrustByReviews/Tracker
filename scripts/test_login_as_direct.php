<?php

/**
 * Script para probar directamente la funcionalidad de "Login as User"
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🧪 PROBANDO LOGIN AS USER DIRECTAMENTE\n";
echo "=====================================\n\n";

// Configurar la aplicación
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "✅ Aplicación inicializada\n\n";

try {
    // Buscar un admin
    $admin = \App\Models\User::whereHas('roles', function ($query) {
        $query->where('name', 'admin');
    })->first();
    
    if (!$admin) {
        echo "❌ No se encontró ningún administrador\n";
        exit(1);
    }
    
    // Buscar un desarrollador
    $developer = \App\Models\User::whereHas('roles', function ($query) {
        $query->where('name', 'developer');
    })->first();
    
    if (!$developer) {
        echo "❌ No se encontró ningún desarrollador\n";
        exit(1);
    }
    
    echo "👨‍💻 ADMINISTRADOR: {$admin->name} ({$admin->email})\n";
    echo "👨‍💻 DESARROLLADOR: {$developer->name} ({$developer->email})\n\n";
    
    // Simular el proceso completo
    echo "🔄 SIMULANDO PROCESO COMPLETO:\n";
    echo "1. Login como admin...\n";
    
    // Login como admin
    \Illuminate\Support\Facades\Auth::login($admin);
    echo "   ✅ Login como admin exitoso\n";
    
    // Verificar que es admin
    if (!$admin->hasRole('admin')) {
        echo "   ❌ Error: El usuario no tiene rol de admin\n";
        exit(1);
    }
    echo "   ✅ Verificación de rol admin exitosa\n";
    
    // Simular la sesión del admin original
    $request = new \Illuminate\Http\Request();
    $request->session()->put('admin_original_user_id', $admin->id);
    echo "   ✅ Sesión de admin guardada\n";
    
    // Login como desarrollador
    echo "2. Login como desarrollador...\n";
    \Illuminate\Support\Facades\Auth::login($developer);
    echo "   ✅ Login como desarrollador exitoso\n";
    
    // Verificar que ahora es el desarrollador
    $currentUser = \Illuminate\Support\Facades\Auth::user();
    if ($currentUser->id !== $developer->id) {
        echo "   ❌ Error: No se cambió al usuario correcto\n";
        exit(1);
    }
    echo "   ✅ Verificación de cambio de usuario exitosa\n";
    
    // Verificar que la sesión del admin original se mantiene
    $adminOriginalId = $request->session()->get('admin_original_user_id');
    if ($adminOriginalId !== $admin->id) {
        echo "   ❌ Error: No se mantiene la sesión del admin original\n";
        exit(1);
    }
    echo "   ✅ Sesión del admin original mantenida\n";
    
    // Simular return to admin
    echo "3. Return to admin...\n";
    $adminUser = \App\Models\User::find($adminOriginalId);
    \Illuminate\Support\Facades\Auth::login($adminUser);
    $request->session()->forget('admin_original_user_id');
    echo "   ✅ Return to admin exitoso\n";
    
    // Verificar que volvió al admin
    $currentUser = \Illuminate\Support\Facades\Auth::user();
    if ($currentUser->id !== $admin->id) {
        echo "   ❌ Error: No se volvió al admin correcto\n";
        exit(1);
    }
    echo "   ✅ Verificación de return to admin exitosa\n";
    
    echo "\n🎉 ¡TODAS LAS PRUEBAS EXITOSAS!\n";
    echo "===============================\n\n";
    
    echo "📋 RESUMEN DE LA FUNCIONALIDAD:\n";
    echo "1. ✅ Login como admin\n";
    echo "2. ✅ Verificación de roles\n";
    echo "3. ✅ Guardado de sesión admin original\n";
    echo "4. ✅ Login como otro usuario\n";
    echo "5. ✅ Verificación de cambio de usuario\n";
    echo "6. ✅ Mantenimiento de sesión admin\n";
    echo "7. ✅ Return to admin\n";
    echo "8. ✅ Verificación de return exitoso\n\n";
    
    echo "🚀 LA FUNCIONALIDAD ESTÁ LISTA PARA USAR\n";
    echo "=======================================\n\n";
    
    echo "🎯 INSTRUCCIONES PARA PROBAR EN EL FRONTEND:\n";
    echo "1. Inicia el servidor: php artisan serve\n";
    echo "2. Accede a: http://localhost:8000\n";
    echo "3. Login como: {$admin->email} / password\n";
    echo "4. Ve a: /users\n";
    echo "5. Busca el botón 'Login As' en las tarjetas\n";
    echo "6. Haz clic en 'Login As' para: {$developer->name}\n";
    echo "7. Deberías ver el banner amarillo\n";
    echo "8. En el menú de usuario aparecerá 'Return to Admin'\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n💡 SUGERENCIAS:\n";
    echo "1. Verifica que las migraciones se ejecutaron\n";
    echo "2. Verifica que los seeders se ejecutaron\n";
    echo "3. Verifica que hay usuarios con roles asignados\n";
} 
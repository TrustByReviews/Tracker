<?php

/**
 * Script para probar la funcionalidad de "Login as User"
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🧪 PROBANDO FUNCIONALIDAD LOGIN AS USER\n";
echo "=====================================\n\n";

// Configurar la aplicación
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "✅ Aplicación inicializada\n\n";

try {
    // Verificar usuarios existentes
    echo "🔍 Verificando usuarios existentes...\n";
    $users = \App\Models\User::with('roles')->get();
    echo "✅ Usuarios encontrados: " . $users->count() . "\n\n";
    
    // Mostrar usuarios y sus roles
    echo "📋 USUARIOS Y ROLES:\n";
    echo "--------------------\n";
    foreach ($users as $user) {
        $roles = $user->roles->pluck('name')->join(', ');
        echo "   {$user->name} ({$user->email}) - Roles: {$roles}\n";
    }
    echo "\n";
    
    // Buscar un admin
    $admin = $users->first(function ($user) {
        return $user->roles->where('name', 'admin')->count() > 0;
    });
    
    if (!$admin) {
        echo "❌ No se encontró ningún administrador\n";
        exit(1);
    }
    
    echo "👨‍💻 ADMINISTRADOR ENCONTRADO:\n";
    echo "   {$admin->name} ({$admin->email})\n\n";
    
    // Buscar un usuario para hacer login
    $targetUser = $users->first(function ($user) {
        return $user->roles->where('name', 'developer')->count() > 0;
    });
    
    if (!$targetUser) {
        echo "❌ No se encontró ningún desarrollador para hacer login\n";
        exit(1);
    }
    
    echo "👨‍💻 USUARIO OBJETIVO:\n";
    echo "   {$targetUser->name} ({$targetUser->email})\n\n";
    
    // Probar el método hasRole
    echo "🔧 PROBANDO MÉTODO hasRole:\n";
    echo "   Admin tiene rol 'admin': " . ($admin->hasRole('admin') ? '✅ Sí' : '❌ No') . "\n";
    echo "   Admin tiene rol 'developer': " . ($admin->hasRole('developer') ? '✅ Sí' : '❌ No') . "\n";
    echo "   Target tiene rol 'developer': " . ($targetUser->hasRole('developer') ? '✅ Sí' : '❌ No') . "\n";
    echo "   Target tiene rol 'admin': " . ($targetUser->hasRole('admin') ? '✅ Sí' : '❌ No') . "\n\n";
    
    // Simular el proceso de login as user
    echo "🔄 SIMULANDO LOGIN AS USER:\n";
    echo "   Admin ID: {$admin->id}\n";
    echo "   Target User ID: {$targetUser->id}\n";
    echo "   URL de login as: /admin/login-as/{$targetUser->id}\n\n";
    
    echo "✅ PRUEBAS COMPLETADAS\n";
    echo "=====================\n\n";
    
    echo "🎯 PARA PROBAR EN EL FRONTEND:\n";
    echo "1. Inicia el servidor: php artisan serve\n";
    echo "2. Accede a: http://localhost:8000\n";
    echo "3. Login como admin: {$admin->email} / password\n";
    echo "4. Ve a la página de usuarios: /users\n";
    echo "5. Busca el botón 'Login As' en las tarjetas de usuario\n";
    echo "6. Haz clic en 'Login As' para el usuario: {$targetUser->name}\n\n";
    
    echo "🔍 VERIFICACIONES:\n";
    echo "- El botón 'Login As' solo debe aparecer para administradores\n";
    echo "- Al hacer clic, deberías ser redirigido al dashboard del usuario objetivo\n";
    echo "- Deberías ver un banner amarillo indicando que estás logueado como otro usuario\n";
    echo "- En el menú de usuario debería aparecer 'Return to Admin'\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n💡 SUGERENCIAS:\n";
    echo "1. Verifica que las migraciones se ejecutaron\n";
    echo "2. Verifica que los seeders se ejecutaron\n";
    echo "3. Verifica que hay usuarios con roles asignados\n";
} 
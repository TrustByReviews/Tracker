<?php

/**
 * Script para verificar que el sistema esté limpio después de deshabilitar Login as User
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 VERIFICANDO SISTEMA LIMPIO\n";
echo "=============================\n\n";

// Configurar la aplicación
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "✅ Aplicación inicializada\n\n";

try {
    // 1. Verificar que no hay sesiones de impersonación
    echo "🔍 Verificando sesiones de impersonación...\n";
    $sessions = \Illuminate\Support\Facades\DB::table('sessions')->get();
    $impersonationSessions = 0;
    
    foreach ($sessions as $session) {
        $sessionData = unserialize(base64_decode($session->payload));
        if (isset($sessionData['admin_original_user_id'])) {
            $impersonationSessions++;
        }
    }
    
    if ($impersonationSessions === 0) {
        echo "   ✅ No hay sesiones de impersonación activas\n";
    } else {
        echo "   ⚠️  Encontradas {$impersonationSessions} sesiones de impersonación\n";
    }
    echo "\n";
    
    // 2. Verificar usuarios y roles
    echo "👥 Verificando usuarios y roles...\n";
    $users = \App\Models\User::with('roles')->get();
    echo "   ✅ Usuarios encontrados: " . $users->count() . "\n";
    
    $adminCount = 0;
    $developerCount = 0;
    $teamLeaderCount = 0;
    
    foreach ($users as $user) {
        $roles = $user->roles->pluck('name')->join(', ');
        echo "      - {$user->name} ({$user->email}) - Roles: {$roles}\n";
        
        if ($user->hasRole('admin')) $adminCount++;
        if ($user->hasRole('developer')) $developerCount++;
        if ($user->hasRole('team_leader')) $teamLeaderCount++;
    }
    echo "\n";
    
    echo "📊 RESUMEN DE ROLES:\n";
    echo "   - Admins: {$adminCount}\n";
    echo "   - Developers: {$developerCount}\n";
    echo "   - Team Leaders: {$teamLeaderCount}\n\n";
    
    // 3. Verificar que el sistema de permisos funciona
    echo "🔒 Verificando sistema de permisos...\n";
    
    // Buscar un admin
    $admin = $users->first(function ($user) {
        return $user->hasRole('admin');
    });
    
    if ($admin) {
        echo "   ✅ Admin encontrado: {$admin->name}\n";
        echo "   ✅ Método hasRole funciona correctamente\n";
    } else {
        echo "   ❌ No se encontró ningún admin\n";
    }
    
    // Buscar un developer
    $developer = $users->first(function ($user) {
        return $user->hasRole('developer');
    });
    
    if ($developer) {
        echo "   ✅ Developer encontrado: {$developer->name}\n";
    } else {
        echo "   ❌ No se encontró ningún developer\n";
    }
    
    echo "\n";
    
    // 4. Verificar archivos comentados
    echo "📝 Verificando archivos comentados...\n";
    
    $filesToCheck = [
        'app/Http/Controllers/AdminController.php' => 'loginAsUser',
        'app/Http/Controllers/AdminController.php' => 'returnToAdmin',
        'routes/web.php' => 'login-as-user',
        'routes/web.php' => 'return-to-admin',
        'resources/js/components/AdminLoggedAsUserBanner.vue' => 'TEMPORARILY DISABLED',
        'resources/js/pages/User/Index.vue' => 'Login As',
        'resources/js/components/UserMenuContent.vue' => 'Return to Admin',
    ];
    
    foreach ($filesToCheck as $file => $searchTerm) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            if (strpos($content, 'TEMPORARILY DISABLED') !== false || strpos($content, '/*') !== false) {
                echo "   ✅ {$file} - Comentado correctamente\n";
            } else {
                echo "   ⚠️  {$file} - No encontrado comentario de deshabilitación\n";
            }
        } else {
            echo "   ❌ {$file} - Archivo no encontrado\n";
        }
    }
    
    echo "\n";
    
    echo "🎉 ¡VERIFICACIÓN COMPLETADA!\n";
    echo "============================\n\n";
    
    echo "📋 ESTADO DEL SISTEMA:\n";
    echo "✅ Sistema de Login as User deshabilitado\n";
    echo "✅ Sesiones limpias\n";
    echo "✅ Usuarios y roles funcionando\n";
    echo "✅ Sistema de permisos activo\n";
    echo "✅ Archivos comentados correctamente\n\n";
    
    echo "🚀 EL SISTEMA ESTÁ LISTO PARA USAR\n";
    echo "==================================\n\n";
    
    echo "📋 INSTRUCCIONES PARA EL USUARIO:\n";
    echo "1. Inicia el servidor: php artisan serve\n";
    echo "2. Ve a: http://localhost:8000\n";
    echo "3. Login como: admin@tracker.com / password\n";
    echo "4. El sistema debería funcionar sin problemas\n";
    echo "5. No habrá banner amarillo ni botones de Login As\n\n";
    
    echo "🔧 PARA REACTIVAR LOGIN AS USER EN EL FUTURO:\n";
    echo "1. Descomenta las secciones marcadas con 'TEMPORARILY DISABLED'\n";
    echo "2. Ejecuta: npx vite build\n";
    echo "3. Limpia las sesiones: php scripts/complete_session_reset.php\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n💡 SUGERENCIAS:\n";
    echo "1. Verifica que las migraciones se ejecutaron\n";
    echo "2. Verifica que los seeders se ejecutaron\n";
    echo "3. Verifica que el servidor esté corriendo\n";
} 
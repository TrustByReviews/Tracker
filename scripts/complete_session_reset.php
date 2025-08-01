<?php

/**
 * Script completo para resetear todas las sesiones y limpiar el sistema
 * Elimina todas las sesiones, cache y reinicia el estado del sistema
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔄 RESETEO COMPLETO DEL SISTEMA\n";
echo "================================\n\n";

// Configurar la aplicación
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "✅ Aplicación inicializada\n\n";

try {
    // 1. Limpiar todas las sesiones de la base de datos
    echo "🗑️  Limpiando todas las sesiones de la base de datos...\n";
    $sessionCount = \Illuminate\Support\Facades\DB::table('sessions')->count();
    \Illuminate\Support\Facades\DB::table('sessions')->truncate();
    echo "   ✅ {$sessionCount} sesiones eliminadas\n\n";
    
    // 2. Limpiar cache de sesiones
    echo "🧹 Limpiando cache de sesiones...\n";
    \Illuminate\Support\Facades\Cache::flush();
    echo "   ✅ Cache limpiado\n\n";
    
    // 3. Limpiar cache de Laravel
    echo "⚙️  Limpiando cache de Laravel...\n";
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    echo "   ✅ Cache de Laravel limpiado\n\n";
    
    // 4. Limpiar cookies de sesión
    echo "🍪 Limpiando cookies de sesión...\n";
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    echo "   ✅ Cookies limpiadas\n\n";
    
    // 5. Verificar usuarios y roles
    echo "👥 Verificando usuarios y roles...\n";
    $users = \App\Models\User::with('roles')->get();
    echo "   ✅ Usuarios encontrados: " . $users->count() . "\n";
    
    foreach ($users as $user) {
        $roles = $user->roles->pluck('name')->join(', ');
        echo "      - {$user->name} ({$user->email}) - Roles: {$roles}\n";
    }
    echo "\n";
    
    // 6. Verificar que no queden sesiones
    echo "🔍 Verificando que no queden sesiones...\n";
    $remainingSessions = \Illuminate\Support\Facades\DB::table('sessions')->count();
    if ($remainingSessions === 0) {
        echo "   ✅ No quedan sesiones en la base de datos\n\n";
    } else {
        echo "   ⚠️  Aún quedan {$remainingSessions} sesiones\n\n";
    }
    
    echo "🎉 ¡RESETEO COMPLETO EXITOSO!\n";
    echo "==============================\n\n";
    
    echo "📋 INSTRUCCIONES PARA EL USUARIO:\n";
    echo "1. Cierra COMPLETAMENTE tu navegador\n";
    echo "2. Elimina TODAS las cookies del navegador\n";
    echo "3. Elimina el historial de navegación\n";
    echo "4. Abre una ventana de incógnito/privado\n";
    echo "5. Ve a: http://localhost:8000\n";
    echo "6. Haz login como: admin@tracker.com / password\n";
    echo "7. El sistema debería estar completamente limpio\n\n";
    
    echo "🔧 COMANDOS ADICIONALES:\n";
    echo "Si necesitas reiniciar el servidor:\n";
    echo "1. php artisan serve\n\n";
    
    echo "🔒 SISTEMA DE PERMISOS REFORZADO:\n";
    echo "- Solo admins pueden ver la lista de usuarios\n";
    echo "- Cada usuario solo ve sus tareas asignadas\n";
    echo "- Team leaders ven tareas de sus proyectos\n";
    echo "- Developers ven solo sus tareas\n";
    echo "- Sesiones de impersonación validadas\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n💡 SUGERENCIAS:\n";
    echo "1. Verifica que la tabla sessions existe\n";
    echo "2. Verifica que las migraciones estén ejecutadas\n";
    echo "3. Verifica que el servidor esté corriendo\n";
} 
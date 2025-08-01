<?php

/**
 * Script para forzar el logout y limpiar completamente la sesión
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🚪 FORZANDO LOGOUT Y LIMPIANDO SESIÓN\n";
echo "=====================================\n\n";

// Configurar la aplicación
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "✅ Aplicación inicializada\n\n";

try {
    // Limpiar todas las sesiones
    echo "🧹 Limpiando todas las sesiones...\n";
    \Illuminate\Support\Facades\Session::flush();
    \Illuminate\Support\Facades\Session::regenerate();
    echo "   ✅ Sesiones limpiadas\n\n";
    
    // Limpiar cache
    echo "🗑️  Limpiando cache...\n";
    \Illuminate\Support\Facades\Cache::flush();
    echo "   ✅ Cache limpiado\n\n";
    
    // Limpiar cookies de sesión
    echo "🍪 Limpiando cookies de sesión...\n";
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    echo "   ✅ Cookies limpiadas\n\n";
    
    // Limpiar cache de Laravel
    echo "⚙️  Limpiando cache de Laravel...\n";
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    echo "   ✅ Cache de Laravel limpiado\n\n";
    
    echo "🎉 ¡LOGOUT FORZADO COMPLETADO!\n";
    echo "==============================\n\n";
    
    echo "📋 INSTRUCCIONES PARA EL USUARIO:\n";
    echo "1. Cierra COMPLETAMENTE tu navegador\n";
    echo "2. Elimina todas las cookies del navegador\n";
    echo "3. Abre una ventana de incógnito/privado\n";
    echo "4. Ve a: http://localhost:8000\n";
    echo "5. Haz login como: admin@tracker.com / password\n";
    echo "6. El menú de usuario debería funcionar correctamente\n\n";
    
    echo "🔧 COMANDOS ADICIONALES:\n";
    echo "Si el problema persiste, ejecuta estos comandos:\n";
    echo "1. php artisan session:table (si no existe la tabla)\n";
    echo "2. php artisan migrate (para asegurar las tablas)\n";
    echo "3. Reinicia el servidor: php artisan serve\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n💡 SUGERENCIAS:\n";
    echo "1. Verifica que el servidor esté corriendo\n";
    echo "2. Verifica que las migraciones estén ejecutadas\n";
    echo "3. Verifica que los seeders estén ejecutados\n";
} 
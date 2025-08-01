<?php

/**
 * Script para resetear las sesiones de "Login as User"
 * Limpia las sesiones corruptas y resetea el estado
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔄 RESETEANDO SESIONES DE LOGIN AS USER\n";
echo "========================================\n\n";

// Configurar la aplicación
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "✅ Aplicación inicializada\n\n";

try {
    // Limpiar cache de sesiones
    echo "🧹 Limpiando cache de sesiones...\n";
    \Illuminate\Support\Facades\Cache::flush();
    echo "   ✅ Cache limpiado\n\n";
    
    // Limpiar cache de configuración
    echo "⚙️  Limpiando cache de configuración...\n";
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    echo "   ✅ Configuración limpiada\n\n";
    
    // Limpiar cache de rutas
    echo "🛣️  Limpiando cache de rutas...\n";
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    echo "   ✅ Rutas limpiadas\n\n";
    
    // Limpiar cache de vistas
    echo "👁️  Limpiando cache de vistas...\n";
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    echo "   ✅ Vistas limpiadas\n\n";
    
    // Verificar usuarios admin
    echo "👨‍💻 Verificando usuarios administradores...\n";
    $admins = \App\Models\User::whereHas('roles', function ($query) {
        $query->where('name', 'admin');
    })->get();
    
    echo "   ✅ Administradores encontrados: " . $admins->count() . "\n";
    foreach ($admins as $admin) {
        echo "      - {$admin->name} ({$admin->email})\n";
    }
    echo "\n";
    
    echo "🎉 ¡RESETEO COMPLETADO!\n";
    echo "=======================\n\n";
    
    echo "📋 INSTRUCCIONES PARA EL USUARIO:\n";
    echo "1. Cierra completamente tu navegador\n";
    echo "2. Abre una nueva ventana del navegador\n";
    echo "3. Ve a: http://localhost:8000\n";
    echo "4. Haz login como: admin@tracker.com / password\n";
    echo "5. El banner amarillo debería desaparecer\n";
    echo "6. Ya no deberías ver 'Return to Admin'\n\n";
    
    echo "🔧 SI EL PROBLEMA PERSISTE:\n";
    echo "1. Limpia las cookies del navegador\n";
    echo "2. Usa modo incógnito/privado\n";
    echo "3. Reinicia el servidor: php artisan serve\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n💡 SUGERENCIAS:\n";
    echo "1. Verifica que el servidor esté corriendo\n";
    echo "2. Verifica que las migraciones estén ejecutadas\n";
    echo "3. Verifica que los seeders estén ejecutados\n";
} 
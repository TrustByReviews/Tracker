<?php

/**
 * Script específico para limpiar la sesión de "Login as User"
 * Elimina específicamente admin_original_user_id de todas las sesiones
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🧹 LIMPIANDO SESIÓN DE LOGIN AS USER\n";
echo "====================================\n\n";

// Configurar la aplicación
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "✅ Aplicación inicializada\n\n";

try {
    // Limpiar específicamente la sesión de admin_original_user_id
    echo "🔑 Limpiando sesión admin_original_user_id...\n";
    
    // Obtener todas las sesiones activas
    $sessions = \Illuminate\Support\Facades\DB::table('sessions')->get();
    $cleanedCount = 0;
    
    foreach ($sessions as $session) {
        $sessionData = unserialize(base64_decode($session->payload));
        
        // Verificar si la sesión tiene admin_original_user_id
        if (isset($sessionData['admin_original_user_id'])) {
            echo "   - Limpiando sesión ID: {$session->id}\n";
            
            // Remover admin_original_user_id de la sesión
            unset($sessionData['admin_original_user_id']);
            
            // Actualizar la sesión en la base de datos
            $newPayload = base64_encode(serialize($sessionData));
            \Illuminate\Support\Facades\DB::table('sessions')
                ->where('id', $session->id)
                ->update(['payload' => $newPayload]);
            
            $cleanedCount++;
        }
    }
    
    echo "   ✅ Sesiones limpiadas: {$cleanedCount}\n\n";
    
    // Limpiar cache de sesiones
    echo "🗑️  Limpiando cache de sesiones...\n";
    \Illuminate\Support\Facades\Cache::flush();
    echo "   ✅ Cache limpiado\n\n";
    
    // Limpiar cache de Laravel
    echo "⚙️  Limpiando cache de Laravel...\n";
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    echo "   ✅ Cache de Laravel limpiado\n\n";
    
    // Verificar que no queden sesiones con admin_original_user_id
    echo "🔍 Verificando que no queden sesiones corruptas...\n";
    $remainingSessions = \Illuminate\Support\Facades\DB::table('sessions')->get();
    $corruptedCount = 0;
    
    foreach ($remainingSessions as $session) {
        $sessionData = unserialize(base64_decode($session->payload));
        if (isset($sessionData['admin_original_user_id'])) {
            $corruptedCount++;
        }
    }
    
    if ($corruptedCount === 0) {
        echo "   ✅ No quedan sesiones corruptas\n\n";
    } else {
        echo "   ⚠️  Aún quedan {$corruptedCount} sesiones corruptas\n\n";
    }
    
    echo "🎉 ¡LIMPIEZA COMPLETADA!\n";
    echo "========================\n\n";
    
    echo "📋 INSTRUCCIONES PARA EL USUARIO:\n";
    echo "1. Cierra COMPLETAMENTE tu navegador\n";
    echo "2. Elimina TODAS las cookies del navegador\n";
    echo "3. Abre una ventana de incógnito/privado\n";
    echo "4. Ve a: http://localhost:8000\n";
    echo "5. Haz login como: admin@tracker.com / password\n";
    echo "6. El banner amarillo debería desaparecer completamente\n";
    echo "7. El menú de usuario debería funcionar correctamente\n\n";
    
    echo "🔧 SI EL PROBLEMA PERSISTE:\n";
    echo "1. Ejecuta: php artisan session:table\n";
    echo "2. Ejecuta: php artisan migrate\n";
    echo "3. Reinicia el servidor: php artisan serve\n";
    echo "4. Usa un navegador diferente\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n💡 SUGERENCIAS:\n";
    echo "1. Verifica que la tabla sessions existe\n";
    echo "2. Verifica que las migraciones estén ejecutadas\n";
    echo "3. Verifica que el servidor esté corriendo\n";
} 
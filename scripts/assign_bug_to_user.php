<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ASSIGNING BUG TO USER ===\n\n";

try {
    $userEmail = 'andresxfernandezx@gmail.com';
    $bugId = '6fc81a68-08bc-4e96-baa4-165081e082da';
    
    // 1. Verificar usuario
    echo "👤 Verificando usuario: {$userEmail}\n";
    $user = DB::table('users')->where('email', $userEmail)->first();
    
    if (!$user) {
        echo "❌ Usuario no encontrado\n";
        exit;
    }
    
    echo "✅ Usuario encontrado: {$user->name} (ID: {$user->id})\n\n";
    
    // 2. Verificar bug
    echo "🐛 Verificando bug: {$bugId}\n";
    $bug = DB::table('bugs')->where('id', $bugId)->first();
    
    if (!$bug) {
        echo "❌ Bug no encontrado\n";
        exit;
    }
    
    echo "✅ Bug encontrado: {$bug->title}\n";
    echo "   - Estado actual: {$bug->status}\n";
    echo "   - Usuario actual: {$bug->user_id}\n\n";
    
    // 3. Asignar bug al usuario
    echo "🔄 Asignando bug al usuario...\n";
    
    DB::table('bugs')->where('id', $bugId)->update([
        'user_id' => $user->id,
        'assigned_by' => $user->id,
        'assigned_at' => now(),
        'status' => 'assigned'
    ]);
    
    echo "✅ Bug asignado exitosamente\n\n";
    
    // 4. Verificar la asignación
    echo "🔍 Verificando asignación...\n";
    $updatedBug = DB::table('bugs')->where('id', $bugId)->first();
    
    echo "   - Título: {$updatedBug->title}\n";
    echo "   - Estado: {$updatedBug->status}\n";
    echo "   - Usuario asignado: {$updatedBug->user_id}\n";
    echo "   - Asignado por: {$updatedBug->assigned_by}\n";
    echo "   - Asignado en: {$updatedBug->assigned_at}\n\n";
    
    // 5. Verificar que el usuario puede trabajar en el bug
    if ($updatedBug->user_id === $user->id) {
        echo "✅ El usuario ahora puede trabajar en este bug\n";
    } else {
        echo "❌ Error: El bug no se asignó correctamente\n";
    }
    
    echo "\n🎯 Ahora el usuario puede:\n";
    echo "   1. Ver el bug en su lista\n";
    echo "   2. Iniciar el trabajo\n";
    echo "   3. El contador debería funcionar correctamente\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
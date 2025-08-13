<?php

/**
 * Script para restablecer la contraseña de un usuario de prueba
 * 
 * Este script restablece la contraseña de un usuario específico
 * y genera una nueva contraseña segura.
 */

// Inicializar Laravel
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== RESTABLECER CONTRASEÑA DE USUARIO DE PRUEBA ===\n\n";

// Email del usuario a restablecer
$email = 'carlos.rodriguez@techstore.com';

echo "Buscando usuario: {$email}\n";

// Buscar el usuario
$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ Usuario no encontrado en la base de datos.\n";
    echo "Verifica que el email sea correcto.\n";
    exit(1);
}

echo "✅ Usuario encontrado: {$user->name}\n";
echo "   - ID: {$user->id}\n";
echo "   - Email: {$user->email}\n";
echo "   - Rol: " . ($user->roles->first()->name ?? 'Sin rol') . "\n\n";

// Generar nueva contraseña segura
$newPassword = 'Test123!@#';
$hashedPassword = Hash::make($newPassword);

echo "Generando nueva contraseña...\n";

try {
    // Actualizar la contraseña
    $user->update([
        'password' => $hashedPassword
    ]);
    
    echo "✅ Contraseña actualizada exitosamente!\n\n";
    echo "=== INFORMACIÓN DE ACCESO ===\n";
    echo "Email: {$user->email}\n";
    echo "Contraseña: {$newPassword}\n";
    echo "=============================\n\n";
    
    echo "⚠️  IMPORTANTE:\n";
    echo "- Esta es una contraseña temporal para pruebas\n";
    echo "- Cambia la contraseña después del primer inicio de sesión\n";
    echo "- No compartas esta información en producción\n";
    
} catch (Exception $e) {
    echo "❌ Error al actualizar la contraseña: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== RESTABLECIMIENTO COMPLETADO ===\n";

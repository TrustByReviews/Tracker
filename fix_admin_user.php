<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

echo "=== VERIFICANDO Y ARREGLANDO USUARIO ADMIN ===\n\n";

try {
    $email = 'andresxfernandezx@gmail.com';
    $password = 'admin123456';
    
    // Verificar si el usuario existe
    $user = User::where('email', $email)->first();
    
    if ($user) {
        echo "✅ Usuario encontrado: {$user->name}\n";
        echo "📧 Email: {$user->email}\n";
        echo "🆔 ID: {$user->id}\n";
        echo "📊 Estado: {$user->status}\n";
        
        // Verificar si tiene rol admin
        $hasAdminRole = $user->roles()->where('name', 'admin')->exists();
        if ($hasAdminRole) {
            echo "✅ Ya tiene rol admin\n";
        } else {
            echo "⚠️ No tiene rol admin, asignando...\n";
            $adminRole = Role::where('name', 'admin')->first();
            if ($adminRole) {
                $user->roles()->attach($adminRole->id);
                echo "✅ Rol admin asignado\n";
            }
        }
        
        // Actualizar contraseña
        echo "🔄 Actualizando contraseña...\n";
        $user->update([
            'password' => Hash::make($password)
        ]);
        echo "✅ Contraseña actualizada\n";
        
    } else {
        echo "❌ Usuario no encontrado, creando nuevo...\n";
        
        $user = User::create([
            'name' => 'Andres Fernandez',
            'nickname' => 'Andres',
            'email' => $email,
            'password' => Hash::make($password),
            'hour_value' => 50,
            'work_time' => 'full',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        
        // Asignar rol admin
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $user->roles()->attach($adminRole->id);
            echo "✅ Rol admin asignado\n";
        }
        
        echo "✅ Usuario creado exitosamente\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎯 CREDENCIALES ACTUALIZADAS\n";
    echo str_repeat("=", 50) . "\n";
    echo "📧 Email: {$email}\n";
    echo "🔑 Contraseña: {$password}\n";
    echo "👤 Nombre: {$user->name}\n";
    echo "🎭 Rol: admin\n";
    echo "📊 Estado: {$user->status}\n";
    echo str_repeat("=", 50) . "\n";
    
    echo "\n🔗 ACCESO AL SISTEMA:\n";
    echo "🌐 URL: http://127.0.0.1:8000/login\n";
    echo "📧 Email: {$email}\n";
    echo "🔑 Contraseña: {$password}\n";
    
    echo "\n✅ ¡Ahora deberías poder acceder al sistema!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
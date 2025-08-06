<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "=== CREANDO USUARIO ADMIN FINAL ===\n\n";

try {
    $email = 'andresxfernandezx@gmail.com';
    $password = 'admin123456';
    
    // Verificar si el usuario existe
    $existingUser = User::where('email', $email)->first();
    
    if ($existingUser) {
        echo "⚠️ Usuario ya existe: {$existingUser->name}\n";
        echo "🗑️ Borrando usuario existente...\n";
        
        // Borrar relaciones primero
        $existingUser->roles()->detach();
        $existingUser->delete();
        echo "✅ Usuario existente borrado\n";
    }
    
    echo "🔄 Creando nuevo usuario admin...\n";
    
    // Crear el usuario usando DB::transaction para asegurar consistencia
    DB::transaction(function () use ($email, $password) {
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
        
        echo "✅ Usuario creado con ID: {$user->id}\n";
        
        // Asignar rol admin
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $user->roles()->attach($adminRole->id);
            echo "✅ Rol admin asignado\n";
        } else {
            echo "❌ No se encontró el rol admin\n";
        }
        
        // Verificar que el usuario se puede recuperar
        $testUser = User::where('email', $email)->first();
        if ($testUser) {
            echo "✅ Usuario verificado en base de datos\n";
        } else {
            echo "❌ Error: Usuario no encontrado después de crear\n";
        }
        
        return $user;
    });
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎯 CREDENCIALES DEL USUARIO ADMIN\n";
    echo str_repeat("=", 50) . "\n";
    echo "📧 Email: {$email}\n";
    echo "🔑 Contraseña: {$password}\n";
    echo "👤 Nombre: Andres Fernandez\n";
    echo "🎭 Rol: admin\n";
    echo "📊 Estado: active\n";
    echo str_repeat("=", 50) . "\n";
    
    echo "\n🔗 ACCESO AL SISTEMA:\n";
    echo "🌐 URL: http://127.0.0.1:8000/login\n";
    echo "📧 Email: {$email}\n";
    echo "🔑 Contraseña: {$password}\n";
    
    echo "\n📊 VERIFICACIÓN FINAL:\n";
    $finalUser = User::where('email', $email)->with('roles')->first();
    if ($finalUser) {
        echo "✅ Usuario encontrado en BD: {$finalUser->name}\n";
        echo "✅ Email: {$finalUser->email}\n";
        echo "✅ ID: {$finalUser->id}\n";
        echo "✅ Estado: {$finalUser->status}\n";
        $role = $finalUser->roles->first();
        echo "✅ Rol: " . ($role ? $role->name : 'Sin rol') . "\n";
        
        // Probar autenticación
        if (Hash::check($password, $finalUser->password)) {
            echo "✅ Contraseña verificada correctamente\n";
        } else {
            echo "❌ Error: Contraseña no coincide\n";
        }
    } else {
        echo "❌ Error: Usuario no encontrado en verificación final\n";
    }
    
    echo "\n✅ ¡Usuario admin creado exitosamente!\n";
    echo "🚀 ¡Ya puedes acceder al sistema!\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
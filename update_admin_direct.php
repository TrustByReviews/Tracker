<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== ACTUALIZANDO USUARIO ADMIN DIRECTAMENTE ===\n\n";

try {
    $email = 'andresxfernandezx@gmail.com';
    $password = 'admin123456';
    
    echo "1. Buscando usuario en la base de datos...\n";
    
    // Buscar usuario directamente en la base de datos
    $user = DB::table('users')->where('email', $email)->first();
    
    if ($user) {
        echo "✅ Usuario encontrado:\n";
        echo "   ID: {$user->id}\n";
        echo "   Nombre: {$user->name}\n";
        echo "   Email: {$user->email}\n";
        echo "   Estado: {$user->status}\n";
        
        // Verificar si tiene rol admin
        $hasAdminRole = DB::table('role_user')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('role_user.user_id', $user->id)
            ->where('roles.name', 'admin')
            ->exists();
            
        if ($hasAdminRole) {
            echo "✅ Ya tiene rol admin\n";
        } else {
            echo "⚠️ No tiene rol admin, asignando...\n";
            
            // Obtener el rol admin
            $adminRole = DB::table('roles')->where('name', 'admin')->first();
            if ($adminRole) {
                // Verificar si ya existe la relación
                $existingRole = DB::table('role_user')
                    ->where('user_id', $user->id)
                    ->where('role_id', $adminRole->id)
                    ->first();
                    
                if (!$existingRole) {
                    DB::table('role_user')->insert([
                        'user_id' => $user->id,
                        'role_id' => $adminRole->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    echo "✅ Rol admin asignado\n";
                } else {
                    echo "✅ Rol admin ya estaba asignado\n";
                }
            } else {
                echo "❌ No se encontró el rol admin\n";
            }
        }
        
        // Actualizar contraseña
        echo "🔄 Actualizando contraseña...\n";
        $hashedPassword = Hash::make($password);
        
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password' => $hashedPassword,
                'updated_at' => now(),
            ]);
            
        echo "✅ Contraseña actualizada\n";
        
    } else {
        echo "❌ Usuario no encontrado en la base de datos\n";
        echo "Creando nuevo usuario...\n";
        
        // Crear nuevo usuario
        $userId = DB::table('users')->insertGetId([
            'name' => 'Andres Fernandez',
            'nickname' => 'Andres',
            'email' => $email,
            'password' => Hash::make($password),
            'hour_value' => 50,
            'work_time' => 'full',
            'status' => 'active',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "✅ Usuario creado con ID: {$userId}\n";
        
        // Asignar rol admin
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        if ($adminRole) {
            DB::table('role_user')->insert([
                'user_id' => $userId,
                'role_id' => $adminRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "✅ Rol admin asignado\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎯 CREDENCIALES ACTUALIZADAS\n";
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
    
    // Verificación final
    echo "\n📊 VERIFICACIÓN FINAL:\n";
    $finalUser = DB::table('users')->where('email', $email)->first();
    if ($finalUser) {
        echo "✅ Usuario encontrado en BD: {$finalUser->name}\n";
        echo "✅ Email: {$finalUser->email}\n";
        echo "✅ ID: {$finalUser->id}\n";
        echo "✅ Estado: {$finalUser->status}\n";
        
        // Verificar rol
        $hasAdminRole = DB::table('role_user')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('role_user.user_id', $finalUser->id)
            ->where('roles.name', 'admin')
            ->exists();
            
        echo "✅ Rol admin: " . ($hasAdminRole ? 'Sí' : 'No') . "\n";
        
        // Verificar contraseña
        if (Hash::check($password, $finalUser->password)) {
            echo "✅ Contraseña verificada correctamente\n";
        } else {
            echo "❌ Error: Contraseña no coincide\n";
        }
    }
    
    echo "\n✅ ¡Usuario admin actualizado exitosamente!\n";
    echo "🚀 ¡Ya puedes acceder al sistema!\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
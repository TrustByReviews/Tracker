<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

echo "=== CREANDO USUARIO ADMIN ===\n\n";

try {
    $email = 'andresxfernandezx@gmail.com';
    $name = 'Andres Fernandez';
    $password = 'admin123456';
    
    // Verificar si el usuario ya existe
    $existingUser = User::where('email', $email)->first();
    
    if ($existingUser) {
        echo "Borrando usuario existente...\n";
        $existingUser->delete();
    }
    
    echo "Creando nuevo usuario admin...\n";
    
    // Crear el usuario
    $user = User::create([
        'name' => $name,
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
    }
    
    echo "\n✅ USUARIO ADMIN CREADO EXITOSAMENTE!\n";
    echo "📧 Email: {$email}\n";
    echo "🔑 Contraseña: {$password}\n";
    echo "🌐 URL: http://127.0.0.1:8000/login\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} 
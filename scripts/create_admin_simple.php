<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;

echo "=== CREANDO USUARIO ADMIN ===\n\n";

try {
    $email = 'andresxfernandezx@gmail.com';
    $name = 'Andres Fernandez';
    
    // Verificar si el usuario ya existe
    $existingUser = User::where('email', $email)->first();
    
    if ($existingUser) {
        echo "⚠ Usuario ya existe: {$existingUser->name}\n";
        echo "Actualizando a rol admin...\n";
        
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $existingUser->roles()->attach($adminRole->id);
            echo "✓ Rol admin asignado\n";
        }
        
        $user = $existingUser;
    } else {
        echo "Creando nuevo usuario admin...\n";
        
        $user = User::create([
            'name' => $name,
            'nickname' => 'Andres',
            'email' => $email,
            'password' => bcrypt('admin123'),
            'hour_value' => 50,
            'work_time' => 'full',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $user->roles()->attach($adminRole->id);
        }
        
        echo "✓ Usuario creado exitosamente\n";
    }
    
    echo "\n📋 INFORMACIÓN DEL USUARIO:\n";
    echo "  - Nombre: {$user->name}\n";
    echo "  - Email: {$user->email}\n";
    echo "  - Contraseña: admin123\n";
    echo "  - Rol: admin\n";
    echo "  - Estado: {$user->status}\n";
    
    echo "\n🔗 ACCESO AL SISTEMA:\n";
    echo "  - URL: http://127.0.0.1:8000/login\n";
    echo "  - Email: {$email}\n";
    echo "  - Contraseña: admin123\n";
    
    echo "\n📊 ESTADÍSTICAS DE LA BASE DE DATOS:\n";
    echo "  - Usuarios: " . User::count() . "\n";
    echo "  - Proyectos: " . \App\Models\Project::count() . "\n";
    echo "  - Sprints: " . \App\Models\Sprint::count() . "\n";
    echo "  - Tareas: " . \App\Models\Task::count() . "\n";
    echo "  - Logs de actividad: " . \App\Models\DeveloperActivityLog::count() . "\n";
    echo "  - Reportes de pago: " . \App\Models\PaymentReport::count() . "\n";
    
    echo "\n✅ USUARIO ADMIN CREADO/ACTUALIZADO EXITOSAMENTE!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} 
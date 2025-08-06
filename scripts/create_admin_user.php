<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== CREANDO USUARIO ADMIN ===\n\n";

try {
    // Inicializar Laravel
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    $email = 'andresxfernandezx@gmail.com';
    $name = 'Andres Fernandez';
    
    echo "1. Verificando si el usuario ya existe...\n";
    
    // Verificar si el usuario ya existe
    $existingUser = \App\Models\User::where('email', $email)->first();
    
    if ($existingUser) {
        echo "⚠ Usuario ya existe con email: {$email}\n";
        echo "Actualizando rol a admin...\n";
        
        // Verificar si ya tiene rol admin
        $hasAdminRole = $existingUser->roles()->where('name', 'admin')->exists();
        
        if (!$hasAdminRole) {
            // Asignar rol admin
            $adminRole = \App\Models\Role::where('name', 'admin')->first();
            if ($adminRole) {
                $existingUser->roles()->attach($adminRole->id);
                echo "✓ Rol admin asignado al usuario existente\n";
            }
        } else {
            echo "✓ Usuario ya tiene rol admin\n";
        }
        
        echo "\n📋 INFORMACIÓN DEL USUARIO:\n";
        echo "  - Nombre: {$existingUser->name}\n";
        echo "  - Email: {$existingUser->email}\n";
        echo "  - Contraseña: (la que ya tenía configurada)\n";
        echo "  - Rol: admin\n";
        echo "  - Estado: {$existingUser->status}\n";
        
    } else {
        echo "2. Creando nuevo usuario admin...\n";
        
        // Crear el usuario
        $user = \App\Models\User::create([
            'name' => $name,
            'nickname' => 'Andres',
            'email' => $email,
            'password' => bcrypt('admin123'),
            'hour_value' => 50,
            'work_time' => 'full',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        
        echo "✓ Usuario creado exitosamente\n";
        
        // Asignar rol admin
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        if ($adminRole) {
            $user->roles()->attach($adminRole->id);
            echo "✓ Rol admin asignado\n";
        }
        
        echo "\n📋 INFORMACIÓN DEL USUARIO:\n";
        echo "  - Nombre: {$user->name}\n";
        echo "  - Email: {$user->email}\n";
        echo "  - Contraseña: admin123\n";
        echo "  - Rol: admin\n";
        echo "  - Estado: {$user->status}\n";
    }
    
    echo "\n🔗 ACCESO AL SISTEMA:\n";
    echo "  - URL: http://127.0.0.1:8000/login\n";
    echo "  - Email: {$email}\n";
    echo "  - Contraseña: admin123\n";
    
    echo "\n📊 DATOS DISPONIBLES:\n";
    $stats = [
        'users' => \App\Models\User::count(),
        'projects' => \App\Models\Project::count(),
        'sprints' => \App\Models\Sprint::count(),
        'tasks' => \App\Models\Task::count(),
        'activity_logs' => \App\Models\DeveloperActivityLog::count(),
        'payment_reports' => \App\Models\PaymentReport::count(),
    ];
    
    foreach ($stats as $key => $value) {
        echo "  - {$key}: {$value}\n";
    }
    
    echo "\n🎯 FUNCIONALIDADES DISPONIBLES:\n";
    echo "  - Dashboard de Sprints con filtros avanzados\n";
    echo "  - Dashboard de Actividad de Desarrolladores\n";
    echo "  - Gestión de Proyectos y Tareas\n";
    echo "  - Reportes de Pago\n";
    echo "  - Exportación de datos (Excel/PDF)\n";
    
    echo "\n✅ USUARIO ADMIN CREADO/ACTUALIZADO EXITOSAMENTE!\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

echo "=== CREANDO USUARIO ADMIN NUEVO ===\n\n";

try {
    // Datos del usuario admin
    $email = 'andresxfernandezx@gmail.com';
    $name = 'Andres Fernandez';
    $password = 'admin123456';
    
    echo "1. Verificando si el usuario ya existe...\n";
    
    // Verificar si el usuario ya existe
    $existingUser = User::where('email', $email)->first();
    
    if ($existingUser) {
        echo "⚠ Usuario ya existe: {$existingUser->name}\n";
        echo "🗑️ Borrando usuario existente...\n";
        
        // Borrar el usuario existente
        $existingUser->delete();
        echo "✓ Usuario existente borrado\n";
    }
    
    echo "2. Creando nuevo usuario admin...\n";
    
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
    
    echo "✓ Usuario creado exitosamente\n";
    
    // Asignar rol admin
    $adminRole = Role::where('name', 'admin')->first();
    if ($adminRole) {
        $user->roles()->attach($adminRole->id);
        echo "✓ Rol admin asignado\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎯 CREDENCIALES DEL USUARIO ADMIN\n";
    echo str_repeat("=", 50) . "\n";
    echo "📧 Email: {$email}\n";
    echo "🔑 Contraseña: {$password}\n";
    echo "👤 Nombre: {$user->name}\n";
    echo "🎭 Rol: admin\n";
    echo "📊 Estado: {$user->status}\n";
    echo "💰 Valor por hora: \${$user->hour_value}\n";
    echo "⏰ Tiempo de trabajo: {$user->work_time}\n";
    echo str_repeat("=", 50) . "\n";
    
    echo "\n🔗 ACCESO AL SISTEMA:\n";
    echo "🌐 URL: http://127.0.0.1:8000/login\n";
    echo "📧 Email: {$email}\n";
    echo "🔑 Contraseña: {$password}\n";
    
    echo "\n📊 ESTADÍSTICAS DE LA BASE DE DATOS:\n";
    echo "👥 Usuarios: " . User::count() . "\n";
    echo "📁 Proyectos: " . \App\Models\Project::count() . "\n";
    echo "🏃 Sprints: " . \App\Models\Sprint::count() . "\n";
    echo "✅ Tareas: " . \App\Models\Task::count() . "\n";
    echo "📝 Logs de actividad: " . \App\Models\DeveloperActivityLog::count() . "\n";
    echo "💰 Reportes de pago: " . \App\Models\PaymentReport::count() . "\n";
    
    echo "\n🎯 FUNCIONALIDADES DISPONIBLES:\n";
    echo "• Dashboard de Sprints con filtros avanzados\n";
    echo "• Dashboard de Actividad de Desarrolladores\n";
    echo "• Gestión de Proyectos y Tareas\n";
    echo "• Reportes de Pago\n";
    echo "• Exportación de datos (Excel/PDF)\n";
    
    echo "\n✅ USUARIO ADMIN CREADO EXITOSAMENTE!\n";
    echo "🚀 ¡Ya puedes acceder al sistema!\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
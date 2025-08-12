<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

// Inicializar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Verificando usuario luis.perez130@test.com...\n";

// Buscar el usuario
$user = User::where('email', 'luis.perez130@test.com')->first();

if ($user) {
    echo "✅ Usuario encontrado:\n";
    echo "- ID: {$user->id}\n";
    echo "- Nombre: {$user->name}\n";
    echo "- Email: {$user->email}\n";
    echo "- Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    
    // Generar nueva contraseña
    $newPassword = \Illuminate\Support\Str::random(12);
    $user->update([
        'password' => Hash::make($newPassword)
    ]);
    
    echo "\n🔑 Contraseña restablecida exitosamente!\n";
    echo "Nueva contraseña: {$newPassword}\n";
    echo "Por favor, proporciona esta contraseña al usuario de forma segura.\n";
    
} else {
    echo "❌ Usuario no encontrado.\n";
    
    // Mostrar usuarios similares
    $similarUsers = User::where('email', 'like', '%luis%')->orWhere('name', 'like', '%Luis%')->get();
    if ($similarUsers->count() > 0) {
        echo "\nUsuarios similares encontrados:\n";
        foreach ($similarUsers as $similarUser) {
            echo "- {$similarUser->name} ({$similarUser->email})\n";
        }
    }
    
    // Preguntar si crear el usuario
    echo "\n¿Deseas crear el usuario luis.perez130@test.com? (s/N): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) === 's' || trim(strtolower($line)) === 'si' || trim(strtolower($line)) === 'y' || trim(strtolower($line)) === 'yes') {
        // Crear el usuario
        $newPassword = \Illuminate\Support\Str::random(12);
        
        $user = User::create([
            'name' => 'Luis Pérez',
            'email' => 'luis.perez130@test.com',
            'password' => Hash::make($newPassword),
            'email_verified_at' => now()
        ]);
        
        // Asignar rol de desarrollador
        $developerRole = Role::where('name', 'developer')->first();
        if ($developerRole) {
            $user->roles()->sync([$developerRole->id]);
        }
        
        echo "\n✅ Usuario creado exitosamente!\n";
        echo "- Nombre: {$user->name}\n";
        echo "- Email: {$user->email}\n";
        echo "- Contraseña: {$newPassword}\n";
        echo "- Rol: developer\n";
        echo "Por favor, proporciona estas credenciales al usuario de forma segura.\n";
    } else {
        echo "❌ Usuario no creado.\n";
    }
}

echo "\n¡Operación completada!\n";

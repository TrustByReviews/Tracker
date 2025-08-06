<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'andresxfernandezx@gmail.com';
        $name = 'Andres Fernandez';
        
        // Verificar si el usuario ya existe
        $existingUser = User::where('email', $email)->first();
        
        if ($existingUser) {
            $this->command->info("Usuario ya existe: {$existingUser->name}");
            $this->command->info("Actualizando a rol admin...");
            
            // Verificar si ya tiene rol admin
            $hasAdminRole = $existingUser->roles()->where('name', 'admin')->exists();
            
            if (!$hasAdminRole) {
                $adminRole = Role::where('name', 'admin')->first();
                if ($adminRole) {
                    $existingUser->roles()->attach($adminRole->id);
                    $this->command->info("✓ Rol admin asignado al usuario existente");
                }
            } else {
                $this->command->info("✓ Usuario ya tiene rol admin");
            }
            
        } else {
            // Crear nuevo usuario admin
            $user = User::create([
                'name' => $name,
                'nickname' => 'Andres',
                'email' => $email,
                'password' => Hash::make('admin123'),
                'hour_value' => 50,
                'work_time' => 'full',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            
            // Asignar rol admin
            $adminRole = Role::where('name', 'admin')->first();
            if ($adminRole) {
                $user->roles()->attach($adminRole->id);
                $this->command->info("✓ Rol admin asignado");
            }
            
            $this->command->info("✓ Usuario admin creado exitosamente");
        }
        
        $this->command->info("\n📋 INFORMACIÓN DEL USUARIO ADMIN:");
        $this->command->info("  - Email: {$email}");
        $this->command->info("  - Contraseña: admin123");
        $this->command->info("  - Rol: admin");
        
        $this->command->info("\n🔗 ACCESO AL SISTEMA:");
        $this->command->info("  - URL: http://127.0.0.1:8000/login");
        $this->command->info("  - Email: {$email}");
        $this->command->info("  - Contraseña: admin123");
    }
}

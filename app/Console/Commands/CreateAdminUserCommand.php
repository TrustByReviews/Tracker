<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserCommand extends Command
{
    protected $signature = 'user:create-admin {email} {name}';
    protected $description = 'Create an admin user with the specified email and name';

    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->argument('name');

        $this->info("Creating admin user: {$name} ({$email})");

        // Verificar si el usuario ya existe
        $existingUser = User::where('email', $email)->first();
        
        if ($existingUser) {
            $this->warn("User already exists with email: {$email}");
            $this->info("Updating user to admin role...");
            
            // Verificar si ya tiene rol admin
            $hasAdminRole = $existingUser->roles()->where('name', 'admin')->exists();
            
            if (!$hasAdminRole) {
                // Asignar rol admin
                $adminRole = Role::where('name', 'admin')->first();
                if ($adminRole) {
                    $existingUser->roles()->attach($adminRole->id);
                    $this->info("✓ Admin role assigned to existing user");
                }
            } else {
                $this->info("✓ User already has admin role");
            }
            
            $this->info("\nUser Information:");
            $this->info("  - Name: {$existingUser->name}");
            $this->info("  - Email: {$existingUser->email}");
            $this->info("  - Status: {$existingUser->status}");
            $this->info("  - Role: admin");
            
        } else {
            // Crear nuevo usuario
            $user = User::create([
                'name' => $name,
                'nickname' => explode(' ', $name)[0],
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
                $this->info("✓ Admin role assigned");
            }

            $this->info("✓ User created successfully");
            $this->info("\nUser Information:");
            $this->info("  - Name: {$user->name}");
            $this->info("  - Email: {$user->email}");
            $this->info("  - Password: admin123");
            $this->info("  - Role: admin");
            $this->info("  - Status: {$user->status}");
        }

        $this->info("\nAccess Information:");
        $this->info("  - URL: http://127.0.0.1:8000/login");
        $this->info("  - Email: {$email}");
        $this->info("  - Password: admin123");

        $this->info("\nDatabase Statistics:");
        $this->info("  - Users: " . User::count());
        $this->info("  - Projects: " . \App\Models\Project::count());
        $this->info("  - Sprints: " . \App\Models\Sprint::count());
        $this->info("  - Tasks: " . \App\Models\Task::count());
        $this->info("  - Activity Logs: " . \App\Models\DeveloperActivityLog::count());
        $this->info("  - Payment Reports: " . \App\Models\PaymentReport::count());

        $this->info("\n✅ Admin user created/updated successfully!");

        return 0;
    }
} 
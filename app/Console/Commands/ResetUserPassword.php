<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-password {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset password for a specific user by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        // Buscar el usuario por email
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }
        
        // Generar una nueva contraseña segura
        $newPassword = Str::random(12);
        
        // Actualizar la contraseña del usuario
        $user->update([
            'password' => Hash::make($newPassword)
        ]);
        
        $this->info("Password reset successfully for user: {$user->name} ({$user->email})");
        $this->info("New password: {$newPassword}");
        $this->warn("Please provide this password to the user securely.");
        
        return 0;
    }
}

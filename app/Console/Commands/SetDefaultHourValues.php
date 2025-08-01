<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class SetDefaultHourValues extends Command
{
    protected $signature = 'users:set-hour-values';
    protected $description = 'Set default hour values for all users based on their roles';

    public function handle()
    {
        $this->info("💰 Setting default hour values for all users...");

        // Obtener todos los usuarios
        $users = User::with('roles')->get();

        foreach ($users as $user) {
            $role = $user->roles->first()?->name ?? 'no role';
            
            // Establecer valores por defecto según el rol
            switch ($role) {
                case 'developer':
                    $hourValue = 25;
                    break;
                case 'team_leader':
                    $hourValue = 35;
                    break;
                case 'admin':
                    $hourValue = 40;
                    break;
                default:
                    $hourValue = 20;
                    break;
            }

            // Solo actualizar si no tiene valor o si es 0
            if (!$user->hour_value || $user->hour_value == 0) {
                $user->hour_value = $hourValue;
                $user->save();
                $this->line("   ✅ {$user->name} ({$role}): \${$hourValue}/hr");
            } else {
                $this->line("   ℹ️  {$user->name} ({$role}): \${$user->hour_value}/hr (already set)");
            }
        }

        $this->info("🎉 Hour values set successfully!");
        return 0;
    }
} 
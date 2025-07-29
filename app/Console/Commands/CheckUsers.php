<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check users and roles in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Verificando usuarios y roles ===');
        
        // Verificar roles
        $roles = \App\Models\Role::all();
        $this->info('Roles encontrados: ' . $roles->count());
        foreach ($roles as $role) {
            $this->line("- {$role->name} ({$role->value}) - ID: {$role->id}");
        }
        
        $this->newLine();
        
        // Verificar usuarios
        $users = \App\Models\User::all();
        $this->info('Usuarios encontrados: ' . $users->count());
        foreach ($users as $user) {
            $userRoles = $user->roles->pluck('name')->implode(', ');
            $this->line("- {$user->name} ({$user->email}) - Roles: {$userRoles} - ID: {$user->id}");
        }
        
        $this->newLine();
        $this->info('=== Verificación completada ===');
    }
}

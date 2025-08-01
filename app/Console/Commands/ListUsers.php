<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ListUsers extends Command
{
    protected $signature = 'users:list';
    protected $description = 'List all users with their roles';

    public function handle()
    {
        $users = User::with('roles')->get();

        $this->info("👥 Users in the system:");
        $this->newLine();

        foreach ($users as $user) {
            $roles = $user->roles->pluck('name')->implode(', ');
            $this->line("📧 {$user->email} - {$user->name} (Roles: {$roles})");
        }

        return 0;
    }
} 
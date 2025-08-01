<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixHourValues extends Command
{
    protected $signature = 'fix:hour-values';
    protected $description = 'Fix hour values for all users';

    public function handle()
    {
        $this->info("💰 Fixing hour values for all users...");

        // Actualizar desarrolladores
        DB::table('users')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('roles.name', 'developer')
            ->where(function($query) {
                $query->whereNull('users.hour_value')
                      ->orWhere('users.hour_value', 0);
            })
            ->update(['users.hour_value' => 25]);

        // Actualizar team leaders
        DB::table('users')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('roles.name', 'team_leader')
            ->where(function($query) {
                $query->whereNull('users.hour_value')
                      ->orWhere('users.hour_value', 0);
            })
            ->update(['users.hour_value' => 35]);

        // Actualizar admins
        DB::table('users')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('roles.name', 'admin')
            ->where(function($query) {
                $query->whereNull('users.hour_value')
                      ->orWhere('users.hour_value', 0);
            })
            ->update(['users.hour_value' => 40]);

        $this->info("✅ Hour values updated successfully!");
        return 0;
    }
} 
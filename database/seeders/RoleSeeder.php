<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles con UUIDs
        $adminRole = Role::create([
            'name' => 'admin',
            'value' => 'admin'
        ]);

        $developerRole = Role::create([
            'name' => 'developer',
            'value' => 'developer'
        ]);

        $teamLeaderRole = Role::create([
            'name' => 'team_leader',
            'value' => 'team_leader'
        ]);

        // Asignar rol de admin al primer usuario si existe
        $firstUser = User::first();
        if ($firstUser) {
            $firstUser->roles()->attach($adminRole->id);
        }
    }
}

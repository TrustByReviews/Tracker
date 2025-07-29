<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        $adminUser = User::create([
            'name' => 'Administrador',
            'email' => 'admin@tracker.com',
            'password' => Hash::make('password'),
            'nickname' => 'admin',
            'hour_value' => 0,
            'work_time' => 'full',
            'status' => 'active',
        ]);

        // Asignar rol de administrador
        $adminRole = Role::where('value', 'admin')->first();
        if ($adminRole) {
            $adminUser->roles()->attach($adminRole->id);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class ClientRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear el rol Client
        $clientRole = Role::create([
            'name' => 'client',
            'value' => 'client',
        ]);

        // Definir permisos específicos para clientes
        $clientPermissions = [
            // Proyectos - Solo lectura
            'projects.view' => 'View projects',
            'projects.show' => 'View project details',
            
            // Tareas - Solo lectura
            'tasks.view' => 'View tasks',
            'tasks.show' => 'View task details',
            
            // Sprints - Solo lectura
            'sprints.view' => 'View sprints',
            'sprints.show' => 'View sprint details',
            
            // Dashboard - Acceso básico
            'dashboard.view' => 'View dashboard',
            
            // Perfil propio
            'profile.view' => 'View own profile',
            'profile.update' => 'Update own profile',
        ];

        // Crear permisos si no existen y asignarlos al rol
        foreach ($clientPermissions as $permissionName => $permissionDescription) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
            ], [
                'display_name' => $permissionDescription,
                'description' => $permissionDescription,
            ]);

            // Asignar permiso al rol Client
            $clientRole->permissions()->attach($permission->id);
        }

        $this->command->info('Client role and permissions created successfully!');
        $this->command->info('Client role ID: ' . $clientRole->id);
    }
}

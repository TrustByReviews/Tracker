<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UnlimitedTasksPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si el permiso ya existe
        $existingPermission = DB::table('permissions')->where('name', 'unlimited_simultaneous_tasks')->first();
        
        if (!$existingPermission) {
            // Crear el permiso para tareas simultáneas sin límite
            $permissionId = Str::uuid();
            DB::table('permissions')->insert([
                'id' => $permissionId,
                'name' => 'unlimited_simultaneous_tasks',
                'display_name' => 'Tareas Simultáneas Sin Límite',
                'description' => 'Permite al usuario trabajar en más de 3 tareas simultáneamente',
                'module' => 'tasks',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $permissionId = $existingPermission->id;
        }

        // Asignar el permiso a roles específicos (evitar duplicados)
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        if ($adminRole) {
            $existingAdminPermission = DB::table('permission_role')
                ->where('permission_id', $permissionId)
                ->where('role_id', $adminRole->id)
                ->first();
                
            if (!$existingAdminPermission) {
                DB::table('permission_role')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $adminRole->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $teamLeaderRole = DB::table('roles')->where('name', 'team_leader')->first();
        if ($teamLeaderRole) {
            $existingTeamLeaderPermission = DB::table('permission_role')
                ->where('permission_id', $permissionId)
                ->where('role_id', $teamLeaderRole->id)
                ->first();
                
            if (!$existingTeamLeaderPermission) {
                DB::table('permission_role')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $teamLeaderRole->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Permiso "unlimited_simultaneous_tasks" verificado y asignado a admin y team_leader');
    }
}

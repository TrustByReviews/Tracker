<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear el permiso para tareas simultáneas sin límite
        $permission = \Spatie\Permission\Models\Permission::create([
            'name' => 'unlimited_simultaneous_tasks',
            'guard_name' => 'web',
            'display_name' => 'Tareas Simultáneas Sin Límite',
            'description' => 'Permite al usuario trabajar en más de 3 tareas simultáneamente'
        ]);

        // Asignar el permiso a roles específicos (opcional)
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permission);
        }

        $teamLeaderRole = \Spatie\Permission\Models\Role::where('name', 'team_leader')->first();
        if ($teamLeaderRole) {
            $teamLeaderRole->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar el permiso
        $permission = \Spatie\Permission\Models\Permission::where('name', 'unlimited_simultaneous_tasks')->first();
        if ($permission) {
            $permission->delete();
        }
    }
};

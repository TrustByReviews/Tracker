<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateClientPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insertar permisos específicos para clientes
        $permissions = [
            [
                'name' => 'client.view.dashboard',
                'display_name' => 'Ver Dashboard de Cliente',
                'description' => 'Permite al cliente ver su dashboard personalizado con información de proyectos',
                'module' => 'client'
            ],
            [
                'name' => 'client.view.projects',
                'display_name' => 'Ver Proyectos Asignados',
                'description' => 'Permite al cliente ver los proyectos que tiene asignados',
                'module' => 'client'
            ],
            [
                'name' => 'client.view.tasks',
                'display_name' => 'Ver Tareas de Proyectos',
                'description' => 'Permite al cliente ver las tareas de los proyectos asignados',
                'module' => 'client'
            ],
            [
                'name' => 'client.view.sprints',
                'display_name' => 'Ver Sprints Actuales',
                'description' => 'Permite al cliente ver información de los sprints en curso',
                'module' => 'client'
            ],
            [
                'name' => 'client.view.team',
                'display_name' => 'Ver Equipo del Proyecto',
                'description' => 'Permite al cliente ver información básica del equipo asignado',
                'module' => 'client'
            ],
            [
                'name' => 'client.create.suggestions',
                'display_name' => 'Crear Sugerencias',
                'description' => 'Permite al cliente crear sugerencias para los proyectos',
                'module' => 'client'
            ],
            [
                'name' => 'client.view.suggestions',
                'display_name' => 'Ver Sugerencias Propias',
                'description' => 'Permite al cliente ver sus propias sugerencias y respuestas',
                'module' => 'client'
            ],
            [
                'name' => 'client.view.project.progress',
                'display_name' => 'Ver Progreso de Proyecto',
                'description' => 'Permite al cliente ver el progreso y avance de los proyectos',
                'module' => 'client'
            ]
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert(array_merge($permission, [
                'id' => \Illuminate\Support\Str::uuid()
            ]));
        }

        // Obtener el rol 'client' si existe, o crearlo
        $clientRole = DB::table('roles')->where('name', 'client')->first();
        
        if (!$clientRole) {
            $clientRoleId = \Illuminate\Support\Str::uuid();
            DB::table('roles')->insert([
                'id' => $clientRoleId,
                'name' => 'client',
                'display_name' => 'Cliente',
                'description' => 'Usuario cliente que puede ver información de proyectos'
            ]);
        } else {
            $clientRoleId = $clientRole->id;
        }

        // Asignar todos los permisos de cliente al rol 'client'
        $clientPermissions = DB::table('permissions')
            ->whereIn('name', [
                'client.view.dashboard',
                'client.view.projects',
                'client.view.tasks',
                'client.view.sprints',
                'client.view.team',
                'client.create.suggestions',
                'client.view.suggestions',
                'client.view.project.progress'
            ])
            ->get();

        foreach ($clientPermissions as $permission) {
            DB::table('permission_role')->insert([
                'permission_id' => $permission->id,
                'role_id' => $clientRoleId
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Eliminar permisos de cliente
        DB::table('permissions')->whereIn('name', [
            'client.view.dashboard',
            'client.view.projects',
            'client.view.tasks',
            'client.view.sprints',
            'client.view.team',
            'client.create.suggestions',
            'client.view.suggestions',
            'client.view.project.progress'
        ])->delete();

        // Eliminar rol 'client' si no tiene otros permisos
        $clientRole = DB::table('roles')->where('name', 'client')->first();
        if ($clientRole) {
            $hasOtherPermissions = DB::table('permission_role')
                ->where('role_id', $clientRole->id)
                ->exists();
            
            if (!$hasOtherPermissions) {
                DB::table('roles')->where('id', $clientRole->id)->delete();
            }
        }
    }
}

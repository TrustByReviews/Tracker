<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CREACIÓN DE PERMISOS PARA QA ===\n\n";

try {
    // Obtener el rol QA
    $qaRole = Role::where('value', 'qa')->first();
    
    if (!$qaRole) {
        echo "❌ Error: No se encontró el rol QA\n";
        exit(1);
    }

    echo "✅ Rol QA encontrado: {$qaRole->name}\n\n";

    // Definir permisos específicos para QA
    $qaPermissions = [
        // Permisos de vista
        'qa.dashboard.view' => 'Ver dashboard de QA',
        'qa.tasks.view' => 'Ver tareas para QA',
        'qa.bugs.view' => 'Ver bugs para QA',
        'qa.projects.view' => 'Ver proyectos asignados (solo lectura)',
        'qa.sprints.view' => 'Ver sprints asignados (solo lectura)',
        
        // Permisos de acciones de QA
        'qa.tasks.approve' => 'Aprobar tareas como QA',
        'qa.tasks.reject' => 'Rechazar tareas como QA',
        'qa.tasks.assign' => 'Asignar tareas a sí mismo como QA',
        'qa.bugs.approve' => 'Aprobar bugs como QA',
        'qa.bugs.reject' => 'Rechazar bugs como QA',
        'qa.bugs.assign' => 'Asignar bugs a sí mismo como QA',
        
        // Permisos de notificaciones
        'qa.notifications.view' => 'Ver notificaciones de QA',
        'qa.notifications.manage' => 'Gestionar notificaciones de QA',
        
        // Permisos de edición limitada
        'qa.tasks.edit' => 'Editar tareas (solo campos de QA)',
        'qa.bugs.edit' => 'Editar bugs (solo campos de QA)',
    ];

    echo "🔧 Creando permisos para QA...\n";
    
    $createdPermissions = [];
    $existingPermissions = [];

    foreach ($qaPermissions as $permissionName => $description) {
        $permission = Permission::where('name', $permissionName)->first();
        
        if (!$permission) {
            $permission = Permission::create([
                'name' => $permissionName,
                'display_name' => $description,
                'description' => $description,
                'module' => 'qa',
                'is_active' => true,
            ]);
            $createdPermissions[] = $permissionName;
            echo "   ✅ Creado: {$permissionName}\n";
        } else {
            $existingPermissions[] = $permissionName;
            echo "   ℹ️  Ya existe: {$permissionName}\n";
        }
    }

    echo "\n📊 RESUMEN DE PERMISOS:\n";
    echo "   - Creados: " . count($createdPermissions) . "\n";
    echo "   - Existentes: " . count($existingPermissions) . "\n";
    echo "   - Total: " . count($qaPermissions) . "\n\n";

    // Asignar permisos al rol QA
    echo "🔗 Asignando permisos al rol QA...\n";
    
    $assignedCount = 0;
    foreach ($qaPermissions as $permissionName => $description) {
        $permission = Permission::where('name', $permissionName)->first();
        
        if ($permission && !$qaRole->permissions()->where('permission_id', $permission->id)->exists()) {
            $qaRole->permissions()->attach($permission->id);
            $assignedCount++;
            echo "   ✅ Asignado: {$permissionName}\n";
        } else {
            echo "   ℹ️  Ya asignado: {$permissionName}\n";
        }
    }

    echo "\n📊 PERMISOS ASIGNADOS AL ROL QA:\n";
    echo "   - Nuevos permisos asignados: {$assignedCount}\n";
    echo "   - Total de permisos del rol: " . $qaRole->permissions()->count() . "\n\n";

    // Verificar usuario QA
    $qaUser = User::where('email', 'qa@tracker.com')->first();
    
    if ($qaUser) {
        echo "👤 Usuario QA verificado:\n";
        echo "   - Nombre: {$qaUser->name}\n";
        echo "   - Email: {$qaUser->email}\n";
        echo "   - Rol: " . implode(', ', $qaUser->roles->pluck('value')->toArray()) . "\n";
        echo "   - Permisos heredados del rol: " . $qaUser->roles->first()->permissions()->count() . "\n\n";
    }

    // Listar todos los permisos de QA
    echo "📋 LISTA COMPLETA DE PERMISOS DE QA:\n";
    $qaRolePermissions = $qaRole->permissions()->orderBy('name')->get();
    
    foreach ($qaRolePermissions as $permission) {
        echo "   - {$permission->name}: {$permission->description}\n";
    }

    echo "\n🎯 PERMISOS ESPECÍFICOS PARA QA:\n";
    echo "   ✅ Dashboard específico de QA\n";
    echo "   ✅ Vista de tareas listas para testear\n";
    echo "   ✅ Vista de bugs listos para testear\n";
    echo "   ✅ Aprobar/rechazar tareas y bugs\n";
    echo "   ✅ Asignar tareas y bugs a sí mismo\n";
    echo "   ✅ Ver proyectos y sprints (solo lectura)\n";
    echo "   ✅ Gestionar notificaciones\n\n";

    echo "🚀 ¡PERMISOS DE QA CONFIGURADOS CORRECTAMENTE!\n";
    echo "   - Login: qa@tracker.com / password\n";
    echo "   - Dashboard: http://127.0.0.1:8000/dashboard\n";
    echo "   - Tasks: http://127.0.0.1:8000/tasks\n";
    echo "   - Bugs: http://127.0.0.1:8000/bugs\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
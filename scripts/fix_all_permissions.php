<?php

/**
 * Script para verificar y corregir todos los permisos necesarios para desarrolladores
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 ARREGLANDO TODOS LOS PERMISOS\n";
echo "================================\n\n";

try {
    // 1. Verificar que todos los permisos existen
    echo "1. Verificando permisos del sistema...\n";
    $allPermissions = Permission::all();
    echo "✅ Se encontraron " . $allPermissions->count() . " permisos en total\n\n";
    
    // 2. Verificar roles
    echo "2. Verificando roles...\n";
    $roles = Role::all();
    foreach ($roles as $role) {
        echo "   - {$role->name}: {$role->display_name}\n";
    }
    echo "\n";
    
    // 3. Definir permisos necesarios para cada rol
    $rolePermissions = [
        'admin' => [
            // Todos los permisos
            'admin.dashboard',
            'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete', 'tasks.assign', 'tasks.approve', 'tasks.reject',
            'sprints.view', 'sprints.create', 'sprints.edit', 'sprints.delete',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'reports.view', 'reports.create', 'reports.export',
            'payments.view', 'payment-reports.view', 'payment-reports.generate', 'payment-reports.approve', 'payment-reports.export',
            'permissions.manage', 'permissions.grant', 'permissions.revoke',
            'team-leader.dashboard', 'team-leader.approve', 'team-leader.manage',
        ],
        'team_leader' => [
            'team-leader.dashboard', 'team-leader.approve', 'team-leader.manage',
            'projects.view', 'projects.edit',
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign', 'tasks.approve', 'tasks.reject',
            'sprints.view', 'sprints.edit',
            'reports.view', 'reports.create', 'reports.export',
            'users.view', 'users.edit',
            'payments.view', 'payment-reports.view', 'payment-reports.export',
        ],
        'developer' => [
            'projects.view',
            'tasks.view', 'tasks.edit',
            'sprints.view',
            'reports.view',
            'payments.view',
        ]
    ];
    
    // 4. Asignar permisos a roles
    echo "3. Asignando permisos a roles...\n";
    foreach ($rolePermissions as $roleName => $permissionNames) {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $permissions = Permission::whereIn('name', $permissionNames)->get();
            $role->permissions()->sync($permissions->pluck('id'));
            echo "   ✅ {$role->display_name}: " . $permissions->count() . " permisos asignados\n";
        } else {
            echo "   ❌ Rol '{$roleName}' no encontrado\n";
        }
    }
    echo "\n";
    
    // 5. Verificar usuarios desarrolladores
    echo "4. Verificando usuarios desarrolladores...\n";
    $developers = User::whereHas('roles', function ($query) {
        $query->where('name', 'developer');
    })->get();
    
    foreach ($developers as $developer) {
        echo "   👤 {$developer->name} ({$developer->email}):\n";
        
        // Verificar permisos específicos
        $requiredPermissions = [
            'projects.view',
            'tasks.view',
            'sprints.view',
            'payments.view'
        ];
        
        foreach ($requiredPermissions as $permission) {
            $hasPermission = $developer->hasPermission($permission);
            echo "     - {$permission}: " . ($hasPermission ? '✅ SÍ' : '❌ NO') . "\n";
        }
        
        // Verificar acceso a módulos
        echo "     Acceso a módulos:\n";
        echo "     - Proyectos: " . ($developer->hasPermission('projects.view') ? '✅ SÍ' : '❌ NO') . "\n";
        echo "     - Tareas: " . ($developer->hasPermission('tasks.view') ? '✅ SÍ' : '❌ NO') . "\n";
        echo "     - Sprints: " . ($developer->hasPermission('sprints.view') ? '✅ SÍ' : '❌ NO') . "\n";
        echo "     - Pagos: " . ($developer->hasPermission('payments.view') ? '✅ SÍ' : '❌ NO') . "\n";
        
        echo "\n";
    }
    
    // 6. Verificar proyectos asignados
    echo "5. Verificando proyectos asignados...\n";
    foreach ($developers as $developer) {
        $assignedProjects = $developer->projects()->count();
        echo "   👤 {$developer->name}: {$assignedProjects} proyectos asignados\n";
        
        if ($assignedProjects > 0) {
            $projects = $developer->projects()->get();
            foreach ($projects as $project) {
                echo "     - {$project->name} (Estado: {$project->status})\n";
            }
        }
        echo "\n";
    }
    
    // 7. Verificar tareas asignadas
    echo "6. Verificando tareas asignadas...\n";
    foreach ($developers as $developer) {
        $assignedTasks = $developer->tasks()->count();
        echo "   👤 {$developer->name}: {$assignedTasks} tareas asignadas\n";
        
        if ($assignedTasks > 0) {
            $tasks = $developer->tasks()->take(3)->get();
            foreach ($tasks as $task) {
                echo "     - {$task->name} (Estado: {$task->status})\n";
            }
            if ($assignedTasks > 3) {
                echo "     ... y " . ($assignedTasks - 3) . " tareas más\n";
            }
        }
        echo "\n";
    }
    
    // 8. Mostrar resumen final
    echo "7. Resumen final:\n";
    echo "   - Desarrolladores verificados: " . $developers->count() . "\n";
    echo "   - Permisos de proyectos: " . $developers->filter(fn($d) => $d->hasPermission('projects.view'))->count() . "/" . $developers->count() . "\n";
    echo "   - Permisos de tareas: " . $developers->filter(fn($d) => $d->hasPermission('tasks.view'))->count() . "/" . $developers->count() . "\n";
    echo "   - Permisos de pagos: " . $developers->filter(fn($d) => $d->hasPermission('payments.view'))->count() . "/" . $developers->count() . "\n";
    
    echo "\n🎉 ¡Permisos verificados y corregidos!\n";
    echo "=====================================\n";
    echo "Ahora los desarrolladores deberían poder acceder a:\n";
    echo "- Proyectos (solo los asignados)\n";
    echo "- Tareas (solo las asignadas)\n";
    echo "- Sprints (de sus proyectos)\n";
    echo "- Pagos (dashboard personal)\n";
    echo "\n";
    echo "Para probar:\n";
    echo "1. Inicia el servidor: php artisan serve\n";
    echo "2. Accede a: http://localhost:8000/login\n";
    echo "3. Usa cualquier desarrollador:\n";
    foreach ($developers as $developer) {
        echo "   - {$developer->email} (password: password)\n";
    }
    echo "\n";
    echo "4. Verifica que aparezcan:\n";
    echo "   - Project (en el sidebar)\n";
    echo "   - Tasks (en el sidebar)\n";
    echo "   - Payment Reports (en el sidebar)\n";
    
} catch (Exception $e) {
    echo "❌ Error durante la corrección: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
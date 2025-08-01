<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class VerifyUserData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:verify-data {email? : Email del usuario a verificar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify user data loading for frontend';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?: 'admin@tracker.com';
        
        $this->info("🔍 Verificando datos del usuario: {$email}");
        
        $user = User::with(['roles.permissions', 'directPermissions'])
            ->where('email', $email)
            ->first();
        
        if (!$user) {
            $this->error("❌ Usuario no encontrado: {$email}");
            return 1;
        }
        
        $this->info("✅ Usuario encontrado: {$user->name}");
        
        // Verificar roles
        $this->info("\n📋 ROLES:");
        foreach ($user->roles as $role) {
            $this->info("  - {$role->name} (ID: {$role->id})");
        }
        
        // Verificar permisos por roles
        $this->info("\n🔐 PERMISOS POR ROLES:");
        $rolePermissions = [];
        foreach ($user->roles as $role) {
            $this->info("  📁 Rol: {$role->name}");
            foreach ($role->permissions as $permission) {
                $rolePermissions[] = $permission->name;
                $this->info("    - {$permission->name} ({$permission->module})");
            }
        }
        
        // Verificar permisos directos
        $this->info("\n🎯 PERMISOS DIRECTOS:");
        if ($user->directPermissions->isEmpty()) {
            $this->info("  - Ningún permiso directo");
        } else {
            foreach ($user->directPermissions as $permission) {
                $this->info("  - {$permission->name} ({$permission->module})");
            }
        }
        
        // Verificar rolePermissions (método getAllPermissions)
        $this->info("\n🔄 PERMISOS TOTALES (getAllPermissions):");
        $allPermissions = $user->getAllPermissions();
        foreach ($allPermissions as $permission) {
            $this->info("  - {$permission->name} ({$permission->module})");
        }
        
        // Verificar métodos de permisos
        $this->info("\n🧪 PRUEBAS DE MÉTODOS:");
        $testPermissions = [
            'admin.dashboard',
            'projects.view',
            'tasks.create',
            'permissions.manage',
            'nonexistent.permission'
        ];
        
        foreach ($testPermissions as $permission) {
            $hasPermission = $user->hasPermission($permission);
            $status = $hasPermission ? '✅ SÍ' : '❌ NO';
            $this->info("  - {$permission}: {$status}");
        }
        
        // Verificar acceso a módulos
        $this->info("\n📦 ACCESO A MÓDULOS:");
        $modules = ['admin', 'projects', 'tasks', 'sprints', 'reports', 'team-leader', 'users', 'permissions'];
        foreach ($modules as $module) {
            $canAccess = $user->getAllPermissions()->where('module', $module)->isNotEmpty();
            $status = $canAccess ? '✅ SÍ' : '❌ NO';
            $this->info("  - {$module}: {$status}");
        }
        
        // Simular datos que se envían al frontend
        $this->info("\n📤 DATOS PARA FRONTEND:");
        $frontendData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'display_name' => $permission->display_name,
                            'module' => $permission->module,
                            'is_active' => $permission->is_active
                        ];
                    })
                ];
            }),
            'directPermissions' => $user->directPermissions->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'display_name' => $permission->display_name,
                    'module' => $permission->module,
                    'is_active' => $permission->is_active
                ];
            }),
            'rolePermissions' => $allPermissions->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'display_name' => $permission->display_name,
                    'module' => $permission->module,
                    'is_active' => $permission->is_active
                ];
            })
        ];
        
        $this->info("  - Total de roles: " . count($frontendData['roles']));
        $this->info("  - Permisos directos: " . count($frontendData['directPermissions']));
        $this->info("  - Permisos totales: " . count($frontendData['rolePermissions']));
        
        $this->info("\n✅ Verificación completada!");
        $this->info("💡 Ahora puedes probar el frontend con este usuario.");
        
        return 0;
    }
} 
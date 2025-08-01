<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DebugSidebarPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:sidebar-permissions {--user= : Email del usuario a verificar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug sidebar permissions for a specific user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('user') ?: 'admin@tracker.com';
        
        $this->info("🔍 Debuggeando permisos del sidebar para: {$email}");
        $this->newLine();

        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("❌ Usuario no encontrado: {$email}");
            return 1;
        }

        $this->info("✅ Usuario encontrado: {$user->name}");
        $this->newLine();

        // Cargar relaciones
        $user->load(['roles.permissions', 'directPermissions']);

        // Verificar permisos específicos del sidebar
        $sidebarPermissions = [
            'admin.dashboard' => 'Dashboard Admin',
            'projects.view' => 'Projects',
            'tasks.view' => 'Tasks',
            'sprints.view' => 'Sprints',
            'reports.view' => 'Reports',
            'team-leader.dashboard' => 'Team Leader Dashboard',
            'users.view' => 'Users',
            'permissions.manage' => 'Permissions',
            'payments.view' => 'Payment Reports',
        ];

        $this->info("📋 Verificando permisos del sidebar:");
        $this->newLine();

        foreach ($sidebarPermissions as $permission => $description) {
            $hasPermission = $user->hasPermission($permission);
            $status = $hasPermission ? '✅' : '❌';
            $this->line("   {$status} {$description} ({$permission}): " . ($hasPermission ? 'SÍ' : 'NO'));
        }

        $this->newLine();
        $this->info("🔍 Verificando acceso a módulos:");
        $this->newLine();

        $modules = ['admin', 'projects', 'tasks', 'sprints', 'reports', 'team-leader', 'users', 'permissions', 'payments'];
        
        foreach ($modules as $module) {
            $canAccess = $user->canAccessModule($module);
            $status = $canAccess ? '✅' : '❌';
            $this->line("   {$status} Módulo {$module}: " . ($canAccess ? 'SÍ' : 'NO'));
        }

        $this->newLine();
        $this->info("📊 Resumen de datos:");
        $this->line("   - Roles: " . $user->roles->count());
        $this->line("   - Permisos directos: " . $user->directPermissions->count());
        $this->line("   - Permisos totales: " . count($user->getAllPermissions()));

        $this->newLine();
        $this->info("🎯 Elementos que deberían aparecer en el sidebar:");
        $this->newLine();

        $sidebarItems = [];
        
        if ($user->hasPermission('admin.dashboard')) {
            $sidebarItems[] = 'Dashboard';
        }
        
        if ($user->hasPermission('projects.view')) {
            $sidebarItems[] = 'Projects';
        }
        
        if ($user->hasPermission('tasks.view')) {
            $sidebarItems[] = 'Tasks';
        }
        
        if ($user->hasPermission('sprints.view')) {
            $sidebarItems[] = 'Sprints';
        }
        
        if ($user->hasPermission('reports.view')) {
            $sidebarItems[] = 'Reports';
        }
        
        if ($user->hasPermission('team-leader.dashboard')) {
            $sidebarItems[] = 'Team Leader Dashboard';
        }
        
        if ($user->hasPermission('users.view')) {
            $sidebarItems[] = 'Users';
        }
        
        if ($user->hasPermission('permissions.manage')) {
            $sidebarItems[] = 'Permissions';
        }
        
        if ($user->hasPermission('payments.view')) {
            $sidebarItems[] = 'Payment Reports';
        }

        if (empty($sidebarItems)) {
            $this->warn("⚠️  No hay elementos que deberían aparecer en el sidebar");
        } else {
            foreach ($sidebarItems as $item) {
                $this->line("   ✅ {$item}");
            }
        }

        $this->newLine();
        $this->info("💡 Si no ves elementos en el sidebar, verifica:");
        $this->line("   1. Que el usuario tenga los permisos correctos");
        $this->line("   2. Que el composable usePermissions esté funcionando");
        $this->line("   3. Que el middleware esté cargando los datos correctamente");
        $this->line("   4. Que no haya errores en la consola del navegador");

        return 0;
    }
}

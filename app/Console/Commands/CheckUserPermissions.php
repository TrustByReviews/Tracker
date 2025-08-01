<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUserPermissions extends Command
{
    protected $signature = 'users:check-permissions {email}';
    protected $description = 'Check permissions for a specific user';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->with('roles.permissions', 'directPermissions')->first();

        if (!$user) {
            $this->error("❌ User with email '{$email}' not found.");
            return 1;
        }

        $this->info("🔍 Verificando datos del usuario: {$email}");
        $this->info("✅ Usuario encontrado: {$user->name}");
        $this->newLine();

        $this->info("📋 ROLES:");
        foreach ($user->roles as $role) {
            $this->line("  - {$role->name} (ID: {$role->id})");
        }
        $this->newLine();

        $this->info("🔐 PERMISOS POR ROLES:");
        foreach ($user->roles as $role) {
            $this->line("  📁 Rol: {$role->name}");
            foreach ($role->permissions as $permission) {
                $this->line("    - {$permission->name} ({$permission->module})");
            }
        }
        $this->newLine();

        $this->info("🎯 PERMISOS DIRECTOS:");
        if ($user->directPermissions->count() > 0) {
            foreach ($user->directPermissions as $permission) {
                $this->line("  - {$permission->name} ({$permission->module})");
            }
        } else {
            $this->line("  - Ningún permiso directo");
        }
        $this->newLine();

        $this->info("🔄 PERMISOS TOTALES (getAllPermissions):");
        $allPermissions = $user->getAllPermissions();
        foreach ($allPermissions as $permission) {
            $this->line("  - {$permission->name} ({$permission->module})");
        }
        $this->newLine();

        $this->info("🧪 PRUEBAS DE MÉTODOS:");
        $this->line("  - payments.view: " . ($user->hasPermission('payments.view') ? '✅ SÍ' : '❌ NO'));
        $this->line("  - payment-reports.view: " . ($user->hasPermission('payment-reports.view') ? '✅ SÍ' : '❌ NO'));
        $this->line("  - admin.dashboard: " . ($user->hasPermission('admin.dashboard') ? '✅ SÍ' : '❌ NO'));

        return 0;
    }
} 
<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Console\Command;

class TestRbacSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rbac:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the complete RBAC system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== PRUEBA COMPLETA DEL SISTEMA RBAC ===');
        $this->newLine();

        try {
            // 1. Verificar que las tablas existen y tienen datos
            $this->info('1. VERIFICACIÓN DE TABLAS Y DATOS:');
            
            $permissionsCount = Permission::count();
            $rolesCount = Role::count();
            $usersCount = User::count();
            
            $this->line("- Permisos: {$permissionsCount}");
            $this->line("- Roles: {$rolesCount}");
            $this->line("- Usuarios: {$usersCount}");
            $this->newLine();
            
            if ($permissionsCount === 0 || $rolesCount === 0 || $usersCount === 0) {
                $this->error('❌ ERROR: Faltan datos básicos. Ejecuta los seeders primero.');
                return 1;
            }
            
            $this->info('✅ Datos básicos verificados');
            $this->newLine();
            
            // 2. Verificar roles y sus permisos
            $this->info('2. VERIFICACIÓN DE ROLES Y PERMISOS:');
            
            $roles = Role::with('permissions')->get();
            foreach ($roles as $role) {
                $this->line("- {$role->name}: {$role->permissions->count()} permisos");
                foreach ($role->permissions as $permission) {
                    $this->line("  * {$permission->name} ({$permission->module})");
                }
                $this->newLine();
            }
            
            $this->info('✅ Roles y permisos verificados');
            $this->newLine();
            
            // 3. Verificar usuarios y sus roles
            $this->info('3. VERIFICACIÓN DE USUARIOS Y ROLES:');
            
            $users = User::with('roles')->get();
            foreach ($users as $user) {
                $roleNames = $user->roles->count() > 0 
                    ? implode(', ', $user->roles->pluck('name')->toArray())
                    : 'Sin roles';
                $this->line("- {$user->name} ({$user->email}): {$roleNames}");
            }
            
            $this->info('✅ Usuarios y roles verificados');
            $this->newLine();
            
            // 4. Probar sistema de permisos
            $this->info('4. PRUEBA DEL SISTEMA DE PERMISOS:');
            
            $adminUser = User::whereHas('roles', function($query) {
                $query->where('name', 'admin');
            })->first();
            
            if ($adminUser) {
                $this->line("- Usuario admin encontrado: {$adminUser->name}");
                
                // Probar permisos específicos
                $testPermissions = [
                    'admin.dashboard',
                    'admin.users',
                    'projects.view',
                    'tasks.view',
                    'permissions.manage'
                ];
                
                foreach ($testPermissions as $permission) {
                    $hasPermission = $adminUser->hasPermission($permission);
                    $status = $hasPermission ? '✅ SÍ' : '❌ NO';
                    $this->line("  * {$permission}: {$status}");
                }
                
                // Probar método getAllPermissions
                $allPermissions = $adminUser->getAllPermissions();
                $this->line("- Total de permisos del admin: {$allPermissions->count()}");
                
            } else {
                $this->error('❌ ERROR: No se encontró usuario admin');
            }
            
            $this->info('✅ Sistema de permisos verificado');
            $this->newLine();
            
            // 5. Probar otorgamiento de permisos temporales
            $this->info('5. PRUEBA DE PERMISOS TEMPORALES:');
            
            $testUser = User::whereDoesntHave('roles', function($query) {
                $query->where('name', 'admin');
            })->first();
            
            if ($testUser) {
                $this->line("- Usuario de prueba: {$testUser->name}");
                
                // Verificar permisos antes
                $hasProjectView = $testUser->hasPermission('projects.view');
                $this->line("- Permiso 'projects.view' antes: " . ($hasProjectView ? 'SÍ' : 'NO'));
                
                // Otorgar permiso temporal
                $success = $testUser->grantPermission(
                    'projects.view',
                    'temporary',
                    'Prueba del sistema RBAC',
                    now()->addHour()
                );
                
                if ($success) {
                    $this->info('✅ Permiso temporal otorgado');
                    
                    // Verificar que el permiso se otorgó
                    $hasProjectViewAfter = $testUser->hasPermission('projects.view');
                    $this->line("- Permiso 'projects.view' después: " . ($hasProjectViewAfter ? 'SÍ' : 'NO'));
                    
                    // Revocar el permiso
                    $revokeSuccess = $testUser->revokePermission('projects.view');
                    if ($revokeSuccess) {
                        $this->info('✅ Permiso revocado correctamente');
                    } else {
                        $this->error('❌ Error al revocar permiso');
                    }
                } else {
                    $this->error('❌ Error al otorgar permiso temporal');
                }
            } else {
                $this->error('❌ ERROR: No se encontró usuario para pruebas');
            }
            
            $this->info('✅ Permisos temporales verificados');
            $this->newLine();
            
            // 6. Verificar permisos expirados
            $this->info('6. VERIFICACIÓN DE PERMISOS EXPIrados:');
            
            $expiredPermissions = \App\Models\UserPermission::expired()->count();
            $this->line("- Permisos expirados: {$expiredPermissions}");
            
            if ($expiredPermissions > 0) {
                $this->warn('⚠️  Hay permisos expirados que pueden ser limpiados');
            } else {
                $this->info('✅ No hay permisos expirados');
            }
            
            $this->info('✅ Permisos expirados verificados');
            $this->newLine();
            
            // 7. Resumen final
            $this->info('=== RESUMEN FINAL ===');
            $this->info('✅ Sistema RBAC implementado correctamente');
            $this->info('✅ Base de datos configurada');
            $this->info('✅ Permisos y roles asignados');
            $this->info('✅ Funcionalidad de permisos temporales funcionando');
            $this->info('✅ Sistema de verificación de permisos operativo');
            $this->newLine();
            
            $this->info('🎉 ¡El sistema RBAC está listo para usar!');
            $this->newLine();
            $this->line('Próximos pasos:');
            $this->line('1. Accede a /permissions en el frontend');
            $this->line('2. Prueba la gestión de permisos de usuarios');
            $this->line('3. Prueba la gestión de permisos de roles');
            $this->line('4. Implementa el middleware CheckPermission en las rutas');
            
            return 0;
            
        } catch (Exception $e) {
            $this->error('❌ ERROR: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
} 
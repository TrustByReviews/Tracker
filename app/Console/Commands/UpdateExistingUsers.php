<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateExistingUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-existing {--force : Force update without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing users with proper roles and permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Actualizando usuarios existentes con roles y permisos...');
        
        // Verificar que los roles existan
        $roles = Role::all();
        if ($roles->isEmpty()) {
            $this->error('❌ No se encontraron roles. Ejecuta primero: php artisan db:seed --class=RoleSeeder');
            return 1;
        }
        
        $this->info("📊 Roles encontrados: " . $roles->pluck('name')->implode(', '));
        
        // Obtener todos los usuarios
        $users = User::all();
        if ($users->isEmpty()) {
            $this->info('ℹ️ No hay usuarios para actualizar.');
            return 0;
        }
        
        $this->info("👥 Usuarios encontrados: {$users->count()}");
        
        // Mostrar usuarios actuales
        $this->table(
            ['ID', 'Nombre', 'Email', 'Roles Actuales'],
            $users->map(function ($user) {
                return [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->roles->pluck('name')->implode(', ') ?: 'Sin roles'
                ];
            })->toArray()
        );
        
        if (!$this->option('force') && !$this->confirm('¿Deseas continuar con la actualización?')) {
            $this->info('❌ Operación cancelada.');
            return 0;
        }
        
        $this->info('🔄 Iniciando actualización...');
        
        $updatedCount = 0;
        $errors = [];
        
        foreach ($users as $user) {
            try {
                DB::beginTransaction();
                
                // Determinar el rol basado en el email o nombre
                $role = $this->determineUserRole($user);
                
                if ($role) {
                    // Detach existing roles
                    $user->roles()->detach();
                    
                    // Attach the correct role
                    $user->roles()->attach($role->id);
                    
                    $this->info("✅ {$user->name} ({$user->email}) -> {$role->name}");
                    $updatedCount++;
                } else {
                    $this->warn("⚠️ No se pudo determinar el rol para {$user->name} ({$user->email})");
                    $errors[] = "Usuario: {$user->name} ({$user->email}) - No se pudo determinar el rol";
                }
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $errorMsg = "Error actualizando {$user->name}: {$e->getMessage()}";
                $this->error($errorMsg);
                $errors[] = $errorMsg;
            }
        }
        
        $this->info("\n📊 Resumen de actualización:");
        $this->info("✅ Usuarios actualizados: {$updatedCount}");
        
        if (!empty($errors)) {
            $this->warn("⚠️ Errores encontrados: " . count($errors));
            foreach ($errors as $error) {
                $this->warn("  - {$error}");
            }
        }
        
        // Verificar que los usuarios tengan permisos
        $this->info("\n🔍 Verificando permisos de usuarios...");
        $this->verifyUserPermissions();
        
        $this->info("\n🎉 Actualización completada!");
        $this->info("💡 Ejecuta 'php artisan rbac:test' para verificar el sistema completo.");
        
        return 0;
    }
    
    /**
     * Determine the appropriate role for a user based on email or name
     */
    private function determineUserRole(User $user): ?Role
    {
        $email = strtolower($user->email);
        $name = strtolower($user->name);
        
        // Admin users
        if (str_contains($email, 'admin') || str_contains($name, 'admin')) {
            return Role::where('name', 'admin')->first();
        }
        
        // Team Leader users
        if (str_contains($email, 'teamleader') || 
            str_contains($name, 'team') || 
            str_contains($name, 'leader')) {
            return Role::where('name', 'team_leader')->first();
        }
        
        // Developer users (default for most users)
        if (str_contains($email, 'developer') || 
            str_contains($name, 'developer') ||
            str_contains($name, 'desarrollador') ||
            str_contains($name, 'desarrolladora')) {
            return Role::where('name', 'developer')->first();
        }
        
        // Default to developer for unknown users
        return Role::where('name', 'developer')->first();
    }
    
    /**
     * Verify that users have proper permissions
     */
    private function verifyUserPermissions(): void
    {
        $users = User::with(['roles.permissions'])->get();
        
        foreach ($users as $user) {
            $roleNames = $user->roles->pluck('name')->implode(', ');
            $permissionCount = $user->roles->flatMap->permissions->count();
            
            $this->info("  - {$user->name}: {$roleNames} ({$permissionCount} permisos)");
        }
    }
} 
<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FINDING USER DIRECTLY IN DATABASE ===\n\n";

try {
    // Buscar directamente en la base de datos
    $user = DB::table('users')->where('email', 'andresxfernandezx@gmail.com')->first();
    
    if ($user) {
        echo "✅ Usuario encontrado en DB:\n";
        echo "   - ID: {$user->id}\n";
        echo "   - Nombre: {$user->name}\n";
        echo "   - Email: {$user->email}\n";
        echo "   - Status: {$user->status}\n";
        echo "   - Created: {$user->created_at}\n";
        echo "   - Updated: {$user->updated_at}\n\n";
        
        // Verificar roles
        $roles = DB::table('role_user')->where('user_id', $user->id)->get();
        echo "📋 Roles asignados:\n";
        foreach ($roles as $role) {
            $roleName = DB::table('roles')->where('id', $role->role_id)->value('name');
            echo "   - {$roleName}\n";
        }
        
        // Verificar si el usuario tiene bugs asignados
        $bugs = DB::table('bugs')->where('user_id', $user->id)->get();
        echo "\n🐛 Bugs asignados: {$bugs->count()}\n";
        
        foreach ($bugs as $bug) {
            echo "   - {$bug->title}\n";
            echo "     * Estado: {$bug->status}\n";
            echo "     * Trabajando: " . ($bug->is_working ? 'Sí' : 'No') . "\n";
            echo "     * Tiempo total: {$bug->total_time_seconds} segundos\n";
            if ($bug->current_session_start) {
                echo "     * Inicio de sesión: {$bug->current_session_start}\n";
            }
            echo "\n";
        }
        
    } else {
        echo "❌ Usuario no encontrado en la base de datos\n";
        
        // Mostrar todos los usuarios para verificar
        echo "\n📋 Todos los usuarios en la base de datos:\n";
        $allUsers = DB::table('users')->get();
        foreach ($allUsers as $u) {
            echo "   - {$u->name} ({$u->email})\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 
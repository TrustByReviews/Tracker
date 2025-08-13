<?php

/**
 * Script para configurar el dashboard de clientes
 * Ejecuta migraciones y verifica la configuración
 */

// Inicializar Laravel
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\Suggestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== CONFIGURACIÓN DEL DASHBOARD DE CLIENTES ===\n\n";

// 1. Verificar migraciones
echo "1. VERIFICANDO MIGRACIONES...\n";

// Verificar si la tabla suggestions existe
if (Schema::hasTable('suggestions')) {
    echo "   ✅ Tabla 'suggestions' existe\n";
} else {
    echo "   ❌ Tabla 'suggestions' no existe - Ejecutando migración...\n";
    try {
        // Ejecutar migración de sugerencias
        $migration = new \CreateSuggestionsTable();
        $migration->up();
        echo "   ✅ Migración de sugerencias ejecutada\n";
    } catch (Exception $e) {
        echo "   ❌ Error al ejecutar migración: " . $e->getMessage() . "\n";
    }
}

// Verificar permisos de cliente
$clientPermissions = [
    'client.view.dashboard',
    'client.view.projects',
    'client.view.tasks',
    'client.view.sprints',
    'client.view.team',
    'client.create.suggestions',
    'client.view.suggestions',
    'client.view.project.progress'
];

echo "\n2. VERIFICANDO PERMISOS DE CLIENTE...\n";

$existingPermissions = DB::table('permissions')
    ->whereIn('name', $clientPermissions)
    ->pluck('name')
    ->toArray();

$missingPermissions = array_diff($clientPermissions, $existingPermissions);

if (empty($missingPermissions)) {
    echo "   ✅ Todos los permisos de cliente existen\n";
} else {
    echo "   ⚠️  Permisos faltantes: " . implode(', ', $missingPermissions) . "\n";
    echo "   🔧 Ejecutando migración de permisos...\n";
    
    try {
        // Ejecutar migración de permisos
        $migration = new \CreateClientPermissions();
        $migration->up();
        echo "   ✅ Permisos de cliente creados\n";
    } catch (Exception $e) {
        echo "   ❌ Error al crear permisos: " . $e->getMessage() . "\n";
    }
}

// 3. Verificar rol de cliente
echo "\n3. VERIFICANDO ROL DE CLIENTE...\n";

$clientRole = DB::table('roles')->where('name', 'client')->first();

if ($clientRole) {
    echo "   ✅ Rol 'client' existe\n";
    
    // Verificar permisos asignados al rol
    $assignedPermissions = DB::table('permission_role')
        ->join('permissions', 'permission_role.permission_id', '=', 'permissions.id')
        ->where('permission_role.role_id', $clientRole->id)
        ->whereIn('permissions.name', $clientPermissions)
        ->pluck('permissions.name')
        ->toArray();
    
    echo "   - Permisos asignados: " . count($assignedPermissions) . "/" . count($clientPermissions) . "\n";
    
    if (count($assignedPermissions) === count($clientPermissions)) {
        echo "   ✅ Todos los permisos están asignados al rol\n";
    } else {
        echo "   ⚠️  Faltan permisos asignados\n";
    }
} else {
    echo "   ❌ Rol 'client' no existe\n";
}

// 4. Verificar usuario cliente
echo "\n4. VERIFICANDO USUARIO CLIENTE...\n";

$clientUser = User::where('email', 'carlos.rodriguez@techstore.com')->first();

if ($clientUser) {
    echo "   ✅ Usuario cliente encontrado: {$clientUser->name}\n";
    
    // Verificar si tiene el rol de cliente
    $hasClientRole = $clientUser->roles()->where('name', 'client')->exists();
    
    if ($hasClientRole) {
        echo "   ✅ Usuario tiene rol 'client'\n";
    } else {
        echo "   ⚠️  Usuario no tiene rol 'client' - Asignando...\n";
        
        $clientRole = DB::table('roles')->where('name', 'client')->first();
        if ($clientRole) {
            DB::table('role_user')->insert([
                'role_id' => $clientRole->id,
                'user_id' => $clientUser->id
            ]);
            echo "   ✅ Rol asignado al usuario\n";
        }
    }
    
    // Verificar proyectos asignados
    $assignedProjects = $clientUser->projects()->count();
    echo "   - Proyectos asignados: {$assignedProjects}\n";
    
    if ($assignedProjects > 0) {
        echo "   ✅ Usuario tiene proyectos asignados\n";
    } else {
        echo "   ⚠️  Usuario no tiene proyectos asignados\n";
    }
    
} else {
    echo "   ❌ Usuario cliente no encontrado\n";
}

// 5. Verificar controladores
echo "\n5. VERIFICANDO CONTROLADORES...\n";

$controllers = [
    'app/Http/Controllers/Client/DashboardController.php',
    'app/Http/Controllers/Client/SuggestionController.php'
];

foreach ($controllers as $controller) {
    if (file_exists($controller)) {
        echo "   ✅ {$controller}\n";
    } else {
        echo "   ❌ {$controller} - No encontrado\n";
    }
}

// 6. Verificar rutas
echo "\n6. VERIFICANDO RUTAS...\n";

if (file_exists('routes/client.php')) {
    echo "   ✅ routes/client.php existe\n";
} else {
    echo "   ❌ routes/client.php - No encontrado\n";
}

// 7. Verificar modelo
echo "\n7. VERIFICANDO MODELO...\n";

if (file_exists('app/Models/Suggestion.php')) {
    echo "   ✅ app/Models/Suggestion.php existe\n";
} else {
    echo "   ❌ app/Models/Suggestion.php - No encontrado\n";
}

// 8. Prueba de funcionalidad
echo "\n8. PRUEBA DE FUNCIONALIDAD...\n";

try {
    // Probar acceso al dashboard
    $clientUser = User::where('email', 'carlos.rodriguez@techstore.com')->first();
    
    if ($clientUser) {
        // Simular datos del dashboard
        $projects = $clientUser->projects()->count();
        echo "   ✅ Usuario puede acceder a proyectos: {$projects} proyectos\n";
        
        // Verificar permisos
        $canViewDashboard = $clientUser->hasPermissionTo('client.view.dashboard');
        $canCreateSuggestions = $clientUser->hasPermissionTo('client.create.suggestions');
        
        echo "   - Puede ver dashboard: " . ($canViewDashboard ? '✅' : '❌') . "\n";
        echo "   - Puede crear sugerencias: " . ($canCreateSuggestions ? '✅' : '❌') . "\n";
        
    } else {
        echo "   ❌ No se pudo probar funcionalidad - Usuario no encontrado\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error en prueba de funcionalidad: " . $e->getMessage() . "\n";
}

// 9. Resumen
echo "\n9. RESUMEN DE CONFIGURACIÓN:\n";

echo "   - Migraciones: " . (Schema::hasTable('suggestions') ? '✅' : '❌') . "\n";
echo "   - Permisos: " . (count($missingPermissions) === 0 ? '✅' : '❌') . "\n";
echo "   - Rol cliente: " . ($clientRole ? '✅' : '❌') . "\n";
echo "   - Usuario cliente: " . ($clientUser ? '✅' : '❌') . "\n";
echo "   - Controladores: " . (file_exists('app/Http/Controllers/Client/DashboardController.php') ? '✅' : '❌') . "\n";
echo "   - Rutas: " . (file_exists('routes/client.php') ? '✅' : '❌') . "\n";
echo "   - Modelo: " . (file_exists('app/Models/Suggestion.php') ? '✅' : '❌') . "\n";

echo "\n=== CONFIGURACIÓN COMPLETADA ===\n";

echo "\n📋 PRÓXIMOS PASOS:\n";
echo "1. Crear las vistas Vue.js para el dashboard de clientes\n";
echo "2. Implementar el middleware de redirección para clientes\n";
echo "3. Crear componentes para mostrar información de proyectos\n";
echo "4. Implementar sistema de sugerencias en el frontend\n";
echo "5. Probar funcionalidad completa\n";

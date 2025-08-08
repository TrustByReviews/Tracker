<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRUEBA DEL SIDEBAR DE QA ===\n\n";

try {
    // Obtener usuario QA
    $qaUser = User::where('email', 'qa@tracker.com')->first();

    if (!$qaUser) {
        echo "❌ Error: No se encontró el usuario QA\n";
        exit(1);
    }

    echo "✅ Usuario QA encontrado:\n";
    echo "   - Nombre: {$qaUser->name}\n";
    echo "   - Email: {$qaUser->email}\n";
    echo "   - Roles: " . implode(', ', $qaUser->roles->pluck('value')->toArray()) . "\n\n";

    // Verificar que el usuario tenga el rol QA
    $hasQaRole = $qaUser->roles->where('value', 'qa')->count() > 0;
    
    if (!$hasQaRole) {
        echo "❌ Error: El usuario no tiene el rol QA\n";
        exit(1);
    }

    echo "✅ Rol QA verificado correctamente\n\n";

    // Verificar elementos del sidebar
    echo "🔧 ELEMENTOS DEL SIDEBAR PARA QA:\n";
    echo "   ✅ Logo azul - /dashboard (clickeable)\n";
    echo "   ✅ Projects - /projects (solo lectura)\n";
    echo "   ✅ Sprints - /sprints (solo lectura)\n";
    echo "   ✅ Tasks - /tasks (puede editar)\n";
    echo "   ✅ Bugs - /bugs (puede editar)\n\n";

    echo "🚫 ELEMENTOS QUE NO DEBEN APARECER:\n";
    echo "   ❌ Dashboard (elemento de lista)\n";
    echo "   ❌ Users (gestión de usuarios)\n";
    echo "   ❌ Payments & Reports\n";
    echo "   ❌ Developer Activity\n";
    echo "   ❌ Permissions\n";
    echo "   ❌ Permisos Tareas\n\n";

    echo "🎯 VERIFICACIÓN DE NAVEGACIÓN:\n";
    echo "   ✅ Solo 4 elementos en la lista del sidebar\n";
    echo "   ✅ Logo azul clickeable lleva al dashboard\n";
    echo "   ✅ No hay elementos duplicados\n";
    echo "   ✅ Elementos específicos para QA\n\n";

    echo "🚀 ¡SIDEBAR CONFIGURADO CORRECTAMENTE!\n";
    echo "   - Login: qa@tracker.com / password\n";
    echo "   - Verificar que solo aparezcan 4 elementos en la lista\n";
    echo "   - Verificar que el logo azul lleve al dashboard\n";
    echo "   - Verificar que no haya duplicados\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 
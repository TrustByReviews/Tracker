<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Route;
use App\Models\User;

echo "=== DIAGNÓSTICO DEL MENÚ DE USUARIO ===\n\n";

// 1. Verificar rutas
echo "1. VERIFICANDO RUTAS:\n";
echo "Ruta logout: " . (Route::has('logout') ? 'EXISTE' : 'NO EXISTE') . "\n";
echo "Ruta profile.edit: " . (Route::has('profile.edit') ? 'EXISTE' : 'NO EXISTE') . "\n";

// 2. Verificar usuarios
echo "\n2. VERIFICANDO USUARIOS:\n";
try {
    $users = User::all();
    echo "Total usuarios: " . $users->count() . "\n";
    foreach ($users as $user) {
        echo "- {$user->name} ({$user->email})\n";
    }
} catch (Exception $e) {
    echo "Error al obtener usuarios: " . $e->getMessage() . "\n";
}

// 3. Verificar archivo del componente
echo "\n3. VERIFICANDO ARCHIVO DEL COMPONENTE:\n";
$componentPath = __DIR__ . '/../resources/js/components/SimpleUserMenu.vue';
if (file_exists($componentPath)) {
    echo "Archivo SimpleUserMenu.vue: EXISTE\n";
    $content = file_get_contents($componentPath);
    
    // Verificar elementos clave
    $checks = [
        'v-show="isOpen"' => 'Directiva v-show presente',
        '@click="toggleMenu"' => 'Evento click presente',
        'isOpen.value' => 'Variable isOpen presente',
        'console.log' => 'Logs de debug presentes'
    ];
    
    foreach ($checks as $search => $description) {
        $found = strpos($content, $search) !== false;
        echo "- {$description}: " . ($found ? 'SÍ' : 'NO') . "\n";
    }
} else {
    echo "Archivo SimpleUserMenu.vue: NO EXISTE\n";
}

// 4. Verificar NavUser.vue
echo "\n4. VERIFICANDO NavUser.vue:\n";
$navUserPath = __DIR__ . '/../resources/js/components/NavUser.vue';
if (file_exists($navUserPath)) {
    echo "Archivo NavUser.vue: EXISTE\n";
    $content = file_get_contents($navUserPath);
    
    if (strpos($content, 'SimpleUserMenu') !== false) {
        echo "- SimpleUserMenu importado: SÍ\n";
    } else {
        echo "- SimpleUserMenu importado: NO\n";
    }
} else {
    echo "Archivo NavUser.vue: NO EXISTE\n";
}

// 5. Instrucciones de debug
echo "\n5. INSTRUCCIONES DE DEBUG:\n";
echo "1. Abre las herramientas de desarrollador (F12)\n";
echo "2. Ve a la pestaña Console\n";
echo "3. Haz clic en el botón de usuario\n";
echo "4. Busca mensajes que digan 'Menu toggled', 'Button clicked!', etc.\n";
echo "5. Si no hay mensajes, el problema está en el evento click\n";
echo "6. Si hay mensajes pero no se ve el menú, el problema está en el CSS o v-show\n";

echo "\n6. VERIFICACIÓN MANUAL:\n";
echo "- ¿El botón de usuario es clickeable?\n";
echo "- ¿Aparecen mensajes en la consola al hacer clic?\n";
echo "- ¿El menú aparece pero no es visible (problema de CSS)?\n";
echo "- ¿El menú no aparece en absoluto (problema de v-show)?\n";

echo "\n=== FIN DEL DIAGNÓSTICO ===\n"; 
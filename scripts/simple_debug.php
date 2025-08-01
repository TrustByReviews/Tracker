<?php

echo "=== DIAGNÓSTICO SIMPLE DEL MENÚ ===\n\n";

// 1. Verificar archivo del componente
echo "1. VERIFICANDO ARCHIVO SimpleUserMenu.vue:\n";
$componentPath = __DIR__ . '/../resources/js/components/SimpleUserMenu.vue';
if (file_exists($componentPath)) {
    echo "✅ Archivo SimpleUserMenu.vue: EXISTE\n";
    $content = file_get_contents($componentPath);
    
    // Verificar elementos clave
    $checks = [
        'v-show="isOpen"' => 'Directiva v-show',
        '@click="toggleMenu"' => 'Evento click',
        'isOpen.value' => 'Variable isOpen',
        'console.log' => 'Logs de debug',
        'fixed top-4 right-4' => 'Posición fija'
    ];
    
    foreach ($checks as $search => $description) {
        $found = strpos($content, $search) !== false;
        echo "- {$description}: " . ($found ? '✅ SÍ' : '❌ NO') . "\n";
    }
} else {
    echo "❌ Archivo SimpleUserMenu.vue: NO EXISTE\n";
}

// 2. Verificar NavUser.vue
echo "\n2. VERIFICANDO NavUser.vue:\n";
$navUserPath = __DIR__ . '/../resources/js/components/NavUser.vue';
if (file_exists($navUserPath)) {
    echo "✅ Archivo NavUser.vue: EXISTE\n";
    $content = file_get_contents($navUserPath);
    
    if (strpos($content, 'SimpleUserMenu') !== false) {
        echo "✅ SimpleUserMenu importado: SÍ\n";
    } else {
        echo "❌ SimpleUserMenu importado: NO\n";
        echo "   El componente no está siendo usado en NavUser.vue\n";
    }
} else {
    echo "❌ Archivo NavUser.vue: NO EXISTE\n";
}

// 3. Verificar rutas web
echo "\n3. VERIFICANDO RUTAS:\n";
$routesPath = __DIR__ . '/../routes/web.php';
if (file_exists($routesPath)) {
    echo "✅ Archivo routes/web.php: EXISTE\n";
    $content = file_get_contents($routesPath);
    
    if (strpos($content, 'logout') !== false) {
        echo "✅ Ruta logout: PRESENTE\n";
    } else {
        echo "❌ Ruta logout: NO ENCONTRADA\n";
    }
    
    if (strpos($content, 'profile.edit') !== false) {
        echo "✅ Ruta profile.edit: PRESENTE\n";
    } else {
        echo "❌ Ruta profile.edit: NO ENCONTRADA\n";
    }
} else {
    echo "❌ Archivo routes/web.php: NO EXISTE\n";
}

echo "\n4. INSTRUCCIONES DE DEBUG:\n";
echo "1. Abre las herramientas de desarrollador (F12)\n";
echo "2. Ve a la pestaña Console\n";
echo "3. Haz clic en el botón de usuario\n";
echo "4. Busca mensajes que digan 'Menu toggled', 'Button clicked!'\n";
echo "5. Si no hay mensajes, el problema está en el evento click\n";
echo "6. Si hay mensajes pero no se ve el menú, el problema está en el CSS\n";

echo "\n5. POSIBLES SOLUCIONES:\n";
echo "- Si SimpleUserMenu no está importado en NavUser.vue, agregarlo\n";
echo "- Si el evento click no funciona, verificar que el botón sea clickeable\n";
echo "- Si el menú no es visible, verificar z-index y posición\n";
echo "- Si las rutas no existen, agregarlas en routes/web.php\n";

echo "\n=== FIN DEL DIAGNÓSTICO ===\n"; 
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 PROBANDO MENÚ MINIMALISTA\n";
echo "============================\n\n";

try {
    // 1. Verificar usuario
    echo "👤 Verificando usuario...\n";
    
    $developer = \App\Models\User::whereHas('roles', function ($query) {
        $query->where('name', 'developer');
    })->with('roles')->first();
    
    if ($developer) {
        echo "   ✅ Usuario: {$developer->name}\n";
        echo "   ✅ Email: {$developer->email}\n";
        echo "   ✅ Roles: " . $developer->roles->pluck('name')->implode(', ') . "\n";
    } else {
        echo "   ❌ No se encontró usuario desarrollador\n";
        exit(1);
    }
    
    echo "\n";
    
    // 2. Instrucciones de prueba
    echo "🚀 INSTRUCCIONES PARA PROBAR EL MENÚ MINIMALISTA:\n";
    echo "=================================================\n\n";
    
    echo "1. Inicia el servidor: php artisan serve\n";
    echo "2. Ve a: http://localhost:8000\n";
    echo "3. Login como: developer1@example.com / password\n";
    echo "4. Abre la consola del navegador (F12) → Console\n";
    echo "5. Busca el ícono de usuario en la esquina inferior izquierda\n";
    echo "6. Haz clic en el ícono y verifica:\n";
    echo "   - Deberías ver: 'UserMenu mounted, user: [objeto]'\n";
    echo "   - Al hacer clic: 'Menu toggled: true'\n";
    echo "   - El menú debería aparecer con solo las opciones Settings y Log out\n";
    echo "   - El menú debería ser más compacto (160px de ancho)\n";
    echo "7. Haz clic en 'Log out' para probar\n\n";
    
    echo "📱 CARACTERÍSTICAS DEL MENÚ MINIMALISTA:\n";
    echo "- Solo ícono de usuario (40x40px)\n";
    echo "- Sin texto visible en el botón\n";
    echo "- Tooltip con el nombre del usuario al pasar el mouse\n";
    echo "- Menú compacto (160px de ancho)\n";
    echo "- Sin información del usuario en el menú\n";
    echo "- Solo opciones Settings y Log out\n";
    echo "- Efecto hover con escala (1.05x)\n";
    echo "- Transición suave en hover\n";
    
    echo "\n✅ PRUEBA LISTA\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 
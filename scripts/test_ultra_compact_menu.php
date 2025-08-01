<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 PROBANDO MENÚ ULTRA COMPACTO\n";
echo "===============================\n\n";

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
    echo "🚀 INSTRUCCIONES PARA PROBAR EL MENÚ ULTRA COMPACTO:\n";
    echo "====================================================\n\n";
    
    echo "1. Inicia el servidor: php artisan serve\n";
    echo "2. Ve a: http://localhost:8000\n";
    echo "3. Login como: developer1@example.com / password\n";
    echo "4. Abre la consola del navegador (F12) → Console\n";
    echo "5. Busca el ícono de usuario en la esquina inferior izquierda\n";
    echo "6. Haz clic en el ícono y verifica:\n";
    echo "   - Deberías ver: 'UserMenu mounted, user: [objeto]'\n";
    echo "   - Al hacer clic: 'Menu toggled: true'\n";
    echo "   - El menú debería aparecer con íconos muy pequeños\n";
    echo "   - El menú debería ser ultra compacto (48px de ancho)\n";
    echo "   - Solo íconos de Settings (engranaje) y Log out (flecha)\n";
    echo "7. Pasa el mouse sobre los íconos para ver tooltips\n";
    echo "8. Haz clic en el ícono de logout para probar\n\n";
    
    echo "📱 CARACTERÍSTICAS DEL MENÚ ULTRA COMPACTO:\n";
    echo "- Botón principal: ícono de usuario (32x32px)\n";
    echo "- Menú desplegable: solo 48px de ancho\n";
    echo "- Opciones: íconos muy pequeños (14x14px)\n";
    echo "- Settings: ícono de engranaje con tooltip\n";
    echo "- Logout: ícono de flecha con tooltip\n";
    echo "- Efecto hover: escala 1.02x en botón principal\n";
    echo "- Efecto hover: escala 1.05x en opciones del menú\n";
    echo "- Transiciones más rápidas (0.15s)\n";
    echo "- Bordes más pequeños (1px outline)\n";
    echo "- Márgenes reducidos (1px)\n";
    
    echo "\n✅ PRUEBA LISTA\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 
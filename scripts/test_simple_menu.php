<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 PROBANDO MENÚ SIMPLIFICADO\n";
echo "=============================\n\n";

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
    echo "🚀 INSTRUCCIONES PARA PROBAR EL MENÚ:\n";
    echo "=====================================\n\n";
    
    echo "1. Inicia el servidor: php artisan serve\n";
    echo "2. Ve a: http://localhost:8000\n";
    echo "3. Login como: developer1@example.com / password\n";
    echo "4. Abre la consola del navegador (F12) → Console\n";
    echo "5. Busca el botón de usuario en la esquina inferior izquierda\n";
    echo "6. Haz clic en el botón y verifica:\n";
    echo "   - Deberías ver: 'UserMenu mounted, user: [objeto]'\n";
    echo "   - Al hacer clic: 'Menu toggled: true'\n";
    echo "   - El menú debería aparecer inmediatamente\n";
    echo "7. Haz clic en 'Log out' para probar\n\n";
    
    echo "🔧 SI EL BOTÓN SIGUE SIN FUNCIONAR:\n";
    echo "1. Verifica la consola del navegador (F12) → Console\n";
    echo "2. Busca errores en rojo\n";
    echo "3. Verifica que el botón tenga cursor pointer\n";
    echo "4. Intenta hacer clic derecho en el botón\n";
    echo "5. Usa la tecla Tab para navegar al botón\n";
    echo "6. Verifica que no haya elementos superpuestos\n";
    echo "7. Intenta hacer clic en diferentes partes del botón\n\n";
    
    echo "📱 CARACTERÍSTICAS DEL MENÚ SIMPLIFICADO:\n";
    echo "- Botón con cursor pointer forzado\n";
    echo "- v-show en lugar de v-if para el menú\n";
    echo "- CSS inline para asegurar visibilidad\n";
    echo "- Eventos simplificados sin preventDefault\n";
    echo "- Logs de debug para diagnóstico\n";
    echo "- Z-index alto (9999) para asegurar visibilidad\n";
    
    echo "\n✅ PRUEBA LISTA\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
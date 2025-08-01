<?php

/**
 * Script para configurar la base de datos con datos de prueba
 * Ejecuta migraciones y seeders de forma segura
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🗃️  CONFIGURANDO BASE DE DATOS CON DATOS DE PRUEBA\n";
echo "==================================================\n\n";

// Configurar la aplicación
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "✅ Aplicación inicializada\n\n";

try {
    // Verificar conexión a la base de datos
    echo "🔍 Verificando conexión a la base de datos...\n";
    \DB::connection()->getPdo();
    echo "✅ Conexión a la base de datos establecida\n\n";

    // Ejecutar migraciones
    echo "🔄 Ejecutando migraciones...\n";
    $output = shell_exec('php artisan migrate:fresh --force 2>&1');
    echo $output;
    echo "✅ Migraciones ejecutadas\n\n";

    // Ejecutar seeders
    echo "🌱 Ejecutando seeders...\n";
    $output = shell_exec('php artisan db:seed --force 2>&1');
    echo $output;
    echo "✅ Seeders ejecutados\n\n";

    // Verificar datos creados
    echo "📊 Verificando datos creados...\n";
    
    $userCount = \App\Models\User::count();
    $projectCount = \App\Models\Project::count();
    $taskCount = \App\Models\Task::count();
    $sprintCount = \App\Models\Sprint::count();
    
    echo "✅ Usuarios creados: {$userCount}\n";
    echo "✅ Proyectos creados: {$projectCount}\n";
    echo "✅ Tareas creadas: {$taskCount}\n";
    echo "✅ Sprints creados: {$sprintCount}\n\n";

    echo "🎉 ¡BASE DE DATOS CONFIGURADA EXITOSAMENTE!\n";
    echo "==================================================\n\n";

    echo "🎯 CREDENCIALES DE PRUEBA:\n";
    echo "---------------------------\n";
    echo "👨‍💻 ADMINISTRADORES:\n";
    echo "   admin@example.com / password\n";
    echo "   admin2@example.com / password\n\n";
    echo "👨‍💼 TEAM LEADERS:\n";
    echo "   teamleader1@example.com / password\n";
    echo "   teamleader2@example.com / password\n";
    echo "   teamleader3@example.com / password\n\n";
    echo "👨‍💻 DESARROLLADORES:\n";
    echo "   developer1@example.com / password\n";
    echo "   developer2@example.com / password\n";
    echo "   developer3@example.com / password\n";
    echo "   developer4@example.com / password\n";
    echo "   developer5@example.com / password\n\n";

    echo "🚀 PRÓXIMOS PASOS:\n";
    echo "------------------\n";
    echo "1. Compilar assets: npm run build\n";
    echo "2. Iniciar servidor: php artisan serve\n";
    echo "3. Acceder a: http://localhost:8000\n";
    echo "4. Usar las credenciales de arriba para probar\n\n";

    echo "✅ ¡Listo para probar todas las funcionalidades!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n💡 SUGERENCIAS:\n";
    echo "1. Verifica la configuración de la base de datos en .env\n";
    echo "2. Asegúrate de que la base de datos existe\n";
    echo "3. Verifica que las credenciales son correctas\n";
    echo "4. Ejecuta: php artisan config:clear\n";
} 
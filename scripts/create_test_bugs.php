<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Bug;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\User;

echo "=== CREANDO BUGS DE PRUEBA ===\n\n";

// Obtener proyectos y sprints
$projects = Project::all();
$sprints = Sprint::all();

if ($projects->count() === 0) {
    echo "❌ No hay proyectos disponibles. Crea algunos proyectos primero.\n";
    exit(1);
}

if ($sprints->count() === 0) {
    echo "❌ No hay sprints disponibles. Crea algunos sprints primero.\n";
    exit(1);
}

// Bugs de prueba
$testBugs = [
    [
        'title' => 'Error en formulario de login',
        'description' => 'El formulario de login no valida correctamente las credenciales',
        'long_description' => 'Cuando un usuario intenta iniciar sesión con credenciales incorrectas, el sistema no muestra el mensaje de error apropiado y en su lugar redirige a una página en blanco.',
        'importance' => 'high',
        'bug_type' => 'frontend',
        'environment' => 'production',
        'browser_info' => 'Chrome 120.0.0.0',
        'os_info' => 'Windows 11',
        'steps_to_reproduce' => "1. Ir a la página de login\n2. Ingresar credenciales incorrectas\n3. Hacer clic en 'Iniciar Sesión'\n4. Observar que no se muestra mensaje de error",
        'expected_behavior' => 'Debería mostrar un mensaje de error indicando credenciales inválidas',
        'actual_behavior' => 'Redirige a una página en blanco sin mostrar error',
        'reproducibility' => 'always',
        'severity' => 'high',
        'estimated_hours' => 2,
        'estimated_minutes' => 30,
        'tags' => 'login,formulario,validación'
    ],
    [
        'title' => 'Problema de rendimiento en listado de usuarios',
        'description' => 'La página de listado de usuarios tarda más de 10 segundos en cargar',
        'long_description' => 'El listado de usuarios en el panel de administración está tomando demasiado tiempo para cargar, lo que afecta la experiencia del usuario.',
        'importance' => 'medium',
        'bug_type' => 'performance',
        'environment' => 'staging',
        'browser_info' => 'Firefox 121.0',
        'os_info' => 'macOS 14.0',
        'steps_to_reproduce' => "1. Ir al panel de administración\n2. Navegar a 'Usuarios'\n3. Esperar a que cargue la lista\n4. Medir el tiempo de carga",
        'expected_behavior' => 'La página debería cargar en menos de 3 segundos',
        'actual_behavior' => 'La página tarda más de 10 segundos en cargar completamente',
        'reproducibility' => 'sometimes',
        'severity' => 'medium',
        'estimated_hours' => 4,
        'estimated_minutes' => 0,
        'tags' => 'rendimiento,listado,usuarios'
    ],
    [
        'title' => 'Error 500 al crear nuevo proyecto',
        'description' => 'Se produce un error 500 cuando se intenta crear un nuevo proyecto',
        'long_description' => 'Al intentar crear un nuevo proyecto desde el panel de administración, el sistema devuelve un error 500 y no se crea el proyecto.',
        'importance' => 'critical',
        'bug_type' => 'backend',
        'environment' => 'production',
        'browser_info' => 'Safari 17.0',
        'os_info' => 'iOS 17.0',
        'steps_to_reproduce' => "1. Ir al panel de administración\n2. Hacer clic en 'Crear Proyecto'\n3. Llenar el formulario\n4. Hacer clic en 'Guardar'\n5. Observar error 500",
        'expected_behavior' => 'El proyecto debería crearse exitosamente',
        'actual_behavior' => 'Se muestra un error 500 y el proyecto no se crea',
        'reproducibility' => 'always',
        'severity' => 'critical',
        'estimated_hours' => 6,
        'estimated_minutes' => 0,
        'tags' => 'proyecto,creación,error-500'
    ],
    [
        'title' => 'Problema de diseño en móviles',
        'description' => 'El diseño no se adapta correctamente en dispositivos móviles',
        'long_description' => 'La interfaz de usuario no se adapta correctamente a pantallas pequeñas, causando problemas de usabilidad en dispositivos móviles.',
        'importance' => 'medium',
        'bug_type' => 'ui_ux',
        'environment' => 'production',
        'browser_info' => 'Chrome Mobile 120.0.0.0',
        'os_info' => 'Android 13',
        'steps_to_reproduce' => "1. Abrir la aplicación en un dispositivo móvil\n2. Navegar por las diferentes páginas\n3. Observar problemas de diseño",
        'expected_behavior' => 'La interfaz debería ser responsive y funcionar bien en móviles',
        'actual_behavior' => 'Elementos se superponen y botones son difíciles de usar',
        'reproducibility' => 'always',
        'severity' => 'medium',
        'estimated_hours' => 8,
        'estimated_minutes' => 0,
        'tags' => 'diseño,móvil,responsive'
    ],
    [
        'title' => 'Problema de seguridad en API',
        'description' => 'La API no valida correctamente los tokens de autenticación',
        'long_description' => 'Se ha detectado que la API no está validando correctamente los tokens de autenticación, lo que podría permitir acceso no autorizado.',
        'importance' => 'critical',
        'bug_type' => 'security',
        'environment' => 'production',
        'browser_info' => 'Postman',
        'os_info' => 'Windows 10',
        'steps_to_reproduce' => "1. Usar Postman para hacer una petición a la API\n2. Enviar un token inválido\n3. Observar que la petición es exitosa",
        'expected_behavior' => 'Debería rechazar peticiones con tokens inválidos',
        'actual_behavior' => 'Acepta peticiones con tokens inválidos',
        'reproducibility' => 'always',
        'severity' => 'critical',
        'estimated_hours' => 3,
        'estimated_minutes' => 0,
        'tags' => 'seguridad,api,autenticación'
    ]
];

$createdCount = 0;

foreach ($testBugs as $index => $bugData) {
    // Seleccionar proyecto y sprint aleatorio
    $project = $projects->random();
    $sprint = $sprints->where('project_id', $project->id)->first();
    
    if (!$sprint) {
        $sprint = $sprints->random();
    }
    
    // Crear el bug
    $bug = Bug::create([
        'title' => $bugData['title'],
        'description' => $bugData['description'],
        'long_description' => $bugData['long_description'],
        'importance' => $bugData['importance'],
        'bug_type' => $bugData['bug_type'],
        'environment' => $bugData['environment'],
        'browser_info' => $bugData['browser_info'],
        'os_info' => $bugData['os_info'],
        'steps_to_reproduce' => $bugData['steps_to_reproduce'],
        'expected_behavior' => $bugData['expected_behavior'],
        'actual_behavior' => $bugData['actual_behavior'],
        'reproducibility' => $bugData['reproducibility'],
        'severity' => $bugData['severity'],
        'sprint_id' => $sprint->id,
        'project_id' => $project->id,
        'estimated_hours' => $bugData['estimated_hours'],
        'estimated_minutes' => $bugData['estimated_minutes'],
        'tags' => $bugData['tags'],
        'status' => 'new',
        'qa_status' => 'testing',
        'priority_score' => rand(1, 20)
    ]);
    
    echo "✅ Bug creado: '{$bugData['title']}' en proyecto '{$project->name}'\n";
    $createdCount++;
}

echo "\n=== RESUMEN ===\n";
echo "Total bugs creados: {$createdCount}\n";
echo "Total bugs en el sistema: " . Bug::count() . "\n";

echo "\n=== INSTRUCCIONES PARA PROBAR ===\n";
echo "1. Inicia sesión con cualquier usuario (admin@test.com / password)\n";
echo "2. Ve a la sección de Bugs\n";
echo "3. Haz clic en cualquier bug para ver sus detalles\n";
echo "4. En la sección 'Assignment' verás las opciones de asignación:\n";
echo "   - 'Assign to Me' (para auto-asignarte)\n";
echo "   - Selector para asignar a otros usuarios\n";
echo "   - 'Unassign' (para desasignar)\n";
echo "5. Prueba las diferentes funcionalidades según tu rol\n";

echo "\n✅ Bugs de prueba creados exitosamente!\n"; 
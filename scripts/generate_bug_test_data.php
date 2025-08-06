<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Bug;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 Generando datos de prueba para Bugs...\n";

try {
    DB::beginTransaction();

    // Obtener proyectos y sprints existentes
    $projects = Project::all();
    $sprints = Sprint::with('project')->get();
    $developers = User::whereHas('roles', function ($query) {
        $query->whereIn('name', ['developer', 'team_leader']);
    })->get();

    if ($projects->isEmpty()) {
        echo "❌ No hay proyectos disponibles. Ejecuta primero el script de generación de datos básicos.\n";
        exit(1);
    }

    if ($sprints->isEmpty()) {
        echo "❌ No hay sprints disponibles. Ejecuta primero el script de generación de datos básicos.\n";
        exit(1);
    }

    if ($developers->isEmpty()) {
        echo "❌ No hay desarrolladores disponibles. Ejecuta primero el script de generación de datos básicos.\n";
        exit(1);
    }

    // Tipos de bugs
    $bugTypes = ['frontend', 'backend', 'database', 'api', 'ui_ux', 'performance', 'security', 'other'];
    $importances = ['low', 'medium', 'high', 'critical'];
    $statuses = ['new', 'assigned', 'in progress', 'resolved', 'verified', 'closed', 'reopened'];
    $reproducibilities = ['always', 'sometimes', 'rarely', 'unable'];
    $severities = ['low', 'medium', 'high', 'critical'];
    $environments = ['development', 'staging', 'production'];
    $browsers = ['Chrome 120.0', 'Firefox 121.0', 'Safari 17.2', 'Edge 120.0'];
    $operatingSystems = ['Windows 11', 'macOS 14.0', 'Ubuntu 22.04', 'iOS 17.0', 'Android 14'];

    // Títulos de bugs de ejemplo
    $bugTitles = [
        'Error 404 en página de contacto',
        'Botón de login no responde',
        'Datos no se guardan en formulario',
        'Imagen no se carga en móviles',
        'API retorna error 500',
        'Validación de email falla',
        'Menú desplegable no funciona',
        'Página se congela al cargar',
        'Datos duplicados en reporte',
        'Sesión expira muy rápido',
        'Botón de descarga no funciona',
        'Filtros no aplican correctamente',
        'Error en cálculo de totales',
        'Imagen se distorsiona en tablet',
        'Formulario no valida campos requeridos',
        'API no acepta caracteres especiales',
        'Página no es responsive',
        'Datos no se actualizan en tiempo real',
        'Error al subir archivos grandes',
        'Menú hamburguesa no se abre',
    ];

    // Descripciones de bugs
    $bugDescriptions = [
        'El usuario reporta que al hacer clic en el botón de contacto aparece un error 404.',
        'El botón de login no responde cuando se hace clic, no hay feedback visual.',
        'Los datos ingresados en el formulario no se guardan al enviar.',
        'Las imágenes no se cargan correctamente en dispositivos móviles.',
        'La API está retornando error 500 en algunas consultas.',
        'La validación de email no funciona correctamente con ciertos formatos.',
        'El menú desplegable no se despliega al hacer hover.',
        'La página se congela completamente al cargar con muchos datos.',
        'El reporte muestra datos duplicados en algunas filas.',
        'La sesión del usuario expira muy rápidamente.',
        'El botón de descarga no inicia la descarga del archivo.',
        'Los filtros no aplican correctamente los criterios seleccionados.',
        'Hay un error en el cálculo de los totales del dashboard.',
        'Las imágenes se distorsionan cuando se ve en tablet.',
        'El formulario no valida los campos marcados como requeridos.',
        'La API no acepta caracteres especiales en los parámetros.',
        'La página no se adapta correctamente a diferentes tamaños de pantalla.',
        'Los datos no se actualizan en tiempo real como se esperaba.',
        'Error al intentar subir archivos de más de 10MB.',
        'El menú hamburguesa no se abre en dispositivos móviles.',
    ];

    // Pasos para reproducir
    $stepsToReproduce = [
        '1. Ir a la página de contacto\n2. Hacer clic en "Enviar"\n3. Observar error 404',
        '1. Ir a la página de login\n2. Ingresar credenciales\n3. Hacer clic en "Iniciar Sesión"\n4. Observar que no hay respuesta',
        '1. Llenar el formulario\n2. Hacer clic en "Guardar"\n3. Verificar que los datos no se guardan',
        '1. Abrir la página en un dispositivo móvil\n2. Navegar a la sección de imágenes\n3. Observar que las imágenes no cargan',
        '1. Hacer una petición a la API\n2. Observar respuesta 500\n3. Revisar logs del servidor',
        '1. Intentar registrar con email inválido\n2. Observar que no se valida correctamente',
        '1. Hacer hover sobre el menú\n2. Observar que no se despliega',
        '1. Cargar la página con muchos datos\n2. Observar que se congela',
        '1. Generar reporte\n2. Revisar datos duplicados',
        '1. Iniciar sesión\n2. Esperar 5 minutos\n3. Observar que la sesión expira',
        '1. Hacer clic en botón de descarga\n2. Observar que no descarga',
        '1. Aplicar filtros\n2. Observar que no funcionan',
        '1. Revisar dashboard\n2. Verificar cálculos incorrectos',
        '1. Ver página en tablet\n2. Observar distorsión de imágenes',
        '1. Dejar campos requeridos vacíos\n2. Intentar enviar\n3. Observar que no valida',
        '1. Enviar caracteres especiales a la API\n2. Observar error',
        '1. Cambiar tamaño de ventana\n2. Observar que no es responsive',
        '1. Actualizar datos\n2. Observar que no se actualizan en tiempo real',
        '1. Intentar subir archivo de 15MB\n2. Observar error',
        '1. Abrir en móvil\n2. Hacer clic en menú hamburguesa\n3. Observar que no se abre',
    ];

    $bugsCreated = 0;
    $totalBugs = 20;

    for ($i = 0; $i < $totalBugs; $i++) {
        $project = $projects->random();
        $sprint = $sprints->where('project_id', $project->id)->random();
        $developer = $developers->random();
        
        $bugType = $bugTypes[array_rand($bugTypes)];
        $importance = $importances[array_rand($importances)];
        $status = $statuses[array_rand($statuses)];
        $reproducibility = $reproducibilities[array_rand($reproducibilities)];
        $severity = $severities[array_rand($severities)];
        $environment = $environments[array_rand($environments)];
        $browser = $browsers[array_rand($browsers)];
        $os = $operatingSystems[array_rand($operatingSystems)];

        // Calcular priority score
        $importanceScore = match($importance) {
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 1,
        };

        $severityScore = match($severity) {
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 1,
        };

        $reproducibilityScore = match($reproducibility) {
            'always' => 4,
            'sometimes' => 3,
            'rarely' => 2,
            'unable' => 1,
            default => 2,
        };

        $priorityScore = ($importanceScore * 3) + ($severityScore * 2) + $reproducibilityScore;

        // Determinar si asignar o no
        $isAssigned = $status !== 'new';
        $assignedUser = $isAssigned ? $developer : null;
        $assignedBy = $isAssigned ? $developer : null;
        $assignedAt = $isAssigned ? now()->subDays(rand(1, 30)) : null;

        // Tiempo estimado y real
        $estimatedHours = rand(1, 8);
        $estimatedMinutes = rand(0, 59);
        $actualHours = $status === 'resolved' || $status === 'verified' || $status === 'closed' ? rand(1, 12) : 0;
        $actualMinutes = $status === 'resolved' || $status === 'verified' || $status === 'closed' ? rand(0, 59) : 0;
        $totalTimeSeconds = ($actualHours * 3600) + ($actualMinutes * 60);

        // Fechas de resolución y verificación
        $resolvedAt = null;
        $verifiedAt = null;
        $resolvedBy = null;
        $verifiedBy = null;

        if (in_array($status, ['resolved', 'verified', 'closed'])) {
            $resolvedAt = now()->subDays(rand(1, 15));
            $resolvedBy = $developer;
            
            if (in_array($status, ['verified', 'closed'])) {
                $verifiedAt = $resolvedAt->addDays(rand(1, 3));
                $verifiedBy = $developers->random();
            }
        }

        // Archivos adjuntos (simulados)
        $attachments = [];
        if (rand(0, 1)) {
            $attachments = [
                [
                    'name' => 'screenshot_' . rand(1, 999) . '.png',
                    'path' => 'bug-attachments/screenshot_' . rand(1, 999) . '.png',
                    'size' => rand(100000, 2000000),
                    'type' => 'image/png',
                ]
            ];
        }

        $bug = Bug::create([
            'title' => $bugTitles[$i],
            'description' => $bugDescriptions[$i],
            'long_description' => $bugDescriptions[$i] . "\n\nEste es un bug que requiere atención inmediata. Se ha reportado por múltiples usuarios y afecta la funcionalidad principal del sistema.",
            'importance' => $importance,
            'bug_type' => $bugType,
            'environment' => $environment,
            'browser_info' => $browser,
            'os_info' => $os,
            'steps_to_reproduce' => $stepsToReproduce[$i],
            'expected_behavior' => 'El sistema debería funcionar correctamente sin errores.',
            'actual_behavior' => $bugDescriptions[$i],
            'reproducibility' => $reproducibility,
            'severity' => $severity,
            'sprint_id' => $sprint->id,
            'project_id' => $project->id,
            'user_id' => $assignedUser ? $assignedUser->id : null,
            'assigned_by' => $assignedBy ? $assignedBy->id : null,
            'assigned_at' => $assignedAt,
            'estimated_hours' => $estimatedHours,
            'estimated_minutes' => $estimatedMinutes,
            'actual_hours' => $actualHours,
            'actual_minutes' => $actualMinutes,
            'total_time_seconds' => $totalTimeSeconds,
            'status' => $status,
            'priority_score' => $priorityScore,
            'tags' => implode(',', [$bugType, $environment, $importance]),
            'attachments' => $attachments,
            'resolved_by' => $resolvedBy ? $resolvedBy->id : null,
            'resolved_at' => $resolvedAt,
            'verified_by' => $verifiedBy ? $verifiedBy->id : null,
            'verified_at' => $verifiedAt,
            'resolution_notes' => $status === 'resolved' || $status === 'verified' || $status === 'closed' 
                ? 'Bug resuelto correctamente. Se implementó la solución y se realizaron las pruebas correspondientes.' 
                : null,
        ]);

        $bugsCreated++;
        echo "✅ Bug creado: {$bug->title}\n";
    }

    DB::commit();

    echo "\n🎉 ¡Datos de prueba generados exitosamente!\n";
    echo "📊 Resumen:\n";
    echo "   - Bugs creados: {$bugsCreated}\n";
    echo "   - Proyectos utilizados: {$projects->count()}\n";
    echo "   - Sprints utilizados: {$sprints->count()}\n";
    echo "   - Desarrolladores utilizados: {$developers->count()}\n";

    // Mostrar estadísticas
    $stats = [
        'total_bugs' => Bug::count(),
        'new_bugs' => Bug::where('status', 'new')->count(),
        'assigned_bugs' => Bug::where('status', 'assigned')->count(),
        'in_progress_bugs' => Bug::where('status', 'in progress')->count(),
        'resolved_bugs' => Bug::where('status', 'resolved')->count(),
        'verified_bugs' => Bug::where('status', 'verified')->count(),
        'closed_bugs' => Bug::where('status', 'closed')->count(),
        'critical_bugs' => Bug::where('importance', 'critical')->count(),
        'high_priority_bugs' => Bug::where('importance', 'high')->count(),
    ];

    echo "\n📈 Estadísticas de Bugs:\n";
    echo "   - Total: {$stats['total_bugs']}\n";
    echo "   - Nuevos: {$stats['new_bugs']}\n";
    echo "   - Asignados: {$stats['assigned_bugs']}\n";
    echo "   - En progreso: {$stats['in_progress_bugs']}\n";
    echo "   - Resueltos: {$stats['resolved_bugs']}\n";
    echo "   - Verificados: {$stats['verified_bugs']}\n";
    echo "   - Cerrados: {$stats['closed_bugs']}\n";
    echo "   - Críticos: {$stats['critical_bugs']}\n";
    echo "   - Alta prioridad: {$stats['high_priority_bugs']}\n";

} catch (Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
    echo "📁 Archivo: " . $e->getFile() . "\n";
    exit(1);
} 
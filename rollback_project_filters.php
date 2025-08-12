<?php

echo "🔄 ROLLBACK DE FILTROS DE PROYECTO EN REPORTES DE PAGOS\n";
echo "======================================================\n\n";

echo "Este script revertirá todos los cambios relacionados con los filtros de proyecto\n";
echo "en los reportes de pagos.\n\n";

echo "¿Estás seguro de que quieres continuar? (y/N): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim($line) !== 'y' && trim($line) !== 'Y') {
    echo "Operación cancelada.\n";
    exit(0);
}

echo "\n🔄 Iniciando rollback...\n\n";

// 1. Crear backups
echo "1. Creando backups de archivos modificados...\n";
$backupDir = 'backups/' . date('Y-m-d_H-i-s') . '_project_filters_rollback';
if (!is_dir('backups')) {
    mkdir('backups');
}
mkdir($backupDir);

$filesToBackup = [
    'routes/web.php',
    'app/Http/Controllers/ProjectController.php',
    'app/Http/Controllers/PaymentController.php',
    'app/Services/PaymentService.php'
];

foreach ($filesToBackup as $file) {
    if (file_exists($file)) {
        $backupPath = $backupDir . '/' . basename($file);
        copy($file, $backupPath);
        echo "   ✅ Backup creado: $backupPath\n";
    }
}

echo "\n2. Revertiendo cambios...\n";

// 2. Revertir routes/web.php
echo "   🔄 Revertiendo routes/web.php...\n";
$routesContent = file_get_contents('routes/web.php');

// Remover el endpoint API de proyectos
$routesContent = preg_replace(
    '/\/\/ API routes for reports\s*Route::middleware\(\'permission:reports\.view\'\)->group\(function \(\) \{\s*Route::get\(\'\/api\/projects\', \[ProjectController::class, \'getProjectsForReports\'\]\)->name\(\'api\.projects\'\);\s*\};/s',
    '',
    $routesContent
);

file_put_contents('routes/web.php', $routesContent);
echo "   ✅ routes/web.php revertido\n";

// 3. Revertir ProjectController.php
echo "   🔄 Revertiendo ProjectController.php...\n";
$projectControllerContent = file_get_contents('app/Http/Controllers/ProjectController.php');

// Remover el método getProjectsForReports
$projectControllerContent = preg_replace(
    '/\s*\/\*\*\s*\* Get projects for reports API endpoint\s*\*\/\s*public function getProjectsForReports\(\)\s*\{[^}]*\}/s',
    '',
    $projectControllerContent
);

file_put_contents('app/Http/Controllers/ProjectController.php', $projectControllerContent);
echo "   ✅ ProjectController.php revertido\n";

// 4. Revertir PaymentController.php
echo "   🔄 Revertiendo PaymentController.php...\n";
$paymentControllerContent = file_get_contents('app/Http/Controllers/PaymentController.php');

// Remover validación de project_id y report_type
$paymentControllerContent = preg_replace(
    '/\'project_id\' => \'nullable\|exists:projects,id\',\s*\'report_type\' => \'nullable\|in:developers,project,role,week\',/',
    '',
    $paymentControllerContent
);

// Remover filtros de proyecto en la consulta de desarrolladores
$paymentControllerContent = preg_replace(
    '/\/\/ Filtrar desarrolladores por proyecto si se especifica\s*\$developerQuery = User::whereIn\(\'id\', \$request->developer_ids\);\s*if \(\$request->project_id\) \{\s*\$developerQuery->whereHas\(\'projects\', function \(\$query\) use \(\$request\) \{\s*\$query->where\(\'projects\.id\', \$request->project_id\);\s*\}\);\s*\}\s*\$developers = \$developerQuery->get\(\)->map\(function \(\$developer\) use \(\$startDate, \$endDate, \$request\) \{/',
    '$developers = User::whereIn(\'id\', $request->developer_ids)->get()->map(function ($developer) use ($startDate, $endDate) {',
    $paymentControllerContent
);

// Remover project_id de las llamadas al PaymentService
$paymentControllerContent = preg_replace(
    '/\$this->paymentService->generateReportForDateRange\(\$developer, \$startDate, \$endDate, \$request->project_id\);/',
    '$this->paymentService->generateReportForDateRange($developer, $startDate, $endDate);',
    $paymentControllerContent
);

file_put_contents('app/Http/Controllers/PaymentController.php', $paymentControllerContent);
echo "   ✅ PaymentController.php revertido\n";

// 5. Revertir PaymentService.php
echo "   🔄 Revertiendo PaymentService.php...\n";
$paymentServiceContent = file_get_contents('app/Services/PaymentService.php');

// Remover parámetro projectId del método generateReportForDateRange
$paymentServiceContent = preg_replace(
    '/public function generateReportForDateRange\(User \$user, Carbon \$startDate, Carbon \$endDate, \$projectId = null\): PaymentReport/',
    'public function generateReportForDateRange(User $user, Carbon $startDate, Carbon $endDate): PaymentReport',
    $paymentServiceContent
);

// Remover filtros de proyecto de todas las consultas
$paymentServiceContent = preg_replace(
    '/\$completedTasksQuery = \$user->tasks\(\)\s*->where\(\'status\', \'done\'\)\s*->whereBetween\(\'actual_finish\', \[\$startDate, \$endDate\]\);\s*if \(\$projectId\) \{\s*\$completedTasksQuery->whereHas\(\'sprint\.project\', function \(\$query\) use \(\$projectId\) \{\s*\$query->where\(\'id\', \$projectId\);\s*\}\);\s*\}\s*\$completedTasks = \$completedTasksQuery->get\(\);/',
    '$completedTasks = $user->tasks()\n            ->where(\'status\', \'done\')\n            ->whereBetween(\'actual_finish\', [$startDate, $endDate])\n            ->get();',
    $paymentServiceContent
);

$paymentServiceContent = preg_replace(
    '/\$inProgressTasksQuery = \$user->tasks\(\)\s*->where\(\'status\', \'in progress\'\)\s*->where\(\'total_time_seconds\', \'>\', 0\)\s*->where\(function \(\$query\) use \(\$startDate, \$endDate\) \{\s*\$query->whereBetween\(\'actual_start\', \[\$startDate, \$endDate\]\)\s*->orWhereBetween\(\'updated_at\', \[\$startDate, \$endDate\]\);\s*\}\);\s*if \(\$projectId\) \{\s*\$inProgressTasksQuery->whereHas\(\'sprint\.project\', function \(\$query\) use \(\$projectId\) \{\s*\$query->where\(\'id\', \$projectId\);\s*\}\);\s*\}\s*\$inProgressTasks = \$inProgressTasksQuery->get\(\);/',
    '$inProgressTasks = $user->tasks()\n            ->where(\'status\', \'in progress\')\n            ->where(\'total_time_seconds\', \'>\', 0)\n            ->where(function ($query) use ($startDate, $endDate) {\n                $query->whereBetween(\'actual_start\', [$startDate, $endDate])\n                      ->orWhereBetween(\'updated_at\', [$startDate, $endDate]);\n            })\n            ->get();',
    $paymentServiceContent
);

$paymentServiceContent = preg_replace(
    '/\$completedBugsQuery = \$user->bugs\(\)\s*->whereIn\(\'status\', \[\'resolved\', \'verified\', \'closed\'\]\)\s*->whereBetween\(\'resolved_at\', \[\$startDate, \$endDate\]\);\s*if \(\$projectId\) \{\s*\$completedBugsQuery->whereHas\(\'sprint\.project\', function \(\$query\) use \(\$projectId\) \{\s*\$query->where\(\'id\', \$projectId\);\s*\}\);\s*\}\s*\$completedBugs = \$completedBugsQuery->get\(\);/',
    '$completedBugs = $user->bugs()\n            ->whereIn(\'status\', [\'resolved\', \'verified\', \'closed\'])\n            ->whereBetween(\'resolved_at\', [$startDate, $endDate])\n            ->get();',
    $paymentServiceContent
);

$paymentServiceContent = preg_replace(
    '/\$inProgressBugsQuery = \$user->bugs\(\)\s*->whereIn\(\'status\', \[\'assigned\', \'in progress\'\]\)\s*->where\(\'total_time_seconds\', \'>\', 0\)\s*->where\(function \(\$query\) use \(\$startDate, \$endDate\) \{\s*\$query->whereBetween\(\'assigned_at\', \[\$startDate, \$endDate\]\)\s*->orWhereBetween\(\'updated_at\', \[\$startDate, \$endDate\]\);\s*\}\);\s*if \(\$projectId\) \{\s*\$inProgressBugsQuery->whereHas\(\'sprint\.project\', function \(\$query\) use \(\$projectId\) \{\s*\$query->where\(\'id\', \$projectId\);\s*\}\);\s*\}\s*\$inProgressBugs = \$inProgressBugsQuery->get\(\);/',
    '$inProgressBugs = $user->bugs()\n            ->whereIn(\'status\', [\'assigned\', \'in progress\'])\n            ->where(\'total_time_seconds\', \'>\', 0)\n            ->where(function ($query) use ($startDate, $endDate) {\n                $query->whereBetween(\'assigned_at\', [$startDate, $endDate])\n                      ->orWhereBetween(\'updated_at\', [$startDate, $endDate]);\n            })\n            ->get();',
    $paymentServiceContent
);

file_put_contents('app/Services/PaymentService.php', $paymentServiceContent);
echo "   ✅ PaymentService.php revertido\n";

// 6. Limpiar caché
echo "\n3. Limpiando caché de Laravel...\n";
system('php artisan route:clear');
system('php artisan config:clear');
system('php artisan cache:clear');
echo "   ✅ Caché limpiado\n";

// 7. Verificar integridad
echo "\n4. Verificando integridad...\n";
$errors = [];

// Verificar que los archivos existen y son válidos
foreach ($filesToBackup as $file) {
    if (!file_exists($file)) {
        $errors[] = "❌ Archivo no encontrado: $file";
    }
}

if (empty($errors)) {
    echo "   ✅ Todos los archivos están presentes y válidos\n";
} else {
    echo "   ⚠️  Se encontraron algunos problemas:\n";
    foreach ($errors as $error) {
        echo "      $error\n";
    }
}

echo "\n✅ ROLLBACK COMPLETADO\n";
echo "=====================\n";
echo "Los cambios han sido revertidos. Los backups están en: $backupDir\n";
echo "Si necesitas restaurar algún archivo, puedes copiarlo desde el directorio de backup.\n\n";

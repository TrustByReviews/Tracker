<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ACTUALIZANDO VALORES DE QA_STATUS ===\n\n";

// Actualizar valores 'pending' a 'ready_for_test' en tasks
$updatedTasks = DB::table('tasks')->where('qa_status', 'pending')->update(['qa_status' => 'ready_for_test']);
echo "Tareas actualizadas de 'pending' a 'ready_for_test': {$updatedTasks}\n";

// Actualizar valores 'pending' a 'ready_for_test' en bugs
$updatedBugs = DB::table('bugs')->where('qa_status', 'pending')->update(['qa_status' => 'ready_for_test']);
echo "Bugs actualizados de 'pending' a 'ready_for_test': {$updatedBugs}\n";

echo "\n=== VALORES ACTUALIZADOS ===\n";

// Verificar valores en tasks
echo "Valores de qa_status en tasks:\n";
$taskStatuses = DB::table('tasks')->select('qa_status')->distinct()->get();
foreach ($taskStatuses as $status) {
    echo "  - {$status->qa_status}\n";
}

echo "\nValores de qa_status en bugs:\n";
$bugStatuses = DB::table('bugs')->select('qa_status')->distinct()->get();
foreach ($bugStatuses as $status) {
    echo "  - {$status->qa_status}\n";
}

echo "\n=== FIN ===\n"; 
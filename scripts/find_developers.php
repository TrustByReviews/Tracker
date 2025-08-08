<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== BUSCANDO DESARROLLADORES ===\n\n";

$developers = User::whereHas('roles', function ($query) {
    $query->where('name', 'developer');
})->get(['name', 'email']);

echo "Desarrolladores encontrados:\n";
foreach ($developers as $dev) {
    echo "- {$dev->name} ({$dev->email})\n";
}

echo "\n=== BUSCANDO QAS ===\n\n";

$qas = User::whereHas('roles', function ($query) {
    $query->where('name', 'qa');
})->get(['name', 'email']);

echo "QAs encontrados:\n";
foreach ($qas as $qa) {
    echo "- {$qa->name} ({$qa->email})\n";
} 
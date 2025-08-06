<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== OBTENIENDO UUIDs DE USUARIOS ===\n\n";

try {
    $users = \App\Models\User::select('id', 'name', 'email')->get();
    
    echo "Usuarios disponibles:\n";
    foreach ($users as $user) {
        echo "- ID: {$user->id}\n";
        echo "  Nombre: {$user->name}\n";
        echo "  Email: {$user->email}\n";
        echo "\n";
    }
    
    echo "Total de usuarios: " . $users->count() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 
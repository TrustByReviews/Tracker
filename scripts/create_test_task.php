<?php

/**
 * Script para crear una tarea de prueba para el desarrollador
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "📝 CREANDO TAREA DE PRUEBA\n";
echo "==========================\n\n";

// Configurar la aplicación
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "✅ Aplicación inicializada\n\n";

try {
    // 1. Buscar desarrollador
    echo "👥 Buscando desarrollador...\n";
    
    $developer = \App\Models\User::whereHas('roles', function ($query) {
        $query->where('name', 'developer');
    })->first();
    
    if (!$developer) {
        echo "❌ No se encontró ningún desarrollador\n";
        exit(1);
    }
    
    echo "   ✅ Desarrollador: {$developer->name} ({$developer->email})\n";
    
    // 2. Buscar proyecto y sprint
    echo "\n📋 Buscando proyecto y sprint...\n";
    
    $project = \App\Models\Project::first();
    if (!$project) {
        echo "❌ No se encontró ningún proyecto\n";
        exit(1);
    }
    
    $sprint = \App\Models\Sprint::where('project_id', $project->id)->first();
    if (!$sprint) {
        echo "❌ No se encontró ningún sprint en el proyecto\n";
        exit(1);
    }
    
    echo "   ✅ Proyecto: {$project->name}\n";
    echo "   ✅ Sprint: {$sprint->name}\n";
    
    // 3. Crear tarea de prueba
    echo "\n🔨 Creando tarea de prueba...\n";
    
    $task = \App\Models\Task::create([
        'name' => 'Probar sistema de tracking de tiempo',
        'description' => 'Esta es una tarea de prueba para verificar que el sistema de tracking de tiempo funciona correctamente. Incluye iniciar, pausar, reanudar y finalizar el trabajo.',
        'status' => 'to do',
        'priority' => 'medium',
        'category' => 'full stack',
        'story_points' => 3,
        'estimated_hours' => 2,
        'sprint_id' => $sprint->id,
        'user_id' => $developer->id,
        'assigned_by' => $developer->id,
        'assigned_at' => now(),
    ]);
    
    echo "   ✅ Tarea creada: {$task->name}\n";
    echo "   ✅ ID: {$task->id}\n";
    echo "   ✅ Estado: {$task->status}\n";
    echo "   ✅ Asignada a: {$developer->name}\n";
    
    // 4. Verificar que la tarea se creó correctamente
    echo "\n🔍 Verificando tarea creada...\n";
    
    $createdTask = \App\Models\Task::with(['user', 'sprint', 'project'])
        ->where('id', $task->id)
        ->first();
    
    if ($createdTask) {
        echo "   ✅ Tarea encontrada en la base de datos\n";
        echo "   ✅ Usuario: {$createdTask->user->name}\n";
        echo "   ✅ Sprint: {$createdTask->sprint->name}\n";
        if ($createdTask->project) {
            echo "   ✅ Proyecto: {$createdTask->project->name}\n";
        } else {
            echo "   ⚠️  Proyecto: No disponible\n";
        }
    } else {
        echo "   ❌ Error: La tarea no se encontró en la base de datos\n";
    }
    
    echo "\n🎉 ¡TAREA DE PRUEBA CREADA EXITOSAMENTE!\n";
    echo "=========================================\n\n";
    
    echo "📋 DETALLES DE LA TAREA:\n";
    echo "   - Nombre: {$task->name}\n";
    echo "   - Descripción: {$task->description}\n";
    echo "   - Estado: {$task->status}\n";
    echo "   - Prioridad: {$task->priority}\n";
    echo "   - Categoría: {$task->category}\n";
    echo "   - Story Points: {$task->story_points}\n";
    echo "   - Horas estimadas: {$task->estimated_hours}h\n";
    echo "   - Asignada a: {$developer->name}\n";
    echo "   - Sprint: {$sprint->name}\n";
    echo "   - Proyecto: {$project->name}\n\n";
    
    echo "🚀 INSTRUCCIONES PARA PROBAR:\n";
    echo "1. Inicia el servidor: php artisan serve\n";
    echo "2. Ve a: http://localhost:8000\n";
    echo "3. Login como: {$developer->email} / password\n";
    echo "4. Ve a la página de tareas: /tasks\n";
    echo "5. Busca la tarea: '{$task->name}'\n";
    echo "6. Haz clic en 'Iniciar' para comenzar el tracking\n";
    echo "7. Verás el tiempo en tiempo real\n";
    echo "8. Usa 'Pausar', 'Reanudar' y 'Finalizar' según necesites\n\n";
    
    echo "✅ ¡EL SISTEMA DE TRACKING ESTÁ LISTO PARA USAR!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n💡 SUGERENCIAS:\n";
    echo "1. Verifica que las migraciones se ejecutaron\n";
    echo "2. Verifica que hay proyectos y sprints creados\n";
    echo "3. Verifica que hay desarrolladores en el sistema\n";
} 
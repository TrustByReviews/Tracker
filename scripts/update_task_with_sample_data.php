<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Task;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Update Task with Sample Data ===\n\n";

try {
    // Buscar una tarea para actualizar
    $task = Task::first();
    
    if (!$task) {
        echo "No hay tareas disponibles para actualizar\n";
        exit(1);
    }
    
    echo "Actualizando tarea: {$task->name} (ID: {$task->id})\n\n";
    
    // Datos de ejemplo para mostrar todos los campos
    $sampleData = [
        'long_description' => 'Esta es una descripción detallada de la tarea que incluye contexto adicional, requisitos específicos, consideraciones técnicas y cualquier información relevante que ayude a entender completamente el alcance del trabajo a realizar.',
        
        'acceptance_criteria' => "1. El sistema debe validar correctamente los datos de entrada\n2. Los errores deben mostrarse de forma clara al usuario\n3. La funcionalidad debe funcionar en todos los navegadores principales\n4. El rendimiento debe ser aceptable (menos de 2 segundos de respuesta)\n5. Los tests unitarios deben pasar al 100%",
        
        'technical_notes' => "Implementar usando React hooks para el estado local. Considerar usar React Query para el manejo de datos del servidor. Asegurar que la validación sea consistente tanto en frontend como backend. Documentar cualquier cambio en la API.",
        
        'tags' => 'frontend,react,validation,testing,performance',
        
        'attachments' => [
            [
                'name' => 'mockup-design.png',
                'path' => 'task-attachments/mockup-design.png',
                'size' => 245760,
                'type' => 'image/png'
            ],
            [
                'name' => 'technical-specs.pdf',
                'path' => 'task-attachments/technical-specs.pdf',
                'size' => 512000,
                'type' => 'application/pdf'
            ]
        ],
        
        'estimated_minutes' => 30, // 5h 30m total
        
        'complexity_level' => 'high',
        
        'task_type' => 'feature',
        
        'priority' => 'medium'
    ];
    
    // Actualizar la tarea
    $task->update($sampleData);
    
    echo "✅ Tarea actualizada exitosamente\n\n";
    
    echo "=== Datos agregados ===\n";
    echo "Long Description: " . (strlen($task->long_description) > 50 ? substr($task->long_description, 0, 50) . "..." : $task->long_description) . "\n";
    echo "Acceptance Criteria: " . (strlen($task->acceptance_criteria) > 50 ? substr($task->acceptance_criteria, 0, 50) . "..." : $task->acceptance_criteria) . "\n";
    echo "Technical Notes: " . (strlen($task->technical_notes) > 50 ? substr($task->technical_notes, 0, 50) . "..." : $task->technical_notes) . "\n";
    echo "Tags: {$task->tags}\n";
    echo "Attachments: " . count($task->attachments) . " archivos\n";
    echo "Estimated Minutes: {$task->estimated_minutes}\n";
    echo "Complexity Level: {$task->complexity_level}\n";
    echo "Task Type: {$task->task_type}\n";
    echo "Priority: {$task->priority}\n";
    
    echo "\n=== URL para probar ===\n";
    echo "http://127.0.0.1:8000/tasks/{$task->id}\n";
    
    echo "\n=== Campos que ahora deberían aparecer ===\n";
    echo "✅ Descripción detallada\n";
    echo "✅ Criterios de aceptación\n";
    echo "✅ Notas técnicas\n";
    echo "✅ Etiquetas (5 etiquetas)\n";
    echo "✅ Archivos adjuntos (2 archivos)\n";
    echo "✅ Tiempo estimado: 5h 30m\n";
    echo "✅ Nivel de complejidad: High\n";
    echo "✅ Tipo de tarea: Feature\n";
    echo "✅ Prioridad: Medium\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== End ===\n"; 
<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SCRIPT DE TRADUCCIÓN ESPECÍFICA DEL PROYECTO ===\n\n";

// Diccionario específico del proyecto de tracking
$projectTranslations = [
    // Estados de tareas
    'Pendiente' => 'Pending',
    'En Progreso' => 'In Progress',
    'En Desarrollo' => 'In Development',
    'En Revisión' => 'In Review',
    'En Testing' => 'In Testing',
    'Completado' => 'Completed',
    'Cerrado' => 'Closed',
    'Reabierto' => 'Reopened',
    'Bloqueado' => 'Blocked',
    'Resuelto' => 'Resolved',
    'Verificado' => 'Verified',
    'Aprobado' => 'Approved',
    'Rechazado' => 'Rejected',
    
    // Estados de bugs
    'Nuevo' => 'New',
    'Asignado' => 'Assigned',
    'En Desarrollo' => 'In Development',
    'En Testing' => 'In Testing',
    'Testing Pausado' => 'Testing Paused',
    'Testing Finalizado' => 'Testing Finished',
    'Aprobado' => 'Approved',
    'Rechazado' => 'Rejected',
    'Cerrado' => 'Closed',
    
    // Tipos de tareas
    'Feature' => 'Feature',
    'Bug' => 'Bug',
    'Mejora' => 'Improvement',
    'Tarea' => 'Task',
    'Historia' => 'Story',
    'Epic' => 'Epic',
    'Subtask' => 'Subtask',
    
    // Tipos de bugs
    'Frontend' => 'Frontend',
    'Backend' => 'Backend',
    'Database' => 'Database',
    'API' => 'API',
    'UI/UX' => 'UI/UX',
    'Performance' => 'Performance',
    'Security' => 'Security',
    'Otro' => 'Other',
    
    // Categorías
    'Frontend' => 'Frontend',
    'Backend' => 'Backend',
    'Full Stack' => 'Full Stack',
    'Diseño' => 'Design',
    'Despliegue' => 'Deployment',
    'Correcciones' => 'Fixes',
    'Testing' => 'Testing',
    'Documentación' => 'Documentation',
    'Base de Datos' => 'Database',
    'API' => 'API',
    'Seguridad' => 'Security',
    'Rendimiento' => 'Performance',
    
    // Reproducibilidad
    'Siempre' => 'Always',
    'A veces' => 'Sometimes',
    'Raramente' => 'Rarely',
    'No se puede reproducir' => 'Unable to Reproduce',
    
    // Ambientes
    'Development' => 'Development',
    'Staging' => 'Staging',
    'Production' => 'Production',
    'Testing' => 'Testing',
    
    // Roles
    'Administrador' => 'Administrator',
    'Líder de Equipo' => 'Team Leader',
    'Desarrollador' => 'Developer',
    'QA Tester' => 'QA Tester',
    'Cliente' => 'Client',
    'Product Owner' => 'Product Owner',
    'Scrum Master' => 'Scrum Master',
    
    // Campos específicos
    'Story Points' => 'Story Points',
    'Tiempo Estimado' => 'Estimated Time',
    'Tiempo Real' => 'Actual Time',
    'Tiempo Restante' => 'Remaining Time',
    'Criterios de Aceptación' => 'Acceptance Criteria',
    'Notas Técnicas' => 'Technical Notes',
    'Pasos para Reproducir' => 'Steps to Reproduce',
    'Comportamiento Esperado' => 'Expected Behavior',
    'Comportamiento Actual' => 'Actual Behavior',
    'Información del Navegador' => 'Browser Info',
    'Información del Sistema' => 'OS Info',
    'Tarea Relacionada' => 'Related Task',
    'Archivos Adjuntos' => 'Attachments',
    'Etiquetas' => 'Tags',
    
    // Mensajes específicos
    'Tarea creada exitosamente' => 'Task created successfully',
    'Bug creado exitosamente' => 'Bug created successfully',
    'Sprint creado exitosamente' => 'Sprint created successfully',
    'Proyecto creado exitosamente' => 'Project created successfully',
    'Usuario creado exitosamente' => 'User created successfully',
    'Tarea actualizada exitosamente' => 'Task updated successfully',
    'Bug actualizado exitosamente' => 'Bug updated successfully',
    'Sprint actualizado exitosamente' => 'Sprint updated successfully',
    'Proyecto actualizado exitosamente' => 'Project updated successfully',
    'Usuario actualizado exitosamente' => 'User updated successfully',
    'Tarea eliminada exitosamente' => 'Task deleted successfully',
    'Bug eliminado exitosamente' => 'Bug deleted successfully',
    'Sprint eliminado exitosamente' => 'Sprint deleted successfully',
    'Proyecto eliminado exitosamente' => 'Project deleted successfully',
    'Usuario eliminado exitosamente' => 'User deleted successfully',
    
    // Errores específicos
    'Error al crear tarea' => 'Error creating task',
    'Error al crear bug' => 'Error creating bug',
    'Error al crear sprint' => 'Error creating sprint',
    'Error al crear proyecto' => 'Error creating project',
    'Error al crear usuario' => 'Error creating user',
    'Error al actualizar tarea' => 'Error updating task',
    'Error al actualizar bug' => 'Error updating bug',
    'Error al actualizar sprint' => 'Error updating sprint',
    'Error al actualizar proyecto' => 'Error updating project',
    'Error al actualizar usuario' => 'Error updating user',
    'Error al eliminar tarea' => 'Error deleting task',
    'Error al eliminar bug' => 'Error deleting bug',
    'Error al eliminar sprint' => 'Error deleting sprint',
    'Error al eliminar proyecto' => 'Error deleting project',
    'Error al eliminar usuario' => 'Error deleting user',
    
    // Placeholders
    'Ingresa el nombre' => 'Enter name',
    'Ingresa la descripción' => 'Enter description',
    'Selecciona una opción' => 'Select an option',
    'Selecciona el tipo' => 'Select type',
    'Selecciona la prioridad' => 'Select priority',
    'Selecciona la severidad' => 'Select severity',
    'Selecciona el estado' => 'Select status',
    'Selecciona el ambiente' => 'Select environment',
    'Selecciona el desarrollador' => 'Select developer',
    'Selecciona el proyecto' => 'Select project',
    'Selecciona el sprint' => 'Select sprint',
    'Selecciona el rol' => 'Select role',
    
    // Botones específicos
    'Crear Tarea' => 'Create Task',
    'Crear Bug' => 'Create Bug',
    'Crear Sprint' => 'Create Sprint',
    'Crear Proyecto' => 'Create Project',
    'Crear Usuario' => 'Create User',
    'Actualizar Tarea' => 'Update Task',
    'Actualizar Bug' => 'Update Bug',
    'Actualizar Sprint' => 'Update Sprint',
    'Actualizar Proyecto' => 'Update Project',
    'Actualizar Usuario' => 'Update User',
    'Eliminar Tarea' => 'Delete Task',
    'Eliminar Bug' => 'Delete Bug',
    'Eliminar Sprint' => 'Delete Sprint',
    'Eliminar Proyecto' => 'Delete Project',
    'Eliminar Usuario' => 'Delete User',
    'Asignar a Mí' => 'Assign to Me',
    'Desasignar' => 'Unassign',
    'Marcar como Completado' => 'Mark as Completed',
    'Marcar como Pendiente' => 'Mark as Pending',
    'Reabrir' => 'Reopen',
    'Cerrar' => 'Close',
    
    // Títulos de páginas
    'Dashboard' => 'Dashboard',
    'Tareas' => 'Tasks',
    'Bugs' => 'Bugs',
    'Sprints' => 'Sprints',
    'Proyectos' => 'Projects',
    'Usuarios' => 'Users',
    'Equipos' => 'Teams',
    'Reportes' => 'Reports',
    'Configuración' => 'Settings',
    'Perfil' => 'Profile',
    'Notificaciones' => 'Notifications',
    'Actividad' => 'Activity',
    'Timeline' => 'Timeline',
    'Calendario' => 'Calendar',
    'Kanban' => 'Kanban',
    'Lista' => 'List',
    'Detalles' => 'Details',
    'Vista General' => 'Overview',
    'Estadísticas' => 'Statistics',
    'Métricas' => 'Metrics',
    'Análisis' => 'Analysis',
    
    // Mensajes de confirmación
    '¿Estás seguro de que quieres eliminar esta tarea?' => 'Are you sure you want to delete this task?',
    '¿Estás seguro de que quieres eliminar este bug?' => 'Are you sure you want to delete this bug?',
    '¿Estás seguro de que quieres eliminar este sprint?' => 'Are you sure you want to delete this sprint?',
    '¿Estás seguro de que quieres eliminar este proyecto?' => 'Are you sure you want to delete this project?',
    '¿Estás seguro de que quieres eliminar este usuario?' => 'Are you sure you want to delete this user?',
    '¿Estás seguro de que quieres cerrar? Cualquier cambio no guardado se perderá.' => 'Are you sure you want to close? Any unsaved changes will be lost.',
    
    // Mensajes de validación
    'Este campo es requerido' => 'This field is required',
    'El email debe ser válido' => 'Email must be valid',
    'La contraseña debe tener al menos 8 caracteres' => 'Password must be at least 8 characters',
    'Las contraseñas no coinciden' => 'Passwords do not match',
    'El nombre debe tener al menos 3 caracteres' => 'Name must be at least 3 characters',
    'La descripción debe tener al menos 10 caracteres' => 'Description must be at least 10 characters',
    
    // Estados de carga
    'Creando...' => 'Creating...',
    'Actualizando...' => 'Updating...',
    'Eliminando...' => 'Deleting...',
    'Cargando...' => 'Loading...',
    'Guardando...' => 'Saving...',
    'Procesando...' => 'Processing...',
    'Enviando...' => 'Sending...',
    'Descargando...' => 'Downloading...',
    'Subiendo...' => 'Uploading...',
    'Buscando...' => 'Searching...',
    'Filtrando...' => 'Filtering...',
    'Ordenando...' => 'Sorting...',
];

// Directorios a procesar
$directories = [
    'resources/js/components',
    'resources/js/pages',
    'resources/js/layouts',
];

$totalFiles = 0;
$totalChanges = 0;

foreach ($directories as $directory) {
    if (!is_dir($directory)) {
        echo "⚠️  Directorio no encontrado: $directory\n";
        continue;
    }
    
    echo "📁 Procesando directorio: $directory\n";
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory)
    );
    
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'vue') {
            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);
            $originalContent = $content;
            $fileChanges = 0;
            
            echo "  📄 Procesando: " . basename($filePath) . "\n";
            
            // Aplicar traducciones específicas del proyecto
            foreach ($projectTranslations as $spanish => $english) {
                $count = 0;
                $content = str_replace($spanish, $english, $content, $count);
                $fileChanges += $count;
            }
            
            // Guardar archivo si hubo cambios
            if ($content !== $originalContent) {
                file_put_contents($filePath, $content);
                echo "    ✅ Cambios aplicados: $fileChanges\n";
                $totalChanges += $fileChanges;
            } else {
                echo "    ⏭️  Sin cambios\n";
            }
            
            $totalFiles++;
        }
    }
}

echo "\n=== RESUMEN ===\n";
echo "Total archivos procesados: $totalFiles\n";
echo "Total cambios aplicados: $totalChanges\n";
echo "Palabras específicas del proyecto: " . count($projectTranslations) . "\n";

echo "\n✅ Script completado!\n";
echo "Revisa los archivos para verificar que las traducciones sean correctas.\n";

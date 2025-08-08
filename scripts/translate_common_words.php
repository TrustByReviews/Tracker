<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SCRIPT DE TRADUCCIÓN DE PALABRAS COMUNES ===\n\n";

// Diccionario de traducciones comunes
$translations = [
    // Botones y acciones
    'Guardar' => 'Save',
    'Cancelar' => 'Cancel',
    'Cerrar' => 'Close',
    'Eliminar' => 'Delete',
    'Editar' => 'Edit',
    'Actualizar' => 'Update',
    'Crear' => 'Create',
    'Nuevo' => 'New',
    'Siguiente' => 'Next',
    'Anterior' => 'Previous',
    'Aceptar' => 'Accept',
    'Rechazar' => 'Reject',
    'Confirmar' => 'Confirm',
    'Limpiar' => 'Clear',
    'Buscar' => 'Search',
    'Filtrar' => 'Filter',
    'Ordenar' => 'Sort',
    'Exportar' => 'Export',
    'Importar' => 'Import',
    'Descargar' => 'Download',
    'Subir' => 'Upload',
    'Enviar' => 'Send',
    'Recibir' => 'Receive',
    
    // Formularios
    'Nombre' => 'Name',
    'Apellido' => 'Last Name',
    'Email' => 'Email',
    'Contraseña' => 'Password',
    'Confirmar Contraseña' => 'Confirm Password',
    'Teléfono' => 'Phone',
    'Dirección' => 'Address',
    'Ciudad' => 'City',
    'País' => 'Country',
    'Descripción' => 'Description',
    'Comentarios' => 'Comments',
    'Notas' => 'Notes',
    'Observaciones' => 'Observations',
    
    // Estados
    'Activo' => 'Active',
    'Inactivo' => 'Inactive',
    'Pendiente' => 'Pending',
    'Completado' => 'Completed',
    'En Proceso' => 'In Progress',
    'Cancelado' => 'Cancelled',
    'Aprobado' => 'Approved',
    'Rechazado' => 'Rejected',
    'Draft' => 'Draft',
    'Publicado' => 'Published',
    
    // Prioridades
    'Alta' => 'High',
    'Media' => 'Medium',
    'Baja' => 'Low',
    'Crítica' => 'Critical',
    'Urgente' => 'Urgent',
    'Normal' => 'Normal',
    
    // Tiempo
    'Hoy' => 'Today',
    'Ayer' => 'Yesterday',
    'Mañana' => 'Tomorrow',
    'Esta Semana' => 'This Week',
    'Este Mes' => 'This Month',
    'Este Año' => 'This Year',
    'Hora' => 'Hour',
    'Minuto' => 'Minute',
    'Segundo' => 'Second',
    'Día' => 'Day',
    'Semana' => 'Week',
    'Mes' => 'Month',
    'Año' => 'Year',
    
    // Mensajes
    '¿Estás seguro?' => 'Are you sure?',
    '¿Deseas continuar?' => 'Do you want to continue?',
    'Operación exitosa' => 'Operation successful',
    'Error en la operación' => 'Operation error',
    'Datos guardados' => 'Data saved',
    'Datos actualizados' => 'Data updated',
    'Datos eliminados' => 'Data deleted',
    'No se encontraron resultados' => 'No results found',
    'Cargando...' => 'Loading...',
    'Procesando...' => 'Processing...',
    'Espera un momento' => 'Wait a moment',
    
    // Navegación
    'Inicio' => 'Home',
    'Dashboard' => 'Dashboard',
    'Perfil' => 'Profile',
    'Configuración' => 'Settings',
    'Ayuda' => 'Help',
    'Acerca de' => 'About',
    'Contacto' => 'Contact',
    'Salir' => 'Logout',
    'Entrar' => 'Login',
    'Registrarse' => 'Register',
    
    // Títulos de secciones
    'Información General' => 'General Information',
    'Información Personal' => 'Personal Information',
    'Información de Contacto' => 'Contact Information',
    'Detalles' => 'Details',
    'Resumen' => 'Summary',
    'Lista' => 'List',
    'Vista' => 'View',
    'Formulario' => 'Form',
    'Tabla' => 'Table',
    'Gráfico' => 'Chart',
    'Reporte' => 'Report',
    
    // Campos específicos del proyecto
    'Proyecto' => 'Project',
    'Tarea' => 'Task',
    'Bug' => 'Bug',
    'Sprint' => 'Sprint',
    'Usuario' => 'User',
    'Rol' => 'Role',
    'Permiso' => 'Permission',
    'Equipo' => 'Team',
    'Desarrollador' => 'Developer',
    'Líder de Equipo' => 'Team Leader',
    'Administrador' => 'Administrator',
    'Tester' => 'Tester',
    'Cliente' => 'Client',
    
    // Estados de tareas/bugs
    'Nuevo' => 'New',
    'En Desarrollo' => 'In Development',
    'En Revisión' => 'In Review',
    'En Testing' => 'In Testing',
    'Completado' => 'Completed',
    'Cerrado' => 'Closed',
    'Reabierto' => 'Reopened',
    'Bloqueado' => 'Blocked',
    'Resuelto' => 'Resolved',
    'Verificado' => 'Verified',
    
    // Tipos
    'Tipo' => 'Type',
    'Categoría' => 'Category',
    'Etiqueta' => 'Tag',
    'Prioridad' => 'Priority',
    'Severidad' => 'Severity',
    'Importancia' => 'Importance',
    'Complejidad' => 'Complexity',
    'Duración' => 'Duration',
    'Estimación' => 'Estimation',
    'Tiempo Real' => 'Actual Time',
    'Tiempo Estimado' => 'Estimated Time',
    
    // Archivos
    'Archivo' => 'File',
    'Archivos' => 'Files',
    'Documento' => 'Document',
    'Imagen' => 'Image',
    'Video' => 'Video',
    'Audio' => 'Audio',
    'PDF' => 'PDF',
    'Excel' => 'Excel',
    'Word' => 'Word',
    'Adjuntar' => 'Attach',
    'Descargar' => 'Download',
    'Subir' => 'Upload',
    'Eliminar Archivo' => 'Delete File',
    
    // Fechas
    'Fecha' => 'Date',
    'Fecha de Creación' => 'Creation Date',
    'Fecha de Modificación' => 'Modification Date',
    'Fecha de Inicio' => 'Start Date',
    'Fecha de Fin' => 'End Date',
    'Fecha Límite' => 'Due Date',
    'Fecha de Entrega' => 'Delivery Date',
    
    // Otros
    'Sí' => 'Yes',
    'No' => 'No',
    'Verdadero' => 'True',
    'Falso' => 'False',
    'N/A' => 'N/A',
    'Sin Asignar' => 'Unassigned',
    'Sin Descripción' => 'No Description',
    'Sin Comentarios' => 'No Comments',
    'Vacío' => 'Empty',
    'Todos' => 'All',
    'Ninguno' => 'None',
    'Seleccionar' => 'Select',
    'Seleccionar Todo' => 'Select All',
    'Deseleccionar Todo' => 'Deselect All',
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
            
            // Aplicar traducciones
            foreach ($translations as $spanish => $english) {
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
echo "Palabras en el diccionario: " . count($translations) . "\n";

echo "\n✅ Script completado!\n";
echo "Revisa los archivos para verificar que las traducciones sean correctas.\n";

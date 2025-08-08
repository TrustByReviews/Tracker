# Scripts de Traducción Automática

Este directorio contiene scripts para automatizar la traducción de textos del frontend de español a inglés.

## 📁 Archivos Disponibles

### Scripts PHP
- **`translate_common_words.php`** - Traduce palabras comunes (botones, formularios, estados, etc.)
- **`translate_project_specific.php`** - Traduce palabras específicas del proyecto de tracking

### Scripts de Ejecución
- **`translate_all.ps1`** - Script de PowerShell para Windows
- **`translate_all.sh`** - Script de Bash para Unix/Linux

## 🚀 Cómo Usar

### Opción 1: Script Completo (Recomendado)

#### En Windows (PowerShell):
```powershell
.\scripts\translate_all.ps1
```

#### En Unix/Linux (Bash):
```bash
chmod +x scripts/translate_all.sh
./scripts/translate_all.sh
```

### Opción 2: Scripts Individuales

#### Traducir palabras comunes:
```bash
php scripts/translate_common_words.php
```

#### Traducir palabras específicas del proyecto:
```bash
php scripts/translate_project_specific.php
```

## 📋 Palabras que se Traducen

### Palabras Comunes (translate_common_words.php)
- **Botones**: Guardar → Save, Cancelar → Cancel, Cerrar → Close
- **Formularios**: Nombre → Name, Email → Email, Contraseña → Password
- **Estados**: Activo → Active, Pendiente → Pending, Completado → Completed
- **Prioridades**: Alta → High, Media → Medium, Baja → Low
- **Navegación**: Inicio → Home, Dashboard → Dashboard, Perfil → Profile
- **Mensajes**: ¿Estás seguro? → Are you sure?, Cargando... → Loading...

### Palabras Específicas del Proyecto (translate_project_specific.php)
- **Estados de Tareas**: En Progreso → In Progress, En Desarrollo → In Development
- **Estados de Bugs**: Nuevo → New, Asignado → Assigned, En Testing → In Testing
- **Tipos**: Feature → Feature, Bug → Bug, Mejora → Improvement
- **Categorías**: Frontend → Frontend, Backend → Backend, Full Stack → Full Stack
- **Roles**: Administrador → Administrator, Líder de Equipo → Team Leader
- **Campos**: Story Points → Story Points, Tiempo Estimado → Estimated Time
- **Mensajes**: Tarea creada exitosamente → Task created successfully

## ⚠️ Importante

### Antes de Ejecutar:
1. **Hacer backup** de los archivos importantes
2. **Verificar** que estás en el directorio raíz del proyecto Laravel
3. **Revisar** el diccionario de traducciones en los scripts

### Después de Ejecutar:
1. **Revisar** los archivos modificados
2. **Verificar** que las traducciones sean correctas
3. **Probar** la aplicación para asegurar que todo funcione
4. **Continuar** con la traducción manual de textos complejos

## 🔧 Personalización

### Agregar Nuevas Traducciones

Para agregar nuevas palabras al diccionario, edita los archivos PHP:

```php
// En translate_common_words.php o translate_project_specific.php
$translations = [
    'Palabra en Español' => 'Word in English',
    // ... más traducciones
];
```

### Modificar Directorios

Para cambiar qué directorios se procesan:

```php
$directories = [
    'resources/js/components',
    'resources/js/pages',
    'resources/js/layouts',
    // Agregar más directorios aquí
];
```

## 📊 Estadísticas

Los scripts mostrarán:
- Total de archivos procesados
- Total de cambios aplicados
- Número de palabras en el diccionario
- Archivos modificados

## 🐛 Solución de Problemas

### Error: "Script no encontrado"
- Verifica que estés en el directorio raíz del proyecto
- Asegúrate de que los archivos de script existan

### Error: "Directorio no encontrado"
- Verifica que los directorios `resources/js/components`, `resources/js/pages`, etc. existan

### Traducciones Incorrectas
- Revisa el diccionario en los scripts
- Modifica las traducciones según sea necesario
- Ejecuta el script nuevamente

## 📝 Notas

- Los scripts procesan solo archivos `.vue`
- Las traducciones son reemplazos directos de texto
- Se recomienda revisar manualmente los cambios
- Los scripts no modifican archivos de configuración o backend

## 🤝 Contribuir

Para agregar más traducciones o mejorar los scripts:

1. Edita los archivos PHP correspondientes
2. Agrega las nuevas traducciones al diccionario
3. Prueba los cambios en un entorno de desarrollo
4. Documenta las nuevas traducciones agregadas

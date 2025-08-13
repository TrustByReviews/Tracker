# Plan de Trabajo: Dashboard para Usuarios Client

## 🎯 Objetivo
Crear un dashboard específico para usuarios con rol "client" que les permita visualizar información de avance de proyectos de forma superficial y segura, sin capacidad de edición.

## 📊 Funcionalidades Requeridas

### Dashboard Principal
- **Avance del proyecto en porcentaje**
- **Cantidad de tareas totales**
- **Información del sprint actual**
- **Tareas completadas vs pendientes**
- **Sin información de bugs**

### Página de Proyectos
- **Lista de proyectos asignados**
- **Información de desarrolladores y QAs**
- **Sistema de sugerencias**
- **Solo visualización, sin edición**

### Página de Tareas
- **Vista de tareas del proyecto**
- **Estado y progreso**
- **Información superficial**

## 🔐 Sistema de Permisos

### Permisos Específicos para Client
- `client.view.dashboard` - Ver dashboard de cliente
- `client.view.projects` - Ver proyectos asignados
- `client.view.tasks` - Ver tareas de proyectos
- `client.view.sprints` - Ver sprints actuales
- `client.view.team` - Ver equipo del proyecto
- `client.create.suggestions` - Crear sugerencias
- `client.view.suggestions` - Ver sugerencias propias

### Restricciones
- ❌ No puede crear/editar/eliminar tareas
- ❌ No puede ver bugs
- ❌ No puede acceder a reportes de pago
- ❌ No puede ver información interna del equipo
- ❌ No puede acceder a configuraciones del sistema

## 📅 Plan de Implementación por Fases

### **Fase 1: Análisis y Diseño de Permisos** (1-2 días)
- [ ] Crear permisos específicos para clientes
- [ ] Modificar middleware de autenticación
- [ ] Crear roles y permisos en base de datos
- [ ] Actualizar sistema de autorización

### **Fase 2: Desarrollo del Dashboard Client** (3-4 días)
- [ ] Crear controlador específico para clientes
- [ ] Desarrollar vista del dashboard principal
- [ ] Implementar cálculos de avance de proyecto
- [ ] Crear componentes Vue.js para el dashboard

### **Fase 3: Página de Proyectos para Clientes** (2-3 días)
- [ ] Crear vista de proyectos para clientes
- [ ] Implementar sistema de sugerencias
- [ ] Mostrar información del equipo
- [ ] Crear formulario de sugerencias

### **Fase 4: Página de Tareas para Clientes** (2-3 días)
- [ ] Crear vista de tareas filtrada
- [ ] Implementar filtros por sprint
- [ ] Mostrar progreso de tareas
- [ ] Excluir información de bugs

### **Fase 5: Sistema de Sugerencias** (2-3 días)
- [ ] Crear modelo para sugerencias
- [ ] Implementar CRUD de sugerencias
- [ ] Crear notificaciones para administradores
- [ ] Sistema de respuesta a sugerencias

### **Fase 6: Testing y Refinamiento** (2-3 días)
- [ ] Pruebas de funcionalidad
- [ ] Pruebas de seguridad
- [ ] Optimización de consultas
- [ ] Refinamiento de UI/UX

### **Fase 7: Documentación y Despliegue** (1-2 días)
- [ ] Documentar funcionalidades
- [ ] Crear manual de usuario
- [ ] Desplegar cambios
- [ ] Capacitación de administradores

## 🛠️ Archivos a Crear/Modificar

### Nuevos Archivos
- `app/Http/Controllers/Client/DashboardController.php`
- `app/Http/Controllers/Client/ProjectController.php`
- `app/Http/Controllers/Client/TaskController.php`
- `app/Http/Controllers/Client/SuggestionController.php`
- `app/Models/Suggestion.php`
- `database/migrations/create_suggestions_table.php`
- `resources/js/pages/Client/Dashboard.vue`
- `resources/js/pages/Client/Projects.vue`
- `resources/js/pages/Client/Tasks.vue`
- `resources/js/pages/Client/Suggestions.vue`

### Archivos a Modificar
- `app/Http/Middleware/CheckRole.php`
- `app/Http/Middleware/CheckPermission.php`
- `routes/web.php`
- `resources/js/presentation/router/routes/index.ts`
- `database/seeders/PermissionSeeder.php`

## 🔧 Implementación Técnica

### Middleware de Autorización
```php
// Verificar si el usuario es client y tiene permisos específicos
if ($user->hasRole('client')) {
    // Aplicar restricciones específicas para clientes
    // Redirigir a dashboard de cliente
}
```

### Cálculo de Avance de Proyecto
```php
// Calcular porcentaje de avance basado en tareas completadas
$totalTasks = $project->tasks()->count();
$completedTasks = $project->tasks()->where('status', 'done')->count();
$progressPercentage = ($totalTasks > 0) ? ($completedTasks / $totalTasks) * 100 : 0;
```

### Sistema de Sugerencias
```php
// Modelo para sugerencias de clientes
class Suggestion extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'title',
        'description',
        'status', // pending, reviewed, implemented, rejected
        'admin_response'
    ];
}
```

## 📱 Interfaz de Usuario

### Dashboard Principal
- **Header**: Logo, nombre del cliente, logout
- **Resumen del Proyecto**: Porcentaje de avance, tareas totales
- **Sprint Actual**: Información del sprint en curso
- **Gráfico de Progreso**: Visualización del avance
- **Navegación**: Proyectos, Tareas, Sugerencias

### Página de Proyectos
- **Lista de Proyectos**: Tarjetas con información básica
- **Equipo del Proyecto**: Desarrolladores y QAs asignados
- **Botón de Sugerencia**: Para cada proyecto
- **Filtros**: Por estado del proyecto

### Página de Tareas
- **Lista de Tareas**: Solo tareas, sin bugs
- **Filtros**: Por sprint, estado, prioridad
- **Información Superficial**: Nombre, estado, asignado
- **Sin Detalles Técnicos**: No mostrar descripciones técnicas

## 🔒 Consideraciones de Seguridad

### Validación de Acceso
- Verificar que el cliente solo vea proyectos asignados
- Validar permisos en cada endpoint
- Sanitizar datos de entrada en sugerencias

### Protección de Datos
- No mostrar información sensible del equipo
- Ocultar detalles técnicos de tareas
- Restringir acceso a configuraciones

### Auditoría
- Registrar todas las acciones del cliente
- Mantener log de sugerencias creadas
- Monitorear accesos al sistema

## 📈 Métricas de Éxito

### Funcionales
- ✅ Cliente puede ver avance de proyectos
- ✅ Sistema de sugerencias funciona
- ✅ No puede acceder a información restringida
- ✅ Interfaz intuitiva y fácil de usar

### Técnicas
- ✅ Rendimiento optimizado
- ✅ Seguridad implementada
- ✅ Código mantenible
- ✅ Documentación completa

## 🚀 Cronograma Estimado

| Fase | Duración | Dependencias |
|------|----------|--------------|
| Fase 1 | 1-2 días | - |
| Fase 2 | 3-4 días | Fase 1 |
| Fase 3 | 2-3 días | Fase 2 |
| Fase 4 | 2-3 días | Fase 2 |
| Fase 5 | 2-3 días | Fase 3 |
| Fase 6 | 2-3 días | Fases 2-5 |
| Fase 7 | 1-2 días | Todas las fases |

**Tiempo Total Estimado**: 13-20 días

## 📝 Notas Importantes

- **Prioridad**: Alta - El cliente necesita acceso inmediato
- **Riesgos**: Cambios en la estructura de permisos existente
- **Dependencias**: Sistema de autenticación actual
- **Recursos**: Desarrollador full-stack, diseñador UI/UX
- **Testing**: Pruebas exhaustivas de seguridad y funcionalidad

---

**Estado**: 📋 Planificado
**Responsable**: Equipo de Desarrollo
**Fecha de Inicio**: Pendiente de aprobación

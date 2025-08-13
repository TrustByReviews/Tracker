# Plan de Trabajo: Dashboard de Cliente

## Fase 1: Análisis y Diseño de Permisos ✅ COMPLETADO
- [x] Definir permisos específicos para usuarios cliente
- [x] Crear rol 'client' con permisos limitados
- [x] Crear tabla `suggestions` para el sistema de sugerencias
- [x] Desarrollar modelo `Suggestion` con relaciones
- [x] Implementar controladores `Client/DashboardController` y `Client/SuggestionController`
- [x] Configurar rutas API específicas para clientes
- [x] Verificar y probar funcionalidades backend

## Fase 2: Desarrollo del Frontend del Dashboard de Cliente
- [ ] Crear vistas Vue.js para el dashboard de cliente
  - [ ] `resources/js/pages/Client/Dashboard.vue` - Vista principal del dashboard
  - [ ] `resources/js/pages/Client/Projects.vue` - Lista de proyectos asignados
  - [ ] `resources/js/pages/Client/Tasks.vue` - Vista de tareas (sin bugs)
  - [ ] `resources/js/pages/Client/Suggestions.vue` - Sistema de sugerencias
- [ ] Desarrollar componentes específicos para clientes
  - [ ] Componente de progreso de proyecto
  - [ ] Componente de estadísticas de sprint
  - [ ] Componente de formulario de sugerencias
  - [ ] Componente de lista de sugerencias
- [ ] Implementar middleware de redirección para clientes
- [ ] Integrar sistema de permisos en el frontend

## Fase 3: Sistema de Sugerencias Avanzado
- [ ] **NUEVO**: Extender modelo `Suggestion` para incluir relaciones con tareas y sprints
  - [ ] Agregar campos `task_id` y `sprint_id` (opcionales)
  - [ ] Actualizar migración de sugerencias
  - [ ] Agregar relaciones en el modelo
- [ ] **NUEVO**: Crear controlador `Admin/SuggestionController` para gestión de sugerencias
  - [ ] Método para listar todas las sugerencias de clientes
  - [ ] Método para ver detalles de sugerencia
  - [ ] Método para responder a sugerencias
  - [ ] Método para cambiar estado de sugerencias
- [ ] **NUEVO**: Crear vistas de administración para sugerencias
  - [ ] `resources/js/pages/Admin/Suggestions.vue` - Lista de sugerencias
  - [ ] `resources/js/pages/Admin/Suggestions/Show.vue` - Detalles de sugerencia
  - [ ] Componente de respuesta a sugerencias
- [ ] **NUEVO**: Agregar sección de sugerencias en sidebar de admin
- [ ] **NUEVO**: Implementar notificaciones para admin cuando se crean sugerencias

## Fase 4: Integración y Funcionalidades Adicionales
- [ ] Implementar sistema de notificaciones
- [ ] Agregar filtros y búsqueda en sugerencias
- [ ] Implementar paginación en listas
- [ ] Crear reportes de sugerencias para admin
- [ ] Implementar exportación de sugerencias

## Fase 5: Testing y Refinamiento
- [ ] Pruebas funcionales del dashboard de cliente
- [ ] Pruebas de seguridad y permisos
- [ ] Optimización de UI/UX
- [ ] Pruebas de integración
- [ ] Corrección de bugs y mejoras

## Fase 6: Documentación y Despliegue
- [ ] Documentar funcionalidades del dashboard de cliente
- [ ] Crear manual de usuario para clientes
- [ ] Documentar sistema de permisos
- [ ] Preparar para despliegue
- [ ] Entrenamiento de usuarios

## Nuevas Funcionalidades del Sistema de Sugerencias

### Relaciones con Tareas y Sprints
- Las sugerencias pueden estar vinculadas a tareas específicas
- Las sugerencias pueden estar vinculadas a sprints específicos
- Las sugerencias pueden ser independientes (sin tarea o sprint específico)

### Vista de Administración
- Nueva sección en sidebar de admin para "Sugerencias de Clientes"
- Lista de todas las sugerencias con filtros por estado, proyecto, cliente
- Vista detallada de cada sugerencia mostrando:
  - Cliente que la creó
  - Proyecto relacionado
  - Tarea/Sprint vinculado (si aplica)
  - Descripción de la sugerencia
  - Estado actual
  - Respuesta del admin (si existe)
- Capacidad de responder y cambiar estado de sugerencias

### Estados de Sugerencias
- **Pendiente**: Recién creada, esperando revisión
- **En Revisión**: Admin está analizando la sugerencia
- **Implementada**: La sugerencia fue implementada
- **Rechazada**: La sugerencia no será implementada
- **En Progreso**: La sugerencia está siendo trabajada

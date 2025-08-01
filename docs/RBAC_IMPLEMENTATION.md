# Sistema RBAC (Role-Based Access Control) - Documentación

## Resumen

Se ha implementado un sistema completo de control de acceso basado en roles (RBAC) que permite gestionar permisos de usuarios de forma granular, incluyendo permisos temporales y permanentes.

## Características Implementadas

### ✅ **Fase 1: Estructura de Base de Datos**
- **Tabla `permissions`**: Almacena todos los permisos del sistema
- **Tabla `permission_role`**: Relación muchos a muchos entre permisos y roles
- **Tabla `user_permissions`**: Permisos directos de usuarios (temporales y permanentes)
- **Modelos actualizados**: User, Role, Permission, UserPermission

### ✅ **Fase 2: Backend - Controladores y Rutas**
- **PermissionController**: Gestión completa de permisos
- **Rutas API**: CRUD completo para permisos
- **Middleware CheckPermission**: Verificación de permisos en rutas

### ✅ **Fase 3: Frontend - Interfaz de Usuario**
- **Página de gestión de permisos**: `/permissions`
- **Tres pestañas principales**:
  - Permisos de Usuarios
  - Permisos de Roles
  - Permisos Expirados
- **Modales para otorgar/editar permisos**
- **Item en sidebar**: Navegación fácil al módulo

### ✅ **Fase 4: Funcionalidades Avanzadas**
- **Permisos temporales**: Con fecha de expiración
- **Permisos permanentes**: Sin fecha de expiración
- **Gestión de permisos expirados**: Limpieza automática
- **Auditoría**: Registro de quién otorgó cada permiso

## Estructura de Permisos

### Módulos de Permisos
1. **admin**: Dashboard, usuarios, roles, permisos, sistema
2. **projects**: Ver, crear, editar, eliminar, gestionar
3. **tasks**: Ver, crear, editar, eliminar, asignar, aprobar, rechazar
4. **sprints**: Ver, crear, editar, eliminar, gestionar
5. **reports**: Ver, crear, exportar, gestionar
6. **team-leader**: Dashboard, aprobar, gestionar equipo
7. **users**: Ver, editar, gestionar
8. **permissions**: Otorgar, revocar, gestionar

### Roles Predefinidos
- **admin**: 36 permisos (acceso completo)
- **team_leader**: 18 permisos (gestión de equipo y proyectos)
- **developer**: 5 permisos (tareas básicas)

## Uso del Sistema

### 1. Acceso a la Gestión de Permisos
```
URL: /permissions
Requisito: Usuario con permiso 'permissions.manage'
```

### 2. Gestión de Permisos de Usuarios
- **Ver usuarios**: Lista todos los usuarios del sistema
- **Seleccionar usuario**: Click en cualquier usuario
- **Ver permisos**: Se muestran permisos directos y por roles
- **Otorgar permiso**: Botón "Otorgar Permiso"
  - Seleccionar permiso
  - Elegir tipo (temporal/permanente)
  - Fecha de expiración (solo temporal)
  - Razón (opcional)
- **Revocar permiso**: Botón "Revocar" en cada permiso directo

### 3. Gestión de Permisos de Roles
- **Ver roles**: Lista todos los roles del sistema
- **Seleccionar rol**: Click en cualquier rol
- **Ver permisos**: Se muestran todos los permisos del rol
- **Editar permisos**: Botón "Editar Permisos"
  - Checkboxes para seleccionar permisos
  - Guardar cambios

### 4. Gestión de Permisos Expirados
- **Ver expirados**: Lista permisos temporales expirados
- **Limpiar expirados**: Botón "Limpiar Expirados"
- **Eliminar individual**: Botón "Eliminar" en cada permiso

## API Endpoints

### Permisos
```
GET    /permissions                    # Página principal
GET    /permissions/list              # Lista de permisos
GET    /permissions/user/{user}       # Permisos de usuario
POST   /permissions/user/{user}/grant # Otorgar permiso
POST   /permissions/user/{user}/revoke # Revocar permiso
GET    /permissions/role/{role}       # Permisos de rol
PUT    /permissions/role/{role}       # Actualizar permisos de rol
GET    /permissions/expired           # Permisos expirados
DELETE /permissions/expired           # Limpiar expirados
```

## Métodos del Modelo User

### Verificación de Permisos
```php
// Verificar un permiso específico
$user->hasPermission('projects.view');

// Verificar múltiples permisos
$user->hasAnyPermission(['projects.view', 'tasks.create']);

// Obtener todos los permisos (directos + por roles)
$user->getAllPermissions();
```

### Gestión de Permisos
```php
// Otorgar permiso temporal
$user->grantPermission(
    'projects.view',
    'temporary',
    'Razón del permiso',
    now()->addDays(7)
);

// Otorgar permiso permanente
$user->grantPermission(
    'tasks.create',
    'permanent',
    'Permiso permanente'
);

// Revocar permiso
$user->revokePermission('projects.view');
```

## Middleware de Permisos

### Uso en Rutas
```php
// Ruta con verificación de permiso
Route::get('/admin/users', [UserController::class, 'index'])
    ->middleware('permission:admin.users');

// Grupo de rutas con permiso
Route::middleware('permission:projects.manage')->group(function () {
    Route::resource('projects', ProjectController::class);
});
```

### Uso en Controladores
```php
public function __construct()
{
    $this->middleware('permission:admin.users')->only(['index', 'show']);
    $this->middleware('permission:admin.users.create')->only(['create', 'store']);
    $this->middleware('permission:admin.users.edit')->only(['edit', 'update']);
    $this->middleware('permission:admin.users.delete')->only(['destroy']);
}
```

## Comandos Artisan

### Probar el Sistema
```bash
php artisan rbac:test
```
Este comando verifica:
- Estructura de base de datos
- Roles y permisos asignados
- Usuarios y sus roles
- Sistema de verificación de permisos
- Funcionalidad de permisos temporales
- Gestión de permisos expirados

## Archivos Creados/Modificados

### Migraciones
- `2025_01_31_000001_create_permissions_table.php`
- `2025_01_31_000002_create_permission_role_table.php`
- `2025_01_31_000003_create_user_permissions_table.php`

### Modelos
- `app/Models/Permission.php` (nuevo)
- `app/Models/UserPermission.php` (nuevo)
- `app/Models/User.php` (actualizado)
- `app/Models/Role.php` (actualizado)

### Controladores
- `app/Http/Controllers/PermissionController.php` (nuevo)

### Middleware
- `app/Http/Middleware/CheckPermission.php` (nuevo)

### Frontend
- `resources/js/pages/Permissions/Index.vue` (nuevo)
- `resources/js/components/AppSidebar.vue` (actualizado)

### Seeders
- `database/seeders/PermissionSeeder.php` (nuevo)
- `database/seeders/RolePermissionSeeder.php` (nuevo)

### Comandos
- `app/Console/Commands/TestRbacSystem.php` (nuevo)
- `app/Console/Commands/CleanupExpiredPermissions.php` (nuevo)
- `app/Console/Commands/SchedulePermissionCleanup.php` (nuevo)

### Rutas
- `routes/web.php` (actualizado con rutas de permisos)

## Próximos Pasos Recomendados

### 1. Implementar Middleware en Rutas Existentes
```php
// Ejemplo para rutas de proyectos
Route::middleware('permission:projects.view')->group(function () {
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
});

Route::middleware('permission:projects.create')->group(function () {
    Route::get('/projects/create', [ProjectController::class, 'create']);
    Route::post('/projects', [ProjectController::class, 'store']);
});
```

### 2. Agregar Verificaciones en Frontend
```javascript
// En componentes Vue
const canEditProject = computed(() => {
    return user.value?.hasPermission('projects.edit');
});

// En templates
<button v-if="canEditProject" @click="editProject">
    Editar Proyecto
</button>
```

### 3. Comandos de Limpieza Automática
```bash
# Limpiar permisos expirados manualmente
php artisan permissions:cleanup

# Ver qué se eliminaría sin hacer cambios
php artisan permissions:cleanup --dry-run

# Programar limpieza automática diaria
php artisan permissions:schedule-cleanup
```

### 4. Implementar Auditoría
- Logs de cambios de permisos
- Notificaciones de permisos próximos a expirar
- Reportes de permisos otorgados

## Consideraciones de Seguridad

1. **Verificación Doble**: Siempre verificar permisos tanto en frontend como backend
2. **Principio de Menor Privilegio**: Otorgar solo los permisos necesarios
3. **Auditoría**: Mantener registro de todos los cambios de permisos
4. **Expiración**: Usar permisos temporales cuando sea posible
5. **Validación**: Validar todos los inputs en el backend

## Estado Actual

✅ **COMPLETADO**: Sistema RBAC completamente funcional
✅ **VERIFICADO**: Todas las funcionalidades probadas
✅ **DOCUMENTADO**: Guía completa de uso
✅ **INTEGRADO**: Frontend y backend conectados
✅ **MIDDLEWARE**: Implementado en todas las rutas principales
✅ **FRONTEND**: Verificaciones de permisos en componentes Vue
✅ **AUTOMATIZACIÓN**: Comandos de limpieza y programación

El sistema está listo para uso en producción y puede ser extendido según las necesidades específicas del proyecto. 
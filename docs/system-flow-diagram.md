# 📊 Diagrama de Flujo del Sistema - Tracker

## 🎯 **Tipos de Usuarios**

### 👤 **Administrador (Admin)**
- Acceso completo a todas las funcionalidades
- Puede gestionar usuarios, proyectos, sprints y tareas
- Puede asignar roles y permisos

### 👨‍💻 **Desarrollador (Developer)**
- Puede ver proyectos asignados
- Puede ver y actualizar tareas asignadas
- Puede crear y editar tareas en sprints asignados

### 👥 **Usuario Regular (User)**
- Puede ver proyectos públicos
- Puede ver tareas (solo lectura)
- Acceso limitado a funcionalidades

---

## 🔄 **Flujo Principal del Sistema**

```mermaid
graph TD
    A[Usuario Accede] --> B{¿Está Autenticado?}
    B -->|No| C[Página de Login]
    B -->|Sí| D[Dashboard Principal]
    
    C --> E[Login Exitoso]
    E --> D
    
    D --> F{¿Qué quiere hacer?}
    
    F -->|Ver Proyectos| G[Página de Proyectos]
    F -->|Ver Sprints| H[Página de Sprints]
    F -->|Ver Tareas| I[Página de Tareas]
    F -->|Ver Usuarios| J[Página de Usuarios]
    F -->|Configuración| K[Página de Configuración]
    
    G --> L{¿Crear Proyecto?}
    L -->|Sí| M[Formulario Crear Proyecto]
    L -->|No| N[Ver Lista de Proyectos]
    
    N --> O{¿Ver Detalle de Proyecto?}
    O -->|Sí| P[Página de Detalle de Proyecto]
    O -->|No| N
    
    P --> Q{¿Crear Sprint?}
    Q -->|Sí| R[Formulario Crear Sprint]
    Q -->|No| S[Ver Sprints del Proyecto]
    
    S --> T{¿Ver Detalle de Sprint?}
    T -->|Sí| U[Página de Detalle de Sprint]
    T -->|No| S
    
    U --> V{¿Crear Tarea?}
    V -->|Sí| W[Formulario Crear Tarea]
    V -->|No| X[Ver Tareas del Sprint]
    
    X --> Y{¿Ver Detalle de Tarea?}
    Y -->|Sí| Z[Página de Detalle de Tarea]
    Y -->|No| X
    
    Z --> AA{¿Editar Tarea?}
    AA -->|Sí| BB[Modo Edición de Tarea]
    AA -->|No| Z
    
    BB --> CC[Guardar Cambios]
    CC --> Z
```

---

## 📋 **Flujo Detallado por Funcionalidad**

### 🏗️ **Gestión de Proyectos**

```mermaid
graph TD
    A[Página de Proyectos] --> B{¿Tiene Permisos?}
    B -->|No| C[Acceso Denegado]
    B -->|Sí| D[Ver Lista de Proyectos]
    
    D --> E{¿Crear Proyecto?}
    E -->|Sí| F[Formulario Crear Proyecto]
    E -->|No| G[Ver Proyecto Específico]
    
    F --> H[Validar Datos]
    H --> I{¿Datos Válidos?}
    I -->|No| F
    I -->|Sí| J[Guardar Proyecto]
    J --> K[Redirigir a Proyecto]
    
    G --> L[Página de Detalle de Proyecto]
    L --> M{¿Editar Proyecto?}
    M -->|Sí| N[Formulario Editar Proyecto]
    M -->|No| O{¿Crear Sprint?}
    
    N --> P[Guardar Cambios]
    P --> L
    
    O -->|Sí| Q[Formulario Crear Sprint]
    O -->|No| R[Ver Sprints]
    
    Q --> S[Guardar Sprint]
    S --> L
```

### 🏃 **Gestión de Sprints**

```mermaid
graph TD
    A[Página de Sprints] --> B{¿Tiene Permisos?}
    B -->|No| C[Acceso Denegado]
    B -->|Sí| D[Ver Lista de Sprints]
    
    D --> E{¿Ver Sprint Específico?}
    E -->|Sí| F[Página de Detalle de Sprint]
    E -->|No| G{¿Crear Sprint?}
    
    F --> H[Ver Información del Sprint]
    H --> I[Ver Tareas del Sprint]
    I --> J{¿Crear Tarea?}
    J -->|Sí| K[Formulario Crear Tarea]
    J -->|No| L{¿Editar Sprint?}
    
    K --> M[Guardar Tarea]
    M --> F
    
    L -->|Sí| N[Formulario Editar Sprint]
    L -->|No| O[Ver Estadísticas]
    
    N --> P[Guardar Cambios]
    P --> F
    
    G -->|Sí| Q[Formulario Crear Sprint]
    G -->|No| D
    
    Q --> R[Guardar Sprint]
    R --> D
```

### ✅ **Gestión de Tareas**

```mermaid
graph TD
    A[Página de Tareas] --> B{¿Tiene Permisos?}
    B -->|No| C[Acceso Denegado]
    B -->|Sí| D[Ver Lista de Tareas]
    
    D --> E{¿Ver Tarea Específica?}
    E -->|Sí| F[Página de Detalle de Tarea]
    E -->|No| G{¿Crear Tarea?}
    
    F --> H[Ver Información de Tarea]
    H --> I{¿Editar Tarea?}
    I -->|Sí| J[Modo Edición]
    I -->|No| K[Ver Historial]
    
    J --> L[Editar Campos]
    L --> M{¿Guardar Cambios?}
    M -->|Sí| N[Enviar Actualización]
    M -->|No| O[Cancelar Edición]
    
    N --> P{¿Actualización Exitosa?}
    P -->|Sí| Q[Mostrar Confirmación]
    P -->|No| R[Mostrar Errores]
    
    Q --> F
    R --> J
    
    G -->|Sí| S[Formulario Crear Tarea]
    G -->|No| D
    
    S --> T[Guardar Tarea]
    T --> D
```

---

## 🔐 **Sistema de Permisos**

### 👤 **Administrador**
- ✅ Crear, editar, eliminar proyectos
- ✅ Crear, editar, eliminar sprints
- ✅ Crear, editar, eliminar tareas
- ✅ Gestionar usuarios y roles
- ✅ Ver todas las estadísticas
- ✅ Acceso a configuración del sistema

### 👨‍💻 **Desarrollador**
- ✅ Ver proyectos asignados
- ✅ Ver sprints de proyectos asignados
- ✅ Crear y editar tareas en sprints asignados
- ✅ Actualizar estado de tareas propias
- ✅ Ver estadísticas de proyectos asignados
- ❌ Gestionar usuarios
- ❌ Eliminar proyectos/sprints

### 👥 **Usuario Regular**
- ✅ Ver proyectos públicos
- ✅ Ver sprints públicos
- ✅ Ver tareas (solo lectura)
- ✅ Ver estadísticas básicas
- ❌ Crear o editar contenido
- ❌ Acceso a configuración

---

## 🚨 **Problemas Identificados**

### 1. **Página de Detalle de Tarea**
- ❌ **Botón "Edit Task" no aparece**
- ❌ **Modo de edición no funciona**
- ❌ **Formulario de edición no se muestra**

### 2. **Navegación**
- ❌ **Botón "View more" en tareas no funciona correctamente**
- ❌ **Redirección entre páginas inconsistente**

### 3. **Permisos**
- ❌ **Sistema de permisos no está implementado completamente**
- ❌ **Usuarios pueden acceder a funcionalidades sin autorización**

### 4. **Validación**
- ❌ **Validación de formularios incompleta**
- ❌ **Manejo de errores inconsistente**

---

## 🎯 **Prioridades de Desarrollo**

### 🔥 **Alta Prioridad**
1. **Arreglar edición de tareas**
2. **Implementar navegación correcta**
3. **Completar sistema de permisos**

### 🔶 **Media Prioridad**
1. **Mejorar validación de formularios**
2. **Implementar manejo de errores**
3. **Optimizar rendimiento**

### 🔵 **Baja Prioridad**
1. **Agregar funcionalidades avanzadas**
2. **Mejorar UI/UX**
3. **Implementar reportes**

---

## 📝 **Próximos Pasos**

1. **Diagnosticar problema del botón "Edit Task"**
2. **Verificar estructura de componentes**
3. **Implementar funcionalidad de edición**
4. **Probar flujo completo**
5. **Documentar cambios**

---

*Este diagrama se actualizará conforme se resuelvan los problemas identificados.* 
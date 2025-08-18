# Evolución del Esquema de Base de Datos - Sistema Tracker

## Cronología de Migraciones

### Fase 1: Estructura Base (Abril 2025)
```mermaid
timeline
    title Evolución del Esquema de Base de Datos
    section Fase 1: Estructura Base
        2025-04-06 : Crear users table
        2025-04-06 : Crear roles table
        2025-04-06 : Crear projects table
        2025-04-06 : Crear sprints table
        2025-04-06 : Crear tasks table
        2025-04-06 : Crear role_user table
        2025-04-06 : Crear project_user table
```

### Fase 2: Sistema de Bugs (Enero 2024)
```mermaid
timeline
    title Sistema de Gestión de Bugs
    section Fase 2: Bugs y Time Tracking
        2024-01-15 : Crear bugs table
        2024-01-15 : Crear bug_time_logs table
        2024-01-15 : Crear bug_comments table
        2024-01-15 : Crear suggestions table
```

### Fase 3: Sistema de Permisos (Enero 2025)
```mermaid
timeline
    title Sistema de Permisos Granulares
    section Fase 3: Permisos y Notificaciones
        2025-01-31 : Crear permissions table
        2025-01-31 : Crear permission_role table
        2025-01-31 : Crear user_permissions table
        2025-01-15 : Crear notifications table
```

### Fase 4: Workflow Avanzado (Julio 2025)
```mermaid
timeline
    title Workflow y Time Tracking Avanzado
    section Fase 4: Mejoras de Workflow
        2025-07-29 : Crear task_time_logs table
        2025-07-29 : Modificar tasks table para nuevo workflow
        2025-07-29 : Agregar estados de tareas
        2025-07-29 : Agregar rol team_leader
```

### Fase 5: Reportes y Pagos (Agosto 2025)
```mermaid
timeline
    title Sistema de Reportes y Pagos
    section Fase 5: Reportes y Analytics
        2025-08-01 : Crear payment_reports table
        2025-08-05 : Crear developer_activity_logs table
        2025-08-10 : Agregar campos avanzados a sprints
        2025-08-11 : Agregar campos de finalización a projects
```

## Diagrama de Dependencias de Migraciones

```mermaid
graph TD
    A[0001_01_01_000000_create_users_table] --> B[2025_04_06_061240_create_roles_table]
    A --> C[2025_04_06_062243_create_projects_table]
    A --> D[2025_04_06_061952_create_role_user_table]
    A --> E[2025_04_06_070115_create_project_user_table]
    
    C --> F[2025_04_06_070244_create_sprints_table]
    F --> G[2025_04_06_070452_create_tasks_table]
    
    A --> H[2024_01_15_000001_create_bugs_table]
    F --> H
    C --> H
    
    H --> I[2024_01_15_000002_create_bug_time_logs_table]
    H --> J[2024_01_15_000003_create_bug_comments_table]
    
    B --> K[2025_01_31_000001_create_permissions_table]
    B --> L[2025_01_31_000002_create_permission_role_table]
    A --> M[2025_01_31_000003_create_user_permissions_table]
    K --> M
    
    A --> N[2025_01_15_000002_create_notifications_table]
    
    G --> O[2025_07_29_086000_create_task_time_logs_table]
    A --> O
    
    A --> P[2025_08_01_045418_create_payment_reports_table]
    
    style A fill:#e1f5fe
    style B fill:#f3e5f5
    style C fill:#e8f5e8
    style H fill:#fff3e0
    style K fill:#fce4ec
```

## Campos Agregados por Migraciones Posteriores

### Tabla `tasks` - Evolución de Campos

```mermaid
graph LR
    A[Campos Base] --> B[Campos de Workflow]
    B --> C[Campos de QA]
    C --> D[Campos de Team Leader]
    D --> E[Campos de Time Tracking]
    E --> F[Campos de Auto-Close]
    
    A --> A1[name, description, status, priority, category, story_points, sprint_id, user_id]
    B --> B1[estimated_start, estimated_finish, estimated_hours, actual_start, actual_finish, actual_hours]
    C --> C1[qa_testing_started_at, qa_testing_finished_at, qa_tested_by, qa_testing_notes]
    D --> D1[team_leader_reviewed_by, team_leader_reviewed_at, team_leader_notes]
    E --> E1[work_started_at, is_working, current_session_start]
    F --> F1[auto_close_at, auto_close_enabled]
```

### Tabla `bugs` - Evolución de Campos

```mermaid
graph LR
    A[Campos Base] --> B[Campos de Workflow]
    B --> C[Campos de QA]
    C --> D[Campos de Time Tracking]
    D --> E[Campos de Auto-Pause]
    
    A --> A1[title, description, status, importance, bug_type, project_id, user_id]
    B --> B1[assigned_by, assigned_at, resolved_by, resolved_at, verified_by, verified_at]
    C --> C1[qa_testing_started_at, qa_testing_finished_at, qa_tested_by]
    D --> D1[work_started_at, is_working, total_time_seconds, estimated_hours, actual_hours]
    E --> E1[auto_paused, auto_paused_at, auto_pause_reason, alert_count, last_alert_at]
```

### Tabla `sprints` - Evolución de Campos

```mermaid
graph LR
    A[Campos Base] --> B[Campos Esenciales]
    B --> C[Campos Avanzados]
    C --> D[Campos de Retrospectiva]
    
    A --> A1[name, goal, start_date, end_date, project_id]
    B --> B1[status, velocity, capacity, burndown_data]
    C --> C1[team_velocity, sprint_goal_achievement, impediments, blockers]
    D --> D1[retrospective_notes, lessons_learned, action_items, improvement_suggestions]
```

### Tabla `projects` - Evolución de Campos

```mermaid
graph LR
    A[Campos Base] --> B[Campos Esenciales]
    B --> C[Campos Avanzados]
    C --> D[Campos de Finalización]
    
    A --> A1[name, description, status, create_by]
    B --> B1[client_name, client_email, budget, timeline]
    C --> C1[team_size, technology_stack, repository_url, deployment_url]
    D --> D1[finished_at, final_delivery_date, client_feedback, project_rating]
```

## Enums y Estados del Sistema

### Roles del Sistema
```mermaid
graph TD
    A[Roles] --> B[admin]
    A --> C[team_leader]
    A --> D[developer]
    A --> E[qa]
    A --> F[client]
    
    B --> B1[Acceso completo al sistema]
    C --> C1[Gestión de equipo y proyectos]
    D --> D1[Desarrollo de tareas y bugs]
    E --> E1[Testing y verificación]
    F --> F1[Acceso limitado a proyectos]
```

### Estados de Tareas
```mermaid
stateDiagram-v2
    [*] --> to_do
    to_do --> in_progress
    in_progress --> done
    in_progress --> to_do
    done --> in_progress
    done --> [*]
    
    note right of to_do : Estado inicial
    note right of in_progress : Trabajo activo
    note right of done : Completada
```

### Estados de Bugs
```mermaid
stateDiagram-v2
    [*] --> new
    new --> assigned
    assigned --> in_progress
    in_progress --> resolved
    resolved --> verified
    verified --> closed
    in_progress --> new
    resolved --> in_progress
    verified --> in_progress
    closed --> in_progress
```

### Estados de Proyectos
```mermaid
graph LR
    A[active] --> B[inactive]
    A --> C[completed]
    A --> D[cancelled]
    A --> E[paused]
    
    B --> A
    E --> A
```

## Índices y Optimizaciones

### Índices de Rendimiento Implementados

```sql
-- Índices en bugs
CREATE INDEX idx_bugs_project_status ON bugs(project_id, status);
CREATE INDEX idx_bugs_user_status ON bugs(user_id, status);
CREATE INDEX idx_bugs_sprint_status ON bugs(sprint_id, status);
CREATE INDEX idx_bugs_importance_status ON bugs(importance, status);
CREATE INDEX idx_bugs_type_status ON bugs(bug_type, status);
CREATE INDEX idx_bugs_priority_score ON bugs(priority_score);

-- Índices en tasks
CREATE INDEX idx_tasks_sprint_status ON tasks(sprint_id, status);
CREATE INDEX idx_tasks_user_status ON tasks(user_id, status);
CREATE INDEX idx_tasks_priority ON tasks(priority);
CREATE INDEX idx_tasks_category ON tasks(category);

-- Índices en time logs
CREATE INDEX idx_task_time_logs_task_user ON task_time_logs(task_id, user_id);
CREATE INDEX idx_task_time_logs_started_at ON task_time_logs(started_at);
CREATE INDEX idx_bug_time_logs_bug_started ON bug_time_logs(bug_id, started_at);
CREATE INDEX idx_bug_time_logs_user_started ON bug_time_logs(user_id, started_at);

-- Índices en payment reports
CREATE INDEX idx_payment_reports_user_week ON payment_reports(user_id, week_start_date);
CREATE INDEX idx_payment_reports_week_range ON payment_reports(week_start_date, week_end_date);
CREATE INDEX idx_payment_reports_status ON payment_reports(status);

-- Índices en notifications
CREATE INDEX idx_notifications_user_read ON notifications(user_id, read);
CREATE INDEX idx_notifications_type_created ON notifications(type, created_at);

-- Índices en user permissions
CREATE INDEX idx_user_permissions_user_permission ON user_permissions(user_id, permission_id);
CREATE INDEX idx_user_permissions_expires_at ON user_permissions(expires_at);
```

## Métricas de Rendimiento

### Tamaños de Tablas Estimados
- **users**: ~100-1000 registros
- **projects**: ~50-200 registros
- **sprints**: ~200-1000 registros
- **tasks**: ~1000-10000 registros
- **bugs**: ~500-5000 registros
- **task_time_logs**: ~10000-100000 registros
- **bug_time_logs**: ~5000-50000 registros
- **payment_reports**: ~1000-5000 registros

### Consultas Optimizadas
- Búsqueda de tareas por sprint y estado
- Filtrado de bugs por proyecto y prioridad
- Reportes de tiempo por usuario y período
- Notificaciones no leídas por usuario
- Permisos activos por usuario
- Reportes de pago por semana

## Consideraciones de Escalabilidad

### Particionamiento Recomendado
- **task_time_logs**: Particionar por fecha
- **bug_time_logs**: Particionar por fecha
- **notifications**: Particionar por usuario
- **payment_reports**: Particionar por año

### Estrategias de Cache
- Cache de permisos de usuario
- Cache de roles y relaciones
- Cache de métricas de proyecto
- Cache de reportes de tiempo

### Backup y Recuperación
- Backup diario de tablas críticas
- Backup semanal completo
- Retención de 30 días para backups incrementales
- Retención de 1 año para backups completos

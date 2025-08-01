# Sistema de Gestión de Tareas - Tracker

Un sistema completo de gestión de tareas desarrollado con Laravel, Inertia.js y Vue.js, diseñado para equipos de desarrollo con diferentes roles y funcionalidades avanzadas de seguimiento de tiempo.

## 🚀 Características Principales

### 👥 Gestión de Usuarios y Roles
- **Administradores**: Acceso completo al sistema, métricas y reportes
- **Team Leaders**: Gestión de equipos, aprobación de tareas y asignaciones
- **Desarrolladores**: Trabajo con tareas, seguimiento de tiempo y auto-asignación

### 📋 Gestión de Proyectos
- Creación y gestión de proyectos
- Organización en sprints
- Asignación de usuarios a proyectos
- Seguimiento de progreso

### 🎯 Sistema de Tareas
- **Kanban Board**: Vista visual con drag & drop
- **Estados**: To Do, In Progress, Done
- **Prioridades**: Alta, Media, Baja
- **Auto-asignación**: Los desarrolladores pueden asignarse tareas
- **Asignación por Team Leaders**: Control centralizado de asignaciones

### ⏱️ Seguimiento de Tiempo en Tiempo Real
- Inicio, pausa, reanudación y finalización de trabajo
- Seguimiento de tiempo en tiempo real
- Historial completo de sesiones de trabajo
- Comparación con tiempo estimado

### ✅ Sistema de Aprobación
- Aprobación/rechazo de tareas por Team Leaders
- Motivos de rechazo
- Notificaciones automáticas
- Flujo de trabajo controlado

### 📊 Dashboards y Reportes
- **Dashboard de Desarrollador**: Vista Kanban y tareas asignadas
- **Dashboard de Team Leader**: Gestión de equipo y aprobaciones
- **Dashboard de Administrador**: Métricas del sistema y reportes

### 📈 Métricas y Analytics
- Eficiencia de desarrolladores
- Tiempo promedio por tarea
- Tareas que requieren atención
- Reportes por período (semana, mes, trimestre, año)
- Comparación de tiempo estimado vs real

### 🎨 Interfaz Moderna
- Diseño responsive con Tailwind CSS
- Componentes Vue.js reutilizables
- Notificaciones en tiempo real
- Animaciones y transiciones suaves

## 🛠️ Tecnologías Utilizadas

### Backend
- **Laravel 11**: Framework PHP
- **MySQL/PostgreSQL**: Base de datos
- **Eloquent ORM**: Mapeo objeto-relacional
- **Laravel Sanctum**: Autenticación API

### Frontend
- **Vue.js 3**: Framework JavaScript
- **Inertia.js**: SPA sin API
- **Tailwind CSS**: Framework CSS
- **Vite**: Build tool

### Herramientas
- **Composer**: Gestión de dependencias PHP
- **npm**: Gestión de dependencias Node.js
- **Git**: Control de versiones

## 📦 Instalación

### Prerrequisitos
- PHP 8.2 o superior
- Composer
- Node.js 18 o superior
- npm
- MySQL/PostgreSQL

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone <repository-url>
   cd Tracker
   ```

2. **Instalar dependencias PHP**
   ```bash
   composer install
   ```

3. **Instalar dependencias Node.js**
   ```bash
   npm install
   ```

4. **Configurar variables de entorno**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configurar base de datos en .env**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=tracker
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Ejecutar migraciones**
   ```bash
   php artisan migrate
   ```

7. **Ejecutar seeders**
   ```bash
   php artisan db:seed
   ```

8. **Compilar assets**
   ```bash
   npm run build
   ```

9. **Iniciar servidor**
   ```bash
   php artisan serve
   ```

10. **Acceder a la aplicación**
    ```
    http://localhost:8000
    ```

## 👤 Credenciales por Defecto

### Administrador
- **Email**: admin@example.com
- **Contraseña**: password

### Team Leader
- **Email**: teamleader@example.com
- **Contraseña**: password

### Desarrollador
- **Email**: developer@example.com
- **Contraseña**: password

## 🏗️ Arquitectura del Sistema

### Estructura de Directorios

```
Tracker/
├── app/
│   ├── Http/Controllers/     # Controladores
│   ├── Models/              # Modelos Eloquent
│   ├── Services/            # Servicios de negocio
│   └── Providers/           # Proveedores de servicios
├── database/
│   ├── migrations/          # Migraciones de BD
│   └── seeders/            # Seeders de datos
├── resources/
│   └── js/
│       ├── components/      # Componentes Vue
│       ├── pages/          # Páginas de la aplicación
│       ├── composables/    # Composables Vue
│       └── layouts/        # Layouts de la aplicación
├── routes/                 # Definición de rutas
└── scripts/               # Scripts de verificación
```

### Modelos Principales

#### User
- Gestión de usuarios y autenticación
- Relaciones con roles y proyectos
- Métodos de autorización

#### Task
- Gestión de tareas
- Seguimiento de tiempo
- Estados y prioridades
- Relaciones con proyectos y usuarios

#### TaskTimeLog
- Registro de sesiones de trabajo
- Seguimiento de tiempo en tiempo real
- Historial completo de actividades

#### Project
- Gestión de proyectos
- Organización en sprints
- Asignación de usuarios

### Servicios Principales

#### TaskAssignmentService
- Asignación de tareas por Team Leaders
- Auto-asignación de desarrolladores
- Gestión de disponibilidad

#### TaskTimeTrackingService
- Seguimiento de tiempo en tiempo real
- Gestión de sesiones de trabajo
- Cálculo de métricas de tiempo

#### TaskApprovalService
- Aprobación/rechazo de tareas
- Gestión de flujo de trabajo
- Notificaciones automáticas

#### AdminDashboardService
- Métricas del sistema
- Reportes avanzados
- Analytics de rendimiento

## 🔄 Flujo de Trabajo

### Para Desarrolladores
1. **Acceso al Kanban**: Vista visual de tareas
2. **Auto-asignación**: Tomar tareas disponibles
3. **Trabajo**: Iniciar, pausar, reanudar y finalizar tareas
4. **Seguimiento**: Monitoreo de tiempo en tiempo real

### Para Team Leaders
1. **Dashboard**: Vista general del equipo
2. **Asignación**: Asignar tareas a desarrolladores
3. **Aprobación**: Revisar y aprobar tareas completadas
4. **Gestión**: Monitorear progreso del equipo

### Para Administradores
1. **Métricas**: Vista completa del sistema
2. **Reportes**: Análisis de rendimiento
3. **Gestión**: Control de usuarios y proyectos
4. **Monitoreo**: Identificación de problemas

## 📊 Funcionalidades Avanzadas

### Seguimiento de Tiempo
- **Tiempo Real**: Actualización cada segundo
- **Sesiones**: Registro de inicio, pausa y reanudación
- **Historial**: Log completo de actividades
- **Comparación**: Estimado vs tiempo real

### Sistema de Notificaciones
- **Toast**: Notificaciones en tiempo real
- **Email**: Notificaciones automáticas
- **Estados**: Feedback visual de acciones

### Métricas y Reportes
- **Eficiencia**: Porcentaje de tareas completadas a tiempo
- **Productividad**: Tiempo promedio por tarea
- **Tendencias**: Análisis temporal de rendimiento
- **Alertas**: Tareas que requieren atención

## 🧪 Scripts de Verificación

El proyecto incluye scripts de verificación para cada fase:

```bash
# Verificación de fases individuales
php scripts/verify_phase1.php  # Configuración del proyecto
php scripts/verify_phase2.php  # Autenticación y autorización
php scripts/verify_phase3.php  # Modelos y migraciones
php scripts/verify_phase4.php  # Controladores y rutas
php scripts/verify_phase5.php  # Servicios y lógica de negocio
php scripts/verify_phase6.php  # Interfaces de usuario

# Verificación completa
php scripts/final_verification.php
```

## 🚀 Despliegue

### Producción
1. Configurar variables de entorno de producción
2. Ejecutar `composer install --optimize-autoloader --no-dev`
3. Ejecutar `npm run build`
4. Configurar servidor web (Apache/Nginx)
5. Configurar base de datos de producción

### Docker (Opcional)
```bash
docker-compose up -d
```

## 🤝 Contribución

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 🆘 Soporte

Para soporte técnico o preguntas:
- Crear un issue en GitHub
- Contactar al equipo de desarrollo
- Revisar la documentación técnica

## 🎯 Roadmap

### Próximas Funcionalidades
- [ ] Integración con Git (commits automáticos)
- [ ] API REST para integraciones externas
- [ ] Sistema de chat en tiempo real
- [ ] Reportes PDF automáticos
- [ ] Integración con herramientas de CI/CD
- [ ] Aplicación móvil

---

**Desarrollado con ❤️ usando Laravel, Vue.js y Tailwind CSS** 
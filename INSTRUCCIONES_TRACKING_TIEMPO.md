# ⏱️ Sistema de Tracking de Tiempo - Instrucciones de Prueba

## 🎯 Estado Actual
El sistema de tracking de tiempo ha sido **completamente implementado y probado**. Ahora los desarrolladores pueden:
- ✅ Iniciar trabajo en tareas
- ✅ Pausar trabajo
- ✅ Reanudar trabajo
- ✅ Finalizar trabajo
- ✅ Ver tiempo en tiempo real
- ✅ Ver tiempo total acumulado

## 🚀 Cómo Probar el Sistema

### **1. Iniciar el Servidor**
```bash
php artisan serve
```

### **2. Acceder al Sistema**
- Ve a: http://localhost:8000
- Login como desarrollador: `developer1@example.com` / `password`

### **3. Navegar a las Tareas**
- Ve a la página de tareas: `/tasks`
- Verás la tarea de prueba: **"Probar sistema de tracking de tiempo"**

### **4. Probar el Tracking de Tiempo**

#### **Iniciar Trabajo:**
1. Busca la tarea "Probar sistema de tracking de tiempo"
2. Haz clic en el botón **"Iniciar"** (verde)
3. Verás que:
   - El estado cambia a "In Progress"
   - Aparece un contador de tiempo en tiempo real
   - El botón cambia a "Pausar"

#### **Pausar Trabajo:**
1. Haz clic en el botón **"Pausar"** (amarillo)
2. Verás que:
   - El contador se detiene
   - El tiempo se guarda
   - El botón cambia a "Reanudar"

#### **Reanudar Trabajo:**
1. Haz clic en el botón **"Reanudar"** (azul)
2. Verás que:
   - El contador continúa desde donde se pausó
   - El tiempo total se acumula
   - El botón cambia a "Pausar"

#### **Finalizar Trabajo:**
1. Haz clic en el botón **"Finalizar"** (rojo)
2. Verás que:
   - El estado cambia a "Done"
   - El tiempo total se guarda
   - La tarea queda pendiente de revisión por team leader

## 📊 Funcionalidades Implementadas

### **Backend (Laravel)**
- ✅ **TaskTimeTrackingService**: Servicio completo para manejo de tracking
- ✅ **TaskController**: Métodos para start, pause, resume, finish
- ✅ **Task Model**: Campos para tracking (is_working, work_started_at, total_time_seconds)
- ✅ **TaskTimeLog Model**: Logs detallados de todas las acciones
- ✅ **Rutas API**: POST endpoints para todas las acciones
- ✅ **Migraciones**: Tablas y campos necesarios

### **Frontend (Vue.js)**
- ✅ **TaskCard Component**: Interfaz completa con botones de tracking
- ✅ **Task/Index Page**: Manejo de eventos de tracking
- ✅ **Tiempo en Tiempo Real**: Contador que se actualiza cada segundo
- ✅ **Estados Visuales**: Botones que cambian según el estado
- ✅ **Manejo de Errores**: Alertas para errores de API

## 🔧 Detalles Técnicos

### **Campos de Tracking en Tasks:**
- `is_working`: Boolean - Indica si la tarea está siendo trabajada
- `work_started_at`: Timestamp - Cuándo se inició el trabajo actual
- `total_time_seconds`: Integer - Tiempo total acumulado en segundos

### **Logs de Tiempo:**
- `task_time_logs` table: Registra todas las acciones
- Campos: started_at, paused_at, resumed_at, finished_at, duration_seconds
- Acciones: start, pause, resume, finish

### **Rutas API:**
- `POST /tasks/{task}/start-work`
- `POST /tasks/{task}/pause-work`
- `POST /tasks/{task}/resume-work`
- `POST /tasks/{task}/finish-work`

## 🎨 Interfaz de Usuario

### **Estados de la Tarea:**
- **To Do**: Botón "Iniciar" (verde)
- **In Progress + Trabajando**: Botón "Pausar" (amarillo) + "Finalizar" (rojo)
- **In Progress + Pausado**: Botón "Reanudar" (azul)
- **Done**: Sin botones de tracking

### **Información Visual:**
- **Tiempo Estimado**: Mostrado en la tarjeta
- **Tiempo Total**: Tiempo acumulado de todas las sesiones
- **Tiempo de Sesión Actual**: Tiempo en tiempo real (solo cuando está trabajando)
- **Estado de Trabajo**: Indicador visual de si está trabajando o no

## 🧪 Pruebas Realizadas

### **Scripts de Prueba:**
- ✅ `scripts/test_time_tracking.php`: Prueba completa del backend
- ✅ `scripts/create_test_task.php`: Crea tarea de prueba
- ✅ Todas las funcionalidades verificadas y funcionando

### **Resultados de Pruebas:**
- ✅ startWork: Funciona correctamente
- ✅ pauseWork: Funciona correctamente
- ✅ resumeWork: Funciona correctamente
- ✅ finishWork: Funciona correctamente
- ✅ Logs de tiempo: Se generan correctamente
- ✅ Tiempo en tiempo real: Se actualiza cada segundo

## 🚨 Solución de Problemas

### **Si no ves los botones de tracking:**
1. Verifica que estés logueado como desarrollador
2. Verifica que la tarea esté asignada a ti
3. Verifica que la tarea no esté en estado "done"

### **Si los botones no funcionan:**
1. Verifica que el servidor esté corriendo
2. Verifica la consola del navegador para errores
3. Verifica que las migraciones se ejecutaron

### **Si el tiempo no se actualiza:**
1. Verifica que la tarea esté en estado "is_working = true"
2. Verifica que work_started_at tenga un valor
3. Verifica la consola del navegador para errores JavaScript

## 🎉 ¡Sistema Listo!

El sistema de tracking de tiempo está **completamente funcional** y listo para usar. Los desarrolladores pueden ahora:

1. **Iniciar** trabajo en sus tareas asignadas
2. **Pausar** cuando necesiten hacer una pausa
3. **Reanudar** cuando vuelvan al trabajo
4. **Finalizar** cuando completen la tarea
5. **Ver** el tiempo en tiempo real mientras trabajan
6. **Ver** el tiempo total acumulado

¡El sistema está listo para mejorar la productividad y el seguimiento del tiempo de trabajo! 
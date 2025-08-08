# 🔐 CREDENCIALES PARA TESTING DEL SISTEMA DE QA

## 👥 USUARIOS DISPONIBLES

### 🔧 **Developer (Desarrollador)**
- **Email:** `juan.martinez324@test.com`
- **Contraseña:** `password`
- **Rol:** `developer`
- **Nombre:** Juan Martinez
- **Funcionalidades:**
  - Crear y gestionar tareas
  - Marcar tareas como completadas
  - Recibir notificaciones de QA y Team Leader
  - Ver tareas asignadas

### 👨‍💼 **Team Leader**
- **Email:** `roberto.silva190@test.com`
- **Contraseña:** `password`
- **Rol:** `team_leader`
- **Nombre:** Roberto Silva
- **Funcionalidades:**
  - Aprobar tareas completadas por developers
  - Revisar tareas aprobadas por QA
  - Aprobar completamente o solicitar cambios
  - Dashboard con métricas de equipo
  - Vista QA Review para revisión final

### 🧪 **QA Tester**
- **Email:** `qa@tracker.com`
- **Contraseña:** `password`
- **Rol:** `qa`
- **Nombre:** QA Tester
- **Funcionalidades:**
  - Dashboard dedicado para QA
  - Vista Kanban con 4 columnas
  - Asignar tareas a sí mismo
  - Aprobar o rechazar tareas con notas
  - Probar bugs finalizados
  - Recibir notificaciones automáticas

### 👑 **Admin**
- **Email:** `carmen.ruiz79@test.com`
- **Contraseña:** `password`
- **Rol:** `admin`
- **Nombre:** Carmen Ruiz
- **Funcionalidades:**
  - Acceso completo al sistema
  - Gestión de usuarios y proyectos
  - Dashboard administrativo

---

## 🚀 FLUJO DE TESTING RECOMENDADO

### **Paso 1: Login como Developer**
```
Email: juan.martinez324@test.com
Contraseña: password
```
- Crear una nueva tarea
- Marcar la tarea como completada
- Verificar que aparece en "Tareas Completadas"

### **Paso 2: Login como Team Leader**
```
Email: roberto.silva190@test.com
Contraseña: password
```
- Ir a "Tareas Pendientes de Aprobación"
- Aprobar la tarea completada por el developer
- Verificar que la tarea se marca como "Lista para QA"

### **Paso 3: Login como QA**
```
Email: qa@tracker.com
Contraseña: password
```
- Ir al Dashboard de QA
- Verificar que aparece la notificación de tarea lista para testing
- Ir a la vista Kanban
- Asignar la tarea a sí mismo (columna "Ready for Test" → "In Testing")
- Aprobar la tarea con notas
- Verificar que se mueve a la columna "Approved"

### **Paso 4: Volver como Team Leader**
```
Email: roberto.silva190@test.com
Contraseña: password
```
- Verificar que recibió notificación de tarea aprobada por QA
- Ir a "QA Review" en el menú
- Revisar la tarea aprobada por QA
- Aprobar completamente o solicitar cambios

### **Paso 5: Verificar como Developer**
```
Email: juan.martinez324@test.com
Contraseña: password
```
- Verificar que recibió notificación de la decisión final del Team Leader
- Si se solicitaron cambios, la tarea vuelve a estado "En Progreso"

---

## 📋 URLS IMPORTANTES

### **Para QA:**
- **Dashboard:** `/qa/dashboard`
- **Kanban:** `/qa/kanban`
- **Notificaciones:** `/qa/notifications`

### **Para Team Leader:**
- **Dashboard:** `/team-leader/dashboard`
- **QA Review:** `/team-leader/qa-review`
- **Tareas Pendientes:** `/team-leader/pending-tasks`
- **Tareas en Progreso:** `/team-leader/in-progress-tasks`

### **Para Developer:**
- **Dashboard:** `/dashboard`
- **Mis Tareas:** `/tasks`
- **Notificaciones:** Disponible en el header

---

## 🧪 DATOS DE PRUEBA DISPONIBLES

### **Proyectos:**
- Mobile Banking App v2
- Learning Management System v2
- Social Media Platform v3
- Real Estate Portal v1
- Food Delivery App v1
- E-commerce Platform v1
- CRM System v2
- Inventory Management v3

### **Sprints:**
- Sprint 1 (en todos los proyectos)
- Sprint 2 (en algunos proyectos)

### **Tareas de Prueba:**
- Ya existen varias tareas en diferentes estados para testing
- Puedes crear nuevas tareas para probar el flujo completo

---

## 🔔 TIPOS DE NOTIFICACIONES A VERIFICAR

### **Para QA:**
- `task_ready_for_qa` - Tarea lista para testing
- `bug_ready_for_qa` - Bug listo para testing

### **Para Team Leader:**
- `task_approved` - Tarea aprobada por QA
- `bug_approved` - Bug aprobado por QA

### **Para Developer:**
- `task_approved` - Tarea aprobada por QA
- `task_final_approved` - Tarea aprobada completamente por Team Leader
- `task_changes_requested` - Cambios solicitados por Team Leader

---

## ⚠️ NOTAS IMPORTANTES

1. **Todos los usuarios usan la misma contraseña:** `password`
2. **Los usuarios ya están asignados a todos los proyectos**
3. **El sistema de notificaciones funciona automáticamente**
4. **Las tareas de prueba ya existen en diferentes estados**
5. **El flujo completo está funcional y probado**

---

## 🎯 ESCENARIOS DE TESTING

### **Escenario 1: Flujo Exitoso**
1. Developer completa tarea
2. Team Leader aprueba
3. QA asigna y aprueba
4. Team Leader aprueba completamente
5. Developer recibe notificación final

### **Escenario 2: Cambios Solicitados**
1. Developer completa tarea
2. Team Leader aprueba
3. QA asigna y aprueba
4. Team Leader solicita cambios
5. Developer recibe notificación de cambios
6. Ciclo se reinicia

### **Escenario 3: QA Rechaza**
1. Developer completa tarea
2. Team Leader aprueba
3. QA asigna y rechaza con motivo
4. Developer recibe notificación de rechazo
5. Tarea vuelve al developer

---

## 🚀 ¡LISTO PARA TESTING!

Con estas credenciales puedes probar completamente el sistema de QA implementado. Todos los flujos están funcionando y las notificaciones se envían automáticamente.

**¡Disfruta probando el sistema! 🎉** 
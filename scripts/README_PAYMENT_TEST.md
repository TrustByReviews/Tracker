# Scripts de Prueba del Sistema de Pagos

Este directorio contiene scripts para probar la funcionalidad de pagos del sistema Tracker, específicamente el escenario de Camilo con 5 tareas completadas y 1 pausada.

## 📋 Escenario de Prueba

**Desarrollador:** Camilo Test  
**Valor por hora:** $14,000 COP  
**Tareas completadas:** 5 (5 horas cada una = 25 horas)  
**Tarea pausada:** 1 (5 horas consumidas = 5 horas)  
**Total de horas:** 30 horas  
**Pago esperado:** 30 × $14,000 = $420,000 COP

## 🚀 Scripts Disponibles

### 1. `run_payment_test_suite.php` (Script Principal)
Ejecuta todo el flujo de prueba completo:
- Limpieza de datos anteriores
- Creación del escenario de Camilo
- Prueba del backend y frontend
- Resumen final

```bash
php scripts/run_payment_test_suite.php
```

### 2. `test_payment_scenario.php`
Crea el escenario de prueba específico:
- Crea el usuario Camilo
- Crea 5 tareas completadas
- Crea 1 tarea pausada
- Genera reportes de pago

```bash
php scripts/test_payment_scenario.php
```

### 3. `test_payment_frontend.php`
Prueba la funcionalidad desde el frontend:
- Verifica reportes generados
- Simula peticiones HTTP
- Genera estadísticas
- Valida cálculos

```bash
php scripts/test_payment_frontend.php
```

### 4. `cleanup_payment_test.php`
Limpia todos los datos de prueba:
- Elimina usuario Camilo
- Elimina tareas de prueba
- Elimina reportes de pago
- Elimina proyecto de prueba

```bash
php scripts/cleanup_payment_test.php
```

## 🔧 Requisitos Previos

1. **Laravel configurado:** El proyecto debe estar funcionando
2. **Base de datos:** Migraciones ejecutadas
3. **Dependencias:** Composer install ejecutado
4. **Servidor web:** Opcional para pruebas de frontend

## 📊 Resultados Esperados

### Backend
- ✅ Usuario Camilo creado con valor por hora $14,000
- ✅ 5 tareas completadas (25 horas totales)
- ✅ 1 tarea pausada (5 horas consumidas)
- ✅ Reporte de pago generado con $420,000 COP
- ✅ Cálculos verificados correctamente

### Frontend
- ✅ Dashboard de pagos accesible
- ✅ Reportes visibles en la interfaz
- ✅ Estadísticas generadas correctamente
- ✅ Endpoints HTTP respondiendo

## 🌐 URLs para Prueba Manual

Una vez ejecutado el script, puedes acceder a:

- **Dashboard de pagos:** `http://localhost:8000/payments/dashboard`
- **Admin dashboard:** `http://localhost:8000/payments/admin`
- **Lista de reportes:** `http://localhost:8000/payments/reports`

### Credenciales de Camilo
- **Email:** `camilo@test.com`
- **Password:** `password123`

## 🔍 Verificación de Resultados

### En la Base de Datos
```sql
-- Verificar usuario Camilo
SELECT * FROM users WHERE email = 'camilo@test.com';

-- Verificar tareas
SELECT name, status, actual_hours FROM tasks WHERE user_id = (SELECT id FROM users WHERE email = 'camilo@test.com');

-- Verificar reportes de pago
SELECT * FROM payment_reports WHERE user_id = (SELECT id FROM users WHERE email = 'camilo@test.com');
```

### En el Frontend
1. Inicia sesión con las credenciales de Camilo
2. Navega al dashboard de pagos
3. Verifica que el reporte muestre $420,000 COP
4. Revisa los detalles de las tareas

## 🧹 Limpieza

Para limpiar todos los datos de prueba:

```bash
php scripts/cleanup_payment_test.php
```

## ⚠️ Notas Importantes

1. **Datos de prueba:** Los scripts crean datos específicos para pruebas
2. **No usar en producción:** Estos scripts son solo para desarrollo
3. **Backup:** Hacer backup antes de ejecutar en ambiente de desarrollo
4. **Permisos:** Asegúrate de tener permisos de escritura en la base de datos

## 🐛 Solución de Problemas

### Error: "Usuario Camilo no encontrado"
- Ejecuta primero `test_payment_scenario.php`

### Error: "No se encontró reporte con pago 420,000"
- Verifica que las tareas tengan `actual_hours` configuradas
- Revisa que las fechas estén en el rango correcto

### Error de conexión a base de datos
- Verifica la configuración de `.env`
- Asegúrate de que las migraciones estén ejecutadas

### Error de permisos
- Verifica que el usuario tenga permisos de desarrollador
- Revisa las políticas de autorización

## 📝 Logs

Los scripts generan logs detallados en la consola. Busca:
- ✅ Para operaciones exitosas
- ❌ Para errores
- ⚠️ Para advertencias
- 🔄 Para operaciones en progreso

## 🤝 Contribución

Para agregar nuevos escenarios de prueba:
1. Crea un nuevo script siguiendo el patrón existente
2. Documenta el escenario en este README
3. Actualiza `run_payment_test_suite.php` si es necesario
4. Prueba en un ambiente limpio 
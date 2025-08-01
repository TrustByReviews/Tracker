# 🚀 Funcionalidad "Login as User" - Instrucciones de Prueba

## 📋 Resumen
Esta funcionalidad permite a los administradores hacer login como cualquier otro usuario del sistema, para poder ver y probar las funcionalidades desde la perspectiva de ese usuario.

## ✅ Estado de la Implementación
- ✅ Backend implementado (AdminController)
- ✅ Rutas configuradas
- ✅ Frontend implementado (botón "Login As")
- ✅ Banner de notificación
- ✅ Botón "Return to Admin"
- ✅ Verificación de roles
- ✅ Gestión de sesiones

## 🎯 Cómo Probar la Funcionalidad

### 1. Preparación
```bash
# Asegúrate de que el servidor esté corriendo
php artisan serve
```

### 2. Acceso al Sistema
1. Abre tu navegador
2. Ve a: `http://localhost:8000`
3. Haz login como administrador:
   - **Email:** `admin@tracker.com`
   - **Password:** `password`

### 3. Navegar a la Página de Usuarios
1. Una vez logueado como admin, ve a la página de usuarios
2. URL: `http://localhost:8000/users`
3. Deberías ver una lista de usuarios con tarjetas

### 4. Probar "Login As User"
1. **Busca el botón "Login As"** en las tarjetas de usuario
   - Solo aparece para administradores
   - Está junto a los botones "View" y "Edit"
2. **Haz clic en "Login As"** para cualquier usuario (recomendado: un desarrollador)
3. **Deberías ser redirigido** al dashboard del usuario seleccionado

### 5. Verificar el Estado de "Login As"
Cuando estés logueado como otro usuario, deberías ver:

#### 🔶 Banner Amarillo
- Aparece en la parte superior de la página
- Dice: "You are currently logged in as [Nombre del Usuario] ([Email])"
- Incluye un botón "Return to Admin"

#### 🔶 Menú de Usuario
- En el menú desplegable del usuario (botón con iniciales)
- Debería aparecer una opción "Return to Admin" con icono de escudo

### 6. Probar las Funcionalidades del Usuario
Una vez logueado como otro usuario, puedes:
- Ver su dashboard personal
- Ver sus tareas asignadas
- Ver sus proyectos
- Probar todas las funcionalidades desde su perspectiva

### 7. Volver al Admin
Tienes dos opciones para volver al admin:
1. **Banner amarillo:** Haz clic en "Return to Admin"
2. **Menú de usuario:** Haz clic en el menú → "Return to Admin"

## 🔍 Verificaciones Importantes

### ✅ Lo que DEBE funcionar:
- [ ] El botón "Login As" solo aparece para administradores
- [ ] Al hacer clic, cambias al usuario seleccionado
- [ ] Aparece el banner amarillo de notificación
- [ ] En el menú aparece "Return to Admin"
- [ ] Puedes navegar por todas las funcionalidades del usuario
- [ ] Al hacer "Return to Admin" vuelves al admin original
- [ ] La sesión del admin original se mantiene segura

### ❌ Lo que NO debe pasar:
- [ ] El botón "Login As" aparece para usuarios no-admin
- [ ] No aparece el banner amarillo
- [ ] No aparece "Return to Admin" en el menú
- [ ] Se pierde la sesión del admin original
- [ ] No puedes volver al admin

## 🛠️ Solución de Problemas

### Problema: No aparece el botón "Login As"
**Solución:**
1. Verifica que estés logueado como administrador
2. Verifica que el usuario tenga rol de admin
3. Recarga la página de usuarios

### Problema: No aparece el banner amarillo
**Solución:**
1. Verifica que la compilación de assets esté actualizada
2. Ejecuta: `npm run build`
3. Recarga la página

### Problema: No funciona "Return to Admin"
**Solución:**
1. Verifica que la ruta esté registrada: `php artisan route:list --name=admin`
2. Verifica que el controlador AdminController tenga el método `returnToAdmin`

### Problema: Error de permisos
**Solución:**
1. Verifica que el usuario tenga rol de admin
2. Verifica que el método `hasRole` esté implementado en el modelo User

## 📊 Usuarios de Prueba Disponibles

### Administradores:
- `admin@tracker.com` / `password`
- `admin@example.com` / `password`
- `admin2@example.com` / `password`

### Team Leaders:
- `teamleader1@example.com` / `password`
- `teamleader2@example.com` / `password`
- `teamleader3@example.com` / `password`

### Desarrolladores:
- `developer1@example.com` / `password`
- `developer2@example.com` / `password`
- `developer3@example.com` / `password`
- `developer4@example.com` / `password`
- `developer5@example.com` / `password`

## 🔧 Comandos Útiles

```bash
# Verificar rutas
php artisan route:list --name=admin

# Verificar usuarios y roles
php scripts/test_login_as.php

# Compilar assets
npm run build

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## 🎉 ¡Listo para Probar!

La funcionalidad está completamente implementada y lista para usar. Sigue las instrucciones paso a paso y deberías poder probar todas las funcionalidades del sistema desde la perspectiva de diferentes usuarios. 
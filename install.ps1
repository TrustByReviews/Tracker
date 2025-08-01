# Script de instalación automática para el Sistema de Gestión de Tareas (PowerShell)
# Autor: Sistema de Gestión de Tareas
# Versión: 1.0.0

Write-Host "🚀 Iniciando instalación del Sistema de Gestión de Tareas..." -ForegroundColor Green
Write-Host "==================================================" -ForegroundColor Green

# Verificar si PHP está instalado
try {
    $phpVersion = php -v 2>$null
    if ($LASTEXITCODE -ne 0) {
        throw "PHP no encontrado"
    }
    Write-Host "✅ PHP encontrado" -ForegroundColor Green
} catch {
    Write-Host "❌ Error: PHP no está instalado. Por favor, instala PHP 8.2 o superior." -ForegroundColor Red
    exit 1
}

# Verificar si Composer está instalado
try {
    $composerVersion = composer -V 2>$null
    if ($LASTEXITCODE -ne 0) {
        throw "Composer no encontrado"
    }
    Write-Host "✅ Composer encontrado" -ForegroundColor Green
} catch {
    Write-Host "❌ Error: Composer no está instalado. Por favor, instala Composer." -ForegroundColor Red
    exit 1
}

# Verificar si Node.js está instalado
try {
    $nodeVersion = node -v 2>$null
    if ($LASTEXITCODE -ne 0) {
        throw "Node.js no encontrado"
    }
    Write-Host "✅ Node.js encontrado" -ForegroundColor Green
} catch {
    Write-Host "❌ Error: Node.js no está instalado. Por favor, instala Node.js 18 o superior." -ForegroundColor Red
    exit 1
}

# Verificar si npm está instalado
try {
    $npmVersion = npm -v 2>$null
    if ($LASTEXITCODE -ne 0) {
        throw "npm no encontrado"
    }
    Write-Host "✅ npm encontrado" -ForegroundColor Green
} catch {
    Write-Host "❌ Error: npm no está instalado. Por favor, instala npm." -ForegroundColor Red
    exit 1
}

Write-Host "✅ Prerrequisitos verificados correctamente" -ForegroundColor Green

# Instalar dependencias PHP
Write-Host "📦 Instalando dependencias PHP..." -ForegroundColor Yellow
composer install --no-interaction

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Error al instalar dependencias PHP" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Dependencias PHP instaladas" -ForegroundColor Green

# Instalar dependencias Node.js
Write-Host "📦 Instalando dependencias Node.js..." -ForegroundColor Yellow
npm install

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Error al instalar dependencias Node.js" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Dependencias Node.js instaladas" -ForegroundColor Green

# Verificar si existe el archivo .env
if (-not (Test-Path ".env")) {
    Write-Host "⚙️  Configurando archivo .env..." -ForegroundColor Yellow
    Copy-Item ".env.example" ".env"
    
    # Generar clave de aplicación
    php artisan key:generate
    
    Write-Host "✅ Archivo .env configurado" -ForegroundColor Green
} else {
    Write-Host "✅ Archivo .env ya existe" -ForegroundColor Green
}

# Solicitar configuración de base de datos
Write-Host ""
Write-Host "🗄️  Configuración de Base de Datos" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
$configure_db = Read-Host "¿Deseas configurar la base de datos ahora? (y/n)"

if ($configure_db -eq "y" -or $configure_db -eq "Y") {
    Write-Host ""
    Write-Host "Por favor, configura tu base de datos en el archivo .env" -ForegroundColor Yellow
    Write-Host "Ejemplo de configuración:" -ForegroundColor Yellow
    Write-Host "DB_CONNECTION=mysql" -ForegroundColor Gray
    Write-Host "DB_HOST=127.0.0.1" -ForegroundColor Gray
    Write-Host "DB_PORT=3306" -ForegroundColor Gray
    Write-Host "DB_DATABASE=tracker" -ForegroundColor Gray
    Write-Host "DB_USERNAME=root" -ForegroundColor Gray
    Write-Host "DB_PASSWORD=tu_password" -ForegroundColor Gray
    Write-Host ""
    Read-Host "Presiona Enter cuando hayas configurado la base de datos"
}

# Ejecutar migraciones
Write-Host "🗃️  Ejecutando migraciones..." -ForegroundColor Yellow
php artisan migrate --force

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Error al ejecutar migraciones" -ForegroundColor Red
    Write-Host "Por favor, verifica la configuración de la base de datos en .env" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Migraciones ejecutadas" -ForegroundColor Green

# Ejecutar seeders
Write-Host "🌱 Ejecutando seeders..." -ForegroundColor Yellow
php artisan db:seed --force

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Error al ejecutar seeders" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Seeders ejecutados" -ForegroundColor Green

# Compilar assets
Write-Host "🔨 Compilando assets..." -ForegroundColor Yellow
npm run build

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Error al compilar assets" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Assets compilados" -ForegroundColor Green

# Verificación final
Write-Host "🔍 Ejecutando verificación final..." -ForegroundColor Yellow
php scripts/final_verification.php

Write-Host ""
Write-Host "🎉 ¡Instalación completada exitosamente!" -ForegroundColor Green
Write-Host "==================================================" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Próximos pasos:" -ForegroundColor Cyan
Write-Host "1. Inicia el servidor: php artisan serve" -ForegroundColor White
Write-Host "2. Abre tu navegador en: http://localhost:8000" -ForegroundColor White
Write-Host "3. Inicia sesión con las credenciales por defecto:" -ForegroundColor White
Write-Host "   - Email: admin@example.com" -ForegroundColor Gray
Write-Host "   - Contraseña: password" -ForegroundColor Gray
Write-Host ""
Write-Host "📚 Documentación: README.md" -ForegroundColor Cyan
Write-Host "🐛 Reportar problemas: Crear un issue en GitHub" -ForegroundColor Cyan
Write-Host ""
Write-Host "¡Gracias por usar el Sistema de Gestión de Tareas! 🚀" -ForegroundColor Green 
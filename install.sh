#!/bin/bash

# Script de instalación automática para el Sistema de Gestión de Tareas
# Autor: Sistema de Gestión de Tareas
# Versión: 1.0.0

echo "🚀 Iniciando instalación del Sistema de Gestión de Tareas..."
echo "=================================================="

# Verificar si PHP está instalado
if ! command -v php &> /dev/null; then
    echo "❌ Error: PHP no está instalado. Por favor, instala PHP 8.2 o superior."
    exit 1
fi

# Verificar si Composer está instalado
if ! command -v composer &> /dev/null; then
    echo "❌ Error: Composer no está instalado. Por favor, instala Composer."
    exit 1
fi

# Verificar si Node.js está instalado
if ! command -v node &> /dev/null; then
    echo "❌ Error: Node.js no está instalado. Por favor, instala Node.js 18 o superior."
    exit 1
fi

# Verificar si npm está instalado
if ! command -v npm &> /dev/null; then
    echo "❌ Error: npm no está instalado. Por favor, instala npm."
    exit 1
fi

echo "✅ Prerrequisitos verificados correctamente"

# Instalar dependencias PHP
echo "📦 Instalando dependencias PHP..."
composer install --no-interaction

if [ $? -ne 0 ]; then
    echo "❌ Error al instalar dependencias PHP"
    exit 1
fi

echo "✅ Dependencias PHP instaladas"

# Instalar dependencias Node.js
echo "📦 Instalando dependencias Node.js..."
npm install

if [ $? -ne 0 ]; then
    echo "❌ Error al instalar dependencias Node.js"
    exit 1
fi

echo "✅ Dependencias Node.js instaladas"

# Verificar si existe el archivo .env
if [ ! -f .env ]; then
    echo "⚙️  Configurando archivo .env..."
    cp .env.example .env
    
    # Generar clave de aplicación
    php artisan key:generate
    
    echo "✅ Archivo .env configurado"
else
    echo "✅ Archivo .env ya existe"
fi

# Solicitar configuración de base de datos
echo ""
echo "🗄️  Configuración de Base de Datos"
echo "=================================="
read -p "¿Deseas configurar la base de datos ahora? (y/n): " configure_db

if [ "$configure_db" = "y" ] || [ "$configure_db" = "Y" ]; then
    echo ""
    echo "Por favor, configura tu base de datos en el archivo .env"
    echo "Ejemplo de configuración:"
    echo "DB_CONNECTION=mysql"
    echo "DB_HOST=127.0.0.1"
    echo "DB_PORT=3306"
    echo "DB_DATABASE=tracker"
    echo "DB_USERNAME=root"
    echo "DB_PASSWORD=tu_password"
    echo ""
    read -p "Presiona Enter cuando hayas configurado la base de datos..."
fi

# Ejecutar migraciones
echo "🗃️  Ejecutando migraciones..."
php artisan migrate --force

if [ $? -ne 0 ]; then
    echo "❌ Error al ejecutar migraciones"
    echo "Por favor, verifica la configuración de la base de datos en .env"
    exit 1
fi

echo "✅ Migraciones ejecutadas"

# Ejecutar seeders
echo "🌱 Ejecutando seeders..."
php artisan db:seed --force

if [ $? -ne 0 ]; then
    echo "❌ Error al ejecutar seeders"
    exit 1
fi

echo "✅ Seeders ejecutados"

# Compilar assets
echo "🔨 Compilando assets..."
npm run build

if [ $? -ne 0 ]; then
    echo "❌ Error al compilar assets"
    exit 1
fi

echo "✅ Assets compilados"

# Verificación final
echo "🔍 Ejecutando verificación final..."
php scripts/final_verification.php

echo ""
echo "🎉 ¡Instalación completada exitosamente!"
echo "=================================================="
echo ""
echo "📋 Próximos pasos:"
echo "1. Inicia el servidor: php artisan serve"
echo "2. Abre tu navegador en: http://localhost:8000"
echo "3. Inicia sesión con las credenciales por defecto:"
echo "   - Email: admin@example.com"
echo "   - Contraseña: password"
echo ""
echo "📚 Documentación: README.md"
echo "🐛 Reportar problemas: Crear un issue en GitHub"
echo ""
echo "¡Gracias por usar el Sistema de Gestión de Tareas! 🚀" 
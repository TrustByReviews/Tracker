#!/bin/bash

# Script de Bash para ejecutar todos los scripts de traducción
# Ejecutar desde la raíz del proyecto: ./scripts/translate_all.sh

echo "=== SCRIPT DE TRADUCCIÓN COMPLETA ==="
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "composer.json" ]; then
    echo "❌ Error: Debes ejecutar este script desde la raíz del proyecto Laravel"
    exit 1
fi

echo "📁 Directorio actual: $(pwd)"
echo ""

# Función para ejecutar script PHP
execute_php_script() {
    local script_path="$1"
    local description="$2"
    
    echo "🔄 Ejecutando: $description"
    echo "📄 Script: $script_path"
    
    if [ -f "$script_path" ]; then
        if php "$script_path"; then
            echo "✅ $description completado"
        else
            echo "❌ Error ejecutando $description"
        fi
    else
        echo "⚠️  Script no encontrado: $script_path"
    fi
    
    echo ""
}

# Ejecutar scripts en orden
echo "🚀 Iniciando proceso de traducción..."
echo ""

# 1. Traducción de palabras comunes
execute_php_script "scripts/translate_common_words.php" "Traducción de palabras comunes"

# 2. Traducción específica del proyecto
execute_php_script "scripts/translate_project_specific.php" "Traducción específica del proyecto"

echo "🎉 Proceso de traducción completado!"
echo ""
echo "📋 Resumen de acciones realizadas:"
echo "   ✅ Traducción de palabras comunes (botones, formularios, etc.)"
echo "   ✅ Traducción específica del proyecto (estados, tipos, mensajes)"
echo ""
echo "🔍 Próximos pasos:"
echo "   1. Revisar los archivos modificados"
echo "   2. Verificar que las traducciones sean correctas"
echo "   3. Probar la aplicación para asegurar que todo funcione"
echo "   4. Continuar con la traducción manual de textos complejos"
echo ""
echo "💡 Tip: Puedes ejecutar los scripts individualmente si necesitas más control"
echo "   php scripts/translate_common_words.php"
echo "   php scripts/translate_project_specific.php"

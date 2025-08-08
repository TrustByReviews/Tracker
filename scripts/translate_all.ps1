# Script de PowerShell para ejecutar todos los scripts de traducción
# Ejecutar desde la raíz del proyecto: .\scripts\translate_all.ps1

Write-Host "=== SCRIPT DE TRADUCCIÓN COMPLETA ===" -ForegroundColor Green
Write-Host ""

# Verificar que estamos en el directorio correcto
if (-not (Test-Path "composer.json")) {
    Write-Host "❌ Error: Debes ejecutar este script desde la raíz del proyecto Laravel" -ForegroundColor Red
    exit 1
}

Write-Host "📁 Directorio actual: $(Get-Location)" -ForegroundColor Yellow
Write-Host ""

# Función para ejecutar script PHP
function Execute-PHPScript {
    param(
        [string]$ScriptPath,
        [string]$Description
    )
    
    Write-Host "🔄 Ejecutando: $Description" -ForegroundColor Cyan
    Write-Host "📄 Script: $ScriptPath" -ForegroundColor Gray
    
    if (Test-Path $ScriptPath) {
        try {
            php $ScriptPath
            Write-Host "✅ $Description completado" -ForegroundColor Green
        }
        catch {
            Write-Host "❌ Error ejecutando $Description : $_" -ForegroundColor Red
        }
    }
    else {
        Write-Host "⚠️  Script no encontrado: $ScriptPath" -ForegroundColor Yellow
    }
    
    Write-Host ""
}

# Ejecutar scripts en orden
Write-Host "🚀 Iniciando proceso de traducción..." -ForegroundColor Green
Write-Host ""

# 1. Traducción de palabras comunes
Execute-PHPScript -ScriptPath "scripts/translate_common_words.php" -Description "Traducción de palabras comunes"

# 2. Traducción específica del proyecto
Execute-PHPScript -ScriptPath "scripts/translate_project_specific.php" -Description "Traducción específica del proyecto"

Write-Host "🎉 Proceso de traducción completado!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Resumen de acciones realizadas:" -ForegroundColor Yellow
Write-Host "   ✅ Traducción de palabras comunes (botones, formularios, etc.)" -ForegroundColor White
Write-Host "   ✅ Traducción específica del proyecto (estados, tipos, mensajes)" -ForegroundColor White
Write-Host ""
Write-Host "🔍 Próximos pasos:" -ForegroundColor Yellow
Write-Host "   1. Revisar los archivos modificados" -ForegroundColor White
Write-Host "   2. Verificar que las traducciones sean correctas" -ForegroundColor White
Write-Host "   3. Probar la aplicación para asegurar que todo funcione" -ForegroundColor White
Write-Host "   4. Continuar con la traducción manual de textos complejos" -ForegroundColor White
Write-Host ""
Write-Host "💡 Tip: Puedes ejecutar los scripts individualmente si necesitas más control" -ForegroundColor Cyan
Write-Host "   php scripts/translate_common_words.php" -ForegroundColor Gray
Write-Host "   php scripts/translate_project_specific.php" -ForegroundColor Gray

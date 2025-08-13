<?php

/**
 * Script para verificar que los usuarios con rol client son excluidos de los reportes
 */

// Inicializar Laravel
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Services\PaymentService;
use Carbon\Carbon;

echo "=== VERIFICACIÓN DE EXCLUSIÓN DE USUARIOS CLIENT ===\n\n";

// 1. Verificar usuarios en el sistema
echo "1. VERIFICANDO USUARIOS EN EL SISTEMA...\n";

$allUsers = User::with('roles')->get();
echo "   - Total de usuarios: " . $allUsers->count() . "\n";

$clientUsers = $allUsers->filter(function ($user) {
    return $user->roles->contains('name', 'client');
});

$nonClientUsers = $allUsers->filter(function ($user) {
    return !$user->roles->contains('name', 'client');
});

echo "   - Usuarios con rol 'client': " . $clientUsers->count() . "\n";
echo "   - Usuarios sin rol 'client': " . $nonClientUsers->count() . "\n";

if ($clientUsers->count() > 0) {
    echo "   - Usuarios client encontrados:\n";
    foreach ($clientUsers as $user) {
        echo "     * {$user->name} ({$user->email})\n";
    }
}

// 2. Verificar que PaymentService excluye usuarios client
echo "\n2. VERIFICANDO PAYMENTSERVICE...\n";

$paymentService = new PaymentService();

// Probar generateWeeklyReportsForAllDevelopers
try {
    $weeklyReports = $paymentService->generateWeeklyReportsForAllDevelopers();
    echo "   - Reportes semanales generados: " . count($weeklyReports['reports']) . "\n";
    
    $reportedUserIds = collect($weeklyReports['reports'])->pluck('user_id')->toArray();
    $reportedUsers = User::whereIn('id', $reportedUserIds)->with('roles')->get();
    
    $clientInReports = $reportedUsers->filter(function ($user) {
        return $user->roles->contains('name', 'client');
    });
    
    if ($clientInReports->count() > 0) {
        echo "   ❌ ERROR: Usuarios client encontrados en reportes semanales:\n";
        foreach ($clientInReports as $user) {
            echo "     * {$user->name} ({$user->email})\n";
        }
    } else {
        echo "   ✅ Usuarios client correctamente excluidos de reportes semanales\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error al generar reportes semanales: " . $e->getMessage() . "\n";
}

// Probar generateReportsUntilDate
try {
    $startDate = Carbon::now()->subDays(30);
    $endDate = Carbon::now();
    $dateRangeReports = $paymentService->generateReportsUntilDate($startDate, $endDate);
    echo "   - Reportes por rango de fechas generados: " . count($dateRangeReports['reports']) . "\n";
    
    $reportedUserIds = collect($dateRangeReports['reports'])->pluck('user_id')->toArray();
    $reportedUsers = User::whereIn('id', $reportedUserIds)->with('roles')->get();
    
    $clientInReports = $reportedUsers->filter(function ($user) {
        return $user->roles->contains('name', 'client');
    });
    
    if ($clientInReports->count() > 0) {
        echo "   ❌ ERROR: Usuarios client encontrados en reportes por rango:\n";
        foreach ($clientInReports as $user) {
            echo "     * {$user->name} ({$user->email})\n";
        }
    } else {
        echo "   ✅ Usuarios client correctamente excluidos de reportes por rango\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error al generar reportes por rango: " . $e->getMessage() . "\n";
}

// 3. Verificar proyecto específico
echo "\n3. VERIFICANDO PROYECTO 'E-commerce Platform Development'...\n";

$project = Project::where('name', 'E-commerce Platform Development')->first();

if ($project) {
    echo "   - Proyecto encontrado: {$project->name}\n";
    
    // Usuarios asignados al proyecto
    $projectUsers = $project->users()->with('roles')->get();
    echo "   - Total de usuarios asignados al proyecto: " . $projectUsers->count() . "\n";
    
    $clientProjectUsers = $projectUsers->filter(function ($user) {
        return $user->roles->contains('name', 'client');
    });
    
    $nonClientProjectUsers = $projectUsers->filter(function ($user) {
        return !$user->roles->contains('name', 'client');
    });
    
    echo "   - Usuarios client en el proyecto: " . $clientProjectUsers->count() . "\n";
    echo "   - Usuarios no-client en el proyecto: " . $nonClientProjectUsers->count() . "\n";
    
    if ($clientProjectUsers->count() > 0) {
        echo "   - Usuarios client en el proyecto:\n";
        foreach ($clientProjectUsers as $user) {
            echo "     * {$user->name} ({$user->email})\n";
        }
    }
    
    if ($nonClientProjectUsers->count() > 0) {
        echo "   - Usuarios no-client en el proyecto:\n";
        foreach ($nonClientProjectUsers as $user) {
            $roles = $user->roles->pluck('name')->implode(', ');
            echo "     * {$user->name} ({$user->email}) - Roles: {$roles}\n";
        }
    }
    
} else {
    echo "   - Proyecto 'E-commerce Platform Development' no encontrado\n";
}

// 4. Resumen
echo "\n4. RESUMEN DE VERIFICACIÓN:\n";

$totalClientUsers = $clientUsers->count();
$totalNonClientUsers = $nonClientUsers->count();

echo "   - Total de usuarios client en el sistema: {$totalClientUsers}\n";
echo "   - Total de usuarios no-client en el sistema: {$totalNonClientUsers}\n";

if ($totalClientUsers > 0) {
    echo "   ✅ Usuarios client identificados y serán excluidos de reportes\n";
} else {
    echo "   ℹ️  No hay usuarios client en el sistema\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";

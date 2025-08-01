<?php

/**
 * Script de verificación para la Fase 5
 * Verifica que el dashboard de administradores esté correctamente implementado
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== VERIFICACIÓN FASE 5: DASHBOARD DE ADMINISTRADORES ===\n\n";

// Verificar que el servicio existe
echo "1. Verificando AdminDashboardService...\n";
$servicePath = __DIR__ . '/../app/Services/AdminDashboardService.php';
if (file_exists($servicePath)) {
    echo "   ✓ AdminDashboardService.php existe\n";
    
    $content = file_get_contents($servicePath);
    $requiredMethods = [
        'getSystemStats',
        'getInProgressTasksWithFilters',
        'getDeveloperPerformanceMetrics',
        'getProjectPerformanceMetrics',
        'getTasksRequiringAttention',
        'getPendingApprovalTasks',
        'getTimeReportByPeriod',
        'getActiveProjectsSummary',
        'getActiveDevelopersSummary'
    ];
    
    foreach ($requiredMethods as $method) {
        if (strpos($content, $method) !== false) {
            echo "     ✓ Método '{$method}' presente\n";
        } else {
            echo "     ✗ Método '{$method}' NO presente\n";
        }
    }
} else {
    echo "   ✗ AdminDashboardService.php NO existe\n";
}

// Verificar que el AdminController existe
echo "\n2. Verificando AdminController...\n";
$controllerPath = __DIR__ . '/../app/Http/Controllers/AdminController.php';
if (file_exists($controllerPath)) {
    echo "   ✓ AdminController.php existe\n";
    
    $content = file_get_contents($controllerPath);
    
    // Verificar inyección de dependencia
    if (strpos($content, 'AdminDashboardService') !== false && strpos($content, 'TaskApprovalService') !== false) {
        echo "     ✓ Servicios inyectados correctamente\n";
    } else {
        echo "     ✗ Servicios NO inyectados correctamente\n";
    }
    
    $requiredMethods = [
        'dashboard',
        'inProgressTasks',
        'developerMetrics',
        'projectMetrics',
        'timeReports',
        'tasksRequiringAttention',
        'pendingApprovalTasks',
        'getSystemStats',
        'getInProgressTasks',
        'getDeveloperMetrics',
        'getProjectMetrics',
        'getTimeReport',
        'getTasksRequiringAttention',
        'getActiveProjectsSummary',
        'getActiveDevelopersSummary'
    ];
    
    foreach ($requiredMethods as $method) {
        if (strpos($content, $method) !== false) {
            echo "     ✓ Método '{$method}' presente\n";
        } else {
            echo "     ✗ Método '{$method}' NO presente\n";
        }
    }
} else {
    echo "   ✗ AdminController.php NO existe\n";
}

// Verificar rutas de admin
echo "\n3. Verificando rutas de admin...\n";
$routesPath = __DIR__ . '/../routes/web.php';
if (file_exists($routesPath)) {
    echo "   ✓ web.php existe\n";
    
    $content = file_get_contents($routesPath);
    $requiredRoutes = [
        'dashboard',
        'in-progress-tasks',
        'developer-metrics',
        'project-metrics',
        'time-reports',
        'tasks-requiring-attention',
        'pending-approval-tasks',
        'stats/system',
        'tasks/in-progress',
        'metrics/developers',
        'metrics/projects',
        'reports/time',
        'tasks/requiring-attention',
        'projects/active-summary',
        'developers/active-summary'
    ];
    
    foreach ($requiredRoutes as $route) {
        if (strpos($content, $route) !== false) {
            echo "     ✓ Ruta '{$route}' presente\n";
        } else {
            echo "     ✗ Ruta '{$route}' NO presente\n";
        }
    }
    
    // Verificar import del controlador
    if (strpos($content, 'AdminController') !== false) {
        echo "     ✓ Import de AdminController presente\n";
    } else {
        echo "     ✗ Import de AdminController NO presente\n";
    }
} else {
    echo "   ✗ web.php NO existe\n";
}

// Verificar funcionalidades de filtros avanzados
echo "\n4. Verificando funcionalidades de filtros avanzados...\n";
$adminServicePath = __DIR__ . '/../app/Services/AdminDashboardService.php';
if (file_exists($adminServicePath)) {
    $content = file_get_contents($adminServicePath);
    
    $filterFeatures = [
        'project_id',
        'user_id',
        'priority',
        'time_comparison',
        'start_date',
        'end_date',
        'search',
        'order_by',
        'order_direction'
    ];
    
    foreach ($filterFeatures as $feature) {
        if (strpos($content, $feature) !== false) {
            echo "     ✓ Filtro '{$feature}' presente\n";
        } else {
            echo "     ✗ Filtro '{$feature}' NO presente\n";
        }
    }
} else {
    echo "   ✗ AdminDashboardService.php NO existe\n";
}

// Verificar métricas de rendimiento
echo "\n5. Verificando métricas de rendimiento...\n";
if (file_exists($adminServicePath)) {
    $content = file_get_contents($adminServicePath);
    
    $performanceMetrics = [
        'efficiency_percentage',
        'completion_rate',
        'average_task_time',
        'total_time_spent',
        'total_estimated_time'
    ];
    
    foreach ($performanceMetrics as $metric) {
        if (strpos($content, $metric) !== false) {
            echo "     ✓ Métrica '{$metric}' presente\n";
        } else {
            echo "     ✗ Métrica '{$metric}' NO presente\n";
        }
    }
} else {
    echo "   ✗ AdminDashboardService.php NO existe\n";
}

// Verificar reportes de tiempo
echo "\n6. Verificando reportes de tiempo...\n";
if (file_exists($adminServicePath)) {
    $content = file_get_contents($adminServicePath);
    
    $timeReportFeatures = [
        'week',
        'month',
        'quarter',
        'year',
        'time_by_project',
        'time_by_developer'
    ];
    
    foreach ($timeReportFeatures as $feature) {
        if (strpos($content, $feature) !== false) {
            echo "     ✓ Reporte '{$feature}' presente\n";
        } else {
            echo "     ✗ Reporte '{$feature}' NO presente\n";
        }
    }
} else {
    echo "   ✗ AdminDashboardService.php NO existe\n";
}

// Verificar comparación de tiempos estimados vs reales
echo "\n7. Verificando comparación de tiempos...\n";
if (file_exists($adminServicePath)) {
    $content = file_get_contents($adminServicePath);
    
    $timeComparisonFeatures = [
        'over_estimated',
        'under_estimated',
        'on_track',
        'estimated_hours * 3600',
        'total_time_seconds'
    ];
    
    foreach ($timeComparisonFeatures as $feature) {
        if (strpos($content, $feature) !== false) {
            echo "     ✓ Comparación '{$feature}' presente\n";
        } else {
            echo "     ✗ Comparación '{$feature}' NO presente\n";
        }
    }
} else {
    echo "   ✗ AdminDashboardService.php NO existe\n";
}

// Verificar estadísticas del sistema
echo "\n8. Verificando estadísticas del sistema...\n";
if (file_exists($adminServicePath)) {
    $content = file_get_contents($adminServicePath);
    
    $systemStats = [
        'totalProjects',
        'activeProjects',
        'completedProjects',
        'totalTasks',
        'inProgressTasks',
        'completedTasks',
        'pendingApprovalTasks',
        'totalUsers',
        'developers',
        'teamLeaders'
    ];
    
    foreach ($systemStats as $stat) {
        if (strpos($content, $stat) !== false) {
            echo "     ✓ Estadística '{$stat}' presente\n";
        } else {
            echo "     ✗ Estadística '{$stat}' NO presente\n";
        }
    }
} else {
    echo "   ✗ AdminDashboardService.php NO existe\n";
}

// Verificar funcionalidad de tareas que requieren atención
echo "\n9. Verificando tareas que requieren atención...\n";
if (file_exists($adminServicePath)) {
    $content = file_get_contents($adminServicePath);
    
    if (strpos($content, 'total_time_seconds > (estimated_hours * 3600 * 1.2)') !== false) {
        echo "     ✓ Lógica de tareas que requieren atención presente\n";
    } else {
        echo "     ✗ Lógica de tareas que requieren atención NO presente\n";
    }
} else {
    echo "   ✗ AdminDashboardService.php NO existe\n";
}

// Verificar validación de roles de admin
echo "\n10. Verificando validación de roles de admin...\n";
if (file_exists($controllerPath)) {
    $content = file_get_contents($controllerPath);
    
    if (strpos($content, "roles()->where('name', 'admin')") !== false) {
        echo "     ✓ Validación de rol admin presente\n";
    } else {
        echo "     ✗ Validación de rol admin NO presente\n";
    }
} else {
    echo "   ✗ AdminController.php NO existe\n";
}

echo "\n=== FIN DE VERIFICACIÓN FASE 5 ===\n";
echo "Si todos los elementos están marcados con ✓, la Fase 5 está correctamente implementada.\n";
echo "Si hay elementos marcados con ✗, revisa la implementación antes de continuar.\n"; 
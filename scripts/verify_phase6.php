<?php

/**
 * Script de verificación para la Fase 6
 * Verifica que las interfaces de usuario estén correctamente implementadas
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== VERIFICACIÓN FASE 6: INTERFACES DE USUARIO ===\n\n";

// Verificar que las páginas Vue.js existen
echo "1. Verificando páginas Vue.js...\n";
$pages = [
    'Developer/Kanban.vue' => 'Vista Kanban para desarrolladores',
    'TeamLeader/Dashboard.vue' => 'Dashboard para team leaders',
    'Admin/Dashboard.vue' => 'Dashboard para administradores'
];

foreach ($pages as $pagePath => $description) {
    $fullPath = __DIR__ . '/../resources/js/pages/' . $pagePath;
    if (file_exists($fullPath)) {
        echo "   ✓ {$description} existe\n";
        
        $content = file_get_contents($fullPath);
        
        // Verificar características específicas de cada página
        switch ($pagePath) {
            case 'Developer/Kanban.vue':
                $kanbanFeatures = [
                    'Kanban Board',
                    'dragstart',
                    'onDrop',
                    'startWork',
                    'pauseWork',
                    'resumeWork',
                    'finishWork',
                    'selfAssignTask',
                    'columns.toDo',
                    'columns.inProgress',
                    'columns.done'
                ];
                
                foreach ($kanbanFeatures as $feature) {
                    if (strpos($content, $feature) !== false) {
                        echo "     ✓ Característica '{$feature}' presente\n";
                    } else {
                        echo "     ✗ Característica '{$feature}' NO presente\n";
                    }
                }
                break;
                
            case 'TeamLeader/Dashboard.vue':
                $teamLeaderFeatures = [
                    'approveTask',
                    'rejectTask',
                    'showRejectModal',
                    'pendingTasks',
                    'developersWithTasks',
                    'approvalStats',
                    'recentlyCompleted'
                ];
                
                foreach ($teamLeaderFeatures as $feature) {
                    if (strpos($content, $feature) !== false) {
                        echo "     ✓ Característica '{$feature}' presente\n";
                    } else {
                        echo "     ✗ Característica '{$feature}' NO presente\n";
                    }
                }
                break;
                
            case 'Admin/Dashboard.vue':
                $adminFeatures = [
                    'systemStats',
                    'tasksRequiringAttention',
                    'activeProjectsSummary',
                    'developerMetrics',
                    'navigateToInProgressTasks',
                    'navigateToDeveloperMetrics',
                    'navigateToTimeReports'
                ];
                
                foreach ($adminFeatures as $feature) {
                    if (strpos($content, $feature) !== false) {
                        echo "     ✓ Característica '{$feature}' presente\n";
                    } else {
                        echo "     ✗ Característica '{$feature}' NO presente\n";
                    }
                }
                break;
        }
    } else {
        echo "   ✗ {$description} NO existe\n";
    }
}

// Verificar componentes Vue.js
echo "\n2. Verificando componentes Vue.js...\n";
$components = [
    'TaskCard.vue' => 'Componente de tarjeta de tarea',
    'Toast.vue' => 'Componente de notificaciones'
];

foreach ($components as $componentPath => $description) {
    $fullPath = __DIR__ . '/../resources/js/components/' . $componentPath;
    if (file_exists($fullPath)) {
        echo "   ✓ {$description} existe\n";
        
        $content = file_get_contents($fullPath);
        
        if ($componentPath === 'TaskCard.vue') {
            $taskCardFeatures = [
                'isWorking',
                'currentSessionTime',
                'formatTime',
                'start-work',
                'pause-work',
                'resume-work',
                'finish-work',
                'self-assign',
                'showApprovalStatus'
            ];
            
            foreach ($taskCardFeatures as $feature) {
                if (strpos($content, $feature) !== false) {
                    echo "     ✓ Característica '{$feature}' presente\n";
                } else {
                    echo "     ✗ Característica '{$feature}' NO presente\n";
                }
            }
        } elseif ($componentPath === 'Toast.vue') {
            $toastFeatures = [
                'TransitionGroup',
                'toast-enter-active',
                'toast-leave-active',
                'removeToast'
            ];
            
            foreach ($toastFeatures as $feature) {
                if (strpos($content, $feature) !== false) {
                    echo "     ✓ Característica '{$feature}' presente\n";
                } else {
                    echo "     ✗ Característica '{$feature}' NO presente\n";
                }
            }
        }
    } else {
        echo "   ✗ {$description} NO existe\n";
    }
}

// Verificar composables
echo "\n3. Verificando composables...\n";
$composables = [
    'useToast.ts' => 'Composable para notificaciones'
];

foreach ($composables as $composablePath => $description) {
    $fullPath = __DIR__ . '/../resources/js/composables/' . $composablePath;
    if (file_exists($fullPath)) {
        echo "   ✓ {$description} existe\n";
        
        $content = file_get_contents($fullPath);
        $toastFeatures = [
            'addToast',
            'removeToast',
            'success',
            'error',
            'warning',
            'info',
            'toasts'
        ];
        
        foreach ($toastFeatures as $feature) {
            if (strpos($content, $feature) !== false) {
                echo "     ✓ Función '{$feature}' presente\n";
            } else {
                echo "     ✗ Función '{$feature}' NO presente\n";
            }
        }
    } else {
        echo "   ✗ {$description} NO existe\n";
    }
}

// Verificar funcionalidades de tiempo real
echo "\n4. Verificando funcionalidades de tiempo real...\n";
$taskCardPath = __DIR__ . '/../resources/js/components/TaskCard.vue';
if (file_exists($taskCardPath)) {
    $content = file_get_contents($taskCardPath);
    
    $realtimeFeatures = [
        'setInterval',
        'clearInterval',
        'currentTime',
        'Date.now()',
        'animate-pulse',
        'isWorking'
    ];
    
    foreach ($realtimeFeatures as $feature) {
        if (strpos($content, $feature) !== false) {
            echo "     ✓ Funcionalidad '{$feature}' presente\n";
        } else {
            echo "     ✗ Funcionalidad '{$feature}' NO presente\n";
        }
    }
} else {
    echo "   ✗ TaskCard.vue NO existe\n";
}

// Verificar funcionalidades de drag & drop
echo "\n5. Verificando funcionalidades de drag & drop...\n";
$kanbanPath = __DIR__ . '/../resources/js/pages/Developer/Kanban.vue';
if (file_exists($kanbanPath)) {
    $content = file_get_contents($kanbanPath);
    
    $dragDropFeatures = [
        'draggable="true"',
        'onDragStart',
        'onDrop',
        'dragover.prevent',
        'dataTransfer',
        'effectAllowed'
    ];
    
    foreach ($dragDropFeatures as $feature) {
        if (strpos($content, $feature) !== false) {
            echo "     ✓ Funcionalidad '{$feature}' presente\n";
        } else {
            echo "     ✗ Funcionalidad '{$feature}' NO presente\n";
        }
    }
} else {
    echo "   ✗ Kanban.vue NO existe\n";
}

// Verificar funcionalidades de aprobación
echo "\n6. Verificando funcionalidades de aprobación...\n";
$teamLeaderPath = __DIR__ . '/../resources/js/pages/TeamLeader/Dashboard.vue';
if (file_exists($teamLeaderPath)) {
    $content = file_get_contents($teamLeaderPath);
    
    $approvalFeatures = [
        'approveTask',
        'rejectTask',
        'rejection_reason',
        'showRejectModal',
        'closeRejectModal',
        'approval_status',
        'pending',
        'approved',
        'rejected'
    ];
    
    foreach ($approvalFeatures as $feature) {
        if (strpos($content, $feature) !== false) {
            echo "     ✓ Funcionalidad '{$feature}' presente\n";
        } else {
            echo "     ✗ Funcionalidad '{$feature}' NO presente\n";
        }
    }
} else {
    echo "   ✗ TeamLeader/Dashboard.vue NO existe\n";
}

// Verificar funcionalidades de métricas
echo "\n7. Verificando funcionalidades de métricas...\n";
$adminPath = __DIR__ . '/../resources/js/pages/Admin/Dashboard.vue';
if (file_exists($adminPath)) {
    $content = file_get_contents($adminPath);
    
    $metricsFeatures = [
        'systemStats',
        'developerMetrics',
        'efficiency_percentage',
        'completion_rate',
        'average_task_time',
        'tasksRequiringAttention',
        'activeProjectsSummary'
    ];
    
    foreach ($metricsFeatures as $feature) {
        if (strpos($content, $feature) !== false) {
            echo "     ✓ Funcionalidad '{$feature}' presente\n";
        } else {
            echo "     ✗ Funcionalidad '{$feature}' NO presente\n";
        }
    }
} else {
    echo "   ✗ Admin/Dashboard.vue NO existe\n";
}

// Verificar funcionalidades de navegación
echo "\n8. Verificando funcionalidades de navegación...\n";
$navigationFeatures = [
    'router.visit',
    'navigateToInProgressTasks',
    'navigateToDeveloperMetrics',
    'navigateToTimeReports',
    'viewTaskDetails',
    'viewProjectDetails'
];

$allPages = [
    $kanbanPath,
    $teamLeaderPath,
    $adminPath
];

foreach ($navigationFeatures as $feature) {
    $found = false;
    foreach ($allPages as $pagePath) {
        if (file_exists($pagePath)) {
            $content = file_get_contents($pagePath);
            if (strpos($content, $feature) !== false) {
                $found = true;
                break;
            }
        }
    }
    
    if ($found) {
        echo "     ✓ Funcionalidad '{$feature}' presente\n";
    } else {
        echo "     ✗ Funcionalidad '{$feature}' NO presente\n";
    }
}

// Verificar funcionalidades de UI/UX
echo "\n9. Verificando funcionalidades de UI/UX...\n";
$uiFeatures = [
    'loading',
    'disabled',
    'hover:',
    'transition-',
    'animate-',
    'focus:',
    'bg-',
    'text-',
    'border-',
    'rounded-',
    'shadow-'
];

$allFiles = [
    $kanbanPath,
    $teamLeaderPath,
    $adminPath,
    __DIR__ . '/../resources/js/components/TaskCard.vue',
    __DIR__ . '/../resources/js/components/Toast.vue'
];

$uiFeaturesFound = 0;
foreach ($uiFeatures as $feature) {
    foreach ($allFiles as $filePath) {
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            if (strpos($content, $feature) !== false) {
                $uiFeaturesFound++;
                break;
            }
        }
    }
}

if ($uiFeaturesFound >= 5) {
    echo "     ✓ Funcionalidades de UI/UX presentes ({$uiFeaturesFound}/" . count($uiFeatures) . ")\n";
} else {
    echo "     ✗ Funcionalidades de UI/UX insuficientes ({$uiFeaturesFound}/" . count($uiFeatures) . ")\n";
}

// Verificar funcionalidades de responsive design
echo "\n10. Verificando funcionalidades de responsive design...\n";
$responsiveFeatures = [
    'grid-cols-1',
    'md:grid-cols-',
    'lg:grid-cols-',
    'max-w-',
    'px-4',
    'sm:px-',
    'lg:px-',
    'overflow-x-auto'
];

$responsiveFeaturesFound = 0;
foreach ($responsiveFeatures as $feature) {
    foreach ($allFiles as $filePath) {
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            if (strpos($content, $feature) !== false) {
                $responsiveFeaturesFound++;
                break;
            }
        }
    }
}

if ($responsiveFeaturesFound >= 4) {
    echo "     ✓ Funcionalidades de responsive design presentes ({$responsiveFeaturesFound}/" . count($responsiveFeatures) . ")\n";
} else {
    echo "     ✗ Funcionalidades de responsive design insuficientes ({$responsiveFeaturesFound}/" . count($responsiveFeatures) . ")\n";
}

echo "\n=== FIN DE VERIFICACIÓN FASE 6 ===\n";
echo "Si todos los elementos están marcados con ✓, la Fase 6 está correctamente implementada.\n";
echo "Si hay elementos marcados con ✗, revisa la implementación antes de continuar.\n"; 
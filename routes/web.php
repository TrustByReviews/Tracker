<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\QaController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamLeaderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => false, // Disable public registration
    ]);
})->name('home');

Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Incluir rutas de clientes
require __DIR__.'/client.php';

// Incluir rutas de administración
require __DIR__.'/admin.php';

Route::middleware(['auth'])->group(function () {
    // Projects routes with permissions
    Route::middleware(['auth'])->group(function () {
        Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    });

    // Team Leader Routes
    Route::middleware(['auth'])->group(function () {
        // Dashboard y páginas principales
        Route::get('team-leader/dashboard', [TeamLeaderController::class, 'dashboard'])->name('team-leader.dashboard');
        Route::get('team-leader/projects', [TeamLeaderController::class, 'projects'])->name('team-leader.projects');
        Route::get('team-leader/sprints', [TeamLeaderController::class, 'sprints'])->name('team-leader.sprints');
        Route::get('team-leader/notifications', [TeamLeaderController::class, 'notifications'])->name('team-leader.notifications');
        Route::get('team-leader/reports', [TeamLeaderController::class, 'reports'])->name('team-leader.reports');
        Route::get('team-leader/settings', [TeamLeaderController::class, 'settings'])->name('team-leader.settings');
        
        // API endpoints para el sidebar
        Route::get('api/team-leader/stats', [TeamLeaderController::class, 'getStats'])->name('api.team-leader.stats');
        Route::get('api/team-leader/notifications', [TeamLeaderController::class, 'getNotifications'])->name('api.team-leader.notifications');
        
        // Notifications routes
        Route::get('team-leader/notifications', [TeamLeaderController::class, 'notifications'])->name('team-leader.notifications');
        Route::post('team-leader/notifications/{notification}/read', [TeamLeaderController::class, 'markNotificationAsRead'])->name('team-leader.notifications.read');
        Route::post('team-leader/notifications/read-all', [TeamLeaderController::class, 'markAllNotificationsAsRead'])->name('team-leader.notifications.read-all');
        
        // Review Routes
        Route::get('team-leader/review/tasks', [App\Http\Controllers\TeamLeaderReviewController::class, 'getTasksReadyForReview'])->name('team-leader.review.tasks');
        Route::get('team-leader/review/bugs', [App\Http\Controllers\TeamLeaderReviewController::class, 'getBugsReadyForReview'])->name('team-leader.review.bugs');
        Route::get('team-leader/review/stats', [App\Http\Controllers\TeamLeaderReviewController::class, 'getReviewStats'])->name('team-leader.review.stats');
        
        Route::post('team-leader/review/tasks/{task}/approve', [App\Http\Controllers\TeamLeaderReviewController::class, 'approveTask'])->name('team-leader.review.tasks.approve');
        Route::post('team-leader/review/tasks/{task}/request-changes', [App\Http\Controllers\TeamLeaderReviewController::class, 'requestTaskChanges'])->name('team-leader.review.tasks.request-changes');
        
        Route::post('team-leader/review/bugs/{bug}/approve', [App\Http\Controllers\TeamLeaderReviewController::class, 'approveBug'])->name('team-leader.review.bugs.approve');
        Route::post('team-leader/review/bugs/{bug}/request-changes', [App\Http\Controllers\TeamLeaderReviewController::class, 'requestBugChanges'])->name('team-leader.review.bugs.request-changes');
        
        // Rutas para gestionar sprints y sus elementos
        Route::get('team-leader/sprints/{sprint}', [TeamLeaderController::class, 'showSprint'])->name('team-leader.sprints.show');
        Route::get('team-leader/sprints/{sprint}/tasks', [TeamLeaderController::class, 'sprintTasks'])->name('team-leader.sprints.tasks');
        Route::get('team-leader/sprints/{sprint}/bugs', [TeamLeaderController::class, 'sprintBugs'])->name('team-leader.sprints.bugs');
    });
    
    Route::middleware('permission:projects.create')->group(function () {
        Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
    });
    
    Route::middleware('permission:projects.edit')->group(function () {
        Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    });
    
    Route::middleware('permission:projects.delete')->group(function () {
        Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    });

    Route::middleware('permission:projects.edit')->group(function () {
        Route::post('projects/{project}/finish', [ProjectController::class, 'finish'])->name('projects.finish');
        Route::put('projects/{project}/users', [ProjectController::class, 'updateUsers'])->name('projects.users.update');
    });

    // Sprints routes with permissions
    Route::middleware(['auth'])->group(function () {
        Route::get('sprints', [SprintController::class, 'index'])->name('sprints.index');
        Route::get('projects/{project}/sprints/{sprint}', [SprintController::class, 'show'])->name('sprints.show');
    });
    
    Route::middleware('permission:sprints.create')->group(function () {
        Route::post('sprints', [SprintController::class , 'store'])->name('sprints.store');
    });
    
    Route::middleware('permission:sprints.edit')->group(function () {
        Route::put('projects/{project}/sprints/{sprint}', [SprintController::class, 'update'])->name('sprints.update');
        Route::put('sprints/{sprint}', [SprintController::class, 'update'])->name('sprints.update.general');
    });
    
    Route::middleware('permission:sprints.delete')->group(function () {
        Route::delete('projects/{project}/sprints/{sprint}', [SprintController::class, 'destroy'])->name('sprints.destroy');
    });

    // Sprint completion and retrospective routes
    Route::middleware(['auth'])->group(function () {
        Route::post('sprints/{sprint}/finish', [SprintController::class, 'finishSprint'])->name('sprints.finish');
        Route::post('sprints/{sprint}/retrospective', [SprintController::class, 'addRetrospective'])->name('sprints.retrospective');
        Route::post('sprints/{sprint}/finish-with-retrospective', [SprintController::class, 'finishSprintWithRetrospective'])->name('sprints.finish-with-retrospective');
        Route::get('sprints/{sprint}/retrospective-summary', [SprintController::class, 'getRetrospectiveSummary'])->name('sprints.retrospective-summary');
        Route::get('sprints/{sprint}/finish-status', [SprintController::class, 'getFinishStatus'])->name('sprints.finish-status');
    });

    // Tasks routes with permissions
    Route::middleware(['auth'])->group(function () {
        Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::get('tasks/available', [TaskController::class, 'getAvailableTasks'])->name('tasks.available');
        Route::get('tasks/my-tasks', [TaskController::class, 'getMyTasks'])->name('tasks.my-tasks');
        Route::get('projects/{project}/developers', [TaskController::class, 'getAvailableDevelopers'])->name('projects.developers');
    });
    
    // Task assignment routes
    Route::middleware('permission:tasks.assign')->group(function () {
        Route::post('tasks/{task}/assign', [TaskController::class, 'assignTask'])->name('tasks.assign');
        Route::post('tasks/{task}/self-assign', [TaskController::class, 'selfAssignTask'])->name('tasks.self-assign');
    });
    
    // Task time tracking routes (available to all authenticated users)
    Route::post('tasks/{task}/start-work', [TaskController::class, 'startWork'])->name('tasks.start-work');
    Route::post('tasks/{task}/pause-work', [TaskController::class, 'pauseWork'])->name('tasks.pause-work');
    Route::post('tasks/{task}/resume-work', [TaskController::class, 'resumeWork'])->name('tasks.resume-work');
    Route::post('tasks/{task}/finish-work', [TaskController::class, 'finishWork'])->name('tasks.finish-work');
    Route::get('tasks/{task}/current-time', [TaskController::class, 'getCurrentWorkTime'])->name('tasks.current-time');
    Route::get('tasks/{task}/time-logs', [TaskController::class, 'getTaskTimeLogs'])->name('tasks.time-logs');
    Route::get('tasks/active', [TaskController::class, 'getActiveTasks'])->name('tasks.active');
    Route::get('tasks/paused', [TaskController::class, 'getPausedTasks'])->name('tasks.paused');
    Route::get('tasks/auto-paused', [TaskController::class, 'getAutoPausedTasks'])->name('tasks.auto-paused');
    Route::post('tasks/{task}/resume-auto-paused', [TaskController::class, 'resumeAutoPausedTask'])->name('tasks.resume-auto-paused');

    // Team Leader routes with permissions
    Route::prefix('team-leader')->name('team-leader.')->middleware('permission:team-leader.dashboard')->group(function () {
        Route::get('dashboard', [TeamLeaderController::class, 'dashboard'])->name('dashboard');
        Route::get('pending-tasks', [TeamLeaderController::class, 'pendingTasks'])->name('pending-tasks');
        Route::get('in-progress-tasks', [TeamLeaderController::class, 'inProgressTasks'])->name('in-progress-tasks');
        Route::get('developers', [TeamLeaderController::class, 'developers'])->name('developers');

        
        Route::middleware('permission:tasks.approve')->group(function () {
            Route::post('tasks/{task}/approve', [TeamLeaderController::class, 'approveTask'])->name('tasks.approve');
            Route::post('tasks/{task}/reject', [TeamLeaderController::class, 'rejectTask'])->name('tasks.reject');
        });
        
        Route::middleware('permission:tasks.assign')->group(function () {
            Route::post('tasks/{task}/assign', [TeamLeaderController::class, 'assignTask'])->name('tasks.assign');
        });
        

        
        Route::get('stats/approval', [TeamLeaderController::class, 'getApprovalStats'])->name('stats.approval');
        Route::get('stats/developer-time', [TeamLeaderController::class, 'getDeveloperTimeSummary'])->name('stats.developer-time');
        Route::get('stats/recently-completed', [TeamLeaderController::class, 'getRecentlyCompleted'])->name('stats.recently-completed');
    });

    // QA routes (for actions only)
    Route::prefix('qa')->name('qa.')->middleware('role:qa')->group(function () {
        // Task management
        Route::post('tasks/{task}/assign', [QaController::class, 'assignTask'])->name('tasks.assign');
        Route::post('tasks/{task}/approve', [QaController::class, 'approveTask'])->name('tasks.approve');
        Route::post('tasks/{task}/reject', [QaController::class, 'rejectTask'])->name('tasks.reject');
        Route::get('tasks/{task}', [QaController::class, 'showTask'])->name('tasks.show');
        
        // Finished items for QA approval (unified view)
        Route::get('finished-items', [QaController::class, 'finishedItems'])->name('finished-items');
        Route::post('tasks/{task}/start-testing', [QaController::class, 'startTestingTask'])->name('tasks.start-testing');
        Route::post('tasks/{task}/pause-testing', [QaController::class, 'pauseTestingTask'])->name('tasks.pause-testing');
        Route::post('tasks/{task}/resume-testing', [QaController::class, 'resumeTestingTask'])->name('tasks.resume-testing');
        Route::post('tasks/{task}/finish-testing', [QaController::class, 'finishTestingTask'])->name('tasks.finish-testing');
        
        // Bug management
        Route::post('bugs/{bug}/assign', [QaController::class, 'assignBug'])->name('bugs.assign');
        Route::post('bugs/{bug}/approve', [QaController::class, 'approveBug'])->name('bugs.approve');
        Route::post('bugs/{bug}/reject', [QaController::class, 'rejectBug'])->name('bugs.reject');
        Route::get('bugs/{bug}', [QaController::class, 'showBug'])->name('bugs.show');
        
        // Bug testing actions
        Route::post('bugs/{bug}/start-testing', [QaController::class, 'startTestingBug'])->name('bugs.start-testing');
        Route::post('bugs/{bug}/pause-testing', [QaController::class, 'pauseTestingBug'])->name('bugs.pause-testing');
        Route::post('bugs/{bug}/resume-testing', [QaController::class, 'resumeTestingBug'])->name('bugs.resume-testing');
        Route::post('bugs/{bug}/finish-testing', [QaController::class, 'finishTestingBug'])->name('bugs.finish-testing');
        
        // Notifications
        Route::get('notifications', [QaController::class, 'notifications'])->name('notifications');
        Route::post('notifications/{notification}/read', [QaController::class, 'markNotificationAsRead'])->name('notifications.read');
        Route::post('notifications/read-all', [QaController::class, 'markAllNotificationsAsRead'])->name('notifications.read-all');
        
        // API endpoints para notificaciones
        Route::get('api/qa/notifications', [QaController::class, 'getNotifications'])->name('api.qa.notifications');
    });

    // Admin routes with permissions
    Route::prefix('admin')->name('admin.')->middleware('permission:admin.dashboard')->group(function () {
        // Redirect admin dashboard to main dashboard
        Route::get('dashboard', function () {
            return redirect('/dashboard');
        })->name('dashboard');
        
        Route::get('in-progress-tasks', [AdminController::class, 'inProgressTasks'])->name('in-progress-tasks');
        Route::get('developer-metrics', [AdminController::class, 'developerMetrics'])->name('developer-metrics');
        Route::get('project-metrics', [AdminController::class, 'projectMetrics'])->name('project-metrics');
        Route::get('time-reports', [AdminController::class, 'timeReports'])->name('time-reports');
        Route::get('tasks-requiring-attention', [AdminController::class, 'tasksRequiringAttention'])->name('tasks-requiring-attention');
        Route::get('pending-approval-tasks', [AdminController::class, 'pendingApprovalTasks'])->name('pending-approval-tasks');
        
        // TEMPORARILY DISABLED - Login as user functionality
        // Route::get('login-as/{userId}', [AdminController::class, 'loginAsUser'])->name('login-as-user');
        // Route::get('return-to-admin', [AdminController::class, 'returnToAdmin'])->name('return-to-admin');
        
        Route::middleware('permission:admin.users')->group(function () {
            Route::get('users', [AdminController::class, 'users'])->name('users');
        });
        
        // API endpoints
        Route::get('stats/system', [AdminController::class, 'getSystemStats'])->name('stats.system');
        Route::get('tasks/in-progress', [AdminController::class, 'getInProgressTasks'])->name('tasks.in-progress');
        Route::get('metrics/developers', [AdminController::class, 'getDeveloperMetrics'])->name('metrics.developers');
        Route::get('metrics/projects', [AdminController::class, 'getProjectMetrics'])->name('metrics.projects');
        Route::get('reports/time', [AdminController::class, 'getTimeReport'])->name('reports.time');
        Route::get('tasks/requiring-attention', [AdminController::class, 'getTasksRequiringAttention'])->name('tasks.requiring-attention');
        Route::get('projects/active-summary', [AdminController::class, 'getActiveProjectsSummary'])->name('projects.active-summary');
        Route::get('developers/active-summary', [AdminController::class, 'getActiveDevelopersSummary'])->name('developers.active-summary');
        

    });



    // Reports routes with permissions
Route::middleware('permission:reports.view')->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/current-week', [ReportController::class, 'currentWeek'])->name('reports.current-week');
    Route::get('/reports/{id}', [ReportController::class, 'show'])->name('reports.show');
});

// API routes for reports
Route::middleware('permission:reports.view')->group(function () {
    // Route moved to payments group below
});

// API routes accessible from payments page
Route::get('/api/projects', [ProjectController::class, 'getProjectsForReports'])->name('api.projects');
Route::get('/api/projects/users', [ProjectController::class, 'getUsersByProject'])->name('api.projects.users');
Route::get('/api/payments/rework', [PaymentController::class, 'getReworkData'])->name('api.payments.rework');
    
    Route::middleware('permission:reports.create')->group(function () {
        Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
    });

    // Analytics routes - accessible by admin users
    Route::middleware(['auth'])->group(function () {
        Route::get('/analytics/projects', [ReportController::class, 'projectAnalytics'])->name('analytics.projects');
        Route::get('/analytics/team', [ReportController::class, 'teamAnalytics'])->name('analytics.team');
        Route::get('/analytics/budget', [ReportController::class, 'budgetAnalytics'])->name('analytics.budget');
    });

    // Payment routes (unified)
Route::prefix('payments')->name('payments.')->group(function () {
    // Dashboard unificado (redirige a /payments)
    Route::get('/', [PaymentController::class, 'adminDashboard'])->name('dashboard');
    
    // Dashboard para usuarios normales (redirige a /payments)
    Route::get('dashboard', function () {
        return redirect('/payments');
    })->name('user-dashboard');
    
    // Rutas para admins
    Route::middleware('permission:payment-reports.view')->group(function () {
        Route::get('admin', function () {
            return redirect('/payments');
        })->name('admin');
        Route::get('reports', [PaymentController::class, 'index'])->name('reports');
        Route::get('reports/{paymentReport}', [PaymentController::class, 'show'])->name('reports.show');
        Route::get('export', [PaymentController::class, 'export'])->name('export');
    });
    
    Route::middleware('permission:payment-reports.generate')->group(function () {
    Route::post('generate', [PaymentController::class, 'generate'])->name('generate');
    Route::post('generate-detailed', [PaymentController::class, 'generateDetailedReport'])->name('generate-detailed');
    Route::post('generate-project', [PaymentController::class, 'generateProjectReport'])->name('generate-project');
    Route::post('generate-user-type', [PaymentController::class, 'generateUserTypeReport'])->name('generate-user-type');
    Route::get('test-report', [PaymentController::class, 'testReportGeneration'])->name('test-report');
    Route::post('generate-simple-excel', [PaymentController::class, 'generateSimpleExcel'])->name('generate-simple-excel');
    Route::get('test-communication', [PaymentController::class, 'testCommunication'])->name('test-communication');
});
    
    Route::middleware('permission:payment-reports.approve')->group(function () {
        Route::post('reports/{paymentReport}/approve', [PaymentController::class, 'approve'])->name('reports.approve');
        Route::post('reports/{paymentReport}/mark-paid', [PaymentController::class, 'markAsPaid'])->name('reports.mark-paid');
    });
});



// Payment Reports routes (legacy - redirigen a /payments)
Route::get('/payment-reports', function () {
    return redirect('/payments');
})->name('payment-reports.index');
Route::post('/payment-reports/generate', function () {
    return redirect('/payments');
})->name('payment-reports.generate');



    // Developer Canvas routes
    Route::get('/developer/canvas', [App\Http\Controllers\DeveloperCanvasController::class, 'index'])->name('developer.canvas');
    Route::put('/tasks/{task}/status', [App\Http\Controllers\DeveloperCanvasController::class, 'updateTaskStatus'])->name('tasks.update-status');
    Route::post('/tasks/{task}/approve', [App\Http\Controllers\DeveloperCanvasController::class, 'approveTask'])->name('tasks.approve');
    Route::post('/tasks/{task}/reject', [App\Http\Controllers\DeveloperCanvasController::class, 'rejectTask'])->name('tasks.reject');



    Route::post('tasks', [TaskController::class , 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');

    // Users routes with permissions
    Route::middleware('permission:users.view')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
    });
    
    Route::middleware('permission:users.edit')->group(function () {
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    });


    // Permissions routes with permissions
    Route::prefix('permissions')->name('permissions.')->middleware('permission:permissions.manage')->group(function () {
        Route::get('/', [App\Http\Controllers\PermissionController::class, 'index'])->name('index');
        Route::get('/list', [App\Http\Controllers\PermissionController::class, 'getPermissions'])->name('list');
        Route::get('/user/{user}', [App\Http\Controllers\PermissionController::class, 'getUserPermissions'])->name('user');
        
        Route::middleware('permission:permissions.grant')->group(function () {
            Route::post('/user/{user}/grant', [App\Http\Controllers\PermissionController::class, 'grantPermission'])->name('grant');
        });
        
        Route::middleware('permission:permissions.revoke')->group(function () {
            Route::post('/user/{user}/revoke', [App\Http\Controllers\PermissionController::class, 'revokePermission'])->name('revoke');
        });
        
        Route::get('/role/{role}', [App\Http\Controllers\PermissionController::class, 'getRolePermissions'])->name('role');
        Route::put('/role/{role}', [App\Http\Controllers\PermissionController::class, 'updateRolePermissions'])->name('update-role');
        Route::get('/expired', [App\Http\Controllers\PermissionController::class, 'getExpiredPermissions'])->name('expired');
        Route::delete('/expired', [App\Http\Controllers\PermissionController::class, 'cleanupExpiredPermissions'])->name('cleanup');
    });



    // Bugs routes with permissions
    Route::middleware(['auth'])->group(function () {
        Route::get('bugs', [App\Http\Controllers\BugController::class, 'index'])->name('bugs.index');
        Route::get('bugs/{bug}', [App\Http\Controllers\BugController::class, 'show'])->name('bugs.show');
        Route::get('bugs/available', [App\Http\Controllers\BugController::class, 'getAvailableBugs'])->name('bugs.available');
        Route::get('bugs/my-bugs', [App\Http\Controllers\BugController::class, 'getAssignedBugs'])->name('bugs.my-bugs');
        
        // Allow bug creation for authenticated users (temporarily remove permission requirement)
        Route::post('bugs', [App\Http\Controllers\BugController::class, 'store'])->name('bugs.store');
    });
    
    // Route::middleware('permission:bugs.create')->group(function () {
    //     Route::post('bugs', [App\Http\Controllers\BugController::class, 'store'])->name('bugs.store');
    // });
    
    Route::middleware('permission:bugs.edit')->group(function () {
        Route::put('bugs/{bug}', [App\Http\Controllers\BugController::class, 'update'])->name('bugs.update');
    });
    
    Route::middleware('permission:bugs.delete')->group(function () {
        Route::delete('bugs/{bug}', [App\Http\Controllers\BugController::class, 'destroy'])->name('bugs.destroy');
    });
    
    // Bug assignment routes
    Route::middleware('permission:bugs.assign')->group(function () {
        Route::post('bugs/{bug}/assign', [App\Http\Controllers\BugController::class, 'assignBug'])->name('bugs.assign');
        Route::post('bugs/{bug}/self-assign', [App\Http\Controllers\BugController::class, 'selfAssignBug'])->name('bugs.self-assign');
        Route::post('bugs/{bug}/unassign', [App\Http\Controllers\BugController::class, 'unassignBug'])->name('bugs.unassign');
    });
    
    // Bug time tracking routes (available to all authenticated users)
    Route::post('bugs/{bug}/start-work', [App\Http\Controllers\BugController::class, 'startWork'])->name('bugs.start-work');
    Route::post('bugs/{bug}/pause-work', [App\Http\Controllers\BugController::class, 'pauseWork'])->name('bugs.pause-work');
    Route::post('bugs/{bug}/resume-work', [App\Http\Controllers\BugController::class, 'resumeWork'])->name('bugs.resume-work');
    Route::post('bugs/{bug}/finish-work', [App\Http\Controllers\BugController::class, 'finishWork'])->name('bugs.finish-work');
    Route::get('bugs/{bug}/current-time', [App\Http\Controllers\BugController::class, 'getCurrentWorkTime'])->name('bugs.current-time');
    Route::get('bugs/{bug}/time-logs', [App\Http\Controllers\BugController::class, 'getBugTimeLogs'])->name('bugs.time-logs');
    Route::get('bugs/active', [App\Http\Controllers\BugController::class, 'getActiveBugs'])->name('bugs.active');
    Route::get('bugs/paused', [App\Http\Controllers\BugController::class, 'getPausedBugs'])->name('bugs.paused');
    
    // Bug resolution routes
    Route::middleware('permission:bugs.resolve')->group(function () {
        Route::post('bugs/{bug}/resolve', [App\Http\Controllers\BugController::class, 'resolveBug'])->name('bugs.resolve');
        Route::post('bugs/{bug}/verify', [App\Http\Controllers\BugController::class, 'verifyBug'])->name('bugs.verify');
        Route::post('bugs/{bug}/close', [App\Http\Controllers\BugController::class, 'closeBug'])->name('bugs.close');
        Route::post('bugs/{bug}/reopen', [App\Http\Controllers\BugController::class, 'reopenBug'])->name('bugs.reopen');
    });
    
    // Bug comments routes
    Route::middleware('permission:bugs.comment')->group(function () {
        Route::post('bugs/{bug}/comments', [App\Http\Controllers\BugController::class, 'addComment'])->name('bugs.add-comment');
    });

    // Developer Activity Dashboard (dentro del grupo auth)
    Route::get('developer-activity', [App\Http\Controllers\Admin\DeveloperActivityController::class, 'index'])->name('developer-activity');
    Route::get('developer-activity/data', [App\Http\Controllers\Admin\DeveloperActivityController::class, 'getDeveloperActivity'])->name('developer-activity.data');
    Route::get('developer-activity/team', [App\Http\Controllers\Admin\DeveloperActivityController::class, 'getTeamActivity'])->name('developer-activity.team');
    Route::post('developer-activity/export', [App\Http\Controllers\Admin\DeveloperActivityController::class, 'exportReport'])->name('developer-activity.export');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

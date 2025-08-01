<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProjectController;
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

Route::middleware(['auth'])->group(function () {
    // Projects routes with permissions
    Route::middleware('permission:projects.view')->group(function () {
        Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
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

    // Sprints routes with permissions
    Route::middleware('permission:sprints.view')->group(function () {
        Route::get('sprints', [SprintController::class, 'index'])->name('sprints.index');
        Route::get('projects/{project}/sprints/{sprint}', [SprintController::class, 'show'])->name('sprints.show');
    });
    
    Route::middleware('permission:sprints.create')->group(function () {
        Route::post('sprints', [SprintController::class , 'store'])->name('sprints.store');
    });
    
    Route::middleware('permission:sprints.edit')->group(function () {
        Route::put('projects/{project}/sprints/{sprint}', [SprintController::class, 'update'])->name('sprints.update');
    });
    
    Route::middleware('permission:sprints.delete')->group(function () {
        Route::delete('projects/{project}/sprints/{sprint}', [SprintController::class, 'destroy'])->name('sprints.destroy');
    });

    // Tasks routes with permissions
    Route::middleware('permission:tasks.view')->group(function () {
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

    // Admin routes with permissions
    Route::prefix('admin')->name('admin.')->middleware('permission:admin.dashboard')->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
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
    
    Route::middleware('permission:reports.create')->group(function () {
        Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
    });

    // Payment routes
    Route::prefix('payments')->name('payments.')->group(function () {
        // Dashboard para usuarios normales
        Route::get('dashboard', [PaymentController::class, 'dashboard'])->name('dashboard');
        
        // Rutas para admins
        Route::middleware('permission:payment-reports.view')->group(function () {
            Route::get('admin', [PaymentController::class, 'adminDashboard'])->name('admin');
            Route::get('reports', [PaymentController::class, 'index'])->name('reports');
            Route::get('reports/{paymentReport}', [PaymentController::class, 'show'])->name('reports.show');
            Route::get('export', [PaymentController::class, 'export'])->name('export');
        });
        
        Route::middleware('permission:payment-reports.generate')->group(function () {
            Route::post('generate', [PaymentController::class, 'generate'])->name('generate');
        });
        
        Route::middleware('permission:payment-reports.approve')->group(function () {
            Route::post('reports/{paymentReport}/approve', [PaymentController::class, 'approve'])->name('reports.approve');
            Route::post('reports/{paymentReport}/mark-paid', [PaymentController::class, 'markAsPaid'])->name('reports.mark-paid');
        });
    });

    // Payment Reports routes (legacy)
    Route::get('/payment-reports', [App\Http\Controllers\PaymentReportController::class, 'index'])->name('payment-reports.index');
    Route::post('/payment-reports/generate', [App\Http\Controllers\PaymentReportController::class, 'generate'])->name('payment-reports.generate');

    // Developer Canvas routes
    Route::get('/developer/canvas', [App\Http\Controllers\DeveloperCanvasController::class, 'index'])->name('developer.canvas');
    Route::put('/tasks/{task}/status', [App\Http\Controllers\DeveloperCanvasController::class, 'updateTaskStatus'])->name('tasks.update-status');
    Route::post('/tasks/{task}/approve', [App\Http\Controllers\DeveloperCanvasController::class, 'approveTask'])->name('tasks.approve');
    Route::post('/tasks/{task}/reject', [App\Http\Controllers\DeveloperCanvasController::class, 'rejectTask'])->name('tasks.reject');

    // Team Leader routes
    Route::get('/team-leader', [App\Http\Controllers\TeamLeaderController::class, 'index'])->name('team-leader.index');
    Route::get('/team-leader/projects/{project}', [App\Http\Controllers\TeamLeaderController::class, 'projectDetails'])->name('team-leader.project-details');
    Route::post('/team-leader/tasks/{task}/approve', [App\Http\Controllers\TeamLeaderController::class, 'approveTask'])->name('team-leader.approve-task');
    Route::post('/team-leader/tasks/{task}/reject', [App\Http\Controllers\TeamLeaderController::class, 'rejectTask'])->name('team-leader.reject-task');

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
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\TaskController;
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
    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::get('sprints', [SprintController::class, 'index'])->name('sprints.index');
    Route::get('projects/{project}/sprints/{sprint}', [SprintController::class, 'show'])->name('sprints.show');
    Route::post('sprints', [SprintController::class , 'store'])->name('sprints.store');
    Route::put('projects/{project}/sprints/{sprint}', [SprintController::class, 'update'])->name('sprints.update');
    Route::delete('projects/{project}/sprints/{sprint}', [SprintController::class, 'destroy'])->name('sprints.destroy');

    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');

    // Reports routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/current-week', [ReportController::class, 'currentWeek'])->name('reports.current-week');
    Route::get('/reports/{id}', [ReportController::class, 'show'])->name('reports.show');
    Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');

    // Payment Reports routes
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

    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\SuggestionController;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Client Routes
|--------------------------------------------------------------------------
|
| Aquí están las rutas específicas para usuarios con rol 'client'.
| Estas rutas solo son accesibles para clientes autenticados.
|
*/

Route::middleware(['auth', 'role:client'])->prefix('client')->name('client.')->group(function () {
    
    // Rutas web para vistas Vue.js
    Route::get('/dashboard', function () {
        return Inertia::render('Client/Dashboard');
    })->name('dashboard');
    
    Route::get('/projects', function () {
        return Inertia::render('Client/Projects');
    })->name('projects');
    
    // API endpoints para el dashboard (deben ir antes que las rutas con parámetros)
    Route::get('/dashboard/api', [DashboardController::class, 'index'])->name('dashboard.api');
    Route::get('/dashboard/project/{id}', [DashboardController::class, 'getProjectDetails'])->name('project.details.api');
    Route::get('/projects/api', [DashboardController::class, 'getProjects'])->name('projects.api');
    Route::get('/tasks/api', [DashboardController::class, 'getTasks'])->name('tasks.api');
    Route::get('/sprints/api', [DashboardController::class, 'getSprints'])->name('sprints.api');
    
    Route::get('/projects/{id}', function ($id) {
        // Obtener los datos del proyecto directamente aquí
        $controller = new DashboardController();
        $response = $controller->getProjectDetails($id);
        $data = json_decode($response->getContent(), true);
        
        if ($data['success']) {
            return Inertia::render('Client/ProjectDetails', [
                'projectId' => $id,
                'project' => $data['data']
            ]);
        } else {
            // Si hay error, redirigir a proyectos con mensaje de error
            return redirect()->route('client.projects')->with('error', $data['message'] ?? 'Error al cargar el proyecto');
        }
    })->name('project.details');
    
    Route::get('/tasks', function () {
        return Inertia::render('Client/Tasks');
    })->name('tasks');
    
    Route::get('/sprints', function () {
        return Inertia::render('Client/Sprints');
    })->name('sprints');
    
    Route::get('/suggestions', function () {
        return Inertia::render('Client/Suggestions');
    })->name('suggestions');
    
    // API endpoints para sugerencias
    Route::prefix('suggestions')->name('suggestions.')->group(function () {
        Route::get('/api', [SuggestionController::class, 'index'])->name('index');
        Route::post('/api', [SuggestionController::class, 'store'])->name('store');
        Route::get('/api/{id}', [SuggestionController::class, 'show'])->name('show');
        Route::get('/statistics/overview', [SuggestionController::class, 'statistics'])->name('statistics');
        Route::get('/projects/available', [SuggestionController::class, 'getAvailableProjects'])->name('projects.available');
        Route::get('/projects/{projectId}/tasks', [SuggestionController::class, 'getAvailableTasks'])->name('tasks.available');
        Route::get('/projects/{projectId}/sprints', [SuggestionController::class, 'getAvailableSprints'])->name('sprints.available');
    });
    
    // Redirigir la raíz del cliente al dashboard
    Route::get('/', function () {
        return redirect()->route('client.dashboard');
    })->name('home');
});

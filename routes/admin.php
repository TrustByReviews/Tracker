<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SuggestionController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Aquí están las rutas específicas para usuarios con rol 'admin'.
| Estas rutas solo son accesibles para administradores autenticados.
|
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Gestión de sugerencias de clientes
    Route::prefix('suggestions')->name('suggestions.')->group(function () {
        Route::get('/', [SuggestionController::class, 'index'])->name('index');
        Route::get('/statistics', [SuggestionController::class, 'statistics'])->name('statistics');
        Route::get('/filters', [SuggestionController::class, 'getFilters'])->name('filters');
        Route::get('/{id}', [SuggestionController::class, 'show'])->name('show');
        Route::post('/{id}/respond', [SuggestionController::class, 'respond'])->name('respond');
        Route::patch('/{id}/status', [SuggestionController::class, 'changeStatus'])->name('change-status');
    });
    
    // Redirigir la raíz del admin al dashboard principal
    Route::get('/', function () {
        return redirect()->route('dashboard');
    })->name('home');
});

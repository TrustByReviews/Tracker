<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\TaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Download routes
Route::middleware('auth')->group(function () {
    Route::get('/download-excel', [DownloadController::class, 'downloadExcel']);
    Route::get('/download-pdf', [DownloadController::class, 'downloadPdf']);
    Route::get('/show-report', [DownloadController::class, 'showReport']);
    
    // Task search API
    Route::get('/tasks/search', [TaskController::class, 'search']);
}); 
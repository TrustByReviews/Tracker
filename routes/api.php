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

// Download routes - usar PaymentController que ya funciona
Route::middleware('web')->group(function () {
    Route::post('/download-excel', [App\Http\Controllers\PaymentController::class, 'downloadExcel']);
    Route::post('/download-pdf', [App\Http\Controllers\PaymentController::class, 'downloadPDF']);
    Route::post('/show-report', [App\Http\Controllers\PaymentController::class, 'generateDetailedReport']);
});

// Task search API
Route::middleware('auth')->group(function () {
    Route::get('/tasks/search', [TaskController::class, 'search']);
}); 
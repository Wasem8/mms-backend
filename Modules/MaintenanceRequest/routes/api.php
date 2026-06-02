<?php

use Illuminate\Support\Facades\Route;
use Modules\MaintenanceRequest\Http\Controllers\MaintenanceRequestController;

Route::middleware(['auth:api', 'role:super_admin'])->prefix('maintenance/admin')->group(function () {
    Route::get('/', [MaintenanceRequestController::class, 'adminIndex']);
    Route::put('/{id}', [MaintenanceRequestController::class, 'process']);
});

Route::middleware(['auth:api', 'role:mosque_manager'])->prefix('maintenance')->group(function () {
Route::get('/', [MaintenanceRequestController::class, 'index']);
Route::post('/', [MaintenanceRequestController::class, 'store']);

// Remember to put static routes BEFORE dynamic parameter routes!
Route::get('/track/{maintenance_number}', [MaintenanceRequestController::class, 'track']);

Route::get('/{id}', [MaintenanceRequestController::class, 'show']);
Route::put('/{id}', [MaintenanceRequestController::class, 'update']);
Route::delete('/{id}', [MaintenanceRequestController::class, 'destroy']);
});


Route::get('mosques/{mosqueId}/maintenance/recent', [MaintenanceRequestController::class, 'recentRequests'])->middleware(['auth:api','role:mosque_manager']);
Route::get('mosques/{mosqueId}/maintenance/stats', [MaintenanceRequestController::class, 'pageStats'])->middleware(['auth:api','role:mosque_manager']);


// =========================================================================
// SUPER ADMIN ROUTES
// =========================================================================

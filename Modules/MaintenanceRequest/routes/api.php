<?php

use Illuminate\Support\Facades\Route;
use Modules\MaintenanceRequest\Http\Controllers\MaintenanceRequestController;


Route::group(['prefix' => 'maintenance/public'], function () {
    Route::get('/', [MaintenanceRequestController::class, 'publicIndex']);
    Route::get('/{id}', [MaintenanceRequestController::class, 'publicShow']);
});


Route::middleware([
    'auth:api',
    'active.user',
    'role:super_admin'
])->prefix('maintenance/admin')->group(function () {

    Route::get('/', [MaintenanceRequestController::class, 'adminIndex']);
    Route::put('/{id}', [MaintenanceRequestController::class, 'process']);
});


Route::middleware([
    'auth:api',
    'active.user',
    'role:super_admin,mosque_manager'
])->prefix('maintenance')->group(function () {

    Route::get('/', [MaintenanceRequestController::class, 'index']);

    Route::post('/', [MaintenanceRequestController::class, 'store']);

    Route::get('/recent', [MaintenanceRequestController::class, 'recentRequests']);

    Route::get('/stats', [MaintenanceRequestController::class, 'pageStats']);

    Route::get('/search', [MaintenanceRequestController::class, 'search']);

    // Remember to put static routes BEFORE dynamic parameter routes!
    Route::get('/track/{maintenance_number}', [MaintenanceRequestController::class, 'track']);

    Route::get('/{id}', [MaintenanceRequestController::class, 'show']);

    Route::put('/{id}', [MaintenanceRequestController::class, 'update']);

    Route::delete('/{id}', [MaintenanceRequestController::class, 'destroy']);
});


// =========================================================================
// SUPER ADMIN ROUTES
// =========================================================================

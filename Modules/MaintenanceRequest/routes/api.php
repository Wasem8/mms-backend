<?php

use Illuminate\Support\Facades\Route;
use Modules\MaintenanceRequest\Http\Controllers\MaintenanceRequestController;


Route::prefix('maintenance')->middleware('auth:api')->group(function () {

    Route::prefix('admin')->middleware('role:super_admin')->group(function () {
        Route::get('/', [MaintenanceRequestController::class, 'adminIndex']);
        Route::put('/{id}', [MaintenanceRequestController::class, 'process']);
    });

    Route::middleware('role:mosque_manager')->group(function () {
        Route::post('/', [MaintenanceRequestController::class, 'store']);
        Route::get('/', [MaintenanceRequestController::class, 'index']);
        Route::get('/{id}', [MaintenanceRequestController::class, 'show']);
        Route::put('/{id}', [MaintenanceRequestController::class, 'update']);
        Route::delete('/{id}', [MaintenanceRequestController::class, 'destroy']);
        Route::get('track/{maintenance_number}', [MaintenanceRequestController::class, 'track']);
    });

});

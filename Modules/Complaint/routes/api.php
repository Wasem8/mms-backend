<?php

use Illuminate\Support\Facades\Route;
use Modules\Complaint\Http\Controllers\AdminMaintenanceRequestController;
use Modules\Complaint\Http\Controllers\ComplaintController;
use Modules\Complaint\Http\Controllers\MaintenanceRequestController;

Route::prefix('complaints')->group(function () {
    Route::post('/guest', [ComplaintController::class, 'storeGuest']);
    Route::get('/track/{complaintNumber}', [ComplaintController::class, 'track']);
});

// Public tracking endpoint for maintenance requests (by reference number)

Route::middleware(['auth:api', 'role:super_admin,mosque_manager'])
    ->prefix('admin/complaints')
    ->group(function () {
        Route::get('/statistics', [ComplaintController::class, 'statistics']);
        Route::get('/', [ComplaintController::class, 'index']);
        Route::get('/{id}', [ComplaintController::class, 'show']);
        Route::patch('/{id}/status', [ComplaintController::class, 'updateStatus']);
    });
Route::middleware(['auth:api',])->prefix('complaints/member')->group(function () {
    Route::post('/', [ComplaintController::class, 'storeMember']);
});


Route::prefix('maintenance')->middleware(['auth:api'])->group(function () {
    Route::middleware(['role:mosque_manager,super_admin'])->group(function () {
        Route::get('/track/{reference}', [MaintenanceRequestController::class, 'track']);
        Route::get('/', [MaintenanceRequestController::class, 'index']);
        Route::post('/', [MaintenanceRequestController::class, 'store']);
    });

    Route::middleware(['role:super_admin'])->group(function () {
        Route::get('/admin', [AdminMaintenanceRequestController::class, 'index']);
        Route::put('/{id}/process', [AdminMaintenanceRequestController::class, 'process']);
    });
});

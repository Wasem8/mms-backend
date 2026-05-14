<?php

use Illuminate\Support\Facades\Route;
use Modules\Complaint\Http\Controllers\ComplaintController;

Route::prefix('complaints')->group(function () {
    Route::post('/guest', [ComplaintController::class, 'storeGuest']);
        Route::get('/track/{complaintNumber}', [ComplaintController::class, 'track']);
});

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


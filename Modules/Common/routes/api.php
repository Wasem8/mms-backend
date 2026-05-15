<?php

use Illuminate\Support\Facades\Route;
use Modules\Common\Http\Controllers\CommonController;
use Modules\Common\Http\Controllers\NotificationController;

Route::prefix('common')->group(function () {
    Route::middleware('auth:api')->prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });
});

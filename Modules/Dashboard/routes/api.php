<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardController;
use Modules\Dashboard\Http\Controllers\SupervisorDashboardController;



Route::prefix('dashboard')->group(function () {
    Route::middleware(['auth:api', 'role:halaqa_supervisor'])->group(function () {
        Route::get('/supervisor/stats', [SupervisorDashboardController::class, 'index']);
    });
});

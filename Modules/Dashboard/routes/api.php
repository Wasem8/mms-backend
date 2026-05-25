<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardController;
use Modules\Dashboard\Http\Controllers\ParentDashboardController;
use Modules\Dashboard\Http\Controllers\SupervisorDashboardController;
use Modules\Dashboard\Http\Controllers\TeacherDashboardController;


Route::prefix('dashboard')->group(function () {
    Route::middleware(['auth:api', 'role:halaqa_supervisor'])->group(function () {
        Route::get('/supervisor/stats', [SupervisorDashboardController::class, 'index']);
        Route::get('/supervisor/export-pdf', [SupervisorDashboardController::class, 'exportPdf']);
    });
    Route::middleware(['auth:api', 'role:teacher'])->group(function () {
    Route::get('/teacher/dashboard', [TeacherDashboardController::class, 'index']);
            Route::get('/teacher/export-pdf', [TeacherDashboardController::class, 'exportPdf']);
    });

    Route::middleware(['auth:api', 'role:parent'])->group(function () {
        Route::get('/parent/dashboard', [ParentDashboardController::class, 'index']);
        Route::get('/parent/export-pdf', [ParentDashboardController::class, 'exportPdf']);
    });
});



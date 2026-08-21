<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardController;
use Modules\Dashboard\Http\Controllers\MosqueManagerDashboardController;
use Modules\Dashboard\Http\Controllers\ParentDashboardController;
use Modules\Dashboard\Http\Controllers\SupervisorDashboardController;
use Modules\Dashboard\Http\Controllers\TeacherDashboardController;

Route::prefix('dashboard')->group(function () {

    Route::middleware(['auth:api', 'active.user', 'role:halaqa_supervisor'])->group(function () {
        Route::get('/supervisor/export-pdf', [SupervisorDashboardController::class, 'exportPdf']);
        Route::get('supervisor/stats', [SupervisorDashboardController::class, 'formatted']);
    });

    Route::middleware(['auth:api', 'active.user', 'role:halaqa_supervisor,mosque_manager'])->group(function () {
        Route::get('/supervisor/export-pdf', [SupervisorDashboardController::class, 'exportPdf']);
        Route::get('/statistics', [
            MosqueManagerDashboardController::class,
            'statistics'
        ]);
    });

    Route::middleware(['auth:api', 'active.user', 'role:teacher'])->group(function () {
        Route::get('/teacher/dashboard', [TeacherDashboardController::class, 'index']);
        Route::get('/teacher/export-pdf', [TeacherDashboardController::class, 'exportPdf']);
        Route::get(
            '/teacher/bootstrap',
            [TeacherDashboardController::class, 'bootstrap']
        );
    });

    Route::middleware(['auth:api', 'active.user', 'role:parent'])->group(function () {
        Route::get('/parent/dashboard', [ParentDashboardController::class, 'index']);
        Route::get('/parent/export-pdf', [ParentDashboardController::class, 'exportPdf']);
    });
});

Route::prefix('dashboard/mosque-manager')
    ->middleware(['auth:api', 'active.user'])
    ->group(function () {

        // 1. Endpoint شامل يعيد كافة بيانات اللوحة في Request واحد
        // (الأسرع للـ Frontend)
        Route::get('/', [MosqueManagerDashboardController::class, 'index']);

    });

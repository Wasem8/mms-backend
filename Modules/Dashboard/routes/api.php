<?php

use App\Http\Controllers\AdminActivityController;
use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\AdminDashboardController;
use Modules\Dashboard\Http\Controllers\DashboardController;
use Modules\Dashboard\Http\Controllers\MosqueManagerDashboardController;
use Modules\Dashboard\Http\Controllers\ParentDashboardController;
use Modules\Dashboard\Http\Controllers\ReportController;
use Modules\Dashboard\Http\Controllers\SupervisorDashboardController;
use Modules\Dashboard\Http\Controllers\TeacherDashboardController;

Route::prefix('dashboard')->group(function () {

    Route::middleware(['auth:api', 'active.user', 'role:halaqa_supervisor'])->group(function () {
        Route::get('/supervisor/export-pdf', [SupervisorDashboardController::class, 'exportPdf']);
        Route::get('supervisor/stats', [SupervisorDashboardController::class, 'formatted']);
    });

    Route::middleware(['auth:api', 'active.user', 'role:halaqa_supervisor,mosque_manager'])->group(function () {
        Route::get('/supervisor/export-pdf', [SupervisorDashboardController::class, 'exportPdf']);

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
        Route::get('/statistics', [
            MosqueManagerDashboardController::class,
            'statistics'
        ]);
        Route::get('/export-pdf', [MosqueManagerDashboardController::class, 'exportPdf']);

        // تقارير مدير المسجد (مُقيّدة بمسجده)
        Route::get('/reports/donations', [ReportController::class, 'donations']);
        Route::get('/reports/maintenance', [ReportController::class, 'maintenance']);
        Route::get('/reports/complaints', [ReportController::class, 'complaints']);

        // تنزيل التقارير PDF
        Route::get('/reports/donations/download', [ReportController::class, 'downloadDonations']);
        Route::get('/reports/maintenance/download', [ReportController::class, 'downloadMaintenance']);
        Route::get('/reports/complaints/download', [ReportController::class, 'downloadComplaints']);

        // سجل عمليات المساجد (مُقيّد بمسجد المدير)
        Route::get('/mosque-operations', [AdminDashboardController::class, 'mosqueOperations']);

    });

Route::middleware(['auth:api', 'active.user', 'role:super_admin'])->group(function () {
    Route::get('admin/activity-log', [AdminActivityController::class, 'index']);
    Route::get('admin/dashboard', [AdminDashboardController::class, 'index']);
    Route::get('admin/super-dashboard', [AdminDashboardController::class, 'superAdminDashboard']);
    Route::get('admin/export-pdf', [AdminDashboardController::class, 'exportPdf']);

    // سجل عمليات المساجد (كل المساجد لمدير المنطقة)
    Route::get('admin/mosque-operations', [AdminDashboardController::class, 'mosqueOperations']);

    // تقارير مدير المنطقة (يمكن تصفيتها لأي مسجد + من/إلى تاريخ)
    Route::get('admin/reports/donations', [ReportController::class, 'donations']);
    Route::get('admin/reports/maintenance', [ReportController::class, 'maintenance']);
    Route::get('admin/reports/complaints', [ReportController::class, 'complaints']);

    // تنزيل التقارير PDF
    Route::get('admin/reports/donations/download', [ReportController::class, 'downloadDonations']);
    Route::get('admin/reports/maintenance/download', [ReportController::class, 'downloadMaintenance']);
    Route::get('admin/reports/complaints/download', [ReportController::class, 'downloadComplaints']);
});

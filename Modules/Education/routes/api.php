<?php

use Illuminate\Support\Facades\Route;
use Modules\Education\Http\Controllers\HalaqaController;
use Modules\Education\Http\Controllers\StudentController;
use Modules\Education\Http\Controllers\AttendanceController;

Route::prefix('education')->group(function () {

    Route::middleware(['auth:api', 'role:halaqa_supervisor'])->group(function () {
        Route::apiResource('halaqat', HalaqaController::class);
        Route::get('halaqat/{id}', [HalaqaController::class, 'show']);

    });


    Route::middleware(['auth:api', 'role:parent,halaqa_supervisor'])->group(function () {
        Route::apiResource('students', StudentController::class);

    });

    Route::post('halaqat/{id}/students', [HalaqaController::class, 'attachStudents']);
    Route::delete('halaqat/{id}/students/{studentId}', [HalaqaController::class, 'detachStudent']);



    Route::post('attendance', [AttendanceController::class, 'store']);
    Route::get('attendance', [AttendanceController::class, 'index']);
});

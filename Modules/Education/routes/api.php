<?php

use Illuminate\Support\Facades\Route;
use Modules\Education\Http\Controllers\AttendanceExcuseController;
use Modules\Education\Http\Controllers\EvaluationController;
use Modules\Education\Http\Controllers\HalaqaController;
use Modules\Education\Http\Controllers\StudentController;
use Modules\Education\Http\Controllers\AttendanceController;

Route::prefix('education')->group(function () {

    Route::middleware(['auth:api', 'role:halaqa_supervisor'])->group(function () {
        Route::apiResource('halaqat', HalaqaController::class);
        Route::get('halaqat/{id}', [HalaqaController::class, 'show']);
        Route::post('halaqat/{id}/students', [HalaqaController::class, 'attachStudents']);
        Route::delete('halaqat/{id}/students/{studentId}', [HalaqaController::class, 'detachStudent']);
        Route::patch('students/{id}/approve', [StudentController::class, 'approve']);
        Route::patch('students/{id}/reject', [StudentController::class, 'reject']);


    });


    Route::middleware(['auth:api', 'role:parent'])->group(function () {
        Route::apiResource('students', StudentController::class);

    });




    Route::middleware(['auth:api', 'role:parent,halaqa_supervisor'])->group(function () {
        Route::apiResource('students', StudentController::class)->except(['store']);
    });


    Route::middleware(['auth:api', 'role:teacher'])->group(function () {
        Route::post('attendance', [AttendanceController::class, 'storeBulk']);
    });


    Route::middleware(['auth:api', 'role:parent,halaqa_supervisor,teacher'])->group(function () {
        Route::get('attendance', [AttendanceController::class, 'index']);
    });

    Route::middleware(['auth:api', 'role:parent'])->group(function () {
        Route::post('attendance/excuses', [AttendanceExcuseController::class, 'store']);
        Route::get('my-excuses', [AttendanceExcuseController::class, 'myExcuses']);
    });

    Route::middleware(['auth:api', 'role:teacher'])->group(function () {
        Route::get('teacher/excuses', [AttendanceExcuseController::class, 'indexForTeacher']);
        Route::put('teacher/excuses/{id}/process', [AttendanceExcuseController::class, 'process']);
        Route::post('evaluations', [EvaluationController::class, 'store']);

    });
    Route::middleware(['auth:api', 'role:halaqa_supervisor,parent'])->group(function () {

        Route::get('evaluations', [EvaluationController::class, 'index']);
    });

});

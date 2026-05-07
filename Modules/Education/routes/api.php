<?php

use Illuminate\Support\Facades\Route;
use Modules\Education\Http\Controllers\AttendanceExcuseController;
use Modules\Education\Http\Controllers\EvaluationController;
use Modules\Education\Http\Controllers\HalaqaController;
use Modules\Education\Http\Controllers\StudentController;
use Modules\Education\Http\Controllers\AttendanceController;
use Modules\Education\Http\Controllers\TeacherController;

Route::prefix('education')->group(function () {

    Route::middleware(['auth:api', 'role:halaqa_supervisor'])->group(function () {
        Route::get('halaqat/{id}', [HalaqaController::class, 'show']);
        Route::post('halaqat/{id}/students', [HalaqaController::class, 'attachStudents']);
        Route::delete('halaqat/{id}/students/{studentId}', [HalaqaController::class, 'detachStudent']);
        Route::patch('students/{id}/approve', [StudentController::class, 'approve']);
        Route::patch('students/{id}/reject', [StudentController::class, 'reject']);

    });

    Route::middleware('role:parent,halaqa_supervisor,teacher')->group(function () {
        Route::get('students', [StudentController::class, 'index']);
        Route::get('students/{id}', [StudentController::class, 'show']);
        Route::put('students/{id}', [StudentController::class, 'update']);
        Route::delete('students/{id}', [StudentController::class, 'destroy']);
    });

    Route::middleware(['auth:api', 'role:parent'])->group(function () {
        Route::post('students', [StudentController::class, 'store']);

    });

    Route::prefix('supervisor')->middleware(['auth:api', 'role:halaqa_supervisor'])->group(function () {
        Route::get('teachers', [TeacherController::class, 'index']);
        Route::get('teachers/{id}', [TeacherController::class, 'show']);
    });



    Route::middleware(['auth:api', 'role:teacher'])->group(function () {
        Route::post('attendance', [AttendanceController::class, 'storeBulk']);
    });


    Route::middleware(['auth:api', 'role:parent,halaqa_supervisor,teacher'])->group(function () {
        Route::get('attendance', [AttendanceController::class, 'index']);
        Route::get('evaluations/{id}', [EvaluationController::class, 'show']);
    });

    Route::middleware(['auth:api', 'role:parent'])->group(function () {
        Route::post('attendance/excuses', [AttendanceExcuseController::class, 'store']);
        Route::get('my-excuses', [AttendanceExcuseController::class, 'myExcuses']);
    });

    Route::middleware(['auth:api', 'role:teacher'])->group(function () {
        Route::get('teacher/excuses', [AttendanceExcuseController::class, 'indexForTeacher']);
        Route::put('teacher/excuses/{id}/process', [AttendanceExcuseController::class, 'process']);


    });

    Route::prefix('supervisor')->middleware('role:halaqa_supervisor')->group(function () {
        Route::get('evaluations', [EvaluationController::class, 'indexForSupervisor']);
    });

    // --- بوابة المعلم (Teacher Portal) ---
    Route::prefix('teacher')->middleware('role:teacher')->group(function () {
        Route::get('evaluations', [EvaluationController::class, 'indexForTeacher']);
        Route::post('evaluations', [EvaluationController::class, 'storeBulk']); // الإدخال الجماعي
    });

    // --- بوابة ولي الأمر (Parent Portal) ---
    Route::prefix('parent')->middleware('role:parent')->group(function () {
        Route::get('evaluations', [EvaluationController::class, 'indexForParent']);
    });
});

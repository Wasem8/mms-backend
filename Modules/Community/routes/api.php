<?php

use Illuminate\Support\Facades\Route;
use Modules\Community\Http\Controllers\DawahProgramController;
use Modules\Community\Http\Controllers\ProgramScheduleController;
use Modules\Community\Http\Controllers\SermonController;
use Modules\Community\Http\Controllers\TameemController;

Route::prefix('program')->group(function () {

    // Public
    Route::get('/mosques/{mosque}/dawah_programs', [DawahProgramController::class, 'getProgramsByMosque']);

    Route::get('/mosques/{mosque}/dawah_programs/{program}', [DawahProgramController::class, 'show']);

    Route::get('/dawah_programs', [DawahProgramController::class, 'index']);

    Route::get('/dawah_programs/{program}/schedules', [ProgramScheduleController::class, 'index']);
    Route::get('/dawah_programs/{program}/schedules/{schedule}', [ProgramScheduleController::class, 'show']);


    // Protected
    Route::middleware(['auth:api', 'role:mosque_manager'])->group(function () {

        Route::post('/mosques/{mosque}/dawah_programs', [DawahProgramController::class, 'store']);
        Route::Put('/mosques/{mosque}/dawah_programs/{program}', [DawahProgramController::class, 'update']);
        Route::delete('/mosques/{mosque}/dawah_programs/{program}', [DawahProgramController::class, 'destroy']);

        Route::post('/mosques/{mosque}/dawah_programs/{program}/schedules', [ProgramScheduleController::class, 'store']);
        Route::put('/mosques/{mosque}/dawah_programs/{program}/schedules/{schedule}', [ProgramScheduleController::class, 'update']);
        Route::delete('/mosques/{mosque}/dawah_programs/{program}/schedules/{schedule}', [ProgramScheduleController::class, 'destroy']);
    });
});

Route::prefix('sermons')->middleware('auth:api')->group(function () {

    Route::get('/', [SermonController::class, 'index']);
    Route::get('/pending', [SermonController::class, 'pending']);

    Route::post('/', [SermonController::class, 'store']);
    Route::put('/{id}/approve', [SermonController::class, 'approve']);
    // Route::put('/{id}/reject', [SermonController::class, 'reject']);
});

Route::prefix('tameems')->middleware('auth:api')->group(function () {

Route::middleware('role:super_admin')->group(function () {  
    Route::post('/', [TameemController::class, 'store']);
    Route::put('/{id}', [TameemController::class, 'update']);
    Route::get('/', [TameemController::class, 'index']);
    Route::delete('/{id}', [TameemController::class, 'destroy']);
    Route::get('/{id}', [TameemController::class, 'show']);
});

    Route::get('/my-tameems', [TameemController::class, 'myTameems'])->middleware('role:mosque_manager');
    Route::patch('/{id}/read', [TameemController::class, 'markAsRead'])->middleware('role:mosque_manager');
});



<?php

use Illuminate\Support\Facades\Route;
use Modules\Community\Http\Controllers\DawahProgramController;
use Modules\Community\Http\Controllers\ProgramScheduleController;

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

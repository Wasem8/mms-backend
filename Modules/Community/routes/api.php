<?php

use Illuminate\Support\Facades\Route;
use Modules\Community\Http\Controllers\SermonSelectionController;
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


    // Protected — mosque resolved from the authenticated mosque_manager
    Route::middleware(['auth:api', 'active.user', 'role:mosque_manager'])->group(function () {

        Route::post('/mosques/{mosque}/dawah_programs', [DawahProgramController::class, 'store']);
        Route::Put('/mosques/{mosque}/dawah_programs/{program}', [DawahProgramController::class, 'update']);
        Route::delete('/mosques/{mosque}/dawah_programs/{program}', [DawahProgramController::class, 'destroy']);

        Route::post('/mosques/{mosque}/dawah_programs/{program}/schedules', [ProgramScheduleController::class, 'store']);
        Route::put('/mosques/{mosque}/dawah_programs/{program}/schedules/{schedule}', [ProgramScheduleController::class, 'update']);
        Route::delete('/mosques/{mosque}/dawah_programs/{program}/schedules/{schedule}', [ProgramScheduleController::class, 'destroy']);
    });
});

Route::middleware(['auth:api', 'active.user', 'role:mosque_manager'])->group(function () {
    Route::post('sermon-selections', [SermonSelectionController::class, 'store']);
    Route::get('sermon-selections/mine', [SermonSelectionController::class, 'mine']);
    Route::delete('sermon-selections/{id}', [SermonSelectionController::class, 'destroy']);
});

Route::middleware(['auth:api', 'active.user', 'role:super_admin'])->group(function () {
    Route::get('sermon-selections/upcoming', [SermonSelectionController::class, 'upcoming']);
    Route::get('sermon-selections', [SermonSelectionController::class, 'index']);
});

Route::prefix('sermons')
    ->middleware(['auth:api', 'active.user'])
    ->group(function () {

    Route::delete('/{id}', [SermonController::class, 'destroy'])->middleware('role:mosque_manager');

        Route::get('/search', [SermonController::class, 'search']);

        Route::post('/', [SermonController::class, 'store'])
            ->middleware('role:mosque_manager');

        Route::get('/most-selected', [SermonController::class, 'mostSelected']);

        Route::get('/{id}', [SermonController::class, 'show'])
            ->whereNumber('id');

        Route::get('/pending', [SermonController::class, 'pending']);

        Route::get('/archived', [SermonController::class, 'archived'])
            ->middleware('role:mosque_manager');

        Route::get('/', [SermonController::class, 'index']);

        Route::put('/{id}/approve', [SermonController::class, 'approve'])
            ->middleware('role:super_admin');

        Route::put('/{id}/reject', [SermonController::class, 'reject'])
            ->middleware('role:super_admin');
    });

Route::prefix('tameems')
    ->middleware(['auth:api', 'active.user'])
    ->group(function () {

        Route::get('/my-tameems', [TameemController::class, 'myTameems'])
            ->middleware('role:mosque_manager');

        Route::patch('/{id}/read', [TameemController::class, 'markAsRead'])
            ->middleware('role:mosque_manager');

        Route::middleware('role:super_admin')->group(function () {
            Route::post('/', [TameemController::class, 'store']);
            Route::put('/{id}', [TameemController::class, 'update'])->whereNumber('id');
            Route::get('/', [TameemController::class, 'index']);
            Route::delete('/{id}', [TameemController::class, 'destroy'])->whereNumber('id');
            Route::get('/{id}', [TameemController::class, 'show'])->whereNumber('id');
        });

        // Mosque manager sends a tameem to supervisors/teachers of their own mosque.
        Route::post('/for-mosque', [TameemController::class, 'storeForMosque'])
            ->middleware('role:mosque_manager');

        // Tameems sent by the authenticated mosque manager.
        Route::get('/sent', [TameemController::class, 'sentTameems'])
            ->middleware('role:mosque_manager');
    });

<?php

use Illuminate\Support\Facades\Route;
use Modules\Community\Http\Controllers\CommunityController;
use Modules\Community\Http\Controllers\DawahProgramController;




Route::prefix('Program')->group(function () {

    Route::get('/mosques/{mosque}/dawah_programs', [DawahProgramController::class, 'index']);
    Route::get('/mosques/{mosque}/dawah_programs/{program}', [DawahProgramController::class, 'show']);

    Route::middleware(['auth:api', 'role:mosque_manager'])->group(function () {
        Route::post('/mosques/{mosque}/dawah_programs', [DawahProgramController::class, 'store']); // ✅
        Route::put('/mosques/{mosque}/dawah_programs/{program}', [DawahProgramController::class, 'update']);
        Route::delete('/mosques/{mosque}/dawah_programs/{program}', [DawahProgramController::class, 'destroy']);
    });
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Mosque\Http\Controllers\MosqueController;
use Modules\Mosque\Http\Controllers\FacilitiesController;

Route::prefix('facilities')->group(function () {
    Route::get('/', [FacilitiesController::class, 'index']);

    Route::middleware(['auth:sanctum','role:super_admin'])->group(function () {
        Route::post('/',             [FacilitiesController::class, 'store']);
        Route::put('/{facility}',    [FacilitiesController::class, 'update']);
        Route::delete('/{facility}', [FacilitiesController::class, 'destroy']);
    });
});

// ── العمليات الخاصة بالمساجد (Mosque Routes) ─────────────────────────────
Route::prefix('mosques')->group(function () {

    // ── Public Mosque ─────────────────────────────
    Route::get('/',              [MosqueController::class, 'index']);
    Route::get('/search',        [MosqueController::class, 'search']);
    Route::get('/featured',      [MosqueController::class, 'featured']);
    Route::get('/city/{city}',   [MosqueController::class, 'byCity']);
    Route::get('/{mosque}',      [MosqueController::class, 'show']);

    Route::get('/{mosque}/facilities', [FacilitiesController::class, 'byMosque']);

    Route::middleware('auth:sanctum')->group(function () {
        // ── Mosque Management ──
        Route::middleware('role:super_admin')->group(function () {
            Route::post('/',                       [MosqueController::class, 'store']);
            Route::put('/{mosque}',                [MosqueController::class, 'update']);
            Route::delete('/{mosque}',             [MosqueController::class, 'destroy']);
            Route::patch('/{mosque}/status',       [MosqueController::class, 'updateStatus']);
            Route::patch('/{mosque}/featured',     [MosqueController::class, 'toggleFeatured']);
            Route::patch('/{mosque}/rating',       [MosqueController::class, 'updateRating']);
        });


Route::middleware('role:mosque_manager')->group(function () {
            Route::post('/{mosque}/facilities/attach', [FacilitiesController::class, 'attach']);
            Route::post('/{mosque}/facilities/detach', [FacilitiesController::class, 'detach']);
            Route::post('/{mosque}/facilities/sync',   [FacilitiesController::class, 'sync']);
        });

    });
});

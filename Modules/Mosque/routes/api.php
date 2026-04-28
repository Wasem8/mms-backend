<?php

use Illuminate\Support\Facades\Route;
use Modules\Mosque\Http\Controllers\MosqueController;
use Modules\Mosque\Http\Controllers\FacilitiesController;

Route::prefix('mosques')->group(function () {


    Route::get('/', [MosqueController::class, 'index']);
    Route::get('/featured', [MosqueController::class, 'featured']);
    Route::get('/city/{city}', [MosqueController::class, 'byCity']);
    Route::get('/{id}', [MosqueController::class, 'show']);


    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/', [MosqueController::class, 'store']);
        Route::put('/{mosque}', [MosqueController::class, 'update']);
        Route::delete('/{mosque}', [MosqueController::class, 'destroy']);

        Route::patch('/{mosque}/status', [MosqueController::class, 'updateStatus']);
        Route::patch('/{mosque}/featured', [MosqueController::class, 'toggleFeatured']);
        Route::patch('/{mosque}/rating', [MosqueController::class, 'updateRating']);


        Route::prefix('{mosque}/facilities')->group(function () {
            Route::get('/', [FacilitiesController::class, 'byMosque']);

            Route::post('/sync', [FacilitiesController::class, 'sync']);
            Route::post('/attach', [FacilitiesController::class, 'attach']);
            Route::post('/detach', [FacilitiesController::class, 'detach']);
        });
    });
});

Route::prefix('mosques/{mosque}/facilities')->group(function() {
    Route::get('/',[FacilitiesController::class,'index']);
});

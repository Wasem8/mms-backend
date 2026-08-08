<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\AuthController;
use Modules\User\Http\Controllers\ProfileController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:3,1');
    Route::post('/register-parent', [AuthController::class, 'registerParent'])->middleware('throttle:3,1');
    Route::post('/register-volunteer', [AuthController::class, 'registerVolunteer'])->middleware('throttle:3,1');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,1');
    Route::post('resend-otp', [AuthController::class, 'resendOtp'])
        ->middleware('throttle:1,1');

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('/update-fcm-token', [AuthController::class, 'updateFcmToken']);
        Route::delete('/fcm-token', [AuthController::class, 'deleteFcmToken']);
    });
});

Route::middleware(['auth:api'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
});


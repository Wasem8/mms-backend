<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\AuthController;
use Modules\User\Http\Controllers\ProfileController;
use Modules\User\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    // Guest Routes (Public)
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:3,1');
    Route::post('/register-parent', [AuthController::class, 'registerParent'])->middleware('throttle:3,1');
    Route::post('/register-volunteer', [AuthController::class, 'registerVolunteer'])->middleware('throttle:3,1');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,1');
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:1,1');

    // Authenticated Auth Routes
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/update-fcm-token', [AuthController::class, 'updateFcmToken']);
        Route::delete('/fcm-token', [AuthController::class, 'deleteFcmToken']);
    });
});

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/
Route::prefix('profile')->middleware(['auth:api'])->group(function () {
    Route::get('/', [ProfileController::class, 'show']);
    Route::put('/', [ProfileController::class, 'update'])->middleware('throttle:10,1');

    // الراوت الجديد الخاص بتأكيد البريد عبر الـ OTP
    Route::post('/confirm-email', [ProfileController::class, 'confirmEmailChange'])->middleware('throttle:5,1');
});
/*
|--------------------------------------------------------------------------
| User Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('users')->middleware(['auth:api'])->group(function () {
    Route::patch('/{user}/status', [UserController::class, 'changeStatus']);
});

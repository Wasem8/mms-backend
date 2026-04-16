<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;

// ─── Public routes (لا تحتاج توكن) ───
Route::prefix('auth')->name('auth.')->group(function () {

    // تسجيل الدخول بكلمة المرور
    Route::post('login/password', [AuthController::class, 'loginWithPassword'])
        ->name('login.password');

    // إرسال OTP
    Route::post('otp/send', [AuthController::class, 'sendOtp'])
        ->name('otp.send');

    // التحقق من OTP والدخول
    Route::post('otp/verify', [AuthController::class, 'verifyOtp'])
        ->name('otp.verify');
});

// ─── Protected routes (تحتاج توكن Sanctum) ───
Route::prefix('auth')->name('auth.')->middleware('auth:sanctum')->group(function () {

    Route::post('logout',     [AuthController::class, 'logout'])    ->name('logout');
    Route::post('logout-all', [AuthController::class, 'logoutAll']) ->name('logout.all');
    Route::get('me',          [AuthController::class, 'me'])        ->name('me');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Invitation\Http\Controllers\InvitationController;


Route::prefix('invitations')->group(function () {

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/send', [InvitationController::class, 'send']);
    });

    Route::post('/accept', [InvitationController::class, 'accept']);
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Invitation\Http\Controllers\InvitationController;

Route::prefix('invitations')->group(function () {

    Route::middleware(['auth:api', 'active.user'])->group(function () {
        Route::post('/send', [InvitationController::class, 'send']);
    });

    Route::middleware('web')->group(function () {
        Route::get('/accept', [InvitationController::class, 'showAcceptForm'])
            ->name('invitations.accept_form');

        Route::post('/accept', [InvitationController::class, 'accept'])
            ->name('invitations.accept_process');
    });
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Invitation\Http\Controllers\InvitationController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('invitations', InvitationController::class)->names('invitation');
});

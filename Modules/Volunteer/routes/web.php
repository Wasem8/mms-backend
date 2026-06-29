<?php

use Illuminate\Support\Facades\Route;
use Modules\Volunteer\Http\Controllers\VolunteerController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('volunteers', VolunteerController::class)->names('volunteer');
});

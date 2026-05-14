<?php

use Illuminate\Support\Facades\Route;
use Modules\Donation\Http\Controllers\DonationController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('donations', DonationController::class)->names('donation');
});

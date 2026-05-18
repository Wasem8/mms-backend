<?php

use Illuminate\Support\Facades\Route;
use Modules\Complaint\Http\Controllers\ComplaintController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('complaints', ComplaintController::class)->names('complaint');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Education\Http\Controllers\EducationController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('education', EducationController::class)->names('education');
});

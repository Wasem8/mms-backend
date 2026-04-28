<?php

use Illuminate\Support\Facades\Route;
use Modules\Mosque\Http\Controllers\MosqueController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('mosques', MosqueController::class)->names('mosque');
});

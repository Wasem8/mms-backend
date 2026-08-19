<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminActivityController;

/*
 |--------------------------------------------------------------------------
 | Dashboard / Admin routes (super_admin)
 |--------------------------------------------------------------------------
 */

Route::middleware(['auth:api', 'role:super_admin'])->group(function () {
    Route::get('admin/activity-log', [AdminActivityController::class, 'index']);
});

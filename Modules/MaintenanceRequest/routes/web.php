<?php

use Illuminate\Support\Facades\Route;
use Modules\MaintenanceRequest\Http\Controllers\MaintenanceRequestController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('maintenancerequests', MaintenanceRequestController::class)->names('maintenancerequest');
});

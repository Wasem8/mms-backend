<?php

use Illuminate\Support\Facades\Route;
use Modules\MaintenanceRequest\Http\Controllers\MaintenanceRequestController;




Route::prefix('maintenance')->middleware('auth:api')->group(function() {
    Route::post('/',[MaintenanceRequestController::class,'store']);

});
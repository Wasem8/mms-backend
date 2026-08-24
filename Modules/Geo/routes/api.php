<?php

use Illuminate\Support\Facades\Route;
use Modules\Geo\Http\Controllers\GeoController;



Route::get('/geo',[GeoController::class,'index']);

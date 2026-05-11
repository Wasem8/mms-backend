<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

foreach (glob(__DIR__ . '/mms/*.php') as $routeFile) {
    require $routeFile;
}

Route::get('/force-clear', function() {
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('optimize');
    return "Routes cleared";
});

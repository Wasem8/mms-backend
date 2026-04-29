<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

foreach (glob(base_path('Modules/*/routes/api.php')) as $routeFile) {
    require $routeFile;
}

Route::get('/force-clear', function() {
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('optimize');
    return "Routes cleared";
});

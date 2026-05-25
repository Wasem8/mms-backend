<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/was', function () {
    return "waseem";
});

Route::get('/dashboard-test', function () {
    return view('dashboard-test');
});

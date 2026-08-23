<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sermons:purge-expired')->dailyAt('00:01');




Schedule::command(
    'backup:database --triggered-by=scheduled'
)
    ->dailyAt('02:00')
    ->timezone('Asia/Damascus')
    ->withoutOverlapping()
    ->onOneServer();

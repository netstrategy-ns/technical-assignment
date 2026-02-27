<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

app()->booted(function () {
    app(Schedule::class)
        ->command('holds:expire')
        ->everyMinute();

    app(Schedule::class)
        ->command('queue:process-events')
        ->everyMinute();

    app(Schedule::class)
        ->command('queue:expire-entries')
        ->everyMinute();
});

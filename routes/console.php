<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Expire holds that have passed their 10-minute window
Schedule::command('holds:expire')->everyMinute();

// Process event queues and activate next waiting users
Schedule::command('queues:process')->everyMinute();

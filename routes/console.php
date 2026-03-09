<?php

use App\Jobs\ExpireHoldsJob;
use App\Jobs\ProcessQueueJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new ExpireHoldsJob())->everyMinute();
Schedule::job(new ProcessQueueJob())->everyTwoMinutes();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

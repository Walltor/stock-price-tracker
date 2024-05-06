<?php

use App\Jobs\FetchApiData;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

$fetchApiData = app(FetchApiData::class);
Schedule::job($fetchApiData)->everyMinute();
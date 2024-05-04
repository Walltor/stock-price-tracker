<?php

use App\Http\Controllers\StockPriceController;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    $stockPriceController = app(StockPriceController::class);
    $stockPrices = $stockPriceController->fetchStockPrices();
    dump($stockPrices);
})->everyMinute();
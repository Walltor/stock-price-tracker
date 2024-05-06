<?php

use App\Http\Controllers\StockPriceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/fetch-stock-prices', [StockPriceController::class, 'fetchStockPrices']);
Route::post('/store-stock-price', [StockPriceController::class, 'storeStockPriceBySymbol']);
Route::get('/get-stock-prices', [StockPriceController::class, 'getStockPricesBySymbol']);
Route::get('/get-cached-all', [StockPriceController::class, 'getCachedAll']);
Route::get('/get-cached-single', [StockPriceController::class, 'getCachedSingle']);
Route::get('/get-cached-multiple', [StockPriceController::class, 'getCachedMultiple']);
Route::get('/calculate', [StockPriceController::class, 'calculate']);

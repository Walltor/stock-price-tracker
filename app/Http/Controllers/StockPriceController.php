<?php

namespace App\Http\Controllers;

use App\Services\CacheService;
use App\Services\StockPriceService;
use Illuminate\Http\Request as HttpRequest;

class StockPriceController extends Controller
{
    protected $stockPriceService;
    protected $cacheService;

    public function __construct(
        StockPriceService $stockPriceService,
        CacheService $cacheService
    ) {
        $this->stockPriceService = $stockPriceService;
        $this->cacheService = $cacheService;
    }

    public function fetchStockPrices()
    {
        return $this->stockPriceService->fetchStockPrices();
    }

    public function storeStockPriceBySymbol(HttpRequest $request)
    {
        return $this->stockPriceService->storeStockPriceBySymbol($request);
    }

    public function getStockPricesBySymbol(HttpRequest $request)
    {
        return $this->stockPriceService->getStockPricesBySymbol($request);
    }

    public function getCachedAll()
    {
        return $this->cacheService->getCachedAll();
    }

    public function getCachedSingle(HttpRequest $request)
    {
        return $this->cacheService->getCachedSingle($request);
    }

    public function getCachedMultiple(HttpRequest $request)
    {
        return $this->cacheService->getCachedMultiple($request);
    }

    public function calculate(HttpRequest $request)
    {
        return $this->stockPriceService->calculate($request);
    }
}

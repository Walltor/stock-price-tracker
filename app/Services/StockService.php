<?php

namespace App\Services;

use App\Models\Stock;

class StockService
{
    /**
     * Create a new class instance.
     */
    protected $fetchService;

    public function __construct(FetchService $fetchService)
    {
        $this->fetchService = $fetchService;
    }

    public function getStockBySymbol($symbol)
    {
        return Stock::where('symbol', $symbol)->first();
    }

    public function storeStock($symbol)
    {
        return Stock::firstOrCreate([
            'symbol' => $symbol
        ]);
    }
}

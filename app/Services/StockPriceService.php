<?php

namespace App\Services;

use App\Models\StockPrice;
use Exception;
use Illuminate\Http\Request as HttpRequest;

use function Laravel\Prompts\error;

class StockPriceService
{
    /**
     * Create a new class instance.
     */

    protected $fetchService;
    protected $stockService;
    protected $cacheService;
    public function __construct(
        FetchService $fetchService,
        StockService $stockService,
        CacheService $cacheService
    ) {
        $this->fetchService = $fetchService;
        $this->stockService = $stockService;
        $this->cacheService = $cacheService;
    }

    public function fetchStockPrices()
    {
        $symbols = ['AAPL', 'GOOGL', 'AMZN', 'TSLA', 'CSCO'];
        $stockPrices = [];

        foreach ($symbols as $symbol) {
            $stockPrice = $this->fetchService->fetchStockPrices($symbol);

            if ($stockPrice) {
                $stockPrices[] = $stockPrice;
            }
        }
        return $stockPrices;
    }

    public function storeStockPriceBySymbol(HttpRequest $request)
    {
        $validatedData = $request->validate([
            'symbol' => 'required|string|max:10',
        ]);

        $symbol = $validatedData['symbol'];

        try {
            $stockPriceData = $this->fetchService->fetchStockPrices($symbol);

            if ($stockPriceData) {
                $this->storeStockPrice($stockPriceData);
                return response()->json(['message' => 'Stock data for ' . $symbol . ' stored successfully!'], 200);
            } else {
                return response()->json(['error' => 'No data available for ' . $symbol . '.'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storeStockPrice($stockPriceData)
    {
        $symbol = $stockPriceData['symbol'];
        $stock = $this->stockService->storeStock($symbol);

        StockPrice::create([
            'stock_id' => $stock->id,
            'open' => $stockPriceData['open'],
            'high' => $stockPriceData['high'],
            'low' => $stockPriceData['low'],
            'price' => $stockPriceData['price'],
            'volume' => $stockPriceData['volume'],
            'latest_trading_day' => $stockPriceData['latest trading day'],
            'previous_close' => $stockPriceData['previous close'],
            'change' => $stockPriceData['change'],
            'change_percent' => $stockPriceData['change percent']
        ]);
    }

    public function getStockPricesBySymbol(HttpRequest $request)
    {
        $validatedData = $request->validate([
            'symbol' => 'required|string|max:10'
        ]);

        $symbol = $validatedData['symbol'];

        try {
            $stock = $this->stockService->getStockBySymbol($symbol);

            if ($stock) {
                $stockPrices = StockPrice::where('stock_id', $stock->id)->get();

                if ($stockPrices) {
                    return $stockPrices;
                } else {
                    return response()->json(error('No data found for ' . $symbol . '.'));
                }
            } else {
                return response()->json(error('No stock named ' . $symbol . ' found.'));
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function calculate(HttpRequest $request)
    {
        $validatedData = $request->validate([
            'symbols' => 'required|array',
            'end_date_time' => 'required|date',
            'start_date_time' => 'required|date'
        ]);

        $symbols = $validatedData['symbols'];
        $endDate = $validatedData['end_date_time'];
        $startDate = $validatedData['start_date_time'];
        $results = [];

        foreach ($symbols as $symbol) {
            $stock = $this->stockService->getStockBySymbol($symbol);

            if ($stock) {
                $stockPrices = StockPrice::where('stock_id', $stock->id)->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)->get();
                if ($stockPrices->isEmpty()) {
                    $results[] = ['Message' => 'No price changes for ' .  $symbol . ' recorded between ' . $startDate . ' and ' . $endDate . '.'];
                    continue;
                }
            } else {
                $results[] = ['Message' => 'Stock data for ' . $symbol . ' not found.'];
                continue;
            }

            $earliest = $stockPrices->min('created_at');
            $latest = $stockPrices->max('created_at');
            $priceStart = $stockPrices->where('created_at', $earliest)->first()->price;
            $priceEnd = $stockPrices->where('created_at', $latest)->first()->price;
            $result = round(($priceEnd - $priceStart) / $priceStart, 4);
            $results[] = ['Message' => 'Price change for ' . $symbol . ' between ' . $startDate . ' and ' . $endDate . ':', 'Earliest price: ' => $priceStart, 'Latest price' => $priceEnd, 'Result' => $result];
        }

        return $results;
    }
}

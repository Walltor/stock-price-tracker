<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockPrice;
use App\Services\StockPriceService;
use Exception;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\error;

class StockPriceController extends Controller
{
    protected $service;

    public function __construct(StockPriceService $stockPriceService)
    {
        $this->service = $stockPriceService;
    }

    public function fetchStockPrices()
    {
        $symbols = ['AAPL', 'GOOGL', 'AMZN', 'TSLA', 'CSCO'];
        $stockPrices = [];

        foreach ($symbols as $symbol) {
            $stockPrice = $this->service->fetchStockPrices($symbol);

            if ($stockPrice) {
                $stockPrices[] = $stockPrice;
            }
        }
        return $stockPrices;
    }

    public function storeStockPrice(HttpRequest $request)
    {
        $validatedData = $request->validate([
            'symbol' => 'required|string|max:10',
        ]);

        $symbol = $validatedData['symbol'];

        try {
            $stockPriceData = $this->service->fetchStockPrices($symbol);

            if ($stockPriceData) {
                $this->store($stockPriceData);
                return response()->json(['message' => 'Stock data for ' . $symbol . ' stored successfully!'], 200);
            } else {
                return response()->json(['error' => 'No data available for ' . $symbol . '.'], 404);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store($stockPriceData)
    {
        $stock = Stock::firstOrCreate([
            'symbol' => $stockPriceData['symbol'],
        ]);

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
            $stock = Stock::where('symbol', $symbol)->first();

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

    public function cacheStockPrices(HttpRequest $request)
    {
        $validateData = $request->validate([
            'symbol' => 'required|string|max:10'
        ]);

        $symbol = $validateData['symbol'];

        try {
            $data = $this->service->fetchStockPrices($symbol);

            if ($data) {
                $cacheKey = $data['symbol'];
                Cache::put($cacheKey, $data, now()->addMinutes(1));
                return response()->json(['message' => 'Stock prices cached successfully!', 'data' => $data]);
            } else {
                return response()->json(error('No data found.'), 404);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCachedAll()
    {
        try {
            $keys = DB::table('cache')->pluck('key')->toArray();
            $data = [];
            foreach ($keys as $key) {
                if ($key) {
                    $data[] = ['Message' => 'Stock data:', 'data' => Cache::get($key)];
                } else {
                    return response()->json(error('No data found.'));
                }
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
        return $data;
    }

    public function getCachedSingle(HttpRequest $request)
    {
        $validatedRequest = $request->validate([
            'key' => 'required|string|max:10'
        ]);

        $key = $validatedRequest['key'];
        try {
            if (Cache::has($key)) {
                $data = Cache::get($key);
                if ($data) {
                    return response()->json(['message' => 'Stock data for ' . $key . ':', 'data' => $data]);
                } else {
                    return response()->json(error('No data found.'), 404);
                }
            } else {
                return response()->json('Stock data for ' . $key . ' not found.', 404);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCachedMultiple(HttpRequest $request)
    {
        $validatedRequest = $request->validate([
            'keys' => 'required|array',
        ]);

        $keys = $validatedRequest['keys'];
        $data = [];

        try {
            foreach ($keys as $key) {
                if (Cache::has($key)) {
                    $stock = Cache::get($key);

                    if ($stock) {
                        $data[] = ['message' => 'Stock data for ' . $key . ':', 'data' => $stock];
                    } else {
                        $data[] = ['Message' => 'No data found for ' . $key . '.'];
                        continue;
                    }
                } else {
                    $data[] = ['Message' => 'No data found for ' . $key . '.'];
                }
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return $data;
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
            $stock = Stock::where('symbol', $symbol)->first();

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
            $results[] = ['Message' => 'Price change for ' . $symbol . ' between ' . $startDate . ' and ' . $endDate . ':', 'realiest price: ' => $priceStart, 'latest price' => $priceEnd, 'result' => $result];
        }

        return $results;
    }
}

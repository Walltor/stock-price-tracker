<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\error;

class CacheService
{
    /**
     * Create a new class instance.
     */
    protected $fetchService;
    public function __construct()
    {
    }

    public function cacheStockPrices($data)
    {
        try {
            if ($data) {
                $cacheKey = $data['symbol'];
                Cache::put($cacheKey, $data, now()->addDays(5));
                return response()->json(['Message' => 'Stock prices cached successfully!', 'data' => $data]);
            } else {
                return response()->json(error('No data found.'), 404);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCachedPricesBySymbol($key)
    {
        try {
            if (Cache::has($key)) {
                $data = Cache::get($key);
                if ($data) {
                    return $data;
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

    public function getCachedAll()
    {
        try {
            $keys = DB::table('cache')->pluck('key')->toArray();
            $data = [];
            foreach ($keys as $key) {
                $data[] = $this->getCachedPricesBySymbol($key);
            }
            return $data;
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
        $validatedData = $request->validate([
            'keys' => 'required|array'
        ]);

        $keys = $validatedData['keys'];
        $data = [];
        try {
            foreach ($keys as $key) {
                $data[] = $this->getCachedPricesBySymbol($key);
            }
            return $data;
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cacheHas($symbol)
    {
        return Cache::has($symbol);
    }
}

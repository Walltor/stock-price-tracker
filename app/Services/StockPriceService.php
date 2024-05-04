<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class StockPriceService
{
    protected $http;
    protected $url;

    public function __construct(Client $httpClient)
    {
        $this->http = $httpClient;
        $this->url = Config::get('services.alphavantage.api_url');
    }

    public function fetchStockPrices($symbol)
    {
        try {
            $response = $this->http->get("$this->url/query", [
                'query' => [
                    'function' => 'GLOBAL_QUOTE',
                    'symbol' => $symbol,
                    'apikey' => Config::get('services.alphavantage.api_key')
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['Information']) && strpos($data['Information'], 'API rate limit') !== false) {
                throw new Exception("Daily API rate limit (25) exceeded.", 429);
            }

            if (isset($data['Error Message']) && strpos($data['Error Message'], 'apikey is invalid') !== false) {
                throw new Exception("API key is invalid or missing.", 401);
            }

            if (isset($data['Global Quote'])) {
                return [
                    'symbol' => $data['Global Quote']['01. symbol'],
                    'open' => $data['Global Quote']['02. open'],
                    'high' => $data['Global Quote']['03. high'],
                    'low' => $data['Global Quote']['04. low'],
                    'price' => $data['Global Quote']['05. price'],
                    'volume' => $data['Global Quote']['06. volume'],
                    'latest trading day' => $data['Global Quote']['07. latest trading day'],
                    'previous close' => $data['Global Quote']['08. previous close'],
                    'change' => $data['Global Quote']['09. change'],
                    'change percent' => rtrim($data['Global Quote']['10. change percent'], '%')
                ];
            }
            return null;
        } catch (ClientException | ConnectException | RequestException $e) {
            Log::error('Error fetching stock price: ' . $e->getMessage());
            throw new Exception('Error occurred while making API request.', 500);
        } catch (Exception $e) {
            throw $e;
        }
    }
}

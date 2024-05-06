<?php

namespace App\Jobs;

use App\Services\StockPriceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchApiData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $stockPriceService;
    public function __construct(StockPriceService $stockPriceService)
    {
        $this->stockPriceService = $stockPriceService;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        return $this->stockPriceService->fetchStockPrices();
    }
}

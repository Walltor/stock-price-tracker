<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockPrice extends Model
{
    protected $fillable = [
        'stock_id',
        'open',
        'high',
        'low',
        'price',
        'volume',
        'latest_trading_day',
        'previous_close',
        'change',
        'change_percent'
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}

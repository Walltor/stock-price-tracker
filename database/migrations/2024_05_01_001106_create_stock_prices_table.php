<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('cascade');
            $table->decimal('open', 10, 4);
            $table->decimal('high', 10, 4);
            $table->decimal('low', 10, 4);
            $table->decimal('price', 10, 4);
            $table->bigInteger('volume');
            $table->date('latest_trading_day');
            $table->decimal('previous_close', 10, 4);
            $table->decimal('change', 10, 4);
            $table->decimal('change_percent', 10, 4);
            $table->timestamps();

            $table->index('stock_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_prices');
    }
};

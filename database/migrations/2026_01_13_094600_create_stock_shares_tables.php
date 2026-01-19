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
        Schema::create('stock_shares', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5);
            $table->string('name');
            $table->double('quantity', 8, 2);
            $table->timestamps();
        });

        Schema::create('stock_share_price_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_share_id')->index("idx_stock_share_id");
            $table->dateTime('date');
            $table->double('price_close', 8, 2)->default(0);
            $table->double('price_open', 8, 2)->default(0);
            $table->double('price_high', 8, 2)->default(0);
            $table->double('price_low', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_shares');
    }
};

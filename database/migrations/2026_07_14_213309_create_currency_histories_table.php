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
        Schema::create('currency_histories', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 3);
            $table->decimal('exchange_rate_usd', 18, 6);
            $table->date('recorded_date');
            $table->timestamps();
            
            $table->unique(['currency_code', 'recorded_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_histories');
    }
};

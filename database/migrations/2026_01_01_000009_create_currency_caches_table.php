<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currency_caches', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 3);
            $table->decimal('exchange_rate_usd', 15, 6);
            $table->json('raw_data')->nullable();
            $table->dateTime('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_caches');
    }
};
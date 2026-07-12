<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('iso2_code', 2)->unique();
            $table->string('name');
            $table->string('flag_url', 500)->nullable();
            $table->string('capital')->nullable();
            $table->string('region')->nullable();
            $table->decimal('gdp', 15, 2)->nullable();
            $table->decimal('inflation_rate', 5, 2)->nullable();
            $table->bigInteger('population')->nullable();
            $table->string('currency_code', 3);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_caches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('port_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('condition', 100);
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('wind_speed', 5, 2)->nullable();
            $table->json('raw_data')->nullable();
            $table->dateTime('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_caches');
    }
};
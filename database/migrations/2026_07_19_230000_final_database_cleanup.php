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
        // 1. Drop shipment_routes table
        Schema::dropIfExists('shipment_routes');

        // 2. Drop latitude & longitude from shipment_histories
        Schema::table('shipment_histories', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        // 3. Drop raw_data from currency_caches
        Schema::table('currency_caches', function (Blueprint $table) {
            $table->dropColumn('raw_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not necessary for this cleanup, but included for structure
        Schema::table('currency_caches', function (Blueprint $table) {
            $table->json('raw_data')->nullable();
        });

        Schema::table('shipment_histories', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
        });
        
        Schema::create('shipment_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->integer('order_sequence');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('location_name');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }
};

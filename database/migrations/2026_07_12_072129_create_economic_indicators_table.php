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
        Schema::create('economic_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->decimal('gdp', 18, 2)->nullable();
            $table->decimal('inflation_rate', 8, 2)->nullable();
            $table->bigInteger('population')->nullable();
            $table->decimal('export_value', 18, 2)->nullable();
            $table->decimal('import_value', 18, 2)->nullable();
            $table->integer('data_year')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('economic_indicators');
    }
};

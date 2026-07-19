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
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['flag_url', 'gdp', 'inflation_rate', 'population']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('flag_url')->nullable();
            $table->decimal('gdp', 15, 2)->nullable();
            $table->decimal('inflation_rate', 5, 2)->nullable();
            $table->bigInteger('population')->nullable();
        });
    }
};

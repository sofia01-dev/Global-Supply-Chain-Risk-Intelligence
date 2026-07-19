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
        Schema::table('news_caches', function (Blueprint $table) {
            $table->dropColumn([
                'positive_percentage',
                'neutral_percentage',
                'negative_percentage',
                'sentiment_score'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news_caches', function (Blueprint $table) {
            $table->decimal('positive_percentage', 5, 2)->nullable();
            $table->decimal('neutral_percentage', 5, 2)->nullable();
            $table->decimal('negative_percentage', 5, 2)->nullable();
            $table->decimal('sentiment_score', 5, 2)->nullable();
        });
    }
};

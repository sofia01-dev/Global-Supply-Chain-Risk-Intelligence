<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_caches', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50);
            $table->foreignId('country_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title', 500);
            $table->string('url', 500);
            $table->decimal('positive_percentage', 5, 2)->nullable();
            $table->decimal('neutral_percentage', 5, 2)->nullable();
            $table->decimal('negative_percentage', 5, 2)->nullable();
            $table->decimal('sentiment_score', 5, 2)->nullable();
            $table->enum('sentiment_label', ['Positive', 'Neutral', 'Negative']);
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_caches');
    }
};
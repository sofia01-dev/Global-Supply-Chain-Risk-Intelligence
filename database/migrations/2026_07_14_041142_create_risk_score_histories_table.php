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
        Schema::create('risk_score_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('shipment_id')->nullable();
            
            $table->decimal('final_score', 5, 2);
            $table->enum('risk_level', ['Low', 'Medium', 'High', 'Critical']);
            $table->datetime('calculated_at');
            
            $table->timestamps();

            // Foreign keys
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_score_histories');
    }
};

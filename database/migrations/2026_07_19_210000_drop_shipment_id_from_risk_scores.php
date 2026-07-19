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
        Schema::table('risk_scores', function (Blueprint $table) {
            $table->dropForeign(['shipment_id']);
            $table->dropColumn('shipment_id');
        });

        Schema::table('risk_score_histories', function (Blueprint $table) {
            $table->dropForeign(['shipment_id']);
            $table->dropColumn('shipment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_scores', function (Blueprint $table) {
            $table->foreignId('shipment_id')->nullable()->constrained()->onDelete('cascade');
        });

        Schema::table('risk_score_histories', function (Blueprint $table) {
            $table->foreignId('shipment_id')->nullable()->constrained()->onDelete('cascade');
        });
    }
};

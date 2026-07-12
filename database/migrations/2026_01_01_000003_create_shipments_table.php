<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('shipment_code', 100)->unique();
            $table->foreignId('origin_port_id')->constrained('ports')->cascadeOnDelete();
            $table->foreignId('destination_port_id')->constrained('ports')->cascadeOnDelete();
            $table->enum('current_status', ['pending', 'transit', 'delayed', 'arrived'])->default('pending');
            $table->text('delay_reason')->nullable();
            $table->text('recommendation')->nullable();
            $table->date('departure_date')->nullable();
            $table->date('estimated_arrival')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
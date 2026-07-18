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
        // Using DB raw statement since Doctrine DBAL can struggle with enum changes
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE shipments MODIFY COLUMN current_status ENUM('pending', 'transit', 'delayed', 'arrived', 'delivered') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE shipments MODIFY COLUMN current_status ENUM('pending', 'transit', 'delayed', 'arrived') NOT NULL DEFAULT 'pending'");
    }
};

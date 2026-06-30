<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicle_maintenance_details', function (Blueprint $table) {
            $table->boolean('is_detected')->nullable();
            $table->decimal('estimated_expenditure', 18, 2)->nullable();
            $table->string('shop_owner')->nullable();
            $table->date('repair_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_maintenance_details', function (Blueprint $table) {
            //
        });
    }
};

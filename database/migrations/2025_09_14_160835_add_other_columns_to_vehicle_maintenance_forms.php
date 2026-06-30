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
        Schema::table('vehicle_maintenance_forms', function (Blueprint $table) {
            $table->string('previous_meter_reading')->nullable();
            $table->dateTime('previous_meter_reading_date')->nullable();
            $table->string('present_meter_reading')->nullable();
            $table->dateTime('present_meter_reading_date')->nullable();
            $table->string('difference')->nullable();
            $table->dateTime('last_work_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_maintenance_forms', function (Blueprint $table) {
            //
        });
    }
};

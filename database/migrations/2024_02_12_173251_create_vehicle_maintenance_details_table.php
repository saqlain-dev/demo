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
        Schema::create('vehicle_maintenance_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('vehicle_maintenance_forms');
            $table->string('nature_of_work')->nullable();
            $table->string('previous_meter_reading')->nullable();
            $table->date('previous_meter_reading_date')->nullable();
            $table->string('present_meter_reading')->nullable();
            $table->date('present_meter_reading_date')->nullable();
            $table->string('difference')->nullable();
            $table->date('last_work_date')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('air_travel_requests');
    }
};

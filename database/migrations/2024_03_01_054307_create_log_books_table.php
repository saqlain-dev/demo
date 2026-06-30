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
        Schema::create('log_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles');
            $table->foreignId('driver_id')->nullable()->constrained('employees');
            $table->foreignId('visit_type')->nullable()->constrained('type_values');
            $table->string('vehicle_type')->nullable();
            $table->string('odo_meter_start')->nullable();
            $table->string('odo_meter_end')->nullable();
            $table->string('duration')->nullable();
            $table->string('non_pool_vehicle_cost')->nullable();
            $table->string('non_pool_vehicle_km')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('log_books');
    }
};

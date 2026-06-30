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
        Schema::create('assign_vehicle_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assign_vehicle_id')->constrained('assign_vehicles');
            $table->foreignId('driver_id')->nullable()->constrained('employees');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles');
            $table->string('action');
            $table->date('assigned_date')->nullable();
            $table->json('changes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assign_vehicle_logs');
    }
};

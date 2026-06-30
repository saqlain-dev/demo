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
        Schema::create('vehicle_maintenance_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_type')->nullable()->constrained('type_values');
            $table->string('registration_no')->nullable();

            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->foreignId('department_id')->nullable()->constrained('type_values');
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

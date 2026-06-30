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
        Schema::create('vehicle_maintenance_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_maintenance_id')->nullable()->constrained('vehicle_maintenance_forms');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->tinyInteger('isApplied')->default(0);
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
        Schema::dropIfExists('vehicle_maintenance_vendors');
    }
};

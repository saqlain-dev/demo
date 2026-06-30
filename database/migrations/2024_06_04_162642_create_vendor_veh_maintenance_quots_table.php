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
        Schema::create('vendor_veh_maintenance_quots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_maintenance_id')->nullable()->constrained('vehicle_maintenance_forms');
            $table->foreignId('vehicle_maint_detail_id')->nullable()->constrained('vehicle_maintenance_details');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->float('qty')->nullable();
            $table->double('estimated_cost',18,2)->nullable();
            $table->double('total_estimated_cost',18,2)->nullable();
            $table->text('remarks')->nullable();
            $table->tinyInteger('quotation_status')->default(0);
            $table->text('quotation_accepted_remarks')->nullable();
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
        Schema::dropIfExists('vendor_veh_maintenance_quots');
    }
};

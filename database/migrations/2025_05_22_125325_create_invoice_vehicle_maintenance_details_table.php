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
        Schema::create('invoice_vehicle_maintenance_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->foreignId('vm_id')->nullable()->constrained('vehicle_maintenance_forms');
            
            $table->string('nature_of_work')->nullable();
            $table->integer('qty')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('estimated_unit_cost', 12, 2)->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->foreignId('quotation_id')->nullable()->constrained('vendor_veh_maintenance_quots');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_vehicle_maintenance_details');
    }
};

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
        Schema::create('vehicle_maintenance_invoice_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vm_quo_id')->nullable()->constrained('vendor_veh_maintenance_quots');
            $table->foreignId('vehicle_maintenance_id')->nullable()->constrained('vehicle_maintenance_forms');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->dateTime('date')->nullable();
            $table->string('invoice')->nullable();
            $table->string('vm_document')->nullable();
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
        Schema::dropIfExists('vehicle_maintenance_invoice_documents');
    }
};

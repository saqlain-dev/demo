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
        Schema::create('vehicle_requistion_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('vr_id')->nullable()->constrained('vehicle_requests');
            $table->string('vehicle_name')->nullable();
            $table->decimal('quantity', 12, 2)->nullable();
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->boolean('is_perday')->default(true);
            $table->foreignId('quotation_id')->nullable()->constrained('vendor_vehicle_req_quotations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_requistion_invoices');
    }
};

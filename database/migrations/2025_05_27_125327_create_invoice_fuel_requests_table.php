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
        Schema::create('invoice_fuel_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('fuel_request_id')->nullable()->constrained('fuel_requests');
            $table->foreignId('procurement_id')->nullable()->constrained('procurements');
            $table->foreignId('procurement_detail_id')->nullable()->constrained('procurement_details');
            $table->text('name')->nullable();
            $table->text('remarks')->nullable(); 
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_fuel_requests');
    }
};

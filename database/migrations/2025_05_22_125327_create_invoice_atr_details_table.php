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
        Schema::create('invoice_atr_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('atr_id')->nullable()->constrained('air_travel_requests');
            
            $table->dateTime('datetime')->nullable();
            $table->string('traveler')->nullable();
            $table->string('airline')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->foreignId('quotation_id')->nullable()->constrained('vendor_atr_quotations');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_atr_details');
    }
};

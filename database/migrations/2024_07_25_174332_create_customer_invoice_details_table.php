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
        Schema::create('customer_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_invoice_id')->nullable()->constrained('customer_invoices');
            $table->string('item_detail')->nullable();
            $table->string('item_coa')->nullable();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('quantity')->nullable();
            $table->double('rate',18,2)->nullable();
            $table->double('amount',18,2)->nullable();
            $table->double('total',18,2)->nullable();
            $table->string('class',)->nullable();
            $table->double('tax',18,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_invoice_details');
    }
};

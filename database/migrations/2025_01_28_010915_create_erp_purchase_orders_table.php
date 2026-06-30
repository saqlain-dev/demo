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
        Schema::create('erp_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations');
            $table->date('purchase_date')->nullable();
            $table->string('quotation_no')->nullable();
            $table->date('delivery_date')->nullable();
            $table->time('delivery_time')->nullable();
            $table->string('po_reference')->nullable();
            $table->date('po_date')->nullable();
            $table->string('po_attachment')->nullable();
            $table->string('ship_to_address')->nullable();
            $table->string('email_address')->nullable();
            $table->string('fax_no')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('telephone_no')->nullable();
            $table->string('address')->nullable();
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
        Schema::dropIfExists('erp_purchase_orders');
    }
};

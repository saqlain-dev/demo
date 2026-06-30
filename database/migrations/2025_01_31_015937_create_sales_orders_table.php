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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->nullable()->constrained('erp_purchase_orders');
            $table->string('sales_order_series')->nullable();
            $table->date('date')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->string('customer_purchase_order')->nullable();
            $table->date('delivery_date')->nullable();
            $table->foreignId('order_type')->nullable()->constrained('type_values');
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
        Schema::dropIfExists('sales_orders');
    }
};

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
        Schema::table('erp_purchase_orders', function (Blueprint $table) {
            $table->time('lead_time')->nullable();
            $table->text('remarks')->nullable();
            $table->string('billing_name')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_phone')->nullable();
            $table->string('invoice_name')->nullable();
            $table->string('invoice_address')->nullable();
            $table->string('invoice_phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_purchase_orders', function (Blueprint $table) {
            $table->dropColumn('lead_time');
            $table->dropColumn('remarks');
            $table->dropColumn('billing_name');
            $table->dropColumn('billing_address');
            $table->dropColumn('billing_phone');
            $table->dropColumn('invoice_name');
            $table->dropColumn('invoice_address');
            $table->dropColumn('invoice_phone');
        });
    }
};

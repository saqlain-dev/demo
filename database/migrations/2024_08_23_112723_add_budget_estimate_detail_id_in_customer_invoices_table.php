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
        Schema::table('customer_invoice_details', function (Blueprint $table) {
            $table->integer('budget_estimate_detail_id')->nullable();
        });

        Schema::table('finance_bill_details', function (Blueprint $table) {
            $table->integer('budget_estimate_detail_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_invoice_details', function (Blueprint $table) {
            $table->dropColumn('budget_estimate_detail_id');
        });

        Schema::table('finance_bill_details', function (Blueprint $table) {
            $table->dropColumn('budget_estimate_detail_id');
        });
    }
};

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
        Schema::table('budget_estimates', function (Blueprint $table) {
            $table->integer('admin_bill_id')->nullable();
            $table->integer('admin_invoice_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_estimates', function (Blueprint $table) {
            $table->dropColumn('admin_bill_id');
            $table->dropColumn('admin_invoice_id');
        });
    }
};

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
        Schema::table('project_awardeds', function (Blueprint $table) {
            $table->boolean('ar_status')->default(false);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('rfq_id')->nullable()->constrained('purchase_request_rfqs');
        });

        Schema::table('invoice_details', function (Blueprint $table) {
            $table->foreignId('item_variant_id')->nullable()->constrained('item_variants');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_awardeds', function (Blueprint $table) {
            $table->dropColumn('ar_status');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['rfq_id']);
            $table->dropColumn('rfq_id');
        });

        Schema::table('invoice_details', function (Blueprint $table) {
            $table->dropForeign(['item_variant_id']);
            $table->dropColumn('item_variant_id');
        });
    }
};

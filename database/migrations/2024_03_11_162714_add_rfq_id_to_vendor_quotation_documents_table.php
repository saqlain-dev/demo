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
        Schema::table('vendor_quotation_documents', function (Blueprint $table) {
            $table->foreignId('rfq_id')->nullable()->constrained('purchase_request_rfqs');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_quotation_documents', function (Blueprint $table) {
            $table->dropColumn('rfq_id');
            $table->dropColumn('vendor_id');
        });
    }
};

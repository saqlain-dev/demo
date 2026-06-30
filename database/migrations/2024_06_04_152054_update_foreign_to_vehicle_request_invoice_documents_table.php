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

        Schema::table('vehicle_request_invoice_documents', function (Blueprint $table) {
            $table->dropForeign(['vr_quo_id']);
            //$table->dropColumn('vr_quo_id');
            // Add the new foreign key constraint
            //$table->foreignId('vr_quo_id')->nullable()->constrained('vendor_vehicle_req_quotations');
        });
        Schema::table('vehicle_request_invoice_documents', function (Blueprint $table) {
            $table->dropForeign(['vehicle_req_id']);
            //$table->dropColumn('vehicle_req_id');
            // Add the new foreign key constraint
            //$table->foreignId('vehicle_req_id')->nullable()->constrained('vehicle_requests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_request_invoice_documents', function (Blueprint $table) {
            //
        });
    }
};

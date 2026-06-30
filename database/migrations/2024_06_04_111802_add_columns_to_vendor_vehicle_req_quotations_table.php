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
        Schema::table('vendor_vehicle_req_quotations', function (Blueprint $table) {
            $table->foreignId('vehicle_req_detail_id')->nullable()->constrained('vehicle_request_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_vehicle_req_quotations', function (Blueprint $table) {
            $table->dropForeign(['vehicle_req_detail_id']);
            $table->dropColumn('vehicle_req_detail_id');
        });
    }
};

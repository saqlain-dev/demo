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
        Schema::table('purchase_request_rfqs', function (Blueprint $table) {
            $table->foreignId('dispose_request_id')->nullable()->constrained('dispose_requests');
        });

        Schema::table('purchase_request_rfq_details', function (Blueprint $table) {
            $table->foreignId('dispose_request_detail_id')->nullable()->constrained('dispose_request_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_request_rfqs', function (Blueprint $table) {
            $table->dropForeign(['dispose_request_id']);
            $table->dropColumn('dispose_request_id');
        });

        Schema::table('purchase_request_rfq_details', function (Blueprint $table) {
            $table->dropForeign(['dispose_request_detail_id']);
            $table->dropColumn('dispose_request_detail_id');
        });
    }
};

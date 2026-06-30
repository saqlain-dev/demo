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
        Schema::table('vehicle_request_details', function (Blueprint $table) {
            $table->foreignId('procurement_id')->nullable()->constrained('procurements');
            $table->foreignId('procurement_details_id')->nullable()->constrained('procurement_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_request_details', function (Blueprint $table) {
            $table->dropForeign(['procurement_id']);
            $table->dropColumn('procurement_id');
            $table->dropForeign(['procurement_details_id']);
            $table->dropColumn('procurement_details_id');
        });
    }
};

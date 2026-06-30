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
        Schema::table('purchase_request_details', function (Blueprint $table) {
           $table->foreignId('procurement_detail_id')->nullable()->constrained('procurement_details');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_request_details', function (Blueprint $table) {
            $table->dropForeign(['procurement_detail_id']);
            $table->dropColumn('procurement_detail_id');
        });
    }
};

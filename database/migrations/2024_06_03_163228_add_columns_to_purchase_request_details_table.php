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
            $table->foreignId('procurement_id')->nullable()->constrained('procurements');
            $table->foreignId('account_head_id')->nullable()->constrained('chart_of_accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_request_details', function (Blueprint $table) {
            $table->dropForeign(['procurement_id']);
            $table->dropColumn('procurement_id');
            $table->dropForeign(['account_head_id']);
            $table->dropColumn('account_head_id');
        });
    }
};

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
        Schema::table('purchase_request_rfq_details', function (Blueprint $table) {
            $table->dropForeign('purchase_request_rfq_details _purchase_request_id_foreign');
            $table->dropColumn('purchase_request_id');
            $table->dropForeign('purchase_request_rfq_details _purchase_request_detail_id_foreign');
            $table->dropColumn('purchase_request_detail_id');
        });

        Schema::table('purchase_request_rfq_details', function (Blueprint $table) {
          $table->foreignId('item_id')->nullable()->constrained('items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_request_rfq_details', function (Blueprint $table) {
            //
        });
    }
};

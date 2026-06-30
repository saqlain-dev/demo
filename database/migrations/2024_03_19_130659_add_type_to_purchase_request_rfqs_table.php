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
            $table->unsignedTinyInteger('rfq_type')->default(0);
        });
        Schema::table('purchase_request_rfq_details', function (Blueprint $table) {
            $table->foreignId('inventory_id')->nullable()->constrained('inventories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_request_rfqs', function (Blueprint $table) {
            $table->dropColumn('rfq_type');
        });
        Schema::table('purchase_request_rfq_details', function (Blueprint $table) {
            $table->dropColumn('inventory_id');
        });
    }
};

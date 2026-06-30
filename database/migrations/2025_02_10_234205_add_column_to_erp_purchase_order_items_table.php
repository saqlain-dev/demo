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
        Schema::table('erp_purchase_order_items', function (Blueprint $table) {
            $table->foreignId('erp_category_id')->nullable()->constrained('erp_item_categories');
            $table->foreignId('erp_sub_category_id')->nullable()->constrained('erp_item_sub_categories');
            $table->string('item_name')->nullable();
            $table->tinyInteger('item_type')->default(0);
            $table->tinyInteger('item_status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['erp_category_id']);
            $table->dropForeign(['erp_sub_category_id']);
            $table->dropColumn('erp_category_id');
            $table->dropColumn('erp_sub_category_id');
            $table->dropColumn('item_name');
            $table->dropColumn('item_type');
            $table->dropColumn('item_status');
        });
    }
};

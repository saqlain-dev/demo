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
        Schema::table('rfq_items', function (Blueprint $table) {
            $table->foreignId('rfp_item_id')->nullable()->constrained('rfp_items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rfq_items', function (Blueprint $table) {
            $table->dropForeign(['rfp_item_id']);
            $table->dropColumn('rfp_item_id');
        });
    }
};

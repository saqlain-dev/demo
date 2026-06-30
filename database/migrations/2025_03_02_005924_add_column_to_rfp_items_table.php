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
        Schema::table('rfp_items', function (Blueprint $table) {
            $table->integer('brand_id')->nullable();
            $table->integer('assign_to')->nullable();
            $table->integer('item_type_status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rfp_items', function (Blueprint $table) {
            $table->dropColumn('brand_id');
            $table->dropColumn('assign_to');
            $table->dropColumn('item_type_status');
        });
    }
};

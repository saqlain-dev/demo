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
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropColumn('item_type');
        });
        Schema::table('erp_items', function (Blueprint $table) {
            $table->foreignId('item_type')->nullable()->constrained('type_values');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropForeign(['item_type']);
            $table->dropColumn('item_type');
        });
    }
};

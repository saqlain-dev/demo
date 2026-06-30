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
        Schema::table('vendor_quotation_details', function (Blueprint $table) {
            $table->foreignId('item_variant_id')->nullable()->constrained('item_variants');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_quotation_details', function (Blueprint $table) {
            $table->dropForeign(['item_variant_id']);
            $table->dropColumn('item_variant_id');
        });
    }
};

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
        Schema::table('vendor_atr_quotations', function (Blueprint $table) {
            $table->tinyInteger('quotation_status')->default(0);
            $table->text('quotation_accepted_remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_atr_quotations', function (Blueprint $table) {
            $table->dropColumn('quotation_status');
            $table->dropColumn('quotation_accepted_remarks');
        });
    }
};

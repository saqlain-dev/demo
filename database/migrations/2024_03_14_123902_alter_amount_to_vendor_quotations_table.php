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
        Schema::table('vendor_quotations', function (Blueprint $table) {
            $table->string('total_quotation_amount')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_quotations', function (Blueprint $table) {
            $table->decimal('total_quotation_amount',18,2)->change();
        });
    }
};

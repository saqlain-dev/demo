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
        Schema::table('vouchers', function (Blueprint $table) {
            $table->foreignId('s_tax_section')->nullable()->constrained('tax_management');
            $table->integer('s_tax_type')->nullable();
            $table->float('s_tax_rate')->nullable();
            $table->double('s_tax_amount',18,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeign(['s_tax_section']);
            $table->dropColumn('s_tax_section');
            $table->dropColumn('s_tax_type');
            $table->dropColumn('s_tax_rate');
            $table->dropColumn('s_tax_amount');
        });
    }
};

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
            $table->foreignId('tax_section')->nullable()->constrained('tax_management');
            $table->foreignId('tax_type')->nullable()->constrained('type_values');
            $table->float('tax_rate')->nullable();
            $table->double('tax_amount',18,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeign(['tax_section']);
            $table->dropColumn('tax_section');
            $table->dropForeign(['tax_type']);
            $table->dropColumn('tax_type');
            $table->dropColumn('tax_rate');
            $table->dropColumn('tax_amount');
        });
    }
};

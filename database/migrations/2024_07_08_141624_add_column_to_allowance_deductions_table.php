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
        Schema::table('allowance_deductions', function (Blueprint $table) {
            $table->foreignId('coa_head_id')->nullable()->constrained('chart_of_accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allowance_deductions', function (Blueprint $table) {
            $table->dropForeign(['coa_head_id']);
            $table->dropColumn('coa_head_id');
        });
    }
};

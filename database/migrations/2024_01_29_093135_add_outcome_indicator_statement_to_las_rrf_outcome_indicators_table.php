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
        Schema::table('las_rrf_outcome_indicators', function (Blueprint $table) {
            $table->string('outcome_indicator_statement',200)->nullable();
        });
        Schema::table('las_rrf_output_indicators', function (Blueprint $table) {
            $table->string('output_indicator_statement',200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('las_rrf_outcome_indicators', function (Blueprint $table) {
            $table->dropColumn('outcome_indicator_statement');
        });

        Schema::table('las_rrf_output_indicators', function (Blueprint $table) {
            $table->dropColumn('output_indicator_statement');
        });
    }
};

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
        Schema::table('las_rrf_goal_indicator_targets', function (Blueprint $table) {
            $table->decimal('progress',18,2)->default(0);
        });
        Schema::table('las_rrf_outcome_indicator_targets', function (Blueprint $table) {
            $table->decimal('progress',18,2)->default(0);
        });
        Schema::table('las_rrf_output_indicator_targets', function (Blueprint $table) {
            $table->decimal('progress',18,2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('las_rrf_goal_indicator_targets', function (Blueprint $table) {
            $table->dropColumn('progress');
        });
        Schema::table('las_rrf_outcome_indicator_targets', function (Blueprint $table) {
            $table->dropColumn('progress');
        });
        Schema::table('las_rrf_output_indicator_targets', function (Blueprint $table) {
            $table->dropColumn('progress');
        });
    }
};

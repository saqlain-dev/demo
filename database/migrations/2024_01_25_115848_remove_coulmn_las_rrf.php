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


        Schema::table('result_resource_frameworks', function (Blueprint $table) {
            //$table->dropForeign('project_id');
            $table->dropColumn(['project_goal','goal_indicator','goal_baseline', 'goal_lop_target', 'yearly_target', 'goal_indicator_statement', 'las_rf_statement', 'las_rf_indicator', 'las_sp_statement', 'las_sp_indicator', 'las_rrf_indicator']);
        });

        Schema::table('result_resource_framework_outcomes', function (Blueprint $table) {
            //$table->dropForeign('project_id');
            $table->dropColumn([ 'indicator_baseline', 'lop_target', 'yearly_target', 'indicator_statement', 'sp_indicator_no', 'rrf_indicator']);
        });
        Schema::table('result_resource_framework_outputs', function (Blueprint $table) {
            //$table->dropForeign('project_id');
            $table->dropColumn(['indicator_baseline', 'lop_target', 'yearly_target', 'indicator_statement', 'sp_indicator_no', 'rrf_indicator']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

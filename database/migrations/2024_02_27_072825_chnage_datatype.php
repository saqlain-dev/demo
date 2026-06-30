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
        Schema::table('las_rrf_goal_indicators', function (Blueprint $table) {
            // Change column type to text
            $table->text('goal_indicator_statement')->nullable()->change();
        });
        Schema::table('las_rrf_outcome_indicators', function (Blueprint $table) {
            // Change column type to text
            $table->text('outcome_indicator_statement')->nullable()->change();
        });
        Schema::table('las_rrf_output_indicators', function (Blueprint $table) {
            // Change column type to text
            $table->text('output_indicator_statement')->nullable()->change();
        });
        Schema::table('project_rrf_goal_indicators', function (Blueprint $table) {
            // Change column type to text
            $table->text('goal_indicator_statement')->nullable()->change();
        });
        Schema::table('project_rrf_outcome_indicators', function (Blueprint $table) {
            // Change column type to text
            $table->text('outcome_indicator_statement')->nullable()->change();
        });
        Schema::table('project_rrf_output_indicators', function (Blueprint $table) {
            // Change column type to text
            $table->text('output_indicator_statement')->nullable()->change();
        });
        Schema::table('project_rrf_goals', function (Blueprint $table) {
            // Change column type to text
            $table->text('goal_statement')->nullable()->change();
        });
        Schema::table('project_rrf_outcomes', function (Blueprint $table) {
            // Change column type to text
            $table->text('outcome_statement')->nullable()->change();
        });
        Schema::table('project_rrf_outputs', function (Blueprint $table) {
            // Change column type to text
            $table->text('output_statement')->nullable()->change();
        });
        Schema::table('result_resource_frameworks', function (Blueprint $table) {
            // Change column type to text
            $table->text('goal_statement')->nullable()->change();
        });
        Schema::table('result_resource_framework_outcomes', function (Blueprint $table) {
            // Change column type to text
            $table->text('rrf_outcome_statement')->nullable()->change();
        });
        Schema::table('result_resource_framework_outputs', function (Blueprint $table) {
            // Change column type to text
            $table->text('rrf_output_statement')->nullable()->change();
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

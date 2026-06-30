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
        Schema::table('progress_workplan_goals', function (Blueprint $table) {
            $table->text('goal_quarterly_target')->nullable()->change();
            $table->text('goal_progress')->nullable()->change();
            $table->text('goal_timeline_for_indicators')->nullable()->change();
        });
        Schema::table('progress_workplan_outcomes', function (Blueprint $table) {
            $table->text('outcome_quarterly_target')->nullable()->change();
            $table->text('outcome_progress')->nullable()->change();
            $table->text('outcome_timeline_for_indicators')->nullable()->change();
        });
        Schema::table('progress_workplan_outputs', function (Blueprint $table) {
            $table->text('target')->nullable()->change();
            $table->text('output_progress')->nullable()->change();
            $table->text('output_timeline_for_indicators')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_workplan_goals', function (Blueprint $table) {
            $table->string('goal_quarterly_target')->nullable()->change();
            $table->string('goal_progress')->nullable()->change();
            $table->string('goal_timeline_for_indicators')->nullable()->change();
        });
        Schema::table('progress_workplan_outcomes', function (Blueprint $table) {
            $table->string('outcome_quarterly_target')->nullable()->change();
            $table->string('outcome_progress')->nullable()->change();
            $table->string('outcome_timeline_for_indicators')->nullable()->change();
        });
        Schema::table('progress_workplan_outputs', function (Blueprint $table) {
            $table->string('target')->nullable()->change();
            $table->string('output_progress')->nullable()->change();
            $table->string('output_timeline_for_indicators')->nullable()->change();
        });
    }
};

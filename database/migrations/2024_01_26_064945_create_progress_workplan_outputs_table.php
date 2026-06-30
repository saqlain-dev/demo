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
        Schema::create('progress_workplan_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('project_profiles');
            $table->foreignId('progress_workplan_id')->nullable()->constrained('progress_workplans');
            $table->string('goal_id')->nullable();
            $table->string('outcome_id')->nullable();
            $table->string('output_id')->nullable();
            $table->string('output_indicator_id')->nullable();
            $table->string('output_quarterly_target')->nullable();
            $table->string('output_progress')->nullable();
            $table->string('output_budget_allocated')->nullable();
            $table->string('output_budget_spent')->nullable();
            $table->string('output_movs_ids')->nullable();
            $table->string('output_status')->nullable();
            $table->string('output_timeline_for_indicators')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_workplan_outputs');
    }
};

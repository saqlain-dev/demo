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
        Schema::create('mne_plan_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->nullable()->constrained('project_mne_plans');
            $table->foreignId('project_goal_id')->nullable()->constrained('project_rrf_goals');
            $table->text('indicator_definition')->nullable();
            $table->text('indicator_methodology')->nullable();
            $table->text('data_collection_methodology')->nullable();
            $table->string('disaggregates')->nullable()->comment('will contain array of type_value ids');
            $table->string('mne_tools')->nullable()->comment('will contain array of type_value ids');
            $table->string('data_collection_freq')->nullable()->comment('will contain array of type_value ids (Monthly/Quarterly/Yearly)');
            $table->string('data_reporting_freq')->nullable()->comment('will contain array of type_value ids (Monthly/Quarterly/Yearly)');
            $table->string('required_movs')->nullable()->comment('will contain array of type_value ids');
            $table->text('responsibility')->nullable();

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
        Schema::dropIfExists('mne_plan_goals');
    }
};

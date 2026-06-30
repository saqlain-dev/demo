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
        Schema::create('rmp_methodology_details', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('rm_methodology_id');
            $table->foreignId('rm_plan_id')->nullable()->constrained('rm_plans');
            $table->date('research_methodology_note_start_date')->nullable();
            $table->date('research_methodology_note_end_date')->nullable();
            $table->string('research_methodology_note_responsible')->nullable();

            $table->date('literature_review_start_date')->nullable();
            $table->date('literature_review_end_date')->nullable();
            $table->string('literature_review_responsible')->nullable();

            $table->date('data_collection_tool_start_date')->nullable();
            $table->date('data_collection_tool_end_date')->nullable();
            $table->string('data_collection_tool_responsible')->nullable();

            $table->date('translation_of_data_collection_tool_start_date')->nullable();
            $table->date('translation_of_data_collection_tool_end_date')->nullable();
            $table->string('translation_of_data_collection_tool_responsible')->nullable();


            $table->date('data_collection_tools_online_software_scripting_start_date')->nullable();
            $table->date('data_collection_tools_online_software_scripting_end_date')->nullable();
            $table->string('data_collection_tools_online_software_scripting_responsible')->nullable();

            $table->date('vetting_of_scripted_data_collection_tools_for_logic_checks_start_date')->nullable();
            $table->date('vetting_of_scripted_data_collection_tools_for_logic_checks_end_date')->nullable();
            $table->string('vetting_of_scripted_data_collection_tools_for_logic_checks_responsible')->nullable();


            $table->date('training_of_enumerators_start_date')->nullable();
            $table->string('training_of_enumerators_responsible')->nullable();
            $table->date('training_of_enumerators_end_date')->nullable();

            $table->date('pilot_testing_of_data_collectionTool_start_date')->nullable();
            $table->string('pilot_testing_of_data_collectionTool_responsible')->nullable();
            $table->date('pilot_testing_of_data_collectionTool_end_date')->nullable();

            $table->date('refresher_training_and_feedback_start_date')->nullable();
            $table->string('refresher_training_and_feedback_responsible')->nullable();
            $table->date('refresher_training_and_feedback_end_date')->nullable();

            $table->date('survey_administration_start_date')->nullable();
            $table->string('survey_administration_responsible')->nullable();
            $table->date('survey_administration_end_date')->nullable();

            $table->date('quality_assurance_start_date')->nullable();
            $table->string('quality_assurance_responsible')->nullable();
            $table->date('quality_assurance_end_date')->nullable();

            $table->date('data_cleaning_start_date')->nullable();
            $table->string('data_cleaning_responsible')->nullable();
            $table->date('data_cleaning_end_date')->nullable();

            $table->date('data_analysis_and_interpretation_start_date')->nullable();
            $table->string('data_analysis_and_interpretation_responsible')->nullable();
            $table->date('data_analysis_and_interpretation_end_date')->nullable();

            $table->date('data_workshopping_start_date')->nullable();
            $table->string('data_workshopping_responsible')->nullable();
            $table->date('data_workshopping_end_date')->nullable();

            $table->date('report_drafting_and_finalization_start_date')->nullable();
            $table->string('report_drafting_and_finalization_responsible')->nullable();
            $table->date('report_drafting_and_finalization_end_date')->nullable();

            $table->date('report_design_start_date')->nullable();
            $table->string('report_design_responsible')->nullable();
            $table->date('report_design_end_date')->nullable();

            $table->date('design_finalization_start_date')->nullable();
            $table->string('design_finalization_responsible')->nullable();
            $table->date('design_finalization_end_date')->nullable();

            $table->date('report_printing_start_date')->nullable();
            $table->string('report_printing_responsible')->nullable();
            $table->date('report_printing_end_date')->nullable();

            $table->date('report_dissemination_start_date')->nullable();
            $table->string('report_dissemination_responsible')->nullable();
            $table->date('report_dissemination_end_date')->nullable();

            $table->date('identification_and_recruitment_of_respondents_start_date')->nullable();
            $table->string('identification_and_recruitment_of_respondents_responsible')->nullable();
            $table->date('identification_and_recruitment_of_respondents_end_date')->nullable();

            $table->date('transcriptions_start_date')->nullable();
            $table->string('transcriptions_responsible')->nullable();
            $table->date('transcriptions_end_date')->nullable();

            $table->date('development_of_data_entry_framework_start_date')->nullable();
            $table->string('development_of_data_entry_framework_responsible')->nullable();
            $table->date('development_of_data_entry_framework_end_date')->nullable();

            $table->date('extraction_of_data_from_secondary_sources_start_date')->nullable();
            $table->string('extraction_of_data_from_secondary_sources_responsible')->nullable();
            $table->date('extraction_of_data_from_secondary_sources_end_date')->nullable();

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
        Schema::dropIfExists('rmp_methodology_details');
    }
};

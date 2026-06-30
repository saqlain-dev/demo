<?php

namespace App\Http\Controllers\Api\V1\Program\Rdu;

use App\Enum\RmMethodology;
use App\Http\Controllers\Controller;
use App\Models\Program\Rdu\RmPlan;
use App\Models\Program\Rdu\RmpMethodologyDetail;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RmpMethodologyDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = RmpMethodologyDetail::all();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rm_methodology_id' => 'required|integer',
            'rm_plan_id' => 'required|exists:rm_plans,id',
            'research_methodology_note_start_date' => 'nullable|date',
            'research_methodology_note_end_date' => 'nullable|date|after_or_equal:research_methodology_note_start_date',
            'research_methodology_note_responsible' => 'nullable|string',
            'literature_review_start_date' => 'nullable|date',
            'literature_review_end_date' => 'nullable|date|after_or_equal:literature_review_start_date',
            'literature_review_responsible' => 'nullable|string',
            'data_collection_tool_start_date' => 'nullable|date',
            'data_collection_tool_end_date' => 'nullable|date|after_or_equal:data_collection_tool_start_date',
            'data_collection_tool_responsible' => 'nullable|string',
            'translation_of_data_collection_tool_start_date' => 'nullable|date',
            'translation_of_data_collection_tool_end_date' => 'nullable|date|after_or_equal:translation_of_data_collection_tool_start_date',
            'translation_of_data_collection_tool_responsible' => 'nullable|string',
            'data_collection_tools_online_software_scripting_start_date' => 'nullable|date',
            'data_collection_tools_online_software_scripting_end_date' => 'nullable|date|after_or_equal:data_collection_tools_online_software_scripting_start_date',
            'data_collection_tools_online_software_scripting_responsible' => 'nullable|string',
            'vetting_of_scripted_data_collection_tools_for_logic_checks_start_date' => 'nullable|date',
            'vetting_of_scripted_data_collection_tools_for_logic_checks_end_date' => 'nullable|date|after_or_equal:vetting_of_scripted_data_collection_tools_for_logic_checks_start_date',
            'vetting_of_scripted_data_collection_tools_for_logic_checks_responsible' => 'nullable|string',
            'training_of_enumerators_start_date' => 'nullable|date',
            'training_of_enumerators_end_date' => 'nullable|date|after_or_equal:training_of_enumerators_start_date',
            'training_of_enumerators_responsible' => 'nullable|string',
            'pilot_testing_of_data_collectionTool_start_date' => 'nullable|date',
            'pilot_testing_of_data_collectionTool_end_date' => 'nullable|date|after_or_equal:pilot_testing_of_data_collectionTool_start_date',
            'pilot_testing_of_data_collectionTool_responsible' => 'nullable|string',
            'refresher_training_and_feedback_start_date' => 'nullable|date',
            'refresher_training_and_feedback_end_date' => 'nullable|date|after_or_equal:refresher_training_and_feedback_start_date',
            'refresher_training_and_feedback_responsible' => 'nullable|string',
            'survey_administration_start_date' => 'nullable|date',
            'survey_administration_end_date' => 'nullable|date|after_or_equal:survey_administration_start_date',
            'survey_administration_responsible' => 'nullable|string',
            'quality_assurance_start_date' => 'nullable|date',
            'quality_assurance_end_date' => 'nullable|date|after_or_equal:quality_assurance_start_date',
            'quality_assurance_responsible' => 'nullable|string',
            'data_cleaning_start_date' => 'nullable|date',
            'data_cleaning_end_date' => 'nullable|date|after_or_equal:data_cleaning_start_date',
            'data_cleaning_responsible' => 'nullable|string',
            'data_analysis_and_interpretation_start_date' => 'nullable|date',
            'data_analysis_and_interpretation_end_date' => 'nullable|date|after_or_equal:data_analysis_and_interpretation_start_date',
            'data_analysis_and_interpretation_responsible' => 'nullable|string',
            'data_workshopping_start_date' => 'nullable|date',
            'data_workshopping_end_date' => 'nullable|date|after_or_equal:data_workshopping_start_date',
            'data_workshopping_responsible' => 'nullable|string',
            'report_drafting_and_finalization_start_date' => 'nullable|date',
            'report_drafting_and_finalization_end_date' => 'nullable|date|after_or_equal:report_drafting_and_finalization_start_date',
            'report_drafting_and_finalization_responsible' => 'nullable|string',
            'report_design_start_date' => 'nullable|date',
            'report_design_end_date' => 'nullable|date|after_or_equal:report_design_start_date',
            'report_design_responsible' => 'nullable|string',
            'design_finalization_start_date' => 'nullable|date',
            'design_finalization_end_date' => 'nullable|date|after_or_equal:design_finalization_start_date',
            'design_finalization_responsible' => 'nullable|string',
            'report_printing_start_date' => 'nullable|date',
            'report_printing_end_date' => 'nullable|date|after_or_equal:report_printing_start_date',
            'report_printing_responsible' => 'nullable|string',
            'report_dissemination_start_date' => 'nullable|date',
            'report_dissemination_end_date' => 'nullable|date|after_or_equal:report_dissemination_start_date',
            'report_dissemination_responsible' => 'nullable|string',
            'identification_and_recruitment_of_respondents_start_date' => 'nullable|date',
            'identification_and_recruitment_of_respondents_end_date' => 'nullable|date|after_or_equal:identification_and_recruitment_of_respondents_start_date',
            'identification_and_recruitment_of_respondents_responsible' => 'nullable|string',
            'transcriptions_start_date' => 'nullable|date',
            'transcriptions_end_date' => 'nullable|date|after_or_equal:transcriptions_start_date',
            'transcriptions_responsible' => 'nullable|string',
            'development_of_data_entry_framework_start_date' => 'nullable|date',
            'development_of_data_entry_framework_end_date' => 'nullable|date|after_or_equal:development_of_data_entry_framework_start_date',
            'development_of_data_entry_framework_responsible' => 'nullable|string',
            'extraction_of_data_from_secondary_sources_start_date' => 'nullable|date',
            'extraction_of_data_from_secondary_sources_end_date' => 'nullable|date|after_or_equal:extraction_of_data_from_secondary_sources_start_date',
            'extraction_of_data_from_secondary_sources_responsible' => 'nullable|string',
        ]);
        $item = RmpMethodologyDetail::query()->create($this->input);
        return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);

    }

    /**
     * Display the specified resource.
     */
    public function show(RmpMethodologyDetail $rmpMethodologyDetail): JsonResponse
    {
        return resp('1', 'Successful!', $rmpMethodologyDetail, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rmpMethodologyDetail = RmpMethodologyDetail::query()->findOrFail($id);
        $request->validate([
            'rm_methodology_id' => 'required|integer',
            'rm_plan_id' => 'required|exists:rm_plans,id',
            'research_methodology_note_start_date' => 'nullable|date',
            'research_methodology_note_end_date' => 'nullable|date|after_or_equal:research_methodology_note_start_date',
            'research_methodology_note_responsible' => 'nullable|string',
            'literature_review_start_date' => 'nullable|date',
            'literature_review_end_date' => 'nullable|date|after_or_equal:literature_review_start_date',
            'literature_review_responsible' => 'nullable|string',
            'data_collection_tool_start_date' => 'nullable|date',
            'data_collection_tool_end_date' => 'nullable|date|after_or_equal:data_collection_tool_start_date',
            'data_collection_tool_responsible' => 'nullable|string',
            'translation_of_data_collection_tool_start_date' => 'nullable|date',
            'translation_of_data_collection_tool_end_date' => 'nullable|date|after_or_equal:translation_of_data_collection_tool_start_date',
            'translation_of_data_collection_tool_responsible' => 'nullable|string',
            'data_collection_tools_online_software_scripting_start_date' => 'nullable|date',
            'data_collection_tools_online_software_scripting_end_date' => 'nullable|date|after_or_equal:data_collection_tools_online_software_scripting_start_date',
            'data_collection_tools_online_software_scripting_responsible' => 'nullable|string',
            'vetting_of_scripted_data_collection_tools_for_logic_checks_start_date' => 'nullable|date',
            'vetting_of_scripted_data_collection_tools_for_logic_checks_end_date' => 'nullable|date|after_or_equal:vetting_of_scripted_data_collection_tools_for_logic_checks_start_date',
            'vetting_of_scripted_data_collection_tools_for_logic_checks_responsible' => 'nullable|string',
            'training_of_enumerators_start_date' => 'nullable|date',
            'training_of_enumerators_end_date' => 'nullable|date|after_or_equal:training_of_enumerators_start_date',
            'training_of_enumerators_responsible' => 'nullable|string',
            'pilot_testing_of_data_collectionTool_start_date' => 'nullable|date',
            'pilot_testing_of_data_collectionTool_end_date' => 'nullable|date|after_or_equal:pilot_testing_of_data_collectionTool_start_date',
            'pilot_testing_of_data_collectionTool_responsible' => 'nullable|string',
            'refresher_training_and_feedback_start_date' => 'nullable|date',
            'refresher_training_and_feedback_end_date' => 'nullable|date|after_or_equal:refresher_training_and_feedback_start_date',
            'refresher_training_and_feedback_responsible' => 'nullable|string',
            'survey_administration_start_date' => 'nullable|date',
            'survey_administration_end_date' => 'nullable|date|after_or_equal:survey_administration_start_date',
            'survey_administration_responsible' => 'nullable|string',
            'quality_assurance_start_date' => 'nullable|date',
            'quality_assurance_end_date' => 'nullable|date|after_or_equal:quality_assurance_start_date',
            'quality_assurance_responsible' => 'nullable|string',
            'data_cleaning_start_date' => 'nullable|date',
            'data_cleaning_end_date' => 'nullable|date|after_or_equal:data_cleaning_start_date',
            'data_cleaning_responsible' => 'nullable|string',
            'data_analysis_and_interpretation_start_date' => 'nullable|date',
            'data_analysis_and_interpretation_end_date' => 'nullable|date|after_or_equal:data_analysis_and_interpretation_start_date',
            'data_analysis_and_interpretation_responsible' => 'nullable|string',
            'data_workshopping_start_date' => 'nullable|date',
            'data_workshopping_end_date' => 'nullable|date|after_or_equal:data_workshopping_start_date',
            'data_workshopping_responsible' => 'nullable|string',
            'report_drafting_and_finalization_start_date' => 'nullable|date',
            'report_drafting_and_finalization_end_date' => 'nullable|date|after_or_equal:report_drafting_and_finalization_start_date',
            'report_drafting_and_finalization_responsible' => 'nullable|string',
            'report_design_start_date' => 'nullable|date',
            'report_design_end_date' => 'nullable|date|after_or_equal:report_design_start_date',
            'report_design_responsible' => 'nullable|string',
            'design_finalization_start_date' => 'nullable|date',
            'design_finalization_end_date' => 'nullable|date|after_or_equal:design_finalization_start_date',
            'design_finalization_responsible' => 'nullable|string',
            'report_printing_start_date' => 'nullable|date',
            'report_printing_end_date' => 'nullable|date|after_or_equal:report_printing_start_date',
            'report_printing_responsible' => 'nullable|string',
            'report_dissemination_start_date' => 'nullable|date',
            'report_dissemination_end_date' => 'nullable|date|after_or_equal:report_dissemination_start_date',
            'report_dissemination_responsible' => 'nullable|string',
            'identification_and_recruitment_of_respondents_start_date' => 'nullable|date',
            'identification_and_recruitment_of_respondents_end_date' => 'nullable|date|after_or_equal:identification_and_recruitment_of_respondents_start_date',
            'identification_and_recruitment_of_respondents_responsible' => 'nullable|string',
            'transcriptions_start_date' => 'nullable|date',
            'transcriptions_end_date' => 'nullable|date|after_or_equal:transcriptions_start_date',
            'transcriptions_responsible' => 'nullable|string',
            'development_of_data_entry_framework_start_date' => 'nullable|date',
            'development_of_data_entry_framework_end_date' => 'nullable|date|after_or_equal:development_of_data_entry_framework_start_date',
            'development_of_data_entry_framework_responsible' => 'nullable|string',
            'extraction_of_data_from_secondary_sources_start_date' => 'nullable|date',
            'extraction_of_data_from_secondary_sources_end_date' => 'nullable|date|after_or_equal:extraction_of_data_from_secondary_sources_start_date',
            'extraction_of_data_from_secondary_sources_responsible' => 'nullable|string',
        ]);
        $item = $rmpMethodologyDetail->update($this->input);
        return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RmpMethodologyDetail $rmpMethodologyDetail)
    {
        $rmpMethodologyDetail->delete();
        return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
    }
    public function getRMDropDown(){
        $data['rm_data_source']= Type::getTypeValues('rm-data-source');
        $data['rm_data_availability']= Type::getTypeValues('rm-data-availability');
        $data['rm_methodology']= RmMethodology::cases();
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    public function getMethodologyDetailsByRMPId($id)
    {
        $this->authorizeAny([
            'research_plan_calendar',
            'manage_audit_research_delivery_unit',
        ]);

        $data = RmPlan::query()->with('methodologyDetail.rMResponsibleNote')->findOrFail($id);
        $data['approval_request']=getNextApproval(22,auth()->user()->designation_id,$id);
        $data['approval_request_status']=checkApprovalRequestStatus(22,$id);
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }
}

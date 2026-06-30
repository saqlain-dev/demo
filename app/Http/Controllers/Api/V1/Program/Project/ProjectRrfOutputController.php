<?php

namespace App\Http\Controllers\Api\V1\Program\Project;

use App\Http\Controllers\Controller;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Program\Project\ProjectRrfGoal;
use App\Models\Program\Project\ProjectRrfOutcome;
use App\Models\Program\Project\ProjectRrfOutcomeIndicator;
use App\Models\Program\Project\ProjectRrfOutcomeIndicatorTarget;
use App\Models\Program\Project\ProjectRrfOutput;
use App\Models\Program\Project\ProjectRrfOutputIndicator;
use App\Models\Program\Project\ProjectRrfOutputIndicatorTarget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;


class ProjectRrfOutputController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('project_rrf_view');

        $data = ProjectRrfOutput::with(['ProOutputIndicators','project_rrf_goal', 'project_rrf_outcome'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('project_rrf_create');

        try {
            DB::beginTransaction();
            $request->validate([
                'project_id' => 'required',
                'project_rrf_goal_id' => 'required',
                'project_rrf_outcome_id' => 'required',
                'output_number' => 'required',
                'output_statement' => 'required',
                'las_sp_statement' => 'required',
                'las_rrf_output_id' => 'required',
                'output_indicator.*' => 'required',
            ]);
            $outputIndicators = $this->input['output_indicator'];
            unset($this->input['output_indicator']);

            $item = ProjectRrfOutput::query()->create($this->input);
//            if ($item) {
//                $item->ProOutputIndicators()->createMany($outputIndicators);
//                $item->load('ProOutputIndicators');
//            }

            if ($item) {
                if (!empty($outputIndicators)){
                    foreach ($outputIndicators as $ind){
                        $ind['proj_rrf_output_id'] = $item->id;
                        $target = $ind['target'];
                        unset($ind['target']);
                        //dd($ind);
                        $res = ProjectRrfOutputIndicator::query()->create($ind);
                        if ($res){
                            $res->proOutputIndicatorTargets()->createMany($target);
                        }
                    }
                }
                //$item->ProGoalIndicators()->createMany($goalIndicators);
                $item->load('ProOutputIndicators.proOutputIndicatorTargets');
            }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
 }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $this->authorize('project_rrf_view');

        $responce = ProjectRrfOutput::with('ProOutputIndicators.proOutputIndicatorTargets')->findOrFail($id);
        return resp('1', 'Successful!', $responce, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectRrfOutput $output)
    {
        $this->authorize('project_rrf_update');

        try {
            DB::beginTransaction();

            $request->validate([
                'project_id' => 'required',
                'project_rrf_goal_id' => 'required',
                'project_rrf_outcome_id' => 'required',
                'output_number' => 'required',
                'output_statement' => 'required',
                'las_sp_statement' => 'required',
                'las_rrf_output_id' => 'required',
                'output_indicator.*' => 'required',
            ]);

            $outputIndicators = $this->input['output_indicator'];
            unset($this->input['output_indicator']);
            $item = $output->update($this->input);

            foreach ($outputIndicators as $indicator) {
                $targets = $indicator['target'];
                unset($indicator['target']);
                $res = ProjectRrfOutputIndicator::query()->updateOrCreate(['id' => $indicator['id']], $indicator);
                if ($res){
                    foreach ($targets as $target){
                        ProjectRrfOutputIndicatorTarget::query()->updateOrCreate(['id' => $target['id']], $target);
                    }
                }
            }
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectRrfOutput $output)
    {
        $this->authorize('project_rrf_delete');

        //$item = $output->delete();
        $output->delete();
        $output->ProOutputIndicators()->delete();
        //$output->ProOutcomeIndicators()->delete();
        return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
    }

    public function addProjOutputIndicators(Request $request)
    {
        $this->authorize('project_rrf_create');

        try {
            DB::beginTransaction();

            $request->validate([
                'proj_rrf_output_id' => 'required',
                'sp_id' => 'required',
                'las_rrf_output_id' => 'required',
                'sp_indicator_id' => 'required',
                'output_indicator_number' => 'required',
                'baseline' => 'required',
                'lop_target' => 'required',
                'target.*' => 'required',
                'output_indicator_statement' => 'required'
            ]);
            $target = $this->input['target'];
            unset($this->input['target']);
            $item = ProjectRrfOutputIndicator::query()->create($this->input);
            if ($item){
                $item->proOutputIndicatorTargets()->createMany($target);
            }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function updateProjOutputIndicators(Request $request)
    {
        $this->authorize('project_rrf_update');

        try {
            DB::beginTransaction();
            $request->validate([
                'id' => 'required',
                //'proj_rrf_goal_id' => 'required',
                //'las_rrf_goal_id' => 'required',
                'sp_id' => 'required',
                'sp_indicator_id' => 'required',
                'output_indicator_number' => 'required',
                'baseline' => 'required',
                'lop_target' => 'required',
                'target.*' => 'required',
                'output_indicator_statement' => 'required'
            ]);
            $targets = $this->input['target'];
            unset($this->input['target']);
            $item = ProjectRrfOutputIndicator::query()->updateOrCreate(['id' => $this->input['id']], $this->input);
            if ($item){
                foreach ($targets as $target){
                    ProjectRrfOutputIndicatorTarget::query()->updateOrCreate(['id' => $target['id']], $target);
                }
            }
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function deleteProjOutputIndicators($id)
    {
        $this->authorize('project_rrf_delete');

        $item = ProjectRrfOutputIndicator::query()->findOrFail($id)->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function getOutputByOutcomeId($outcomeId)
    {
        $this->authorize('project_rrf_view');

        $outputs = ProjectRrfOutput::query()->with('ProOutputIndicators.proOutputIndicatorTargets')->where('project_rrf_outcome_id', $outcomeId)->get();
        return resp('1', 'Successful!', $outputs, Response::HTTP_OK);
    }
    public function getProjectOutcomeOutputByProjectId($pid)
    {
        $this->authorizeAny([
            'project_rrf_view',
            'manage_audit_program_planning',
        ]);

        $res = array();
        $res['ProjectDetail'] = ProjectProfile::query()->with(['thematic_area','status','project_manager','pdu_focal_person','implementing_partner','created_by','updated_by','donor.donorDetail'])->findOrFail($pid);
        $res['ProjectGoals'] = ProjectRrfGoal::query()->with(['lasRrfGoalId','lasSpDetail','ProGoalIndicators' => ['SpIndicatorId', 'lasRrfGoalIndicatorId','KpiMappedIndicators','proGoalIndicatorTargets']])->where('project_id', $pid)->get();
        $res['ProjectOutcomes'] = ProjectRrfOutcome::query()->with(['lasRrfOutcomeId','lasSpDetail','project_rrf_goal','ProOutcomeIndicators' => ['SpIndicatorId', 'lasRrfOutcomeIndicatorId', 'KpiMappedIndicators','proOutcomeIndicatorTargets']])->where('project_id', $pid)->get();
        $res['ProjectOutputs'] = ProjectRrfOutput::query()->with(['lasRrfOutputId','lasSpDetail','project_rrf_goal','project_rrf_outcome','ProOutputIndicators' => ['SpIndicatorId', 'lasRrfOutputIndicatorId', 'KpiMappedIndicators','proOutputIndicatorTargets']])->where('project_id', $pid)->get();
        $res['approval_request']=getNextApproval(6,auth()->user()->designation_id,$pid);
        $res['approval_request_status']=checkApprovalRequestStatus(6,$pid);
        return resp('1', 'Successful!', $res, Response::HTTP_OK);
    }

    public function projectProfileWorkPlanView($pid)
    {
        $this->authorizeAny([
            'project_rrf_view',
            'manage_audit_program_planning',
        ]);

        $res = array();
        $res['ProjectDetail'] = ProjectProfile::query()->with(['thematic_area','status','project_manager','pdu_focal_person','implementing_partner','created_by','updated_by','donor.donorDetail'])->findOrFail($pid);
        $res['ProjectGoals'] = ProjectRrfGoal::query()->with(['lasSpDetail','ProGoalIndicators' => ['SpIndicatorId','proWorkPlanIndicators','KpiMappedIndicators','proGoalIndicatorTargets']])->where('project_id', $pid)->get();
        $res['ProjectOutcomes'] = ProjectRrfOutcome::query()->with(['lasSpDetail','project_rrf_goal','ProOutcomeIndicators' => ['SpIndicatorId','KpiMappedIndicators','proWorkPlanIndicators','proOutcomeIndicatorTargets']])->where('project_id', $pid)->get();
        $res['ProjectOutputs'] = ProjectRrfOutput::query()->with(['lasSpDetail','project_rrf_goal','project_rrf_outcome','ProOutputIndicators' => ['SpIndicatorId','proWorkPlanIndicators','KpiMappedIndicators','proOutputIndicatorTargets']])->where('project_id', $pid)->get();
        $res['approval_request']=getNextApproval(6,auth()->user()->designation_id,$pid);
        $res['approval_request_status']=checkApprovalRequestStatus(6,$pid);
        return resp('1', 'Successful!', $res, Response::HTTP_OK);
    }
}

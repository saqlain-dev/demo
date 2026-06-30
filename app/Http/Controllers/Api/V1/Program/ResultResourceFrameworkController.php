<?php

namespace App\Http\Controllers\Api\V1\Program;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Program\LasRrfGoalIndicator;
use App\Models\Program\LasRrfGoalIndicatorTarget;
use App\Models\Program\LasRrfOutcomeIndicator;
use App\Models\Program\LasRrfOutcomeIndicatorTarget;
use App\Models\Program\LasRrfOutputIndicator;
use App\Models\Program\LasRrfOutputIndicatorTarget;
use App\Models\Program\ResultResourceFramework;
use App\Models\Program\ResultResourceFrameworkOutcome;
use App\Models\Program\ResultResourceFrameworkOutput;
use App\Models\StrategicPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ResultResourceFrameworkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $this->authorizeAny([
            'las_rrf_view',
            'manage_audit_program_planning',
        ]);

        $data = ResultResourceFramework::with(['lasSpDetail','goalIndicators.goalIndicatorTargets'])->orderByDesc('id')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('las_rrf_create');

        try {
            DB::beginTransaction();
            $request->validate([
                'goal_number' => 'required',
                'goal_statement' => 'required',
                'las_sp_statement' => 'required',
                'goal_indicator.*' => 'required',
            ]);
            $goalIndicators = $this->input['goal_indicator'];

            unset($this->input['goal_indicator']);

            $item = ResultResourceFramework::query()->create($this->input);
            if ($item) {

                if (!empty($goalIndicators)){
                    foreach ($goalIndicators as $ind){
                        $ind['rrf_goal_id'] = $item->id;
                        $target = $ind['target'];
                        unset($ind['target']);
                        //dd($ind);
                        $res = LasRrfGoalIndicator::query()->create($ind);
                        if ($res){
                            $res->goalIndicatorTargets()->createMany($target);
                        }
                    }
                }
                //$item->goalIndicators()->createMany($goalIndicators);
                $item->load('goalIndicators.goalIndicatorTargets');
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
    public function show(string $id)
    {
        $this->authorizeAny([
            'las_rrf_view',
            'manage_audit_program_planning',
        ]);

        $data['responce'] = ResultResourceFramework::with(['lasSpDetail','goalIndicators.goalIndicatorTargets'])->findOrFail($id);

        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ResultResourceFramework $resultResourceFramework): JsonResponse
    {
        $this->authorize('las_rrf_udpate');

        try {
            DB::beginTransaction();

            $request->validate([
                'goal_number' => 'required',
                'goal_statement' => 'required',
                'las_sp_statement' => 'required',
                'goal_indicator.*' => 'required',
            ]);

            $goalIndicators = $this->input['goal_indicator'];
            unset($this->input['goal_indicator']);

            $item = $resultResourceFramework->update($this->input);

            foreach ($goalIndicators as $indicator) {
                $targets = $indicator['target'];
                unset($indicator['target']);
                $res = LasRrfGoalIndicator::query()->updateOrCreate(['id' => $indicator['id']], $indicator);
                if ($res){
                    foreach ($targets as $target){
                        LasRrfGoalIndicatorTarget::query()->updateOrCreate(['id' => $target['id']], $target);
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
//    public function destroy(ResultResourceFramework $resultResourceFramework): JsonResponse
//    {
//
//        $resultResourceFramework->delete();
//        $resultResourceFramework->goalIndicators()->delete();
//        return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
//
//    }

    public function destroy(ResultResourceFramework $resultResourceFramework): JsonResponse
    {
        $this->authorize('las_rrf_delete');

        $rrfOutcomesExist = $resultResourceFramework->rrf_outcomes()->exists();
        $rrfOutputsExist = $resultResourceFramework->rrf_outputs()->exists();

        // Check if any related outcomes or outputs exist
        if ($rrfOutcomesExist) {
            return resp('0', 'Record Cannot be Deleted! RRF Outcomes Exist', false, Response::HTTP_OK);
        }

        if ($rrfOutputsExist) {
            return resp('0', 'Record Cannot be Deleted! RRF Outputs Exist', false, Response::HTTP_OK);
        }

        try {
            DB::beginTransaction();

            // Delete related goal indicators
            $resultResourceFramework->goalIndicators()->delete();

            // Delete the result resource framework
            $resultResourceFramework->delete();

            DB::commit();
            return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to delete record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }



    public function addGoalIndicators(Request $request)
    {
        $this->authorize('las_rrf_create');

        try {
            DB::beginTransaction();

            $request->validate([
                'rrf_goal_id' => 'required',
                'sp_id' => 'required',
                'sp_indicator_id' => 'required',
                'goal_indicator_number' => 'required',
                'baseline' => 'required',
                'lop_target' => 'required',
                'goal_indicator_statement' => 'required'
            ]);
            $target = $this->input['target'];
            unset($this->input['target']);
            $item = LasRrfGoalIndicator::query()->create($this->input);
            if ($item){
                $item->goalIndicatorTargets()->createMany($target);
            }

            DB::commit();

            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollback();
            // Log the error or handle it as needed
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);

        }
    }

    public function updateGoalIndicators(Request $request)
    {
        $this->authorize('las_rrf_update');

        try {
            DB::beginTransaction();

            $request->validate([
                'id' => 'required',
                //'rrf_goal_id' => 'required',
                'sp_id' => 'required',
                'sp_indicator_id' => 'required',
                'goal_indicator_number' => 'required',
                'baseline' => 'required',
                'lop_target' => 'required',
                'yearly_target' => 'required',
                'goal_indicator_statement' => 'required'
            ]);
            $targets = $this->input['target'];
            unset($this->input['target']);
            $item = LasRrfGoalIndicator::query()->updateOrCreate(['id' => $this->input['id']], $this->input);
            if ($item){
                foreach ($targets as $target){
                    LasRrfGoalIndicatorTarget::query()->updateOrCreate(['id' => $target['id']], $target);
                }
            }
            DB::commit();

            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollback();

            // Log the error or handle it as needed
            return resp(0, 'Failed to Update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

//    public function deleteGoalIndicators($id)
//    {
//        $outcomeIndicator = LasRrfOutcomeIndicator::query()->where('las_goal_indicator_id',$id)->count();
//
//        $item = LasRrfGoalIndicator::query()->findOrFail($id)->delete();
//        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
//    }

    public function deleteGoalIndicators($id)
    {
        $this->authorize('las_rrf_delete');

        // Check if any related LasRrfOutcomeIndicator records exist
        $outcomeIndicatorCount = LasRrfOutcomeIndicator::where('las_goal_indicator_id', $id)->count();

        if ($outcomeIndicatorCount > 0) {
            return resp('1', 'Record Cannot be Deleted! Outcome Indicators Exist', false, Response::HTTP_OK);
        }

        $outputIndicatorCount = LasRrfOutputIndicator::where('las_goal_indicator_id', $id)->count();

        if ($outputIndicatorCount > 0) {
            return resp('1', 'Record Cannot be Deleted! Output Indicators Exist', false, Response::HTTP_OK);
        }

        try {
            DB::beginTransaction();

            // Find and delete the LasRrfGoalIndicator record
            $item = LasRrfGoalIndicator::findOrFail($id);
            $item->delete();

            DB::commit();
            return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to delete record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function getRrfBySpId($spId)
    {
        $this->authorizeAny([
            'las_rrf_view',
            'manage_audit_program_planning',
        ]);

        $data['las_rrf'] = ResultResourceFramework::with(['sPDetail','comments.createdBy','goalIndicators'=>['SpIndicatorId','goalIndicatorTargets'],'rrf_outcomes'=>['outcomeIndicators'=>['SpIndicatorId','outcomeIndicatorsTarget','lasGoalIndicator'], 'sPDetail','lasGoal'],'rrf_outputs'=>['outputIndicators'=>['SpIndicatorId','outputIndicatorsTarget','lasGoalIndicator'],'sPDetail','lasGoal']])->where('las_sp_statement', $spId)->get();
        $data['approval_request']=getNextApproval(3,auth()->user()->designation_id,$spId);
        $data['approval_request_status']=checkApprovalRequestStatus(3,$spId);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function getSpOutcomeOutputbySpId($id){
        $this->authorizeAny([
            'las_rrf_view',
            'manage_audit_program_planning',
        ]);

        $res = array();
        $strategicPlan = StrategicPlan::with('pillars.indicators')->findOrFail($id);
        $res['spIndicator'] = $strategicPlan->pillars->flatMap->indicators;

        $res['spOutcome'] = ResultResourceFrameworkOutcome::with(['outcomeIndicators.outcomeIndicatorsTarget'])->where('sp_statement', $id)->get();
        $res['spOutput'] = ResultResourceFrameworkOutput::with(['outputIndicators.outputIndicatorsTarget'])->where('sp_statement', $id)->get();
        return resp('1', 'Successful!', $res, Response::HTTP_OK);

    }
    public function sendLasRRFForApproval(StrategicPlan $item)
    {
        $this->authorize('las_rrf_update');

        $approval_process=ApprovalProcess::query()->where('approval_process_id',3)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',3)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){
            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                $Approval=ApprovalProcessList::query()->create($insert);
            }
            $update=array('las_rrf_approval'=>2);
            StrategicPlan::query()->where('id',$item->id)->update($update);
            return resp(1,'LAS RRF send for Approval.', $Approval,Response::HTTP_OK);
        }else{
            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'LAS RRF approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function updateLasRrfIndicatorProgress(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'progress' => 'required|array',
            'progress.*.id' => 'required|integer',
            'progress.*.progress_value' => 'required|numeric',
        ]);
        try {
            DB::beginTransaction();
            $type=$request->type;
            $progress=$request->progress;
            if ($type === 'goal' && is_array($progress)) {
                foreach ($progress as $prog) {
                    if (isset($prog['id'], $prog['progress_value'])) {
                        LasRrfGoalIndicatorTarget::where('id', $prog['id'])->update(['progress' => $prog['progress_value']]);
                    }
                }
            }
            if ($type === 'outcome' && is_array($progress)) {
                foreach ($progress as $prog) {
                    if (isset($prog['id'], $prog['progress_value'])) {
                        LasRrfOutcomeIndicatorTarget::where('id', $prog['id'])->update(['progress' => $prog['progress_value']]);
                    }
                }
            }
            if ($type === 'output' && is_array($progress)) {
                foreach ($progress as $prog) {
                    if (isset($prog['id'], $prog['progress_value'])) {
                        LasRrfOutputIndicatorTarget::where('id', $prog['id'])->update(['progress' => $prog['progress_value']]);
                    }
                }
            }
            DB::commit();
            return resp('1', 'Progress Updated Successfully!', [], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

}

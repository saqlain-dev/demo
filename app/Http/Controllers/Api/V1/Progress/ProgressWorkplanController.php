<?php

namespace App\Http\Controllers\Api\V1\Progress;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Program\Project\ProjectRrfOutcome;
use App\Models\Program\Project\ProjectRrfOutput;
use App\Models\Progress\ProgressWorkplan;
use App\Models\Progress\ProgressWorkplanGoals;
use App\Models\Progress\ProgressWorkplanOutcome;
use App\Models\Progress\ProgressWorkplanOutput;
use App\Models\Type;
use App\Models\TypeValue;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProgressWorkplanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'work_plan',
            'manage_audit_program_progress',
            'manage_audit_program_reports',
        ]);

        $data['work_plan_list'] =$work_plan_list= ProgressWorkplan::with(['workPlanGoals'=>['GoalId','GoalIndicatorId','activities'],'workPlanOutcome'=>['OutcomeId','OutcomeIndicatorId','activities'],'workPlanOutput'=>['OutputId','OutputIndicatorId','activities'],'CreatedBy','UpdatedBy'])->orderByDesc('id')->get();
        $data['draft']=$work_plan_list->where('status',4)->count();
        $data['pending']=$work_plan_list->where('status',2)->count();
        $data['approved']=$work_plan_list->where('status',1)->count();
        $data['reject']=$work_plan_list->where('status',3)->count();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('work_plan');

        //dd($request);
        $request->validate([
            'project_id' => 'required',
            'project_workplan' => 'required',
//            'workplan_goals.*' => 'required',

        ]);

        try {
            DB::beginTransaction();
            $item = ProgressWorkplan::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }


        //$workplanGoals = $this->input['workplan_goals'];
        //dd($workplanGoals['activities']);
        //$goalAcitivies = $workplanGoals['activities'];
        //unset($workplanGoals['activities']);
        //unset($this->input['workplan_goals']);

//        if ($item){
//            if (!empty($workplanGoals)){
//                $workplanGoals['progress_workplan_id'] = $item['id'];
//                $goalItem = ProgressWorkplanGoals::query()->create($workplanGoals);
//                foreach ($goalAcitivies as $row){
//                    $activity = new Activity();
//                    $activity->name = $row['name'];
//                    $activity->activityable()->associate($goalItem);
//                    $activity->save();
//                }
//            }
//
//        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $wpid): JsonResponse
    {
        $this->authorizeAny([
            'work_plan',
            'manage_audit_program_progress',
        ]);

        //$progressWorkplan = ProgressWorkplanGoals::find(2);



        $progressWorkplan = ProgressWorkplan::with([
            'workPlanGoals' => ['GoalId', 'GoalIndicatorId', 'GoalStatus', 'activities'],
            'workPlanOutcome' => ['OutcomeId', 'OutcomeIndicatorId', 'OutcomeStatus', 'activities'],
            'workPlanOutput' => ['OutputId', 'OutputIndicatorId', 'OutputStatus', 'activities'],
            'CreatedBy',
            'UpdatedBy'
        ])->find($wpid);

        $progressWorkplan->approval_request=getNextApproval(7,auth()->user()->designation_id,$wpid);

        $progressWorkplan->approval_request_status=checkApprovalRequestStatus(7,$wpid);

        return resp('1', 'Successful!', $progressWorkplan, Response::HTTP_OK);
    }

    public function getWorkplanbyWkplid($wkpid)
    {
        $this->authorize('work_plan');

        $data['workplan'] = ProgressWorkplan::find($wkpid);
        $projectId = $data['workplan']->project_id;
        $data['projectDetail'] = ProjectProfile::with(['donor.donorDetail',
            'projectGoals' =>
                [   'lasSpDetail',
                    'ProGoalIndicators' =>['proWorkPlanIndicators.GoalStatus'],
                    'projectOutcomes' => [
                        'ProOutcomeIndicators' => ['proWorkPlanIndicators.OutcomeStatus'],
                        'projectOutputs' => ['ProOutputIndicators' => ['proWorkPlanIndicators.OutputStatus'],]
                    ]
                ],
        ])->findOrFail($projectId);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProgressWorkplan $progressWorkplan)
    {
        $this->authorize('work_plan');

        $request->validate([
            'project_id' => 'required',
            'project_workplan' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = ProgressWorkplan::query()->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProgressWorkplan $progressWorkplan): JsonResponse
    {
        $this->authorize('work_plan');

        $progressWorkplan->workPlanGoals->activities()->delete();
        $progressWorkplan->workPlanGoals()->delete();
        $progressWorkplan->workPlanOutcome->activities()->delete();
        $progressWorkplan->workPlanOutcome()->delete();
        $progressWorkplan->workPlanOutput->activities()->delete();
        $progressWorkplan->workPlanOutput()->delete();
        $progressWorkplan->delete();
        return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
    }

    public function getWorkPlanGoalByWorkplanId(Request $request, $wpid)
    {
        $this->authorize('work_plan');

        $data = ProgressWorkplanGoals::with('activities')->where('progress_workplan_id', $wpid)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function getWorkPlanGoalById(ProgressWorkplanGoals $ProgressWorkplanGoals)
    {
        $this->authorize('work_plan');

        $data = $ProgressWorkplanGoals->load('activities');
        //dd($data);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function addWorkPlanGoal(Request $request)
    {
        $this->authorize('work_plan');

        $request->validate([
            'project_id' => 'required',
            'progress_workplan_id' => 'required',
            'goal_id' => 'required',
            'goal_indicator_id' => 'required',
            'goal_quarterly_target' => 'required',
            'goal_progress' => 'required',
            //'goal_budget_allocated' => 'required',
            //'goal_budget_spent' => 'required',
            'goal_movs_ids' => 'required',
            'goal_status' => 'required',
            'goal_timeline_for_indicators' => 'required',
        ]);
        //$goalAcitivies = $this->input['activities'];
        //unset($this->input['activities']);
        try {
            DB::beginTransaction();
            $item = ProgressWorkplanGoals::query()->create($this->input);
//            if ($item){
//            foreach ($goalAcitivies as $row){
//                $activity = new Activity();
//                $activity->name = $row['name'];
//                $activity->activity_cat = $row['activity_cat'];
//                $activity->activityable()->associate($item);
//                $activity->save();
//            }
//        }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function updateWorkPlanGoal(Request $request, ProgressWorkplanGoals $ProgressWorkplanGoals)
    {
        $this->authorize('work_plan');

        $request->validate([
            //'id' => 'required',
            //'project_id' => 'required',
            'progress_workplan_id' => 'required',
            'goal_id' => 'required',
            'goal_indicator_id' => 'required',
            'goal_quarterly_target' => 'required',
            'goal_progress' => 'required',
            'goal_budget_allocated' => 'required',
            'goal_budget_spent' => 'required',
            'goal_movs_ids' => 'required',
            'goal_status' => 'required',
            'goal_timeline_for_indicators' => 'required',
        ]);

        //$goalAcitivies = $this->input['activities'];
        //unset($this->input['activities']);
        try {
            DB::beginTransaction();
            $item = $ProgressWorkplanGoals->update($this->input);
            //        if ($item){
//            foreach ($goalAcitivies as $row){
//                $activity = new Activity();
//                $activity->name = $row['name'];
//                $activity->activityable()->associate($item);
//                $activity->save();
//            }
//        }
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function deleteWorkplanGoal($id)
    {
        $this->authorize('work_plan');

        $item = ProgressWorkplanGoals::query()->findOrFail($id);
        $item->activities()->delete();
        $item->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function getWorkPlanOutcomesbyWkpid(Request $request, $wkpid)
    {
        $this->authorize('work_plan');

        //$data = ProgressWorkplanOutcome::with('activities')->where('progress_workplan_id', $wpid)->where('goal_id', $goalid)->get();
        //return resp('1', 'Successful!', $data, Response::HTTP_OK);

        $data['workplan'] = ProgressWorkplan::find($wkpid);
        $projectId = $data['workplan']->project_id;
        $data['projectOutcomeDetail'] = ProjectRrfOutcome::with([
                        'ProOutcomeIndicators' => ['proWorkPlanIndicators.OutcomeStatus'],
                        'lasSpDetail',
                        'project_rrf_goal'
        ])->where('project_id',$projectId)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function getWorkPlanOutcomesByGoal(Request $request, $wpid, $goalid)
    {
        $this->authorize('work_plan');

        $data = ProgressWorkplanOutcome::with('activities')->where('progress_workplan_id', $wpid)->where('goal_id', $goalid)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function getWorkPlanOutcomeByid(ProgressWorkplanOutcome $ProgressWorkplanOutcome)
    {
        $this->authorize('work_plan');

        $data = $ProgressWorkplanOutcome->load('activities');
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function addWorkPlanOutcomes(Request $request)
    {
        $this->authorize('work_plan');

        $request->validate([
            'project_id' => 'required',
            'progress_workplan_id' => 'required',
            'goal_id' => 'required',
            'outcome_id' => 'required',
            'outcome_indicator_id' => 'required',
            'outcome_quarterly_target' => 'required',
            'outcome_progress' => 'required',
            //'outcome_budget_allocated' => 'required',
            //'outcome_budget_spent' => 'required',
            'outcome_movs_ids' => 'required',
            'outcome_status' => 'required',
            'outcome_timeline_for_indicators' => 'required',
        ]);

        //$outcomeAcitivies = $this->input['activities'];
        //unset($this->input['activities']);
        try {
            DB::beginTransaction();
            $item = ProgressWorkplanOutcome::query()->create($this->input);
            //        if ($item){
//            foreach ($outcomeAcitivies as $row){
//                $activity = new Activity();
//                $activity->activity_cat = $row['activity_cat'];
//                $activity->name = $row['name'];
//                $activity->activityable()->associate($item);
//                $activity->save();
//            }
//        }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    public function updateWorkPlanOutcome(Request $request, ProgressWorkplanOutcome $ProgressWorkplanOutcome)
    {
        $this->authorize('work_plan');

        $request->validate([
            //'id' => 'required',
            //'project_id' => 'required',
            'progress_workplan_id' => 'required',
            'goal_id' => 'required',
            'outcome_id' => 'required',
            'outcome_indicator_id' => 'required',
            'outcome_quarterly_target' => 'required',
            'outcome_progress' => 'required',
            'outcome_budget_allocated' => 'required',
            'outcome_budget_spent' => 'required',
            'outcome_movs_ids' => 'required',
            'outcome_status' => 'required',
            'outcome_timeline_for_indicators' => 'required',
        ]);

        //$goalAcitivies = $this->input['activities'];
        //unset($this->input['activities']);

        try {
            DB::beginTransaction();
            $item = $ProgressWorkplanOutcome->update($this->input);            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

//        if ($item){
//            foreach ($goalAcitivies as $row){
//                $activity = new Activity();
//                $activity->name = $row['name'];
//                $activity->activityable()->associate($item);
//                $activity->save();
//            }
//        }
    }

    public function deleteWorkplanOutcome($id)
    {
        $this->authorize('work_plan');

        $item = ProgressWorkplanOutcome::query()->findOrFail($id);
        $item->activities()->delete();
        $item->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function getWorkPlanOutputByOutcome(Request $request, $wpid, $outcomeid)
    {
        $data = ProgressWorkplanOutput::with('activities')->where('progress_workplan_id', $wpid)->where('outcome_id', $outcomeid)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function getWorkPlanOutputByWkpid(Request $request, $wkpid)
    {
        $this->authorize('work_plan');

        //$data = ProgressWorkplanOutcome::with('activities')->where('progress_workplan_id', $wpid)->where('goal_id', $goalid)->get();
        //return resp('1', 'Successful!', $data, Response::HTTP_OK);

        $data['workplan'] = ProgressWorkplan::find($wkpid);
        $projectId = $data['workplan']->project_id;
        $data['projectOutputDetail'] = ProjectRrfOutput::with([ 'ProOutputIndicators' => ['proWorkPlanIndicators' =>['OutputStatus','activities']],'lasSpDetail','project_rrf_outcome'
        ])->where('project_id',$projectId)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function getWorkPlanOutputByid(ProgressWorkplanOutput $ProgressWorkplanOutput)
    {
        $this->authorize('work_plan');

        $data = $ProgressWorkplanOutput->load('activities');
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function addWorkPlanOutputs(Request $request)
    {
        $this->authorize('work_plan');

        $request->validate([
            'project_id' => 'required',
            'progress_workplan_id' => 'required',
            'goal_id' => 'required',
            'outcome_id' => 'required',
            'output_id' => 'required',
            'output_indicator_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'target' => 'required',
            'output_progress' => 'required',
            //'outcome_budget_allocated' => 'required',
            //'outcome_budget_spent' => 'required',
            'output_movs_ids' => 'required',
            'output_status' => 'required',
            'output_timeline_for_indicators' => 'required',
        ]);
        $this->input['output_timeline_for_indicators'] = json_encode($this->input['output_timeline_for_indicators']);
        $this->input['target'] = json_encode($this->input['target']);
        $outputAcitivies = $this->input['activities'];
        unset($this->input['activities']);
        try {
            DB::beginTransaction();
            $item = ProgressWorkplanOutput::query()->create($this->input);
            if ($item){
                foreach ($outputAcitivies as $row){
                    $activity = new Activity();
                    $activity->activity_cat = $row['activity_cat'];
                    $activity->name = $row['name'];
                    $activity->activityable()->associate($item);
                    $activity->save();
                }
            }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function updateWorkPlanOutput(Request $request, ProgressWorkplanOutput $ProgressWorkplanOutput)
    {
        $this->authorize('work_plan');

        $request->validate([
            //'id' => 'required',
            //'project_id' => 'required',
            'progress_workplan_id' => 'required',
            'goal_id' => 'required',
            'outcome_id' => 'required',
            'output_id' => 'required',
            'output_indicator_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'target' => 'required',
            'output_progress' => 'required',
            'output_budget_allocated' => 'required',
            'output_budget_spent' => 'required',
            'output_movs_ids' => 'required',
            'output_status' => 'required',
            'output_timeline_for_indicators' => 'required',
        ]);

        $this->input['output_timeline_for_indicators'] = json_encode($this->input['output_timeline_for_indicators']);
        $this->input['target'] = json_encode($this->input['target']);

        //$goalAcitivies = $this->input['activities'];
        //unset($this->input['activities']);

        try {
            DB::beginTransaction();
            $item = $ProgressWorkplanOutput->update($this->input);
            DB::commit();
            return resp('1', 'Record updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
//        if ($item){
//            foreach ($goalAcitivies as $row){
//                $activity = new Activity();
//                $activity->name = $row['name'];
//                $activity->activityable()->associate($item);
//                $activity->save();
//            }
//        }
    }

    public function deleteWorkplanOutput($id)
    {
        $this->authorize('work_plan');

        $item = ProgressWorkplanOutput::query()->findOrFail($id);
        $item->activities()->delete();
        $item->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
    public function getDropDowns(): JsonResponse
    {
        $this->authorize('work_plan');

        $data['projects'] = ProjectProfile::leftJoin('progress_workplans', 'project_profiles.id', '=', 'progress_workplans.project_id')
            ->whereNull('progress_workplans.project_id')->where('project_profiles.project_rrf_approval',1)
            ->get(['project_profiles.*']);
        $data['activity_categories'] = Type::getTypeValues('activity-categories');
        $data['workplan-statuses'] = Type::getTypeValues('project-workplan-status');
        $data['project-movs'] = Type::getTypeValues('project-movs');
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function getMovsNameByType(Request $request): JsonResponse
    {
        $this->authorize('work_plan');

        $request->validate([
            'type' => 'required',
            'type_id' => 'required',
        ]);
        $type = $this->input['type'];
        $type_id = $this->input['type_id'];
        $movnames = array();
        if ($type == 1){
            $item = ProgressWorkplanGoals::query()->findOrFail($type_id);
            $movs = explode(',', $item->goal_movs_ids);
            foreach ($movs as $key => $mov){
                $movnames[$key]['progress_workplan_id'] = $item->progress_workplan_id;
                $movnames[$key]['id'] = $mov;
                $movnames[$key]['name'] = getAnyTablefieldName('type_values',$mov,'name');
            }
        }else if ($type == 2){
            $item = ProgressWorkplanOutcome::query()->findOrFail($type_id);
            $movs = explode(',', $item->outcome_movs_ids);
            foreach ($movs as $key => $mov){
                $movnames[$key]['progress_workplan_id'] = $item->progress_workplan_id;
                $movnames[$key]['id'] = $mov;
                $movnames[$key]['name'] = getAnyTablefieldName('type_values',$mov,'name');
            }
        }else if ($type == 3){
            $item = ProgressWorkplanOutput::query()->findOrFail($type_id);
            $movs = explode(',', $item->output_movs_ids);
            foreach ($movs as $key => $mov){
                $movnames[$key]['progress_workplan_id'] = $item->progress_workplan_id;
                $movnames[$key]['id'] = $mov;
                $movnames[$key]['name'] = getAnyTablefieldName('type_values',$mov,'name');
            }
        }
        return resp('1', 'Successful!', $movnames, Response::HTTP_OK);
    }
    public function sendProjectWorkplanForApproval(ProgressWorkplan $item)
    {
        $this->authorize('work_plan');

        $approval_process=ApprovalProcess::query()->where('approval_process_id',7)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',7)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0  && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                $Approval=ApprovalProcessList::query()->create($insert);

            }
            $update=array('status'=>2);
            ProgressWorkplan::query()->where('id',$item->id)->update($update);
            return resp(1,'Project work plan send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Project work plan approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Program\Project;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\District;
use App\Models\Donar\DonarProfile;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Program\ProjectImplementingPartner;
use App\Models\Province;
use App\Models\Type;
use App\Models\TypeValue;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProjectProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'project_view',
            'project_rrf_view',
            'manage_audit_program_planning',
        ]);

        $data['project_profiles'] = ProjectProfile::with([
            'projectGoals' =>
            [
                'ProGoalIndicators' => ['KpiMappedIndicators'],
                'projectOutcomes' => [
                    'ProOutcomeIndicators' =>['KpiMappedIndicators'],
                    'projectOutputs' => ['ProOutputIndicators'=>['KpiMappedIndicators']]
                ]
            ],
            'thematic_area','status','project_manager','pdu_focal_person','implementing_partner','created_by','updated_by','donor'
        ])->orderByDesc('id')->get();
        $data['TotalCount']= $data['project_profiles']->count();
        $data['projectdraftCount']= $data['project_profiles']->where('approval_status',4)->count();
        $data['projectpendingCount']= $data['project_profiles']->where('approval_status',2)->count();
        $data['projectapprovedCount']= $data['project_profiles']->where('approval_status',1)->count();
        $data['projectrejectedCount']= $data['project_profiles']->where('approval_status',3)->count();


        $data['projectRrfDraftCount']= $data['project_profiles']->where('approval_status',1)->where('project_rrf_approval',4)->count();
        $data['projectRrfPendingCount']= $data['project_profiles']->where('approval_status',1)->where('project_rrf_approval',2)->count();
        $data['projectRrfApprovedCount']= $data['project_profiles']->where('approval_status',1)->where('project_rrf_approval',1)->count();
        $data['projectRrfRejectedCount']= $data['project_profiles']->where('approval_status',1)->where('project_rrf_approval',3)->count();



        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }
    public function getProjectProfilesRRFReport()
    {
        $this->authorizeAny([
            'project_view',
            'manage_audit_program_reports',
        ]);

        $data['project_profiles'] = ProjectProfile::with([
            'projectGoals' =>
            [
                'lasSpDetail','ProGoalIndicators' => ['lasRrfGoalIndicatorId','SpIndicatorId','KpiMappedIndicators','proGoalIndicatorTargets'],
                'lasRrfGoalId','projectOutcomes' => [
                    'lasRrfOutcomeId','lasSpDetail','project_rrf_goal','ProOutcomeIndicators' =>['lasRrfOutcomeIndicatorId','projectRrfGoalIndicatorId','SpIndicatorId','KpiMappedIndicators','proOutcomeIndicatorTargets'],
                    'projectOutputs' => ['lasRrfOutputId','lasSpDetail','project_rrf_goal','project_rrf_outcome','ProOutputIndicators'=>['lasRrfOutputIndicatorId','projectRrfOutcomeIndicatorId','SpIndicatorId','KpiMappedIndicators','proOutputIndicatorTargets']]
                ]
            ],
            'thematic_area','status','project_manager','pdu_focal_person','implementing_partner','created_by','updated_by','donor.donorDetail'
        ])->where('project_rrf_approval',1)->orderByDesc('id')->get();
//        $data['TotalCount']= $data['project_profiles']->count();
//        $data['projectdraftCount']= $data['project_profiles']->where('approval_status',4)->count();
//        $data['projectpendingCount']= $data['project_profiles']->where('approval_status',2)->count();
//        $data['projectapprovedCount']= $data['project_profiles']->where('approval_status',1)->count();
//        $data['projectrejectedCount']= $data['project_profiles']->where('approval_status',3)->count();
//        $data['projectRrfDraftCount']= $data['project_profiles']->where('approval_status',1)->where('project_rrf_approval',4)->count();
//        $data['projectRrfPendingCount']= $data['project_profiles']->where('approval_status',1)->where('project_rrf_approval',2)->count();
//        $data['projectRrfApprovedCount']= $data['project_profiles']->where('approval_status',1)->where('project_rrf_approval',1)->count();
//        $data['projectRrfRejectedCount']= $data['project_profiles']->where('approval_status',1)->where('project_rrf_approval',3)->count();

        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('project_create');

        $request->validate([
            'project_name' => 'required',
            'award_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'phase' => 'required',
            'end_duration' => 'required',
            'status' => 'required',
            'donors' => 'required|array',
            'project_code' => 'required',
            'thematic_area' => 'required',
            'target_area' => 'required',
            'pdu_focal_person_id' => 'required',
            'project_manager_id' => 'required',
            'budget' => 'required',
            'project_description' => 'required',
            'implementing_partners' => 'required|array',
        ]);
        $item = ProjectProfile::query()->create($this->input);
        $item->implementing_partner()->sync($request->implementing_partners);
        $item->donor_sync()->sync($request->donors);

        return resp('1','Record Created Successfully!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectProfile $projectProfile)
    {
        $this->authorizeAny([
            'project_view',
            'manage_audit_program_planning',
        ]);

        //$data = $projectProfile->load(['thematic_area','status','project_manager','pdu_focal_person','implementing_partner','created_by','updated_by','projectGoals.projectOutcomes.projectOutputs']);
        $data['projectProfile'] = $projectProfile->load([
            'projectGoals' =>
                [
                    'ProGoalIndicators',
                    'projectOutcomes' => [
                        'ProOutcomeIndicators',
                        'projectOutputs' => ['ProOutputIndicators']
                    ]
                ],
            'thematic_area','status','project_manager','pdu_focal_person','implementing_partner','created_by','updated_by','donor.donorDetail',
        ]);
        $targetAreas = explode(',', $projectProfile->target_area);
        $data['projectProfile']->target_area_details = District::query()->whereIn('id',$targetAreas)->get();
        $data['approval_request']=getNextApproval(4,auth()->user()->designation_id,$projectProfile->id);
        $data['approval_request_status']=checkApprovalRequestStatus(4,$projectProfile->id);
        return resp('1','Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectProfile $projectProfile)
    {
        $this->authorize('project_view');

        $request->validate([
            'id' => 'required',
            'project_name' => 'required',
            'award_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'phase' => 'required',
            'end_duration' => 'required',
            'status' => 'required',
            'donors' => 'required',
            'project_code' => 'required',
            'thematic_area' => 'required',
            'target_area' => 'required',
            'pdu_focal_person_id' => 'required',
            'project_manager_id' => 'required',
            'budget' => 'required',
            'project_description' => 'required',
            'implementing_partners' => 'required|array',
        ]);
        $item = $projectProfile->update($this->input);
        $projectProfile->implementing_partner()->sync($request->implementing_partners);
        $projectProfile->donor_sync()->sync($request->donors);

        return resp('1','Record Updated Successfully!', $item, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectProfile $projectProfile)
    {
        $this->authorize('project_view');

        $item = $projectProfile->delete();
        return resp('1','Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function getDropDowns(): JsonResponse
    {
        $this->authorize('project_view');

        $data['users'] = User::all();
        $data['statuses'] = Type::getTypeValues('project-profile-status');
        $data['thematic-areas'] = Type::getTypeValues('thematic-area');
        $data['provinces'] = Province::query()->with('TargetAreas')->get();
        $data['targe_Areas'] = District::all();
        $data['implementing_partners'] = ProjectImplementingPartner::all();
        $data['donars'] = DonarProfile::all();

        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }
    public function getApprovedProjects()
    {
        $this->authorizeAny([
            'project_view',
            'manage_audit_program_delivery_unit',
        ]);

        $data['approved_projects']=ProjectProfile::query()->where('approval_status',1)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('project_update');

        $request->validate(['id','approval_status']);
        $item = ProjectProfile::query()->findOrFail($request->id)->update($request->only(['approval_status']));
        return resp(1,'Successful!', $item,Response::HTTP_OK);
    }

    /*public static function getActivities($project_id)
    {
        $item = ProjectProfile::query()
            ->with([
                'progressWorkplans.workPlanGoals.activities',
                'progressWorkplans.workPlanOutcome.activities',
                'progressWorkplans.workPlanOutput.activities'
            ])
            ->findOrFail($project_id);

        $activitiesCollection = collect();

        $activitiesCollection = $activitiesCollection->merge(
            $item->progressWorkplans->flatMap(function ($workplan) {
                return $workplan->workPlanGoals->flatMap(function ($goal) {
                    return $goal->activities;
                });
            })
        );

        $activitiesCollection = $activitiesCollection->merge(
            $item->progressWorkplans->flatMap(function ($workplan) {
                return $workplan->workPlanOutcome->flatMap(function ($outcome) {
                    return $outcome->activities;
                });
            })
        );

        $activitiesCollection = $activitiesCollection->merge(
            $item->progressWorkplans->flatMap(function ($workplan) {
                return $workplan->workPlanOutput->flatMap(function ($output) {
                    return $output->activities;
                });
            })
        );

        return $activitiesCollection;
    }*/

    public static function getActivities($project_id)
    {
        $item = ProjectProfile::query()
            ->with([
                'progressWorkplans.workPlanGoals.activities',
                'progressWorkplans.workPlanOutcome.activities',
                'progressWorkplans.workPlanOutput.activities'
            ])
            ->findOrFail($project_id);

        $activities = [
            'goals' => collect(),
            'outcomes' => collect(),
            'outputs' => collect(),
        ];

        foreach ($item->progressWorkplans as $workplan) {
            $activities['goals'] = $activities['goals']->merge(
                $workplan->workPlanGoals->flatMap(function ($goal) {
                    return $goal->activities;
                })
            );

            $activities['outcomes'] = $activities['outcomes']->merge(
                $workplan->workPlanOutcome->flatMap(function ($outcome) {
                    return $outcome->activities;
                })
            );

            $activities['outputs'] = $activities['outputs']->merge(
                $workplan->workPlanOutput->flatMap(function ($output) {
                    return $output->activities;
                })
            );
        }

        return $activities;
    }

    public function sendProjectForApproval(ProjectProfile $item)
    {
        $this->authorize('project_view');

        $approval_process=ApprovalProcess::query()->where('approval_process_id',4)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',4)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            $update=array('approval_status'=>2);
            ProjectProfile::query()->where('id',$item->id)->update($update);
            return resp(1,'Project send for Approval.', $Approval,Response::HTTP_OK);
        }else{
            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Project approval already sent.', [],Response::HTTP_OK);
            }


        }
    }
    public function sendProjectRRFForApproval(ProjectProfile $item)
    {
        $this->authorize('project_view');

        $approval_process=ApprovalProcess::query()->where('approval_process_id',6)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',6)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            $update=array('project_rrf_approval'=>2);
            ProjectProfile::query()->where('id',$item->id)->update($update);
            return resp(1,'Project RRF send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Project RRF approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}

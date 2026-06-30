<?php

namespace App\Http\Controllers\Api\V1\Program\Project\MnE;

use App\Enum\FormCategory;
use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Program\Project\MnE\ProjectMnePlan;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Questionnaire\QuestionnaireForm;
use App\Models\Type;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProjectMnePlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($project)
    {
        $data = ProjectProfile::query()->with('mnePlans')->findOrFail($project);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ProjectProfile $project)
    {
        $request->validate([
            'name' => 'required',
            'version' => 'required'
        ]);
        $item = $project->mnePlans()->create($request->only(['name', 'version']));
        return resp('1', 'Successful!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectMnePlan $mnePlan)
    {
        $data['mnePlan']=$mnePlan;
        $data['approval_request']=getNextApproval(5,auth()->user()->designation_id,$mnePlan->id);
        $data['approval_request_status']=checkApprovalRequestStatus(5,$mnePlan->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectMnePlan $mnePlan)
    {
        $request->validate([
            'name' => 'required',
            'version' => 'required'
        ]);
        $item = $mnePlan->update($request->only(['name', 'version']));
        return resp('1', 'Successful!', $item, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectMnePlan $mnePlan)
    {
        if ($mnePlan->planGoals()->count() > 0 || $mnePlan->planOutputs()->count() > 0 || $mnePlan->planOutcomes()->count() > 0) {
            return resp('0', 'Unsuccessful! Can not delete parent record.', [], Response::HTTP_OK);
        }

        $mnePlan->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }

    public function getDropDowns($project)
    {
        $project = ProjectProfile::query()->with(['projectGoals' =>
            [
                'ProGoalIndicators',
                'projectOutcomes' => [
                    'ProOutcomeIndicators',
                    'projectOutputs' => ['ProOutputIndicators']
                ]
            ]
        ])->findOrFail($project);
        return resp('1', 'Successful!', $project, Response::HTTP_OK);
    }

    public function getAllMne()
    {
        $this->authorizeAny([
            'm&e_plans',
            'm_e_progress',
            'view_plans',
            'manage_audit_program_mne',
        ]);

        $data['Items'] =$Items=  ProjectMnePlan::query()->orderBy('id','desc')->get();
        $data['draft']=$Items->where('approval_status',4)->count();
        $data['pending']=$Items->where('approval_status',2)->count();
        $data['approved']=$Items->where('approval_status',1)->count();
        $data['reject']=$Items->where('approval_status',3)->count();
        return resp('1', 'Successful!', $data,Response::HTTP_OK);
    }

    public function getAllApprovedMne()
    {
        $this->authorizeAny([
            'manage_audit_program_reports',
            'internal_calendar',
            'mne_report',
        ]);
        
        $data['Items'] =$Items=  ProjectMnePlan::query()->where('approval_status',1)->orderBy('id','desc')->get();
        return resp('1', 'Successful!', $data,Response::HTTP_OK);
    }

    public function sendMNEPlanForApproval(ProjectMnePlan $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',5)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',5)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();

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
            ProjectMnePlan::query()->where('id',$item->id)->update($update);
            return resp(1,'M&E Plan send for Approval.', $Approval,Response::HTTP_OK);
        }else{

            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'M&E Plan approval already sent.', [],Response::HTTP_OK);
            }

        }
    }

    public function getMneDropdowns()
    {
        $data['form_categories'] = FormCategory::all();
        $data['mne_tools'] = QuestionnaireForm::getMneTools();
        $data['users'] = User::all();
        $data['movs'] = Type::getTypeValues('project-movs');
        $data['disaggregates'] = Type::getDisaggregates();
        $data['measurement_units'] = Type::getTypeValues('measurement-units');
        $data['data_collection_frequencies'] = Type::getTypeValues('data-collection-frequencies');
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function approvedMnePlanProjects()
    {
        $data = ProjectProfile::whereNotIn('id', function (Builder $query) {
            $query->select('project_id')
                ->from('project_mne_plans');
        })
            ->whereRelation('progressWorkplans', 'status', '=', 1)
            ->with('progressWorkplans')
            ->where('approval_status', \STATUS::APPROVED)
            ->get();


        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Program\Rdu;

use App\Http\Controllers\Controller;
use App\Http\Resources\rmCollection;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Employee;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Program\Rdu\ResearchMatrix;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ResearchMatrixController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'research_matrix_view',
            'manage_audit_research_delivery_unit',
        ]);

        $data['research_matrix_list'] =$research_matrix_list=  rmCollection::collection(ResearchMatrix::with([
            'ProgramName',
            'CreatedBy',
            'UpdatedBy',
            'ProgressWorkplanId',
            'MethodologyId',
            'ResearchComponentPlaceId',
            'FocalPerson',
            'Responsible',
            'Accountable',
            'Consulted',
            'Informed',
            'DataSources' => ['DataSourceId', 'DataAvailability'],
            'ReserachOutputs' => ['ResearchOutputId', 'ResearchOutputPlaceId'],
            'RmResources' => ['AllocatedProgramResources', 'ResourcesAvailability']
        ])->orderByDesc('id')->get());
        $data['draft']=$research_matrix_list->where('approval_status',4)->count();
        $data['pending']=$research_matrix_list->where('approval_status',2)->count();
        $data['approved']=$research_matrix_list->where('approval_status',1)->count();
        $data['reject']=$research_matrix_list->where('approval_status',3)->count();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('research_matrix_create');

        $request->validate([
            'program_name' => 'required',
            'program_start_date' => 'required',
            'program_end_date' => 'required',
            'progress_workplan_id' => 'required',
            'type' => 'required',
            'type_id' => 'required',
            //'type_category_id' => 'required',
            'indicator_id' => 'required',
            'research_objective' => 'required',
            'methodology_id' => 'required',
            'research_component_place_id' => 'required',
            'allocated_budget' => 'required',
            'focal_person' => 'required',
            'responsible' => 'required',
            'accountable' => 'required',
            'consulted' => 'required',
            'informed' => 'required',
        ]);
        $item = ResearchMatrix::query()->create($this->input);
        return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);

    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $this->authorizeAny([
            'research_matrix_view',
            'manage_audit_research_delivery_unit',
        ]);

        $data['researchMatrix']=$researchMatrix = new rmCollection(ResearchMatrix::with([
            'ProgramName',
            'CreatedBy',
            'UpdatedBy',
            'ProgressWorkplanId',
            'MethodologyId',
            'ResearchComponentPlaceId',
            'FocalPerson',
            'Responsible',
            'Accountable',
            'Consulted',
            'Informed',
            'DataSources' => ['DataSourceId', 'DataAvailability'],
            'ReserachOutputs' => ['ResearchOutputId', 'ResearchOutputPlaceId'],
            'RmResources' => ['AllocatedProgramResources', 'ResourcesAvailability']
        ])->findOrFail($id));
        $data['approval_request']=getNextApproval(21,auth()->user()->designation_id,$researchMatrix->id);
        $data['approval_request_status']=checkApprovalRequestStatus(21,$researchMatrix->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorize('research_matrix_update');

        $researchMatrix = ResearchMatrix::query()->findOrFail($id);
        $request->validate([
            'program_name' => 'required',
            'program_start_date' => 'required',
            'program_end_date' => 'required',
            'progress_workplan_id' => 'required',
            'type' => 'required',
            'type_id' => 'required',
            //'type_category_id' => 'required',
            'indicator_id' => 'required',
            'research_objective' => 'required',
            'methodology_id' => 'required',
            'research_component_place_id' => 'required',
            'allocated_budget' => 'required',
            'focal_person' => 'required',
            'responsible' => 'required',
            'accountable' => 'required',
            'consulted' => 'required',
            'informed' => 'required',
        ]);
        $item = $researchMatrix->update($this->input);
        return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->authorize('research_matrix_delete');

        $researchMatrix = ResearchMatrix::query()->findOrFail($id);
        $researchMatrix->DataSources()->delete();
        $researchMatrix->ReserachOutputs()->delete();
        $researchMatrix->RmResources()->delete();
        $researchMatrix->delete();
        return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
    }


    public function getRMDropDown(){

        $this->authorize('research_matrix_view');

        //$data['programs']= ProjectProfile::all(['id','project_name','start_date','end_date','status','created_by','updated_by','approval_status']);
        $data['programs'] = ProjectProfile::query()->whereRelation('progressWorkplans','status','=',1)
            ->with('progressWorkplans')
            ->where('approval_status',\STATUS::APPROVED)->get();
        //$data['users']= User::all(['id','name']);

        $employeeTypes = ['Confirmed', 'Probationary', 'Trainee'];
        $data['users'] = Employee::whereHas('employeeTyp', function ($query) use ($employeeTypes) {
            $query->whereIn('name', $employeeTypes);
        })->select('id', 'name')->get();

        $data['rm-methodology']= Type::getTypeValues('rm-methodology');
        $data['data-component-places']= Type::getTypeValues('data-component-places');
        $data['rm-data-source']= Type::getTypeValues('rm-data-source');
        $data['rm-data-availability']= Type::getTypeValues('rm-data-availability');
        $data['rm-research-output']= Type::getTypeValues('rm-research-output');
        $data['rm-research-output-places']= Type::getTypeValues('rm-research-output-places');
        $data['rm-allocated-program-resources']= Type::getTypeValues('rm-allocated-program-resources');
        $data['rm-allocated-resource-availability']= Type::getTypeValues('rm-allocated-resource-availability');
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    public function sendResearchMatrixForApproval(ResearchMatrix $item)
    {

        $this->authorize('research_matrix_update');

        $approval_process_name=ApprovalProcessName::query()->where('id',21)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',21)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',21)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                $Approval=ApprovalProcessList::query()->create($insert);

                sendNotification($approval['designation_id'],$approval_process_name->approval_process_name);

            }
            $update=array('approval_status'=>2);
            ResearchMatrix::query()->where('id',$item->id)->update($update);
            return resp(1,'Research matrix send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Research matrix approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}

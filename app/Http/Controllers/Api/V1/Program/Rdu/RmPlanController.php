<?php

namespace App\Http\Controllers\Api\V1\Program\Rdu;

use App\Enum\RmMethodology;
use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Employee;
use App\Models\Program\Rdu\RmPlan;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RmPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'research_plan_view',
            'manage_audit_research_delivery_unit',
        ]);

        $data['research_plan_list'] =$research_plan_list= RmPlan::with(['dataSources', 'researchPlace','createdBy','updatedBy','researchMatrix' => [
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
        'RmResources' => ['AllocatedProgramResources', 'ResourcesAvailability']]])->orderByDesc('id')->get();

        $data['draft']=$research_plan_list->where('approval_status',4)->count();
        $data['pending']=$research_plan_list->where('approval_status',2)->count();
        $data['approved']=$research_plan_list->where('approval_status',1)->count();
        $data['reject']=$research_plan_list->where('approval_status',3)->count();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('research_plan_create');

        $request->validate([
            'rm_id' => 'required|integer|exists:research_matrices,id',
            'methodology_id' => 'required|integer',
            'research_place_id' => 'required|integer',
        ]);
        $item = RmPlan::query()->create($this->input);
        return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);

    }

    /**
     * Display the specified resource.
     */
    public function show(RmPlan $rmPlan): JsonResponse
    {
        $this->authorizeAny([
            'research_plan_view',
            'manage_audit_research_delivery_unit',
        ]);

        $data['rmPlan']=$rmPlan->load(['dataSources', 'researchPlace','createdBy','updatedBy','researchMatrix' => [
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
            'RmResources' => ['AllocatedProgramResources', 'ResourcesAvailability']]]);

        $data['approval_request']=getNextApproval(22,auth()->user()->designation_id,$rmPlan->id);
        $data['approval_request_status']=checkApprovalRequestStatus(22,$rmPlan->id);

        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RmPlan $rmPlan)
    {
        $this->authorize('research_plan_update');

        $request->validate([
            'rm_id' => 'required|integer|exists:research_matrices,id',
            'methodology_id' => 'required|integer',
            'research_place_id' => 'required|integer',
        ]);
        $item = $rmPlan->update($this->input);
        return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RmPlan $rmPlan)
    {
        $this->authorize('research_plan_delete');

        $rmPlan->delete();
        return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
    }

    public function getRMDropDown()
    {
        $this->authorize('research_plan_view');
        
        //$data['users'] = User::all();
        $employeeTypes = ['Confirmed', 'Probationary', 'Trainee'];
        $data['users'] = Employee::whereHas('employeeTyp', function ($query) use ($employeeTypes) {
            $query->whereIn('name', $employeeTypes);
        })->select('id', 'name')->get();
        $data['rm_methodologies'] = RmMethodology::all();
        $data['rm_research_places']= Type::getTypeValues('rm-research-output-places');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function getRmDataSources($id)
    {
        $this->authorize('research_plan_view');

        $item = RmPlan::query()->with(['dataSources','methodologyDetail.rMResponsibleNote'])->findOrFail($id);
        return resp(1, 'Successful!', $item, Response::HTTP_OK);
    }

    public function getMethodologyDetailsByRMPId()
    {

    }
    public function sendRMPlanForApproval(RmPlan $item)
    {
        $this->authorize('research_plan_update');

        $approval_process=ApprovalProcess::query()->where('approval_process_id',22)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',22)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            RmPlan::query()->where('id',$item->id)->update($update);
            return resp(1,'Research matrix plan send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Research matrix approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}

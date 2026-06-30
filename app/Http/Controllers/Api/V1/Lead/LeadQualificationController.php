<?php

namespace App\Http\Controllers\Api\V1\Lead;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\EmailTemplate;
use App\Models\LeadQualification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class LeadQualificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        //return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|integer',
            'any_bid' => 'required|integer',
            'commercial_compliance' => 'required|integer',
            'competitors' => 'required|integer',
            'internal_reference' => 'required|integer',
            'delivery_material_available' => 'required|integer',
            'assign_to' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            $leadQualification=LeadQualification::query()->create($this->input);
            DB::commit();
            return resp(1, 'Successful!', $leadQualification, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LeadQualification $lead_qualification)
    {

        return resp('1', 'Successful!', $lead_qualification, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeadQualification $lead_qualification)
    {
        $request->validate([
            'lead_id' => 'required|integer',
            'any_bid' => 'required|integer',
            'commercial_compliance' => 'required|integer',
            'competitors' => 'required|integer',
            'internal_reference' => 'required|integer',
            'delivery_material_available' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            $lead_qualification->update($this->input);
            $lead_qualification->refresh();
            DB::commit();
            return resp(1, 'Successful!', $lead_qualification, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeadQualification $lead_qualification)
    {
        $lead_qualification->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }

    public function sendLeadQualificationForApproval(LeadQualification $item)
    {
        $template=EmailTemplate::query()->where('id',61)->where('template_for',1)->first();
        $approval_process_name=ApprovalProcessName::query()->where('id',61)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',61)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',61)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                $Approval=ApprovalProcessList::query()->create($insert);
                if(!empty($template)){
                    $title="New ".$approval_process->approval_process_name." Approval Required";
                    $message="A new ".$approval_process->approval_process_name." is awaiting your approval. Please review and take action.";
                    sendNotification($approval['designation_id'],$title,$message,$template->template_key);
                }


            }
            $update=array('approval_status'=>2);
            LeadQualification::query()->where('id',$item->id)->update($update);
            return resp(1,'Lead Qualification send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Lead Qualification approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}

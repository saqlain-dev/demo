<?php

namespace App\Http\Controllers\Api\V1\ApprovalProcess;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessName;
use App\Models\Designation;
use App\Models\EmailTemplate;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Constraint\Count;
use Termwind\Components\Dd;

class ApprovalProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'manage_finance_configuration',
            'configuration-program',
            'configuration-admin',
            'configuration-hr',
            'manage_communication_configuration',
            'configuration_governance'
        ]);

        $data['designation']=Designation::with('users')->get();
        $data['approval_process_list']=ApprovalProcessName::all();
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }
    public function addProcess($id){
        $this->authorizeAny([
            'configuration-program',
            'manage_finance_configuration',
            'configuration-admin',
            'configuration-hr',
            'manage_communication_configuration',
            'configuration_governance'
        ]);

        $data['process_detail']=ApprovalProcess::with('processName')->where('approval_process_id',$id)->get();
        $data['designation']=Designation::all();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'configuration-program',
            'manage_finance_configuration',
            'configuration-admin',
            'configuration-hr',
            'manage_communication_configuration',
            'configuration_governance'
        ]);

        $request->validate([
            'approval_process_id' => 'required',
        ]);
        $Tcount=count($request->approval);
        $lastIndex=last($request->approval);
        if($Tcount == $lastIndex['process_order']){

        $approvalProcessId=ApprovalProcessName::query()->findOrFail($request->approval_process_id);
        if($approvalProcessId){

            $approvalProcessId->isFinancialApproval=$request->isFinancialApproval;
            $approvalProcessId->save();
        }
        $approvalProcessId->approvalProcess()->delete();
        $approvalProcessId->approvalProcess()->createMany($request->approval);

        return resp(1,'Successful!', $approvalProcessId,Response::HTTP_CREATED);
        }else{
            return resp(0,'Approval Sequence is incorrect.', [],Response::HTTP_CREATED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ApprovalProcess $approvalProcess)
    {
        $this->authorizeAny([
            'manage_finance_configuration',
            'configuration-program',
            'configuration-admin',
            'configuration-hr',
            'manage_communication_configuration',
            'configuration_governance'
        ]);

        $data['approval_process_names_list']=ApprovalProcessName::all();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ApprovalProcess $approvalProcess)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApprovalProcess $approvalProcess)
    {
        //
    }

    public function getPendingApprovals(Request $request)
    {
        //$approvalProcessLists=ApprovalProcessName::query()->whereIn('id',[1,15,54])->get();
        $approvalProcessLists=ApprovalProcessName::query()->whereNotNull('module_path')->get();
        $approval_process_listing=array();
        foreach($approvalProcessLists as $appProcess){
            $designationId = $request->designation_id ?? auth()->user()->designation_id;
            $approval_request=getAllNextApproval($appProcess['id'],$designationId);
            //$approval_request['module_path']=$appProcess['module_path'];
            if($approval_request){
                foreach($approval_request as $key => $requestApp){
                    $requestApp->module_path=$appProcess['module_path'];
                    $requestApp->approval_process_name=$appProcess['approval_process_name'];
                    $requestApp->requested_by_name=$this->requestedByName($requestApp->created_by);
                    $approval_process_listing[]=$requestApp;
                }


            }

        }
        /*$data['approval_request']=getNextApproval($approval_process_id,auth()->user()->designation_id,$leave->id);
        $data['approval_request_status']=checkApprovalRequestStatus($approval_process_id,$leave->id);*/

        return resp(1,'Successful!', $approval_process_listing,Response::HTTP_OK);
    }

    public function getApprovedApprovals()
    {
        $approvalProcessLists=ApprovalProcessName::query()->whereNotNull('module_path')->get();
        $approval_process_listing=array();
        foreach($approvalProcessLists as $appProcess){

            $approval_request=$this->getAllgetApprovedApprovals($appProcess['id'],auth()->user()->designation_id);
            if($approval_request){
                foreach($approval_request as $key => $requestApp){
                    $requestApp->module_path=$appProcess['module_path'];
                    $requestApp->approval_process_name=$appProcess['approval_process_name'];
                    $requestApp->requested_by_name=$this->requestedByName($requestApp->created_by);
                    $approval_process_listing[]=$requestApp;
                }


            }

        }

        return resp(1,'Successful!', $approval_process_listing,Response::HTTP_OK);
    }

    /*public function getPendingHrApprovals()
    {
        //$approvalProcessLists=ApprovalProcessName::query()->whereIn('id',[1,15,54])->get();
        $approvalProcessLists=ApprovalProcessName::query()->whereIn('category',[3,0])->whereNotNull('module_path')->get();
        $approval_process_listing=array();
        foreach($approvalProcessLists as $appProcess){

            $approval_request=getAllNextApproval($appProcess['id'],auth()->user()->designation_id);
            //$approval_request['module_path']=$appProcess['module_path'];
            if($approval_request){
                foreach($approval_request as $key => $requestApp){
                    $requestApp->module_path=$appProcess['module_path'];
                    $requestApp->approval_process_name=$appProcess['approval_process_name'];
                    $approval_process_listing[]=$requestApp;
                }


            }

        }


        return resp(1,'Successful!', $approval_process_listing,Response::HTTP_OK);
    }*/

    public function requestedByName($user_id)
    {
        $user=User::query()->where('id',$user_id)->first();
        if(!empty($user)){
            return $user->name;
        }else{
            return NULL;
        }
    }

    public function getApprovalProcessList()
    {
        $data['approval_process_names']=ApprovalProcessName::query()->get();
        $data['email_templates']=EmailTemplate::query()->get();
        return resp('1', 'View Record', $data, Response::HTTP_OK);
    }
    public function getApprovalProcessDropDown()
    {
        $data['email_templates']=EmailTemplate::query()->get();
        return resp('1', 'View Record', $data, Response::HTTP_OK);
    }

    public function updateApprovalProcess(Request $request)
    {
        $request->validate([
            'approval_process_id' => 'required',
        ]);

        try {
            DB::beginTransaction();


            $approval_process_name=ApprovalProcessName::query()->where('id',$request->approval_process_id)->first();
            if ($approval_process_name) {
                if (!empty($request->category)) {
                    $approval_process_name->category = $request->category;
                }
                if (!empty($request->email_template_id)) {
                    $approval_process_name->email_template_id = $request->email_template_id;
                }
                if (!empty($request->approval_process_name)) {
                    $approval_process_name->approval_process_name = $request->approval_process_name;
                }

                if ($approval_process_name->isDirty()) { // Check if any field has changed before saving
                    $approval_process_name->save();
                    $approval_process_name->refresh();
                }
            }

            DB::commit();
            return resp(1, 'Successful!',$approval_process_name , Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    private function getAllgetApprovedApprovals($app_process_id,$desg_id)
    {
        $lineManagerApprovalRecordList = \App\Models\ApprovalProcessList::query()
        ->when(request()->filled('start_date') && request()->filled('end_date'), function ($query) {
            $query->whereBetween('created_at', [request('start_date'), request('end_date')]);
        })
        ->where('approval_process_id', $app_process_id)
        ->where('designation_id', 1000)
        ->where('approval_request_status', 1)
        ->where('approval_status', 1)
        ->get();

        $records=array();
        if($lineManagerApprovalRecordList) {
            foreach($lineManagerApprovalRecordList as $lineManagerApprovalRecord) {
                if ($lineManagerApprovalRecord->approval_status == 3) {
                    return null;
                } else {
                    if ($lineManagerApprovalRecord->approval_status == 1) {

                        $dbQuery = match ($app_process_id) {
                            2 => \App\Models\ApprovalProcessList::query(),
                            default => \App\Models\ApprovalProcessList::query(),
                        };

                        $resObject = $dbQuery->where('id', $lineManagerApprovalRecord->id)->first();

                        $createdUserDesignation = \App\Models\User::query()->find($resObject->created_by);
                        $loginEmployee = auth()->user()->employee_id;
                        if ($createdUserDesignation->designation_id) {
                            $ReportToEmployee = \App\Models\Employee::query()->where('id', $createdUserDesignation->employee_id)->first();
                            $reportToEmployeeDetail = $ReportToEmployee->reportTo;
                            $reportTodesignation = $reportToEmployeeDetail->designation;

                            $lineManagerApprovalRecord->designation_id = $reportTodesignation->id;
                            $lineManagerApprovalRecord->designation = $reportTodesignation;
                            if ($loginEmployee == $reportToEmployeeDetail->id) {
                                $records[] = $lineManagerApprovalRecord;
                            }
                        } else {
                        }

                    } else {
                    }
                }
            }

        }
        $approvalRecordList=\App\Models\ApprovalProcessList::query()
            ->when(request()->filled('start_date') && request()->filled('end_date'), function ($query) {
                $query->whereBetween('created_at', [request('start_date'), request('end_date')]);
            })
            ->where('approval_process_id',$app_process_id)->where('designation_id',$desg_id)->where('approval_status',1)->where('approval_request_status',1)->get();


            if($approvalRecordList){

                foreach($approvalRecordList as $approvalRecord) {
                    $records[]= $approvalRecord;
                    /*$CheckPreviousApprovalRecord = \App\Models\ApprovalProcessList::query()->where('approval_process_id', $app_process_id)->where('designation_id',$desg_id)->where('approval_request_status', 1)->where('process_order', $approvalRecord->process_order - 1)->first();

                    if ($CheckPreviousApprovalRecord && $CheckPreviousApprovalRecord->approval_status == 3) {
                        //return null;
                    } elseif ($CheckPreviousApprovalRecord && $CheckPreviousApprovalRecord->approval_status == 2) {
                        //return null;
                    } else {
                        if ($approvalRecord->approval_status == 2) {

                            $records[]= $approvalRecord;
                        } else {
                            //return null;
                        }

                    }*/
                }

                //return $records;
            }else{
                //return $records;
            }

        return $records;


    }
}

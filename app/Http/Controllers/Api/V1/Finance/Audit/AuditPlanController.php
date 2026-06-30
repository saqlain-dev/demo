<?php

namespace App\Http\Controllers\Api\V1\Finance\Audit;

use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Employee;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Finance\Audit\AuditPlan;
use App\Models\Finance\FinancialAnalysis\WorkSheet;

class AuditPlanController extends Controller
{
   /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'audit_plan_view',
        ]);

        $data['listing'] = AuditPlan::with('preparedBy')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'audit_plan_create',
        ]);

        $this->input = $request->input();
        $request->validate([
            'name' => 'required',
            // 'attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            'description' => 'required',
        ]);

        try {
            DB::beginTransaction();

            if($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $response = $this->saveAttachment($file, 'auditPlans');
                if ($response) {
                    $this->input['attachment'] = $response;
                }
            }
            $this->input['prepared_by'] = Auth::user()->id;

            $item = AuditPlan::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }


    }

    public function saveAttachment($file, $folder){


        $path = 'uploads/media/' . $folder;
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (!file_exists('uploads/media')) {
            mkdir('uploads/media', 0777, true);
        }
        if (!file_exists('uploads/media/' . $folder)) {
            mkdir('uploads/media/' . $folder, 0777, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        $file_name = str_replace(' ', '_', $filename);
        $file->move($path, $file_name);
        return $path.'/'.$file_name;
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->authorizeAny([
            'audit_plan_view',
        ]);

        $data['auditPlan'] = AuditPlan::with(['preparedBy','audiSchedule'=>[
            'createdBy', 'department', 'auditPlan','ticketSchedule'=>['employee','ticketStatus','observationReport'=>['createdBy','employee']],'auditPlanReport.preparedBy','auditPlanReport.auditPlanStatus'
        ]])->findOrFail($id);
        $data['approval_request']=getNextApproval(42,auth()->user()->designation_id,$id);
        $data['approval_request_status']=checkApprovalRequestStatus(42,$id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAny([
            'audit_plan_update',
        ]);

        $auditPlan = AuditPlan::findOrFail($id);

        $this->input = $request->except('_method');

        $request->validate([
            'name' => 'required',
            // 'attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            'description' => 'required',
        ]);

        try {
            DB::beginTransaction();

            if($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $response = $this->saveAttachment($file, 'auditPlans');
                if ($response) {
                    $this->input['attachment'] = $response;
                }
            }

            $item = $auditPlan->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->authorizeAny([
            'audit_plan_delete',
        ]);

        $auditPlan = AuditPlan::findOrFail($id);
        $auditPlan->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }

    public function sendAuditForApproval(AuditPlan $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',42)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',42)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',42)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            AuditPlan::query()->where('id',$item->id)->update($update);
            return resp(1,'Audit plan send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Audit plan approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function getDropdowns()
    {
        $data['apr_follow_up_status']= Type::getTypeValues('apr-follow-up-status');
        $data['audit_status']= Type::getTypeValues('audit-status');
        $data['employees']= Employee::query()->whereNotIn('employee_type', [14, 16, 17, 18])->get();
        $data['department']= Type::getTypeValues('department-names');
        $data['priority']= Type::getTypeValues('priority');
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }
}

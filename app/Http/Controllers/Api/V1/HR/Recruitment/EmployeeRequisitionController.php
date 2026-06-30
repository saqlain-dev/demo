<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Admin\Library\Book;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Employee;
use App\Models\HR\Recruitment\EmployeeRequisition;
use App\Models\HR\Recruitment\RecruitmentPlan;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmployeeRequisitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'employee_requisition_view',
            'consultant_requisition_view',
            'manage_audit_recruitment',
            'manage_employee_portal',
        ]);

        $data = EmployeeRequisition::with(['RequesterId','HiringSupervisorId','replacementForMs','DepartmentId','RequiredContractType','RequiredJobType','JobMode','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny(['consultant_requisition_create','employee_requisition_create','manage_employee_portal']);

        $request->validate([
            //'requester_id' => 'required',
            //'hiring_supervisor_id' => 'required',
            //'is_budgeted' => 'required',
            //'department_id' => 'required',
            //'job_title' => 'required',
            //'request_reason' => 'required',
            // 'replacement_for_ms' => 'required',
            //'required_contract_type' => 'required',
            //'required_job_type' => 'required',
            //'from_date' => 'required',
            //'from_time' => 'required',
            //'to_date' => 'required',
            //'to_time' => 'required',
            //'job_description' => 'required',
            //'required_skills' => 'required|array',
        ]);
        if($request->hasFile('emp_requisition')) {

            $responce = $this->saveEmployeeRequisition($request, 'empRequisition');

            if ($responce) {
                $this->input['emp_requisition'] = $responce;
            }
        }else{
            unset($this->input['emp_requisition']);
        }

        $statement = DB::select("SELECT IDENT_CURRENT('employee_requisitions') as nextID");
        $this->input['requisition_serial_no'] = sprintf('%04d', $statement[0]->nextID);
        try {
            DB::beginTransaction();
            $item = EmployeeRequisition::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveEmployeeRequisition($request,$folder){

        $file = $request->file('emp_requisition');
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
    public function show(EmployeeRequisition $employeeRequisition): JsonResponse
    {
        $this->authorizeAny([
            'consultant_requisition_view',
            'employee_requisition_view',
            'manage_audit_recruitment',
            'manage_employee_portal',
        ]);

        $data['employeeRequisition']=$employeeRequisition = $employeeRequisition->load(['RequesterId','HiringSupervisorId','DepartmentId','RequiredContractType','replacementForMs','RequiredJobType','JobMode','created_by','updated_by']);
        $data['approval_request']=getNextApproval(30,auth()->user()->designation_id,$employeeRequisition->id);
        $data['approval_request_status']=checkApprovalRequestStatus(30,$employeeRequisition->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeRequisition $employeeRequisition)
    {
        $this->authorizeAny(['consultant_requisition_update','employee_requisition_update','manage_employee_portal']);

        $request->validate([
            //'requester_id' => 'required',
            //'hiring_supervisor_id' => 'required',
            //'is_budgeted' => 'required',
            //'department_id' => 'required',
            //'job_title' => 'required',
            //'request_reason' => 'required',
            // 'replacement_for_ms' => 'required',
//            'required_contract_type' => 'required',
//            'required_job_type' => 'required',
//            'from_date' => 'required',
//            'from_time' => 'required',
//            'to_date' => 'required',
//            'to_time' => 'required',
//            'job_description' => 'required',
//            'required_skills' => 'required',
        ]);
        try {
            DB::beginTransaction();
            if($request->hasFile('emp_requisition')) {

                $responce = $this->saveEmployeeRequisition($request, 'empRequisition');

                if ($responce) {
                    $this->input['emp_requisition'] = $responce;
                }
            }else{
                unset($this->input['emp_requisition']);
            }
            $item = $employeeRequisition->update($this->input);
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
    public function destroy(EmployeeRequisition $employeeRequisition): JsonResponse
    {
        $this->authorize(['consultant_requisition_delete','employee_requisition_delete','manage_employee_portal']);

        $item = $employeeRequisition->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
    public function sendEmployeeRequisitionForApproval(EmployeeRequisition $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',30)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',30)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            EmployeeRequisition::query()->where('id',$item->id)->update($update);
            return resp(1,'Employee Requisition send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Employee Requisition approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function empReqDropDown()
    {
        $data['department']= Type::getTypeValues('department-names');
        $data['required_contract_type']= Type::getTypeValues('required-contract-type');
        $data['required_job_type']= Type::getTypeValues('required-job-type');
        $data['employees']= Employee::with(['EmployeeSalary','employeeTyp'])->get();
        $data['job_mode']= Type::getTypeValues('job-mode');
        $statement = DB::select("SELECT IDENT_CURRENT('employee_requisitions') as nextID");
        $data['requisition_serial_no'] = sprintf('%04d', $statement[0]->nextID) ?? '0001';
        $data['recruitment_plan']= RecruitmentPlan::query()->with('RecruitmentPlanDetail.BudgetDetailId.head_id')->get();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
}

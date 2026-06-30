<?php

namespace App\Http\Controllers\Api\V1\HR\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Admin\Inventory;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Employee;
use App\Models\HR\Insurance\EmployeeInsurances;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmployeeInsurancesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'insurance_view',
            'manage_audit_insurances',
        ]);

        $data = EmployeeInsurances::query()->with(['employee' => ['department','designation'],'relatives.relationId','relatives.fileType'])->get();
        $data->each( function ($record){
            $record->approval_request = getNextApproval(14,auth()->user()->designation_id,$record->id);
            $record->approval_request_status=checkApprovalRequestStatus(14,$record->id);
        });
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'insurance_create',
        ]);

        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'insurance_status'=>'required|integer',
        ]);
        try {
            DB::beginTransaction();
            $record = EmployeeInsurances::query()->where('employee_id', $this->input['employee_id'])->first();
            if($record){
                return resp(0, 'Record already exist', [], Response::HTTP_OK);
            }else{
                $parent = EmployeeInsurances::query()->create($request->all());
                DB::commit();
                return resp(1, 'Successful!', $parent, Response::HTTP_CREATED);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeeInsurances $employeeInsurance)
    {
        $this->authorizeAny([
            'insurance_view',
            'manage_audit_insurances',
        ]);

        $data['item'] = $employeeInsurance->load(['employee' => ['department','designation'],'relatives.relationId','relatives.fileType', 'claimReimbursements']);
        $data['approval_request'] = getNextApproval(14,auth()->user()->designation_id,$employeeInsurance->id);
        $data['approval_request_status']=checkApprovalRequestStatus(14,$employeeInsurance->id);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeInsurances $employeeInsurance)
    {
        $this->authorizeAny([
            'insurance_update',
        ]);

        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'insurance_status'=>'required|integer',
        ]);
        try {
            DB::beginTransaction();

            $parent = $employeeInsurance->update($request->all());

            DB::commit();
            return resp(1, 'Successful!', $employeeInsurance, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeInsurances $employeeInsurance)
    {
        $this->authorizeAny([
            'insurance_delete',
        ]);

        $employeeInsurance->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    public function getDropdowns()
    {
        $data['relative_types'] = Type::getTypeValues('relative-types');
        $data['file_type'] = Type::getTypeValues('file-type');
        $data['employees'] = Employee::query()->whereNotIn('employee_type', [14, 16, 17, 18])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function sendForApproval($itemId)
    {
        /*$item = EmployeeInsurances::query()->findOrFail($itemId);
        $claimAmount=$item->claimReimbursements()->sum('amount_claim');*/
        $item = EmployeeInsurances::withSum('claimReimbursements', 'amount_claim')->find($itemId);
        $claimAmount = $item->claim_reimbursements_sum_amount_claim;

        $approval_process_name=ApprovalProcessName::query()->where('id',14)->first();
        $approval_process = ApprovalProcess::query()->where('approval_process_id', 14)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',14)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if ($approval_process->count() > 0  && $checkProcess == 0) {

            foreach ($approval_process as $approval) {
                $insert = array(
                    'approval_process_id' => $approval['approval_process_id'],
                    'designation_id' => $approval['designation_id'],
                    'process_order' => $approval['process_order'],
                    'request_module_id' => $item->id,
                );

                if($approval_process_name->isFinancialApproval == 1){
                    if($approval->financialAmount < $claimAmount  ){
                        $insert['approval_status']=0;
                        $Approval = ApprovalProcessList::query()->create($insert);
                    }else{
                        $Approval=ApprovalProcessList::query()->create($insert);
                    }
                }else{
                    $Approval=ApprovalProcessList::query()->create($insert);
                }

                sendNotification($approval['designation_id'],$approval_process_name->approval_process_name);
            }

            $item->update(['approval_status' => 2]);
            autoApprovalForSender(auth()->user()->designation_id,$item->id,14);
            $checkRemaining=ApprovalProcessList::query()->where('approval_process_id',14)->where('approval_status',2)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
            if($checkRemaining == 0){
                $item->update(['approval_status' => 1]);
            }
            return resp(1, 'Employee Insurances send for approval.', $Approval, Response::HTTP_OK);
        } else {
            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Employee Insurances approval already sent.', [],Response::HTTP_OK);
            }
        }
    }


}

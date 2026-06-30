<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AirTravelRequest;
use App\Models\Admin\Fleet\VehicleRequest;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Employee;
use App\Models\Finance\ClaimTravelExpense;
use App\Models\Finance\CourtExpense;
use App\Models\PurchaseRequest;
use App\Models\Reimbursement;
use App\Models\VisitReimbursement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class VisitReimbursementController extends Controller
{
    //index
    public function index(Request $request)
    {
        $reimbursements = VisitReimbursement::with(['employee', 'employee.designation','employee.department', 'purchaseRequest', 'airTravelRequest', 'vehicleRequest', 'ExpenseDetails','reimbursementExpenses', 'createdBy', 'updatedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return resp('1', 'Record Fetched Successfully!', $reimbursements, Response::HTTP_OK);
    }

    //store
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'pr_id' => 'sometimes|exists:purchase_requests,id',
            'atr_id' => 'sometimes|exists:air_travel_requests,id',
            'vr_id' => 'sometimes|exists:vehicle_requests,id',
        ]);

        try {
            DB::beginTransaction();
            $reimbursement = VisitReimbursement::create($request->all());
            $reimbursement->load(['employee', 'employee.designation','employee.department', 'purchaseRequest', 'airTravelRequest', 'vehicleRequest', 'ExpenseDetails','reimbursementExpenses', 'createdBy', 'updatedBy']);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $reimbursement, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    //show
    public function show(VisitReimbursement $visitReimbursement)
    {
        $visitReimbursement->load(['employee', 'employee.designation','employee.department', 'purchaseRequest', 'airTravelRequest', 'vehicleRequest', 'ExpenseDetails','reimbursementExpenses', 'createdBy', 'updatedBy']);
        $data['visitReimbursement']=$visitReimbursement;
        $data['approval_request']=getNextApproval(72,auth()->user()->designation_id,$visitReimbursement->id);
        $data['approval_request_status']=checkApprovalRequestStatus(72,$visitReimbursement->id);
        return resp('1', 'Record Fetched Successfully!', $data, Response::HTTP_OK);
    }

    //update
    public function update(Request $request, VisitReimbursement $visitReimbursement)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'pr_id' => 'sometimes|exists:purchase_requests,id',
            'atr_id' => 'sometimes|exists:air_travel_requests,id',
            'vr_id' => 'sometimes|exists:vehicle_requests,id',
        ]);

        try {
            DB::beginTransaction();
            $visitReimbursement->update($request->all());
            $visitReimbursement->load(['employee', 'employee.designation','employee.department', 'purchaseRequest', 'airTravelRequest', 'vehicleRequest', 'ExpenseDetails','reimbursementExpenses', 'createdBy', 'updatedBy']);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $visitReimbursement, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    //destroy
    public function destroy(VisitReimbursement $visitReimbursement)
    {
        try {
            DB::beginTransaction();
            $visitReimbursement->delete();
            DB::commit();
            return resp('1', 'Record Deleted Successfully!', null, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to delete record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    //dropdown
    public function reimbursementDropdown(){
        $data['employees'] = Employee::query()->whereNotIn('employee_type', [14, 16, 17, 18])->get();
        $excludedPrIds = array_filter(array_merge(
            Reimbursement::pluck('pr_id')->toArray() ?? [],
            ClaimTravelExpense::pluck('pr_id')->toArray() ?? [],
            CourtExpense::pluck('pr_id')->toArray() ?? []
        ));

        //dd($excludedPrIds);
        $query = PurchaseRequest::query()->with('procurementDetail.item')->where(['pr_type' => 2,'pr_approval_status' => 1]);

        if (!empty($excludedPrIds)) {
            $query->whereNotIn('id', $excludedPrIds);
        }

        $data['purchase_request'] = $query->get();
        $data['atrs'] = AirTravelRequest::query()->where('approval_status', 1)->get();
        $data['vrs'] = VehicleRequest::query()->where('approval_status', 1)->get();
        return resp('1', 'Records!', $data, Response::HTTP_OK);
    }

    public function sendVisitReimbursementForApproval(VisitReimbursement $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',72)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',72)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',72)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            VisitReimbursement::query()->where('id',$item->id)->update($update);
            return resp(1,'Visit Reimbursement Request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Visit Reimbursement Request approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

}

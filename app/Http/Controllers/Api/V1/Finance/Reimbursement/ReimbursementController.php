<?php

namespace App\Http\Controllers\Api\V1\Finance\Reimbursement;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Employee;
use App\Models\EmployeeOffboarding;
use App\Models\Finance\ClaimTravelExpense;
use App\Models\Finance\CourtExpense;
use App\Models\PurchaseRequest;
use App\Models\Reimbursement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReimbursementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'reimbursement_view',
            'manage_employee_portal',
        ]);
        
        $data = Reimbursement::with(['EmployeeId.designation','PrId','Expenses','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }
    public function reimbursementByUser()
    {
        $this->authorizeAny([
            'reimbursement_view',
            'manage_employee_portal',
        ]);

        $userid = auth()->user()->id;
        $data = Reimbursement::with(['EmployeeId.designation','PrId','Expenses','created_by','updated_by'])->where('created_by', $userid)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'reimbursement_create',
            'manage_employee_portal',
        ]);

        $request->validate([
            'name' => 'required',
            'employee_id' => 'required',
            'pr_id' => 'required',
            'date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = Reimbursement::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Reimbursement $reimbursement): JsonResponse
    {
        $this->authorizeAny([
            'reimbursement_view',
            'manage_employee_portal',
        ]);

        $data['reimbursement'] = $reimbursement = $reimbursement->load(['EmployeeId.designation','PrId','Expenses','created_by','updated_by']);
        $data['approval_request']=getNextApproval(51,auth()->user()->designation_id,$reimbursement->id);
        $data['approval_request_status']=checkApprovalRequestStatus(51,$reimbursement->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reimbursement $reimbursement)
    {
        $this->authorizeAny([
            'reimbursement_update',
            'manage_employee_portal',
        ]);

        $request->validate([
            'name' => 'required',
            'employee_id' => 'required',
            'pr_id' => 'required',
            'date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $reimbursement->update($this->input);
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
    public function destroy(Reimbursement $reimbursement): JsonResponse
    {
        $this->authorizeAny([
            'reimbursement_delete',
            'manage_employee_portal',
        ]);

        $reimbursement->Expenses()->delete();
        $item = $reimbursement->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function expenseDropdown()
    {
        $data['employees'] = Employee::all();
        $excludedPrIds = array_filter(array_merge(
            Reimbursement::pluck('pr_id')->toArray() ?? [],
            ClaimTravelExpense::pluck('pr_id')->toArray() ?? [],
            CourtExpense::pluck('pr_id')->toArray() ?? []
        ));

        //dd($excludedPrIds);
        $query = PurchaseRequest::query()->where(['pr_type' => 2,'pr_approval_status' => 1]);

        if (!empty($excludedPrIds)) {
            $query->whereNotIn('id', $excludedPrIds);
        }

        $data['purchase_request'] = $query->get();
        return resp('1', 'Records!', $data, Response::HTTP_OK);

    }

    public function sendReimbursementForApproval(Reimbursement $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',51)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',51)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            Reimbursement::query()->where('id',$item->id)->update($update);
            return resp(1,'Employee Off Boarding send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Employee Off Boarding approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}

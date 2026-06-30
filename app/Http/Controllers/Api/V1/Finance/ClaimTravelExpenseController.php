<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Finance\ClaimTravelExpense;
use App\Models\Reimbursement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ClaimTravelExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'claim_travel_expenses_view',
            'manage_employee_portal',
        ]);

        $data = ClaimTravelExpense::with(['EmployeeId.designation','PrId','created_by','updated_by','ExpenseDetail'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }
    public function claimTravelExpenseByUser()
    {
        $this->authorizeAny([
            'claim_travel_expenses_view',
            'manage_employee_portal',
        ]);

        $userid = auth()->user()->id;
        $data = ClaimTravelExpense::with(['EmployeeId.designation','PrId','created_by','updated_by','ExpenseDetail'])->where('created_by', $userid)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**ExpenseDetail
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'claim_travel_expenses_create',
            'manage_employee_portal',
        ]);

        $request->validate([
            'name' => 'required',
            'employee_id' => 'required',
            'claim_date' => 'required',
            'departure_date' => 'required',
            'departure_time' => 'required',
            'departure_destination' => 'required',
            'return_date' => 'required',
            'return_time' => 'required',
            'return_destination' => 'required',
            'pr_id' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = ClaimTravelExpense::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveImage($request,$folder){

        $file = $request->file('attachment');
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
    public function show(ClaimTravelExpense $claimTravelExpense): JsonResponse
    {
        $this->authorizeAny([
            'claim_travel_expenses_view',
            'manage_employee_portal',
        ]);

        $data['travel_expense'] = $reimbursement = $claimTravelExpense->load(['EmployeeId.designation','PrId.procurementDetail','created_by','updated_by','ExpenseDetail']);
        $data['approval_request']=getNextApproval(52,auth()->user()->designation_id,$reimbursement->id);
        $data['approval_request_status']=checkApprovalRequestStatus(52,$reimbursement->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClaimTravelExpense $claimTravelExpense)
    {
        $this->authorizeAny([
            'claim_travel_expenses_update',
            'manage_employee_portal',
        ]);

        $request->validate([
            'name' => 'required',
            'employee_id' => 'required',
            'claim_date' => 'required',
            'departure_date' => 'required',
            'departure_time' => 'required',
            'departure_destination' => 'required',
            'return_date' => 'required',
            'return_time' => 'required',
            'return_destination' => 'required',
            'pr_id' => 'required',
        ]);

        if($request->hasFile('attachment')) {
            $responce = $this->saveImage($request, 'travel_expenses');
            if ($responce) {
                $this->input['attachment'] = $responce;
            }
        }
        try {
            DB::beginTransaction();
            $item = $claimTravelExpense->update($this->input);
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
    public function destroy(ClaimTravelExpense $claimTravelExpense): JsonResponse
    {
        $this->authorizeAny([
            'claim_travel_expenses_delete',
            'manage_employee_portal',
        ]);

        $item = $claimTravelExpense->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function sendTravelExpenseForApproval(ClaimTravelExpense $item)
    {
        $item = ClaimTravelExpense::withSum('ExpenseDetail', 'amount')->find($item->id);
        $claimAmount = $item->expense_detail_sum_amount ?? 0;

        $approval_process_name=ApprovalProcessName::query()->where('id',52)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',52)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',52)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                if($approval_process_name->isFinancialApproval == 1){
                    if($approval->financialAmount < $claimAmount ){
                        $insert['approval_status']=0;
                        $Approval=ApprovalProcessList::query()->create($insert);
                    }else{
                        $Approval=ApprovalProcessList::query()->create($insert);
                    }
                }else{
                    $Approval=ApprovalProcessList::query()->create($insert);
                }

                sendNotification($approval['designation_id'],$approval_process_name->approval_process_name);

            }
            $update=array('approval_status'=>2);
            ClaimTravelExpense::query()->where('id',$item->id)->update($update);
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

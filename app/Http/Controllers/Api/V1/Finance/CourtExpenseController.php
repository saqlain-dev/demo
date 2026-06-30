<?php

namespace App\Http\Controllers\Api\V1\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\ApprovalProcess;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\ApprovalProcessList;
use App\Http\Controllers\Controller;
use App\Models\ApprovalProcessName; 
use App\Models\Finance\CourtExpense;
use App\Models\Finance\CourtAdvocateExpense;

class CourtExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'court_expense_view',
            'manage_employee_portal',
        ]);

        $data = CourtExpense::with(['EmployeeId.designation','PrId','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function getUsersCourtExpense()
    {
        $this->authorizeAny([
            'court_expense_view',
            'manage_employee_portal',
        ]);

        $userid = auth()->user()->id;
        $data = CourtExpense::with(['EmployeeId.designation','PrId','created_by','updated_by'])->where('created_by', $userid)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'court_expense_create',
            'manage_employee_portal',
        ]);

        $request->validate([
            'accused_name' => 'nullable',
            'requested_date' => 'nullable',
            'case_no' => 'required',
            'fir_no' => 'required',
            'paper_requested' => 'required',
            'amount' => 'required',
            'employee_id' => 'required', 
            'pr_id' => 'nullable|integer|exists:purchase_requests,id',
            'court_advocate_expense_id'=>'required|exists:court_advocate_expenses,id'
        ]);

        if($request->hasFile('attachment')) {
            $responce = $this->saveImage($request, 'court_expenses');
            if ($responce) {
                $this->input['attachment'] = $responce;
            }
        }
        try {
            DB::beginTransaction();
            $item = CourtExpense::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item->load('courtAdvocateExpense'), Response::HTTP_CREATED);
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
    public function show(CourtExpense $courtExpense): JsonResponse
    {
        $this->authorizeAny([
            'court_expense_view',
            'manage_employee_portal',
        ]);

        $data['court_expense'] = $courtExpense = $courtExpense->load(['courtAdvocateExpense','EmployeeId.designation','PrId.procurementDetail','created_by','updated_by']);
        $data['approval_request']=getNextApproval(53,auth()->user()->designation_id,$courtExpense->id);
        $data['approval_request_status']=checkApprovalRequestStatus(53,$courtExpense->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CourtExpense $courtExpense)
    {
        $this->authorizeAny([
            'court_expense_update',
            'manage_employee_portal',
        ]);

        $request->validate([
            'accused_name' => 'required',
            'requested_date' => 'required',
            'case_no' => 'required',
            'fir_no' => 'required',
            'paper_requested' => 'required',
            'amount' => 'required',
            'employee_id' => 'required',
            'pr_id' => 'required',
        ]);

        if($request->hasFile('attachment')) {
            $responce = $this->saveImage($request, 'court_expenses');
            if ($responce) {
                $this->input['attachment'] = $responce;
            }
        }
        try {
            DB::beginTransaction();
            $item = $courtExpense->update($this->input);
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
    public function destroy(CourtExpense $courtExpense): JsonResponse
    {
        $this->authorizeAny([
            'court_expense_delete',
            'manage_employee_portal',
        ]);

        $item = $courtExpense->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function sendCourtExpenseForApproval(CourtAdvocateExpense $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',53)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',53)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',53)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                if($approval_process_name->isFinancialApproval == 1){
                    if($approval->financialAmount < $item->amount  ){
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
            CourtAdvocateExpense::query()->where('id',$item->id)->update($update);
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

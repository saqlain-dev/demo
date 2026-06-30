<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use App\Models\Finance\CourtAdvocateExpense; 
use Illuminate\Http\JsonResponse; 
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CourtAdvocateExpenseController extends Controller
{ 
    public function index()
    {
        $this->authorizeAny(['court_advocate_expense_view', 'manage_employee_portal']);

        $data = CourtAdvocateExpense::with(['employee','purchaseRequest','created_by', 'updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    } 

    public function courtAdocateExpenseByUser(){
        $user = Auth()->user();
        $data['court_advocate_expense'] = CourtAdvocateExpense::with([
        'courtExpenses',
        'employee',
        'purchaseRequest',
        'created_by',
        'updated_by'
        ])
        ->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
            ->orWhere('employee_id', $user->employee_id);
        })
        ->get();

        return resp('1', 'Successful!', $data, Response::HTTP_OK);
        
    }

    public function store(Request $request)
    {
        // dd(App\Models\PurchaseRequest::first());
        $this->authorizeAny(['court_advocate_expense_create', 'manage_employee_portal']);

        $request->validate([
            'employee_id' => 'required|exists:employees,id', 
            'pr_id' => 'required|integer|exists:purchase_requests,id',
            'requested_date'=>'required|date'
        ]);

        $data = $request->only(['employee_id','requested_date','pr_id']);
        $data['created_by'] = auth()->id();

        try {
            DB::beginTransaction();
            $item = CourtAdvocateExpense::create($data); 
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function show(CourtAdvocateExpense $courtAdvocateExpense): JsonResponse
    {
        $this->authorizeAny(['court_advocate_expense_view', 'manage_employee_portal']);

        $data['court_advocate_expense'] = $courtAdvocateExpense->load(['courtExpenses','employee','purchaseRequest','created_by', 'updated_by']); 
        $data['approval_request']=getNextApproval(53,auth()->user()->designation_id,$courtAdvocateExpense->id);
        $data['approval_request_status']=checkApprovalRequestStatus(53,$courtAdvocateExpense->id);
       
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function update(Request $request, CourtAdvocateExpense $courtAdvocateExpense)
    {
        $this->authorizeAny(['court_advocate_expense_update', 'manage_employee_portal']);

        $request->validate([ 
            'employee_id' => 'required|exists:employees,id',  
            'pr_id' => 'required|integer|exists:purchase_requests,id',
            'requested_date'=>'required|date'
        ]);

        $data = $request->only(['employee_id','requested_date','pr_id']);
        $data['updated_by'] = auth()->id();

        try {
            DB::beginTransaction();
            $courtAdvocateExpense->update($data);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $courtAdvocateExpense, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function destroy(CourtAdvocateExpense $courtAdvocateExpense): JsonResponse
    {
        $this->authorizeAny(['court_advocate_expense_delete', 'manage_employee_portal']);

        $courtAdvocateExpense->delete();
        return resp('1', 'Record Deleted Successfully!', [], Response::HTTP_OK);
    }
}
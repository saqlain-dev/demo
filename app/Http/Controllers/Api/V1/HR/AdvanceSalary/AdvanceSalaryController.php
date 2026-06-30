<?php

namespace App\Http\Controllers\Api\V1\HR\AdvanceSalary;

use App\Http\Controllers\Controller;
use App\Models\Admin\FinancialYear;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Employee;
use App\Models\HR\AdvanceSalary\AdvanceSalary;
use App\Models\HR\AdvanceSalary\AdvanceSalaryInstallment;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AdvanceSalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'advance_loan_view',
            'manage_audit_advance_loan',
        ]);

        $data['data'] = AdvanceSalary::query()->with(['employee','loanCategory','installments'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'advance_loan_create',
        ]);

        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'loan_category_id' => 'required|integer',
            'advance_salary' => 'required|numeric',
            'number_of_installments' => 'required|integer|gt:0',
            'first_installment_date' => 'required|date',
            'insurance_date' => 'nullable|date',
            'remarks' => 'required|string',
        ]);
        try {
            DB::beginTransaction();

            $advanceSalary = AdvanceSalary::query()->create($request->all());
            // Calculate installments
            $this->calculateInstallments($advanceSalary, $validatedData);

            DB::commit();
            return resp(1, 'Successful!', $advanceSalary->load('installments'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AdvanceSalary $advanceSalary)
    {
        $this->authorizeAny([
            'advance_loan_view',
            'manage_audit_advance_loan',
        ]);

        $data['previousAdvanceRequest'] = AdvanceSalary::query()->with(['employee','loanCategory','VoucherId'])->where('employee_id', $advanceSalary->employee_id)->get();
        $data['data'] = $advanceSalary->load(['employee','loanCategory','installments.loanSettlement','VoucherId']);
        $data['approval_request']=getNextApproval(24,auth()->user()->designation_id,$advanceSalary->id);
        $data['approval_request_status']=checkApprovalRequestStatus(24,$advanceSalary->id);
        return resp(1, 'Successful!', $data , Response::HTTP_OK);
    }

    public function getEmployeeAdvanceSalaries($empId)
    {
        $this->authorizeAny([
            'manage_employee_portal',
        ]);

        $data['data'] = $advanceSalary = AdvanceSalary::query()
            ->with(['employee','loanCategory','installments'])
            ->where('employee_id', $empId)
            ->get();

        foreach ($advanceSalary as $adv){
            $adv->approval_request = getNextApproval(24, auth()->user()->designation_id, $adv->id);
            $adv->approval_request_status = checkApprovalRequestStatus(24, $adv->id);
        }

        return resp(1, 'Successful!', $data, Response::HTTP_OK);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdvanceSalary $advanceSalary)
    {
        $this->authorizeAny([
            'advance_loan_update',
        ]);

        $validatedData = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'loan_category_id' => 'required|integer',
            'advance_salary' => 'required|numeric',
            'number_of_installments' => 'required|integer',
            'first_installment_date' => 'required|date',
            'insurance_date' => 'nullable|date',
            'remarks' => 'required|string',
        ]);
        try {
            DB::beginTransaction();

            $parent = $advanceSalary->update($request->all());
            $this->calculateInstallments($advanceSalary, $validatedData);


            DB::commit();
            return resp(1, 'Successful!', $advanceSalary->load('installments'), Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdvanceSalary $advanceSalary)
    {
        $this->authorizeAny([
            'advance_loan_delete',
        ]);

        $advanceSalary->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    public function getDropdowns()
    {
        $data['employees'] = Employee::query()->with('salarySetup')->get();
        $data['loan_categories'] = Type::getTypeValues('loan-categories');
        $data['financialYear'] = FinancialYear::query()->where('status', 1)->first();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

   /* private function calculateInstallments($advanceSalary, $validatedData)
    {
        $advanceSalary->installments()?->delete();

        $financialYear = FinancialYear::query()->where('status', 1)->first();

        $numberOfInstallments = $validatedData['number_of_installments'];
        $installmentAmount = $validatedData['advance_salary'] / $numberOfInstallments;
        $firstInstallmentDate = Carbon::parse($validatedData['first_installment_date']);

        for ($i = 1; $i <= $numberOfInstallments; $i++) {
            $dueDate = $firstInstallmentDate->addMonths($i - 1); // Due date incremented by one month each time
            $installment = new AdvanceSalaryInstallment([
                'advance_salary_id' => $advanceSalary->id,
                'installment_no' => $i,
                'due_date' => $dueDate,
                'due_amount' => $installmentAmount,
            ]);
            $installment->save();
        }
    }*/
    private function calculateInstallments($advanceSalary, $validatedData)
    {
        $advanceSalary->installments()?->delete();

        $firstInstallmentDate = Carbon::parse($validatedData['first_installment_date']);
        $financialYear = FinancialYear::query()->where('status', 1)->first();

        // Calculate the remaining months in the current financial year
        //$remainingMonths = $financialYear->end_date->diffInMonths($firstInstallmentDate);
        $remainingMonths = $advanceSalary->number_of_installments;

        // Calculate the maximum number of installments possible within the remaining months
        $maxInstallments = $remainingMonths;

        // Adjust the number of installments and installment amount accordingly
        $numberOfInstallments = min($maxInstallments, $validatedData['number_of_installments']);
        $installmentAmount = $validatedData['advance_salary'] / $numberOfInstallments;


        // Create an array to hold installment data
        $installments = [];

        for ($i = 1; $i <= $numberOfInstallments; $i++) {
            $dueDate = $firstInstallmentDate->copy()->addMonths($i - 1);
            if ($dueDate->greaterThanOrEqualTo($financialYear->end_date)) {
                $numberOfInstallments--;
                $installmentAmount = $validatedData['advance_salary'] / $numberOfInstallments;
            }
        }

        for ($i = 1; $i <= $numberOfInstallments; $i++) {
            $dueDate = $firstInstallmentDate->copy()->addMonths($i - 1);
            // Push installment data to the array
            $installments[] = [
                'advance_salary_id' => $advanceSalary->id,
                'installment_no' => $i,
                'due_date' => $dueDate,
                'due_amount' => $installmentAmount,
                'created_at' => now(),
                'updated_at' => now(),
                'employee_id' => $advanceSalary->employee_id,
            ];
        }

        // Insert all installments at once
        AdvanceSalaryInstallment::insert($installments);

        if ($numberOfInstallments !=  $validatedData['number_of_installments']){
            $advanceSalary->update(['number_of_installments' => $numberOfInstallments]);
        }

    }


    public function sendAdvanceSalaryRequestForApproval(AdvanceSalary $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',24)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',24)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            AdvanceSalary::query()->where('id',$item->id)->update($update);
            return resp(1,'Advance salary request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Advance salary approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}

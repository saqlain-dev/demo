<?php

namespace App\Http\Controllers\API\V1\HR\SalaryIncrment;

use App\Http\Controllers\Controller;
use App\Models\AppraisalEmployeeAllowance;
use App\Models\Configuration\AllowanceDeduction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AppraisalEmployeeAllowanceController extends Controller
{
    // Store allowances for the employee
    public function store(Request $request)
    {
        $this->authorizeAny([
            'manage_employee_wise_allowance_deduction',
        ]);
        
        $request->validate([
            'employee_id' => 'required|integer',
            'salary_increment_employees_id' => 'required',
            'allowances' => 'required|array',
            'allowances.*.allowance_deduction_id' => 'required|integer', // Validate each allowance_deduction_id
            'allowances.*.allowance_deduction_value' => 'required|numeric|min:0', // Validate each value
        ]);

        try {

            DB::beginTransaction();
            $allownaceDeduction=array();

            foreach($this->input['allowances'] as $allowance_deduction_id){

                $allownaceDeduction[]=$allowance_deduction_id['allowance_deduction_id'];
                $allowance_deduction=AllowanceDeduction::query()->findOrFail($allowance_deduction_id['allowance_deduction_id']);
                $emp_allowance=AppraisalEmployeeAllowance::query()->where('employee_id',$this->input['employee_id'])->where('allowance_deduction_id',$allowance_deduction_id['allowance_deduction_id'])->where('salary_increment_employees_id',$this->input['salary_increment_employees_id'])->first();
                if(empty($emp_allowance)){
                    $insert=array(
                        "employee_id"=>$this->input['employee_id'],
                        "salary_increment_employees_id" =>$this->input['salary_increment_employees_id'],
                        "allowance_deduction_id"=>$allowance_deduction_id['allowance_deduction_id'],
                        "description"=>$allowance_deduction->description,
                        "category"=>$allowance_deduction->category,
                        "calculated_by"=>$allowance_deduction->calculated_by,
                        "value"=>$allowance_deduction_id['allowance_deduction_value'],
                       // "value"=>$allowance_deduction->value,
                        "isGlobal"=>0,
                        "isTaxable"=>$allowance_deduction->is_taxable,
                        "IsVariableAllowance"=>$allowance_deduction->IsVariableAllowance,
                    );

                    AppraisalEmployeeAllowance::query()->create($insert);
                }else{
                    $emp_allowance->description=$allowance_deduction->description;
                    $emp_allowance->category=$allowance_deduction->category;
                    $emp_allowance->calculated_by=$allowance_deduction->calculated_by;
                   // $emp_allowance->value=$allowance_deduction->value;
                    $emp_allowance->value=$allowance_deduction_id['allowance_deduction_value'];
                    $emp_allowance->IsVariableAllowance=$allowance_deduction->IsVariableAllowance;
                    $emp_allowance->isTaxable=$allowance_deduction->is_taxable;
                    $emp_allowance->save();
                }
            }
            DB::commit();
           // EmployeeAllowanceDeduction::query()->whereNotIn('allowance_deduction_id', $allownaceDeduction)->where('employee_id',$this->input['employee_id'])->delete();
            return resp('1', 'Employee salary added Successfully!', null, Response::HTTP_CREATED);


        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(AppraisalEmployeeAllowance $appraisalEmployeeAllowance)
    {
        $appraisalEmployeeAllowance->delete();
        return resp(1, 'Allowance/Deduction deleted successful!', [], Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers\Api\V1\HR\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllowanceDeductionResource;
use App\Models\Configuration\AllowanceDeduction;
use App\Models\Configuration\PositionWiseAllowDeduct;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\HR\Payroll\EmployeeAllowanceDeduction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmployeeAllowanceDeductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'manage_employee_wise_allowance_deduction',
        ]);

        $request->validate([
            'employee_id' => 'required',
            'allowance_deduction_id' => 'required|array',
            'allowance_deduction_id.*' => 'required',
        ]);

        try {

            DB::beginTransaction();
            $allownaceDeduction=array();
            foreach($this->input['allowance_deduction_id'] as $allowance_deduction_id){
                $allownaceDeduction[]=$allowance_deduction_id;
                $allowance_deduction=AllowanceDeduction::query()->findOrFail($allowance_deduction_id);
                $emp_allowance=EmployeeAllowanceDeduction::query()->where('employee_id',$this->input['employee_id'])->where('allowance_deduction_id',$allowance_deduction_id)->first();
                if(empty($emp_allowance)){
                    $insert=array(
                        "employee_id"=>$this->input['employee_id'],
                        "allowance_deduction_id"=>$allowance_deduction_id,
                        "description"=>$allowance_deduction->description,
                        "category"=>$allowance_deduction->category,
                        "calculated_by"=>$allowance_deduction->calculated_by,
                        "value"=>$allowance_deduction->value,
                        "isGlobal"=>0,
                    );
                    EmployeeAllowanceDeduction::query()->create($insert);
                }else{
                    $emp_allowance->description=$allowance_deduction->description;
                    $emp_allowance->category=$allowance_deduction->category;
                    $emp_allowance->calculated_by=$allowance_deduction->calculated_by;
                    $emp_allowance->value=$allowance_deduction->value;
                    $emp_allowance->save();
                }
            }
            DB::commit();
            EmployeeAllowanceDeduction::query()->whereNotIn('allowance_deduction_id', $allownaceDeduction)->where('employee_id',$this->input['employee_id'])->delete();
            $data['employee_allowance']=EmployeeAllowanceDeduction::query()->where('employee_id',$this->input['employee_id'])->get();
            return resp('1', 'Employee salary added Successfully!', $data, Response::HTTP_CREATED);


        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeeAllowanceDeduction $ewAllowanceDeduction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeAllowanceDeduction $ewAllowanceDeduction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeAllowanceDeduction $ewAllowanceDeduction)
    {
        $ewAllowanceDeduction->delete();
        return resp(1, 'Allowance/Deduction deleted successful!', [], Response::HTTP_OK);
    }

    public function employeeWiseDropDown()
    {
        $data['positions']=Designation::query()->with('allowanceDeduction.position','allowanceDeduction.positionAllowanceDeduction')->get();
        $data['allowance_deduction_list']=AllowanceDeductionResource::collection(AllowanceDeduction::all());
        $data['employee_list']=Employee::query()->with('employeeAllowanceDeduction.allowanceDeductionDetail')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}




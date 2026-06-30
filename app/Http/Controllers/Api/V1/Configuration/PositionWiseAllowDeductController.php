<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\AllowanceDeduction;
use App\Models\Configuration\PositionWiseAllowDeduct;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\HR\Payroll\EmployeeAllowanceDeduction;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PositionWiseAllowDeductController extends Controller
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
            'manage_position_wise_allowance_deduction',
        ]);

        try {
            DB::beginTransaction();

            $request->validate([
                'position_id' => 'required|array',
                'position_id.*' => 'required',
                'allowance_deduction_id' => 'required|array',
                'allowance_deduction_id.*' => 'required',
            ]);

                $allownaceDeduction=array();
                foreach($this->input['position_id'] as $position_id) {
                    foreach ($this->input['allowance_deduction_id'] as $allowance_deduction_id) {
                        $allownaceDeduction[] = $allowance_deduction_id;
                        $allowance_deduction = AllowanceDeduction::query()->findOrFail($allowance_deduction_id);
                        $poisition_allowance = PositionWiseAllowDeduct::query()->where('position_id', $position_id)->where('allowance_deduction_id', $allowance_deduction_id)->first();
                        if (empty($poisition_allowance)) {
                            $insert = array(
                                "position_id" => $position_id,
                                "allowance_deduction_id" => $allowance_deduction_id
                            );
                            PositionWiseAllowDeduct::query()->create($insert);
                        } else {
                            $poisition_allowance->position_id = $position_id;
                            $poisition_allowance->allowance_deduction_id = $allowance_deduction_id;
                            $poisition_allowance->save();
                        }
                        $this->updateEmployeeAllowanceDeduction($position_id,$allowance_deduction_id);
                    }
                }
                DB::commit();
                //PositionWiseAllowDeduct::query()->whereNotIn('allowance_deduction_id', $allownaceDeduction)->where('position_id',$this->input['position_id'])->delete();

            $PositionAllowanceDeduction=PositionWiseAllowDeduct::query()->where('position_id',$this->input['position_id'])->with('positionAllowanceDeduction','position')->get();
            DB::commit();
            return resp('1', 'Position wise Allowance / Deduction added Successfully!', $PositionAllowanceDeduction, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function updateEmployeeAllowanceDeduction($position_id,$allowance_deduction_id)
    {
        $employeeList=Employee::query()->where('designation_id',$position_id)->get();

        if($employeeList){
            foreach($employeeList as $emp){

                $allowance_deduction=AllowanceDeduction::query()->findOrFail($allowance_deduction_id);
                $emp_allowance=EmployeeAllowanceDeduction::query()->where('employee_id',$emp->id)->where('allowance_deduction_id',$allowance_deduction_id)->first();
                if(empty($emp_allowance)){
                    $insert=array(
                        "employee_id"=>$emp->id,
                        "allowance_deduction_id"=>$allowance_deduction->id,
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
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PositionWiseAllowDeduct $item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PositionWiseAllowDeduct $item)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PositionWiseAllowDeduct $pw_allowance_deduction)
    {
        $pw_allowance_deduction->delete();
        return resp('1', 'Position wise Allowance / Deduction deleted Successfully!', [], Response::HTTP_OK);
    }

    public function positionWiseDropDown()
    {
        $positions=Designation::query()->with('allowanceDeduction.positionAllowanceDeduction.employeeType')->get();
        foreach ($positions as $key => $position) {
            foreach ($position->allowanceDeduction as $j => $allowanceDeduction) {
                $category=$allowanceDeduction->positionAllowanceDeduction['category'];
                $calculated_by=intval($allowanceDeduction->positionAllowanceDeduction['calculated_by']);
                $employee_calculated_by=intval($allowanceDeduction->positionAllowanceDeduction['employee_calculated_by']);

                $allowanceDeduction->positionAllowanceDeduction['calculated_by'] = getCalculatedByDescription($calculated_by);
                $allowanceDeduction->positionAllowanceDeduction['category'] = getCategoryDescription($category);
                $allowanceDeduction->positionAllowanceDeduction['employee_calculated_by'] = getCalculatedByDescription($employee_calculated_by);
            }
        }

        $data['positions']=$positions;
        $data['allowance_deduction_list']=AllowanceDeduction::all();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}

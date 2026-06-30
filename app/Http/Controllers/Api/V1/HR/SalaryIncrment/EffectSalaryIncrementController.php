<?php

namespace App\Http\Controllers\API\V1\HR\SalaryIncrment;

use App\Http\Controllers\Controller;
use App\Models\SalaryIncrement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EffectSalaryIncrementController extends Controller
{
    //effect salary increment 153
    public function effectSalaryIncrement(Request $request){
        $request->validate([
            'salary_increment_id' =>'required|exists:salary_increments,id',
        ]);

        try {
            set_time_limit(0);
            DB::beginTransaction();
            $salaryIncrement = SalaryIncrement::with([
                                                'employeeChangeLogs.employeeDetail',
                                                'appraisalSalarySetups.employeeDetail.empSalary.salarySegregation',
                                                'appraisalSalarySetups.employeeSalarySegregations',
                                                'empAllowances.employeeDetail.employeeAllowanceDeduction'
                                            ])->findOrFail($request->salary_increment_id);

            //Update Chanage EMployee Logs like Designation, Departments etc
            foreach ($salaryIncrement->employeeChangeLogs as $log) {
                if($log->description == 'Designation'){
                    $log->employeeDetail->update(['designation_id'=>$log->change_to_id]);
                }
                if($log->description == 'Department'){
                    $log->employeeDetail->update(['department_id'=>$log->change_to_id]);
                }
                if($log->description == 'Grade'){
                    $log->employeeDetail->update(['grade'=>$log->change_to_id]);
                }
            }

            //update employee salary
            foreach ($salaryIncrement->appraisalSalarySetups as $salarySetup) {
                $newSalary = Arr::except($salarySetup->toArray(), ['salary_increment_employee_id', 'increment']);

                $employee = $salarySetup->employeeDetail;
                
                $oldSalary = $employee->empSalary()->first();
                if($oldSalary){
                    $oldSalary->salarySegregation()->delete();
                    $oldSalary->delete();
                    $newSalary = $employee->empSalary()->create($newSalary);
                    if($salarySetup->employeeSalarySegregations()->count()>0){
                        $salarySetup->employeeSalarySegregations()->update(['emp_salary_setup_id'=>$newSalary->id]);
                    }
                }
            }

            //update Allownaces
            foreach ($salaryIncrement->empAllowances as $allowance) {
                $allow = Arr::except($allowance->toArray(), ['salary_increment_employee_id']);
                $employee = $allowance->employeeDetail;

                if ($employee) {
                    // update if record exists, otherwise create a new one
                    $employee->employeeAllowanceDeduction()
                            ->updateOrCreate(
                                [
                                    'employee_id'       => $employee->id,
                                    'allowance_deduction_id' => $allow['allowance_deduction_id'], // match per type
                                ],
                                $allow // values to update or insert
                            );
                }
            }

            $salaryIncrement->update(['is_effected'=>true]);
            DB::commit();
            return resp('1', 'Appraisal Effected Successfully!', $salaryIncrement, Response::HTTP_OK);
            
        } catch (\Throwable $th) {
            DB::rollBack(); 
            return resp('0', 'Appraisal Effected Failed!', $th->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

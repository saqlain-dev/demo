<?php

namespace App\Http\Controllers\API\V1\HR\SalaryIncrment;

use App\Http\Controllers\Controller;
use App\Models\AppraisalSalarySetup;
use App\Models\Configuration\AllowanceDeduction;
use App\Models\HR\Payroll\EmployeeSalarySegregation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AppraisalSalarySetupController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'salary_increment_employees_id' => 'required',
            'employee_id' => 'required',
            'monthly_salary' => 'required',
            'bankId' => 'required',
            'bankAccountNumber' => 'required',
        ]);

        try {
            $salary_projects = $request->salary_projects;
            $input = $request->except(['salary_projects']); // instead of unset($this->input)
            $input['old_monthly_salary'] = $request->monthly_salary ?? 0;
            DB::beginTransaction();

            // find existing record by unique keys
            $employeeSalarySetup = AppraisalSalarySetup::query()
                ->where('salary_increment_employees_id', $request->salary_increment_employees_id)
                ->where('employee_id', $request->employee_id)
                ->first();

            if ($employeeSalarySetup) {
                // update existing record
                $employeeSalarySetup->update($input);

                // delete old segregations before re-inserting
                EmployeeSalarySegregation::where('appraisal_salary_setup_id', $employeeSalarySetup->id)->delete();
            } else {
                // create new record
                $employeeSalarySetup = AppraisalSalarySetup::query()->create($input);
            }

            // handle salary projects
            if ($salary_projects) {
                foreach ($salary_projects as $proj) {
                    EmployeeSalarySegregation::create([
                        'appraisal_salary_setup_id' => $employeeSalarySetup->id,
                        'project_id' => $proj['project_id'],
                        'salary_percentage' => $proj['percentage'],
                    ]);
                }
            }

            $data['employeeSalarySetup'] = $employeeSalarySetup->load('employeeDetail', 'bankDetail');

            DB::commit();
            return resp('1', 'Employee salary saved successfully!', $data, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to save record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    

}

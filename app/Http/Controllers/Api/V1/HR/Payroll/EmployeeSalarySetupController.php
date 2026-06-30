<?php

namespace App\Http\Controllers\Api\V1\HR\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\HR\Payroll\EmployeeSalarySegregation;
use App\Models\HR\Payroll\EmployeeSalarySetup;
use App\Models\HR\PreGrossSalaryAllowances;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Type;
use App\Rules\PercentageTotal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmployeeSalarySetupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'employee_salary_view',
            'manage_audit_payroll',
        ]);

        $data['employee_list']=EmployeeSalarySetup::query()->with('employeeDetail','salarySegregation.projectDetail','bankDetail')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'employee_salary_create',
        ]);

        $request->validate([
            'employee_id' => 'required|unique:employee_salary_setups,employee_id',
            'monthly_salary' => 'required',
            'bankId' => 'required',
            'bankAccountNumber' => 'required',
        ]);

        try {

            $salary_projects=$request->salary_projects;
            //$percentage=$request->percentage;
            unset($this->input['salary_projects']);
            // unset($this->input['percentage']);

            $salaryCheck=EmployeeSalarySetup::query()->where('employee_id',$request->employee_id)->count();
            if($salaryCheck == 0){

                DB::beginTransaction();
                $employeeSalarySetup=EmployeeSalarySetup::query()->create($this->input);
                if($employeeSalarySetup){
                    if($salary_projects) {
                        foreach($salary_projects as $key => $proj){

                            $salaryProject = array(
                                'emp_salary_setup_id' => $employeeSalarySetup->id,
                                'project_id' => $proj['project_id'],
                                'salary_percentage' => $proj['percentage'],
                            );
                            EmployeeSalarySegregation::query()->create($salaryProject);

                        }
                    }

                }
                $data['employeeSalarySetup']=$employeeSalarySetup->load('employeeDetail','bankDetail');
                DB::commit();
                return resp('1', 'Employee salary added Successfully!', $data, Response::HTTP_CREATED);
            }else{
                return resp('0', 'Employee salary already added', null, Response::HTTP_INTERNAL_SERVER_ERROR);
            }


        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeeSalarySetup $empSalarySetup)
    {
        $this->authorizeAny([
            'employee_salary_view',
            'manage_audit_payroll',
        ]);

        $data['employee_list']=$empSalarySetup->load('employeeDetail','salarySegregation.projectDetail','bankDetail');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeSalarySetup $empSalarySetup)
    {
        $this->authorizeAny([
            'employee_salary_update',
        ]);

        $request->validate([
            'employee_id' => 'required',
            'monthly_salary' => 'required',
            'bankId' => 'required',
            'bankAccountNumber' => 'required',
        ]);

        try {

            DB::beginTransaction();
            $salary_projects=$request->salary_projects;
            //$percentage=$request->percentage;
            unset($this->input['salary_projects']);
           // unset($this->input['percentage']);

            EmployeeSalarySetup::query()->where('id',$empSalarySetup->id)->update($this->input);
            $empSalarySetup->refresh();
            if($empSalarySetup && $salary_projects){
                EmployeeSalarySegregation::query()->where('emp_salary_setup_id',$empSalarySetup->id)->delete();
                foreach($salary_projects as $key => $proj){

                        $salaryProject = array(
                            'emp_salary_setup_id' => $empSalarySetup->id,
                            'project_id' => $proj['project_id'],
                            'salary_percentage' => $proj['percentage'],
                        );
                        EmployeeSalarySegregation::query()->create($salaryProject);

                }

            }
            $data['employeeSalarySetup']=$empSalarySetup->load('employeeDetail','bankDetail');
            DB::commit();
            return resp('1', 'Employee salary updated Successfully!', $data, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeSalarySetup $employeeSalarySetup)
    {
        //
    }
    public function salarySetupDropDown()
    {
        $data['bank_list']=Type::getTypeValues('bank');

        $employeeList = Employee::with(['salarySetup.bankDetail', 'payScale.SalaryRange','grade'])->get();

        // Gather all unique project IDs from all employees after cleaning each entry
        $allProjectIds = $employeeList->flatMap(function ($employee) {
            // Check if project_id is an array, if not, parse it as JSON or handle as empty
            $projectIds = $employee->project_id;

            if (is_string($projectIds) && preg_match('/^\[.*\]$/', $projectIds)) {
                $projectIds = json_decode($projectIds, true);
            } elseif (!is_array($projectIds)) {
                $projectIds = []; // Invalid format, treat as empty
            }

            // Filter to keep only integer values
            return array_filter($projectIds, function ($id) {
                return is_int($id);
            });
        })->unique()->values()->toArray();

        // Retrieve all projects at once
        $projects = ProjectProfile::whereIn('id', $allProjectIds)->get()->keyBy('id');

        // Attach projects to each employee
        $employeeList->each(function ($employee) use ($projects) {
            $projectIds = $employee->project_id ?? [];
            $employee->projects = $projects->only($projectIds);
        });

        $data['employee_list']=$employeeList;
        $data['projects']=ProjectProfile::query()->where('approval_status',1)->get();
        $data['gross_allowances']=PreGrossSalaryAllowances::query()->with('allowanceType')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function getEmployeeProjects(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
        ]);

        try {
            $employee=Employee::query()->where('id',$request->employee_id)->first();
            if($employee && $employee->project_id !=""){
                DB::beginTransaction();
                $projects_ids=explode(',',$employee->project_id);
                $data['projects']=ProjectProfile::query()->whereIn('id',$projects_ids)->get();
                DB::commit();
                return resp('1', 'Employee Project Details', $data, Response::HTTP_OK);
            }else{
                return resp('0', 'Employee Project not added', null, Response::HTTP_OK);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

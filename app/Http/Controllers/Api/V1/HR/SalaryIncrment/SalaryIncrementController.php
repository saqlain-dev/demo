<?php

namespace App\Http\Controllers\API\V1\HR\SalaryIncrment;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllowanceDeductionResource;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Configuration\AllowanceDeduction;
use App\Models\Configuration\EmployeeChangeLog;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\HR\Appraisal\DepartmentalObjective;
use App\Models\HR\Payscale\PayscaleGrading;
use App\Models\HR\PreGrossSalaryAllowances;
use App\Models\Program\Project\ProjectProfile;
use App\Models\SalaryAllowanceDeduction;
use App\Models\SalaryIncrement;
use App\Models\SalaryIncrementEmployee;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class SalaryIncrementController extends Controller
{
    //index
    public function index()
    {
        // Fetch all salary increments
        $salaryIncrements = SalaryIncrement::with('financialYear')->get();

        return resp('1', 'Record Fetched Successfully!', $salaryIncrements, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'financial_year' => 'nullable|exists:type_values,id',
            'description'    => 'nullable|string',
        ]);

        try {

            DB::beginTransaction();
            $salaryIncrement = SalaryIncrement::create($data);

            if ($salaryIncrement) {
                // Fetch employees to link
                $employees = Employee::where('employee_type', 13)->get();

                // Create salary_increment_employees for each employee
                $salaryIncrementEmployees = $salaryIncrement->salaryIncrementEmployees()->createMany(
                    $employees->map(function ($emp) use ($salaryIncrement) {
                        return [
                            'salary_increment_id' => $salaryIncrement->id,
                            'employee_id'         => $emp->id,
                        ];
                    })
                );

                // Copy allowances for each increment employee
                foreach ($salaryIncrementEmployees as $incrementEmployee) {
                    $employee = $employees->firstWhere('id', $incrementEmployee->employee_id);

                    if ($employee) {
                        $allowances = $employee->employeeAllowanceDeduction->map(function ($allowance) use ($incrementEmployee) {
                            // Take all fields except PK & old FK
                            return Arr::except($allowance->toArray(), ['id', 'employee_id']) + [
                                'salary_increment_employees_id' => $incrementEmployee->id,
                                'employee_id' => $incrementEmployee->employee_id,
                            ];
                        });

                        if ($allowances->isNotEmpty()) {
                            $incrementEmployee->employeeAllowanceDeduction()->createMany($allowances);
                        }
                    }
                }

                $salaryIncrement->load('financialYear');
                DB::commit();
                return resp('1', 'Created Successfully!', $salaryIncrement, Response::HTTP_OK);
            }

            DB::rollBack();
            return resp('0', 'Something went wrong', null, Response::HTTP_OK);

        } catch (\Throwable $th) {
            DB::rollBack();
            return resp('0', 'Something went wrong', $th->getMessage(), Response::HTTP_OK);
        }
    }


    //show
    public function show(SalaryIncrement $salaryIncrement)
    {
        $salaryIncrement->load([
            'financialYear',
            'salaryIncrementEmployees.employee.salutation',
            'salaryIncrementEmployees.employee.gender',
            'salaryIncrementEmployees.employee.employeeTyp',
            'salaryIncrementEmployees.employee.department',
            'salaryIncrementEmployees.employee.branchOffice',
            'salaryIncrementEmployees.employee.reportTo',
            'salaryIncrementEmployees.employee.designation',
            'salaryIncrementEmployees.employee.empSalary.salarySegregation',
            'salaryIncrementEmployees.employee.grade',
            'salaryIncrementEmployees.employee.latestPayScale.latestSalaryRange',
            'salaryIncrementEmployees.changeLogs',
            'salaryIncrementEmployees.appraisalSalarySetups.employeeSalarySegregations',
            'salaryIncrementEmployees.employeeAllowanceDeduction',
            'salaryIncrementEmployees.employee.performancePlanning.performanceFactors' => ['questionSection']
        ]);
        $salaryIncrement->salaryIncrementEmployees->each(function ($sie) {
            $sie->employee->append('project_details');
        });
        $data['salaryIncrement']=$salaryIncrement;
        $data['approval_request'] = getNextApproval(76, auth()->user()->designation_id, $salaryIncrement->id);
        $data['approval_request_status'] = checkApprovalRequestStatus(76, $salaryIncrement->id);
        return resp('1', 'Record Fetched Successfully!', $data, Response::HTTP_OK);
    }

    //update
    public function update(Request $request, SalaryIncrement $salaryIncrement)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'financial_year' => 'nullable|exists:type_values,id',
            'description' => 'nullable|string',
        ]);

        $salaryIncrement->update($data);

        return resp('1', 'Record Updated Successfully!', $salaryIncrement, Response::HTTP_OK);
    }

    //delete
    public function destroy(SalaryIncrement $salaryIncrement)
    {
        $salaryIncrement->delete();

        return resp('1', 'Record Deleted Successfully!', [], Response::HTTP_NO_CONTENT);
    }

    //dropdown
    public function salaryIncrementDropdown()
    {
        $data = [];

         try {
            DB::beginTransaction();

            $data['employee_detail']=Employee::query()->with('department','designation','employeeTyp')->get();
            $data['designation_list']=Designation::all();
            $data['departments']=Type::getTypeValues('department-names');
            $data['employee_type']=Type::getTypeValues('employee-type');
            $data['paysclae_grading']=PayscaleGrading::query()->get();
            $data['projects']=ProjectProfile::query()->where('approval_status',1)->get();
            $data['gross_allowances']=PreGrossSalaryAllowances::query()->with('allowanceType')->get();
            $data['allowance_deduction_list']=AllowanceDeductionResource::collection(AllowanceDeduction::all());

            DB::commit();
            return resp(0,'Successfully!', $data,Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage() .' on line :: '.$e->getLine() , null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function changeEmployeeDetails(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'salary_increments_id' => 'required|integer',
                'salary_increment_employees_id' => 'required|integer',
                'EmployeeID' => 'required|integer',
                'description' => 'required|array',
                'effective_date' => 'required|date',
            ]);

            $EmployeeID   = $request->EmployeeID;
            $effective_date = date('Y-m-d', strtotime($request->effective_date));

            foreach ($request->description as $key => $desc) {
                $data = [
                    "EmployeeID"                   => $EmployeeID,
                    "description"                  => $desc,
                    "change_from"                  => $request->change_from[$key] ?? null,
                    "change_to"                    => $request->change_to[$key] ?? null,
                    "remarks"                      => $request->remarks[$key] ?? null,
                    "effective_date"               => $effective_date,
                    "change_from_id"               => $request->change_from_id[$key] ?? null,
                    "change_to_id"                 => $request->change_to_id[$key] ?? null,
                    "change_type"                  => 2,
                    "salary_increments_id"         => $request->salary_increments_id,
                    "salary_increment_employees_id"=> $request->salary_increment_employees_id,
                ];

                // 🔹 Update if exists, otherwise create
                EmployeeChangeLog::updateOrCreate(
                    [
                        "salary_increment_employees_id" => $request->salary_increment_employees_id,
                        "description"                   => $desc, // you can include more keys if uniqueness requires
                    ],
                    $data
                );
            }

            DB::commit();
            return resp(1, 'Successfully saved!', [], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(
                0,
                'Failed to save record. Error: ' . $e->getMessage() . ' on line :: ' . $e->getLine(),
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }


    public function employeeSalaryDetails($id){

        $salaryIncrement = SalaryIncrementEmployee::
                                with(
                                    [
                                    'employee',
                                    'employee.salutation',
                                    'employee.gender',
                                    'employee.employeeTyp',
                                    'employee.department',
                                    'employee.branchOffice',
                                    'employee.reportTo',
                                    'employee.designation',
                                    'employee.empSalary.salarySegregation',
                                    'employee.grade',
                                    'employee.latestPayScale.latestSalaryRange',
                                    'changeLogs',
                                    'appraisalSalarySetups.employeeSalarySegregations',
                                    'employeeAllowanceDeduction',
                                    'employee.performancePlanning.performanceFactors' => ['questionSection']
                                    ])->find($id);
        if(!$salaryIncrement){
            return resp('0', 'Record Not Found!', null, Response::HTTP_NOT_FOUND);
        }

        $salaryIncrement->employee->append('project_details');
        return resp('1', 'Record Fetched Successfully!', $salaryIncrement, Response::HTTP_OK);
    }

    public function employeeIncrementUpdate(Request $request){
        $request->validate([
            'salary_increment_employee_id' => 'required|integer',
            'approval_status' => 'required',
        ]);
        $salaryIncrementEmployee = SalaryIncrementEmployee::find($request->salary_increment_employee_id);
        if(!$salaryIncrementEmployee){
            return resp('0', 'Record Not Found!', null, Response::HTTP_NOT_FOUND);
        }
        $salaryIncrementEmployee->update(['approval_status'=>$request->approval_status]);
        return resp('1', 'Record Updated Successfully!', $salaryIncrementEmployee, Response::HTTP_OK);
    }
    public function sendSalaryIncrementForApproval(SalaryIncrement $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',76)->first();
        $sp_approval_process=ApprovalProcess::query()->where('approval_process_id',76)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',76)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($sp_approval_process->count() > 0 && $checkProcess == 0){

            foreach ($sp_approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                $Approval=ApprovalProcessList::query()->create($insert);
                sendNotification($approval['designation_id'],$approval_process_name->approval_process_name);

            }
            $update=array('approval_status'=>2);
            SalaryIncrement::query()->where('id',$item->id)->update($update);
            return resp(1,'Salary Increment send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Salary Increment approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    //add employees to salary increment
    public function addSalaryIncrementEmployee(Request $request)
    {
        $data = $request->validate([
            'salary_increment_id' => 'required|exists:salary_increments,id',
            'employee_id'         => 'required|exists:employees,id',
        ]);

        try {
            DB::beginTransaction();

            $salaryIncrement = SalaryIncrement::find($data['salary_increment_id']);
            $employee        = Employee::find($data['employee_id']);

            if ($salaryIncrement && $employee) {
                // 1️⃣ Check for duplicate
                $exists = $salaryIncrement->salaryIncrementEmployees()
                    ->where('employee_id', $employee->id)
                    ->exists();

                if ($exists) {
                    DB::rollBack();
                    return resp('0', 'Employee already added to this Salary Increment.', null, Response::HTTP_OK);
                }

                // 2️⃣ Create salary_increment_employee record
                $incrementEmployee = $salaryIncrement->salaryIncrementEmployees()->create([
                    'salary_increment_id' => $salaryIncrement->id,
                    'employee_id'         => $employee->id,
                ]);

                // 3️⃣ Copy allowances from original employee to this increment employee
                $allowances = $employee->employeeAllowanceDeduction->map(function ($allowance) use ($incrementEmployee) {
                    return Arr::except($allowance->toArray(), ['id', 'employee_id']) + [
                        'salary_increment_employees_id' => $incrementEmployee->id,
                        'employee_id'                   => $incrementEmployee->employee_id,
                    ];
                });

                if ($allowances->isNotEmpty()) {
                    $incrementEmployee->employeeAllowanceDeduction()->createMany($allowances);
                }

                DB::commit();
                return resp('1', 'Employee Added Successfully!', $salaryIncrement->load('salaryIncrementEmployees'), Response::HTTP_OK);
            }

            DB::rollBack();
            return resp('0', 'Salary Increment or Employee not found', null, Response::HTTP_OK);

        } catch (\Throwable $th) {
            DB::rollBack();
            return resp('0', 'Something went wrong', $th->getMessage(), Response::HTTP_OK);
        }
    }

    //delete employees from salary increment
    public function deleteSalaryIncrementEmployee($salary_increment_employee_id)
    {
        try {
            DB::beginTransaction();

            $incrementEmployee = SalaryIncrementEmployee::find($salary_increment_employee_id);

            if (!$incrementEmployee) {
                DB::rollBack();
                return resp('0', 'Salary Increment Employee not found', null, Response::HTTP_OK);
            }

            // 1️⃣ Delete related allowances
            $incrementEmployee->employeeAllowanceDeduction()->delete();

            // 2️⃣ Delete the increment employee record
            $incrementEmployee->delete();

            DB::commit();
            return resp('1', 'Employee Removed Successfully!', null, Response::HTTP_OK);

        } catch (\Throwable $th) {
            DB::rollBack();
            return resp('0', 'Something went wrong', $th->getMessage(), Response::HTTP_OK);
        }
    }



}

<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\EmployeeChangeLog;
use App\Models\Configuration\EmployeeStatusChange;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\HR\Payroll\EmployeeSalarySetup;
use App\Models\HR\Payscale\PayscaleGrading;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmployeeChangeLogController extends Controller
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


        try {
            DB::beginTransaction();
            $request->validate([
                'EmployeeID' => 'required',
                'description' => 'required',
                'fld_request_id' => 'required',
                'effective_date' => 'required|date',
            ]);
            $fld_request_id=$request->fld_request_id;
            $EmployeeID=$request->EmployeeID;
            $description=$request->description;
            $change_from=$request->change_from;
            $change_to=$request->change_to;
            $remarks=$request->remarks;
            $effective_date=date('Y-m-d',strtotime($request->effective_date));
            $change_from_id=$request->change_from_id;
            $change_to_id=$request->change_to_id;

            foreach($description as $key => $desc){
                $insert=array(
                    "EmployeeID"=>$EmployeeID,
                    "description"=>$desc,
                    "change_from"=>$change_from[$key],
                    "change_to"=>$change_to[$key],
                    "remarks"=>$remarks[$key],
                    "effective_date"=>$effective_date,
                    "change_from_id"=>$change_from_id[$key],
                    "change_to_id"=>$change_to_id[$key],
                    "status_change_req_id"=>$fld_request_id,
                );
                EmployeeChangeLog::query()->create( $insert);

                if( $desc == 'Employee Type'){
                    Employee::query()->where('id',$EmployeeID)->update(array('employee_type'=>$change_to_id[$key]));

                    if($change_to_id[$key] == 13){
                        updateEmployeeYearlyLeave($EmployeeID);
                    }
                }
                if( $desc == 'Designation'){
                    Employee::query()->where('id',$EmployeeID)->update(array('designation_id'=>$change_to_id[$key]));
                    User::query()->where('employee_id',$EmployeeID)->update(array('designation_id'=>$change_to_id[$key]));
                }
                if( $desc == 'Department'){
                    Employee::query()->where('id',$EmployeeID)->update(array('department_id'=>$change_to_id[$key]));
                }
                if( $desc == 'Grade'){
                    Employee::query()->where('id',$EmployeeID)->update(array('grade'=>$change_to_id[$key]));

                    $payscale_salary = PayscaleGrading::query()->where('id', $change_to_id[$key])->first();

                    if ($payscale_salary) {
                        EmployeeSalarySetup::query()
                            ->where('employee_id', $EmployeeID)
                            ->update(['monthly_salary' => $payscale_salary->amount]);
                    }
                }

            }
            EmployeeStatusChange::query()->where('id',$request->fld_request_id)->update(array('request_status'=>1));
            DB::commit();
            return resp(1,'Successfully!', [],Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage() .' on line :: '.$e->getLine() , null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeeChangeLog $employeeChangeLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeChangeLog $employeeChangeLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeChangeLog $employeeChangeLog)
    {
        //
    }

    public function employeeChangeLogDropDown(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'emp_id' => 'required',
            ]);

            $data['employee_detail']=Employee::query()->with('department','designation','employeeTyp')->where('id',$request->emp_id)->first();
            $data['designation_list']=Designation::all();
            $data['departments']=Type::getTypeValues('department-names');
            $data['employee_type']=Type::getTypeValues('employee-type');
            $data['paysclae_grading']=PayscaleGrading::query()->get();

            DB::commit();
            return resp(0,'Successfully!', $data,Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage() .' on line :: '.$e->getLine() , null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

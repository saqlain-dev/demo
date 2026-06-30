<?php

namespace App\Http\Controllers\Api\V1\HR\Payscale;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Employee;
use App\Models\HR\Payroll\EmployeeSalarySetup;
use App\Models\HR\Payscale\EmployeeColaHistory;
use App\Models\HR\Payscale\Payscale;
use App\Models\HR\Payscale\SalaryRange;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SalaryRangeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = SalaryRange::with(['PayscaleId','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'payscale_id' => 'required',
            'min_range' => 'required|numeric',
            'max_range' => 'required|numeric',
            'year' => 'required',
            'added_date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = SalaryRange::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SalaryRange $salaryRange): JsonResponse
    {
        $data['salaryRange'] = $salaryRange->load(['PayscaleId','created_by','updated_by']);
        $data['approval_request']=getNextApproval(40,auth()->user()->designation_id,$salaryRange->id);
        $data['approval_request_status']=checkApprovalRequestStatus(40,$salaryRange->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalaryRange $salaryRange)
    {
        $request->validate([
            'payscale_id' => 'required',
            'min_range' => 'required|numeric',
            'max_range' => 'required|numeric',
            'year' => 'required',
            'added_date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $salaryRange->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalaryRange $salaryRange): JsonResponse
    {
        $item = $salaryRange->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function addCola(Request $request, SalaryRange $salaryRange)
    {
        $request->validate([
            'cola_percentage' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
        ]);

        try {
            if($salaryRange->approval_status == 2){
                DB::beginTransaction();
                $item = $salaryRange->update($this->input);
                DB::commit();
                return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
            }else{
                DB::beginTransaction();
                $item = $salaryRange->update($this->input);
                $responce=$this->sendColaPercentageForApproval($salaryRange);
                DB::commit();
                return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function applyCola(SalaryRange $salaryRange)
    {


        try {
            DB::beginTransaction();
            $insert=array(
                'payscale_id'=>$salaryRange->payscale_id,
            );
            $min_percentage = ($salaryRange->cola_percentage / 100) * $salaryRange->min_range;
            $insert['min_range'] = $min_percentage + $salaryRange->min_range;
            $max_percentage= ($salaryRange->cola_percentage / 100) * $salaryRange->max_range;
            $insert['max_range'] = $max_percentage + $salaryRange->max_range;
            $insert['added_date'] = date('Y-m-d');
            $insert['year'] =  $salaryRange->year + 1;
            $newAntry=SalaryRange::query()->create($insert);
            if($newAntry){
                $salaryRange->isApplied=1;
                $salaryRange->save();
                $payscale=Payscale::query()->where('id',$salaryRange->payscale_id)->first();
                if($payscale && $payscale->position != ""){
                    $employee_list=Employee::query()->whereIn('designation_id',explode(',',$payscale->position))->whereNotIn('employee_type', [14, 16, 17, 18])->get();
                    if($employee_list){
                        foreach($employee_list as $employee){
                            $monthlySalary=EmployeeSalarySetup::query()->where('employee_id',$employee['id'])->first();
                            if($monthlySalary){
                                $salary = ($salaryRange->cola_percentage / 100) * $monthlySalary->monthly_salary;
                                $beforeSalary=$monthlySalary->monthly_salary;
                                $afterColaSalary=$monthlySalary->monthly_salary + $salary;
                                $insert=array(
                                    'EmployeeId'=>$monthlySalary->employee_id,
                                    'salary_before_cola'=>$beforeSalary,
                                    'salary_after_cola'=>$afterColaSalary,
                                    'salary_range_id'=>$salaryRange->id,
                                );
                                EmployeeColaHistory::query()->create($insert);
                                $monthlySalary->monthly_salary=$afterColaSalary;
                                $monthlySalary->save();
                            }
                        }
                    }
                }
            }

            DB::commit();
            return resp('1', 'COLA Applied Successfully!', $salaryRange, Response::HTTP_CREATED);


        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function sendColaPercentageForApproval(SalaryRange $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',40)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',40)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',40)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                if($approval_process_name->isFinancialApproval == 1){
                    if($approval->financialAmount < $item->min_range  ){
                        $insert['approval_status']=0;
                        $Approval=ApprovalProcessList::query()->create($insert);
                    }else{
                        $Approval=ApprovalProcessList::query()->create($insert);
                    }
                }else{
                    $Approval=ApprovalProcessList::query()->create($insert);
                }

                sendNotification($approval['designation_id'],$approval_process_name->approval_process_name);

            }
            $update=array('approval_status'=>2);
            SalaryRange::query()->where('id',$item->id)->update($update);
            return resp(1,'COLA request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'COLA approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}

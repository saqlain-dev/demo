<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\BranchOffice;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Finance\CourtExpense;
use App\Models\Finance\SubGrants\SubGrantFinancialReport;
use App\Models\GratuityCalculation;
use App\Models\HeadOffice;
use App\Models\HR\Recruitment\ConsultantTimesheet;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GratuityCalculationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = GratuityCalculation::with(['created_by','updated_by', 'GratuityDetail'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'period' => 'required',
            'year' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $item = GratuityCalculation::query()->create($this->input);
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
    public function show(GratuityCalculation $gratuityCalculation): JsonResponse
    {
        $data['gratuityCalculation']=$gratuityCalculation = $gratuityCalculation->load(['created_by','updated_by','GratuityDetail.EmployeeId' => ['headOffice','branchOffice','designation','grade', 'EmployeeSalary']]);
        $data['approval_request']=getNextApproval(56,auth()->user()->designation_id,$gratuityCalculation->id);
        $data['approval_request_status']=checkApprovalRequestStatus(56,$gratuityCalculation->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GratuityCalculation $gratuityCalculation)
    {
        $request->validate([
            'period' => 'required',
            'year' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $item = $gratuityCalculation->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GratuityCalculation $gratuityCalculation): JsonResponse
    {
        $item = $gratuityCalculation->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function sendGratuityForApproval(GratuityCalculation $item)
    {
        $approval_process=ApprovalProcess::query()->where('approval_process_id',56)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',56)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            GratuityCalculation::query()->where('id',$item->id)->update($update);
            return resp(1,'Gratuity sent for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Gratuity approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function GrDropdown()
    {
        $data['probation_plus_employee'] = Employee::with(['headOffice','branchOffice','designation','grade','EmployeeSalary', 'latestGratuityCalculation.gratuityCalculation'])
            ->whereDate('date_of_joining', '<=', Carbon::now()->subMonths(3))
            ->get();
        $data['department']= Type::getTypeValues('department-names');
        $data['head_office']= HeadOffice::all();
        $data['branch_office']= BranchOffice::all();
        $data['designation'] = Designation::all();
        $data['employeeType']= Type::getTypeValues('employee-type');
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);

    }
}

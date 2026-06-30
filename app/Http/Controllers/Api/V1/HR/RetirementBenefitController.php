<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\HR\Payscale\Payscale;
use App\Models\HR\RetirementBenefit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RetirementBenefitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'retirement_benefit_view',
            'manage_audit_retirement_benefits',
        ]);

        $data['items'] = RetirementBenefit::with(['EmployeeId','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'retirement_benefit_create',
        ]);

        $request->validate([
            'employee_id' => 'required',
            'joining_date' => 'required',
            'resignation_date' => 'required',
            'salary' => 'required',
            'years' => 'required',
            'months' => 'required',
            //'days' => 'required',
            'years_of_calc_gratuity' => 'required',
            'gratuity_amount' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = RetirementBenefit::query()->create($this->input);
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
    public function show(RetirementBenefit $retirementBenefit): JsonResponse
    {
        $this->authorizeAny([
            'retirement_benefit_view',
        ]);

        $data['item'] = $retirementBenefit->load(['EmployeeId','created_by','updated_by']);
        $data['approval_request']=getNextApproval(45,auth()->user()->designation_id,$retirementBenefit->id);
        $data['approval_request_status']=checkApprovalRequestStatus(45,$retirementBenefit->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RetirementBenefit $retirementBenefit)
    {
        $this->authorizeAny([
            'retirement_benefit_update',
        ]);

        $request->validate([
            'employee_id' => 'required',
            'joining_date' => 'required',
            'resignation_date' => 'required',
            'salary' => 'required',
            'years' => 'required',
            'months' => 'required',
            'years_of_calc_gratuity' => 'required',
            'gratuity_amount' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $retirementBenefit->update($this->input);
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
    public function destroy(RetirementBenefit $retirementBenefit): JsonResponse
    {
        $this->authorizeAny([
            'retirement_benefit_delete',
        ]);

        $item = $retirementBenefit->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function sendRetirementBenefitForApproval(RetirementBenefit $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',45)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',45)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',45)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
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
            RetirementBenefit::query()->where('id',$item->id)->update($update);
            return resp(1,'Retirement Benefit request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Retirement Benefit approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}

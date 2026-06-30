<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Finance\CourtExpense;
use App\Models\HR\Recruitment\ConsultantTimesheet;
use App\Models\HR\Recruitment\ConsultantTimesheetDetail;
use App\Models\HR\Recruitment\InterviewCommittee;
use App\Models\HR\Recruitment\RecruitmentPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ConsultantTimesheetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'consultant_timesheet_view',
            'manage_employee_portal'
        ]);

        $data = ConsultantTimesheet::with(['EmployeeId','created_by','updated_by','ConsultantTimesheetDetail'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'consultant_timesheet_create',
            'manage_employee_portal'
        ]);

        $request->validate([
            'employee_id' => 'required',
            'month' => 'required',
            'consultant_timesheet_detail.*'=> 'required',
            'manage_employee_portal'
        ]);
        try {
            DB::beginTransaction();
            $item = ConsultantTimesheet::query()->create($request->except('consultant_timesheet_detail'));
            if ($item){
                foreach ($this->input['consultant_timesheet_detail'] as $detail){
                    $detail['consultant_timesheet_id'] = $item->id;
                    ConsultantTimesheetDetail::query()->create($detail);
                }
            }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ConsultantTimesheet $consultantTimesheet): JsonResponse
    {
        $this->authorizeAny([
            'consultant_timesheet_view',
            'manage_employee_portal'
        ]);

//        $this->authorizeAny([
//            'log_book_view'
//        ]);

        $data['consultantTimesheet'] = $consultantTimesheet = $consultantTimesheet->load(['EmployeeId','created_by','updated_by','ConsultantTimesheetDetail']);
        $data['approval_request']=getNextApproval(55,auth()->user()->designation_id,$consultantTimesheet->id);
        $data['approval_request_status']=checkApprovalRequestStatus(55,$consultantTimesheet->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ConsultantTimesheet $consultantTimesheet)
    {
        $this->authorizeAny([
            'consultant_timesheet_update',
            'manage_employee_portal'
        ]);

        $request->validate([
            'employee_id' => 'required',
            'month' => 'required',
        ]);

        try {
            DB::beginTransaction();

            // Update the main consultant timesheet record
            $item = $consultantTimesheet->update($request->except('consultant_timesheet_detail'));

            // Check if there are details to update
            if ($request->has('consultant_timesheet_detail')) {
                // Delete old details if needed (optional, depends on requirements)
                $consultantTimesheet->ConsultantTimesheetDetail()->delete();

                // Add new or updated details
                foreach ($request->input('consultant_timesheet_detail') as $detail) {
                    $detail['consultant_timesheet_id'] = $consultantTimesheet->id;
                    ConsultantTimesheetDetail::query()->create($detail);
                }
            }

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
    public function destroy(ConsultantTimesheet $consultantTimesheet): JsonResponse
    {
        $this->authorizeAny([
            'consultant_timesheet_delete',
            'manage_employee_portal'
        ]);

//        $this->authorizeAny([
//            'log_book_delete'
//        ]);
        $consultantTimesheet->ConsultantTimesheetDetail()->delete();
        $item = $consultantTimesheet->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
    public function sendConsultantTimesheetForApproval(ConsultantTimesheet $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',55)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',55)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',55)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            ConsultantTimesheet::query()->where('id',$item->id)->update($update);
            return resp(1,'Consultant Timesheet sent for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Consultant Timesheet approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V1\HR\TimeSheet;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Employee;
use App\Models\HR\TimeSheet\EmployeeTimesheet;
use App\Models\HR\TimeSheet\EmployeeTimesheetDetail;
use App\Models\Program\Project\ProjectProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmployeeTimesheetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'consultant_timesheet_view',
            'employee_timesheet_view',
            'manage_audit_payroll',
            'manage_audit_consultant_management',
        ]);

        $data['timesheet_listing']= EmployeeTimesheet::query()->with('employeeDetail.employeeTyp')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'consultant_timesheet_create',
            'employee_timesheet_create',
        ]);

        $request->validate([
            'employeeID' => 'required|integer',
            'timesheet_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();
            $employeeTimesheet= EmployeeTimesheet::query()->where('employeeID',$request->employeeID)->where('timesheet_date',date('Y-m-d',strtotime($request->timesheet_date)))->first();

            $employeeInsert=array();
            $employeeInsert['employeeID']=$request->employeeID;
            $employeeInsert['timesheet_date']=date('Y-m-d',strtotime($request->timesheet_date));
            if(empty($employeeTimesheet)){
                $employeeTimesheet=EmployeeTimesheet::query()->create($employeeInsert);
            }
            if($employeeTimesheet){
                $projects=$request->projects;
                foreach($projects as $projectTimesheet){
                    $insert=array();
                    $insert['employee_time_sheet_id']=$employeeTimesheet->id;
                    $insert['project_id']=$projectTimesheet['project_id'];
                    foreach ($projectTimesheet['timesheet'] as $timesheet)
                    {

                        $insert['timesheet_date']=date('Y-m-d',strtotime($timesheet['timesheet_date']));
                        $insert['employee_work_percent']=$timesheet['employee_work_percent'];
                        $timeSheetDetailCheck=EmployeeTimesheetDetail::query()->where('employee_time_sheet_id',$employeeTimesheet->id)->where('project_id',$projectTimesheet['project_id'])->where('timesheet_date',date('Y-m-d',strtotime($timesheet['timesheet_date'])))->first();

                        if(empty($timeSheetDetailCheck)){
                            EmployeeTimesheetDetail::query()->create($insert);
                        }else{
                            EmployeeTimesheetDetail::query()->where('id',$timeSheetDetailCheck->id)->update($insert);
                        }

                    }


                }
            }
           $data['employeeTimesheet']=$employeeTimesheet->load('employeeSheetDetail');


            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();

            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeeTimesheet $timesheet)
    {
        $this->authorizeAny([
            'consultant_timesheet_view',
            'employee_timesheet_view',
            'manage_audit_consultant_management',
        ]);

        $timesheet->load('employeeSheetDetail');
        $data['timesheet']=$timesheet->groupBy('project_id');

        /*$timesheet['projects'] = $timesheet->employeeSheetDetail->groupBy(function ($factor) {
            //return $factor->project_id;
            return $factor->id;
        });*/
        $timesheet['projects'] = $timesheet->employeeSheetDetail->groupBy('project_id')->map(function ($items, $projectId) {
            $projectName="";
            if($items->first()->project_id){

                $projecDetal=ProjectProfile::query()->where('id',$items->first()->project_id)->first();
                $projectName=$projecDetal->project_name;
            }
           // $projectName = $items->first()->project->project_name; // Assuming there's a project relation
            return [
                'project_id' => $projectId,
                'project_name' => $projectName,
                'details' => $items
            ];
        })->values()->all();

        $data['timesheet']=$timesheet;
        $data['approval_request']=getNextApproval(46,auth()->user()->designation_id,$timesheet->id);
        $data['approval_request_status']=checkApprovalRequestStatus(46,$timesheet->id);

        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeTimesheet $employeeTimesheet)
    {
        $this->authorizeAny([
            'consultant_timesheet_update',
            'employee_timesheet_update',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeTimesheet $employeeTimesheet)
    {
        $this->authorizeAny([
            'consultant_timesheet_delete',
            'employee_timesheet_delete',
        ]);
    }

    public function timeSheetDropDown(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
        ]);
        try {
            DB::beginTransaction();
            $employee_detail=Employee::query()->find($request->employee_id);
            $employee_detail->project_details = $employee_detail->project_details;
            $data['employee_detail']=$employee_detail;

            $data['first_day_this_month']= date('m-01-Y'); // hard-coded '01' for first day
            $data['last_day_this_month']  = date('m-t-Y');
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    public function sendEmployeeTimeSheetForApproval(EmployeeTimesheet $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',46)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',46)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',46)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            EmployeeTimesheet::query()->where('id',$item->id)->update($update);
            return resp(1,'Timesheet send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Timesheet approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}

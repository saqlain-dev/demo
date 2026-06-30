<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\HR\Attendance\EmployeeManuelAttendance;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{

    public function employeeDailyAttendanceReport(Request $request)
    {
        $this->authorizeAny([
            'daily_attendance_report',
            'manage_employee_portal',
            'dashboard-hr',
        ]);

        $request->validate([
            'date' => 'required|date',
        ]);
        try {
            DB::beginTransaction();
            $rawData= DB::select('EXEC AMS_DaillyAttendanceReport ?', [$request->date]);
            $data['daily_attendance'] = json_decode(json_encode($rawData), true);
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    public function employeeAttendance(Request $request)
    {
        $this->authorizeAny([
            'attendance_view',
            'manage_employee_portal',
            'manage_audit_attendance',
        ]);

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'empID' => 'required|integer',
        ]);
        try {
            DB::beginTransaction();
            $start_date=date('Y-m-d',strtotime($request->start_date));
            $end_date=date('Y-m-d',strtotime($request->end_date));
            $empID=$request->empID;
            $rawData= DB::select('EXEC AMS_MonthlyAttendanceReport ?,?,?', [$start_date,$end_date,$empID]);
            $data['monthly_attendance'] = json_decode(json_encode($rawData), true);

            $summary= DB::select('EXEC [AMS_MonthlyAttendanceSummary] ?,?,?', [$start_date,$end_date,$empID]);
            $data['summary_attendance'] = json_decode(json_encode($summary), true);
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function monhtlyAttendance(Request $request)
    {
        $this->authorizeAny([
            'monthly_attendance_report',
        ]);

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);
        try {
            DB::beginTransaction();
            $start_date=date('Y-m-d',strtotime($request->start_date));
            $end_date=date('Y-m-d',strtotime($request->end_date));
            $result = DB::select('EXEC AMS_EmployeesMonthlyAttendanceReport ?, ?', [$start_date, $end_date]);
            $data['monthly_attendance'] = $result;
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function updateAttendance(Request $request)
    {
        $request->validate([
            'att_date' => 'required|date',
            'att_timeIn' => 'required',
            'att_TimeOut' => 'required',
            'USERID' => 'required|integer',
        ]);
        try {
            DB::beginTransaction();

            $att_date=date('Y-m-d',strtotime($request->att_date));
            $att_timeIn=date('Y-m-d H:i:s',strtotime($request->att_timeIn));
            $att_TimeOut=date('Y-m-d H:i:s',strtotime($request->att_TimeOut));
            $USERID=$request->USERID;
            $dayName = Carbon::parse($att_date)->format('l');

            $results = DB::table('shifts')
                ->join('shift_details', 'shifts.id', '=', 'shift_details.shift_id')
                ->select('shift_details.*')
                ->whereIn('shift_details.shift_id', function($query) use ($USERID) {
                    $query->select('shift_id')
                        ->from('employees')
                        ->where('id', $USERID);
                })
                ->where('shift_details.shift_day',$dayName)
                ->first();


            if($results){
                $reultReturn = DB::table('ams_check_in_outs')
                    ->where('USERID', $USERID)
                    ->where('att_date', $att_date)
                    ->first();
                if($reultReturn){
                    $update=array(
                        'USERID'=>$request->USERID,
                        'VERIFYCODE'=>0,
                        'SENSORID'=>29,
                        'att_date'=>$att_date,
                        'att_timeIn'=>$att_timeIn,
                        'att_TimeOut'=>$att_TimeOut,
                        'ShiftStartTime'=>date('H:i A',strtotime($results->shift_start_time)),
                        'ShiftEndTime'=>date('H:i A',strtotime($results->shift_end_time)),
                        'SensorIDOut'=>29,
                        'IsUpdated'=>1,
                        'attendance_remarks'=>$request->attendance_remarks,
                    );
                    $affected = DB::table('ams_check_in_outs')
                        ->where('USERID', $USERID)
                        ->where('att_date', $att_date)
                        ->update($update);
                }else{
                    $update=array(
                        'USERID'=>$request->USERID,
                        'VERIFYCODE'=>0,
                        'SENSORID'=>29,
                        'att_date'=>$att_date,
                        'att_timeIn'=>$att_timeIn,
                        'att_TimeOut'=>$att_TimeOut,
                        'ShiftStartTime'=>date('H:i A',strtotime($results->shift_start_time)),
                        'ShiftEndTime'=>date('H:i A',strtotime($results->shift_end_time)),
                        'SensorIDOut'=>29,
                        'IsUpdated'=>1,
                        'attendance_remarks'=>$request->attendance_remarks,
                    );
                    $affected = DB::table('ams_check_in_outs')
                        ->where('USERID', $USERID)
                        ->where('att_date', $att_date)
                        ->insert($update);
                }

                DB::commit();
                $reultReturn = DB::table('ams_check_in_outs')
                    ->where('USERID', $USERID)
                    ->where('att_date', $att_date)
                    ->first();
                $data['attendance']=$reultReturn;
                return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
            }else{
                return resp(0, 'Failed to update record!', ['error' => 'Shift Record not found'], Response::HTTP_EXPECTATION_FAILED);
            }



        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function addManualAttendance(Request $request)
    {
        $this->authorizeAny([
            'manual_attendance_create',
            'manage_employee_portal',
        ]);

        $request->validate([
            'att_date' => [
                'required',
                'date',
                Rule::unique('employee_manuel_attendances')
                    ->where('userid', $request->USERID)
            ],
            'att_timeIn' => 'required',
            'att_timeOut' => 'required',
            'USERID' => 'required|integer',
        ], [
            'att_date.unique' => 'Attendance for this date already exists for the selected user.',
        ]);
        try {
            DB::beginTransaction();

            $WFH=EmployeeManuelAttendance::query()->whereYear('att_date',date('Y',strtotime($request->att_date)))->whereMonth('att_date',date('m',strtotime($request->att_date)))->whereIn('approval_status',[1,2])->where('userid',$request->USERID)->where('manual_attendance_type',460)->count();

            if($WFH == 0) {

                $att_date = date('Y-m-d', strtotime($request->att_date));
                $att_timeIn = date('Y-m-d H:i:s', strtotime($request->att_timeIn));
                $att_TimeOut = date('Y-m-d H:i:s', strtotime($request->att_timeOut));
                $USERID = $request->USERID;
                $update = array(
                    'userid' => $request->USERID,
                    'att_date' => $att_date,
                    'att_timeIn' => $att_timeIn,
                    'att_timeOut' => $att_TimeOut,
                    'remarks' => $request->remarks,
                    'manual_attendance_type' => $request->manual_attendance_type,
                    'isHoliday' => $request->isHoliday,
                );

                $affected = DB::table('employee_manuel_attendances')
                    ->insert($update);

                DB::commit();
                $attendanceList = EmployeeManuelAttendance::query()->where('userid', $USERID)->with(['Userid', 'manualAttendance'])->get();
                $data['attendance'] = $attendanceList;
                return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
            }else{
                return resp(0, 'Only one WFH request is allowed in a month.', Response::HTTP_EXPECTATION_FAILED);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function updateManualAttendance(Request $request)
    {
        $this->authorizeAny([
            'manual_attendance_update',
            'manage_employee_portal',
        ]);

        $request->validate([
            'att_date' => 'required|date',
            'att_timeIn' => 'required',
            'att_timeOut' => 'required',
            'USERID' => 'required|integer',
            'att_id' => 'required|integer',
        ]);
        try {
            DB::beginTransaction();
            $att_detail=EmployeeManuelAttendance::query()->find($request->att_id);
            if(!empty($att_detail) && $att_detail->approval_status == 4){
                $att_date=date('Y-m-d',strtotime($request->att_date));
                $att_timeIn=date('Y-m-d H:i:s',strtotime($request->att_timeIn));
                $att_TimeOut=date('Y-m-d H:i:s',strtotime($request->att_timeOut));
                $USERID=$request->USERID;
                $att_id=$request->att_id;
                $update=array(
                    'userid'=>$request->USERID,
                    'att_date'=>$att_date,
                    'att_timeIn'=>$att_timeIn,
                    'att_timeOut'=>$att_TimeOut,
                    'remarks'=>$request->remarks,
                    'manual_attendance_type'=>$request->manual_attendance_type,
                    //'isHoliday'=>$request->isHoliday,
                );

                EmployeeManuelAttendance::query()->find($att_id)
                    ->update($update);

                DB::commit();
                $attendanceList=EmployeeManuelAttendance::query()->where('userid',$USERID)->with(['Userid','manualAttendance'])->get();
                $data['attendance']=$attendanceList;
                return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
            }else{
                return resp(1, 'You cannot update this record.', [], Response::HTTP_OK);
            }


        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function manualAttendanceListing(Request $request)
    {
        $this->authorizeAny([
            'manual_attendance_view',
            'manage_employee_portal',
        ]);

        $request->validate([
            'userid' => 'required|integer',
        ]);
        try {
            $USERID=$request->userid;
            $status=$request->status;
            if($status == 1){
                $attendanceList=EmployeeManuelAttendance::query()->where('userid',$USERID)->with(['Userid','manualAttendance'])->get();
            }else{
                $attendanceList=EmployeeManuelAttendance::query()->with(['Userid','manualAttendance'])->get();
            }
            $data['attendance']=$attendanceList;
            $data['attendance']->each( function ($record){
                $record->approval_request = getNextApproval(54,auth()->user()->designation_id,$record->id);
                $record->approval_request_status=checkApprovalRequestStatus(54,$record->id);
            });

            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function manualAttendanceDropDown()
    {
        $data['manual_attendance_dropdown']=Type::getTypeValues('manual-attendance-type');
        return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
    }

    public function sendManualAttendanceForApproval(EmployeeManuelAttendance $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',54)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',54)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            EmployeeManuelAttendance::query()->where('id',$item->id)->update($update);
            return resp(1,'Manual attendance send for Approval.', $Approval,Response::HTTP_OK);
        }else{
            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Manual attendance approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}

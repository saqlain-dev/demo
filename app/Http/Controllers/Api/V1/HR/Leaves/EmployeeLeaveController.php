<?php

namespace App\Http\Controllers\Api\V1\HR\Leaves;

use App\Http\Controllers\Controller;
use App\Models\Admin\FinancialYear;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Employee;
use App\Models\HR\Leaves\EmployeeLeave;
use App\Models\HR\Leaves\LeaveBalance;
use App\Models\Type;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmployeeLeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'leave_request_view',
            'manage_employee_portal',
        ]);

        $leaveList=EmployeeLeave::query()->where('employee_number',auth()->user()->employee_id)->with('leave_type')->orderBy('id','DESC')->get();
        // foreach($leaveList as $key=> $leave){
        //     $leaveList[$key]['approval_request']=getNextApproval(15,auth()->user()->designation_id,$leave->id);
        //     $leaveList[$key]['approval_request_status']=checkApprovalRequestStatus(15,$leave->id);
        // }
        $data['leaveList']=$leaveList->load('empDetail.department','empDetail.designation');
        $data['leave_date']=$this->getLeaveBalanceByID(auth()->user()->employee_id);
        $data['employee_detail']=Employee::query()->with('department','headOffice','branchOffice','designation')->where('id',auth()->user()->employee_id)->first();
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    public function getEmployeeLeaveReport(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            $employee_id=$request->employee_id;
            $leaveList=EmployeeLeave::query()->where('employee_number',$employee_id)->with('leave_type')->orderBy('id','DESC')->get();
            $data['leaveList']=$leaveList->load('empDetail.department','empDetail.designation');
            $data['leave_date']=$this->getLeaveBalanceByID($employee_id);
            $data['employee_detail']=Employee::query()->with('department','headOffice','branchOffice','designation')->where('id',$employee_id)->first();
            return resp('1', 'Successfully!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }
    public function getLeaveBalanceByID($employeeNumber)
    {

        $departmentId= NULL;
        $FYID=FinancialYear::query()->where('status',1)->with('financialYear')->first();
        $leaveData = DB::select('EXEC get_leave_balances ?, ? , ?', [$employeeNumber, $departmentId,$FYID->id]);
        $groupedData = [];

        foreach ($leaveData as $leave) {
            $employeeId = $leave->id; // Assuming 'id' is the employee ID field
            $employeeName = $leave->employee_name;

            if (!isset($groupedData[$employeeId])) {
                // Initialize the employee entry if it doesn't exist
                $groupedData[$employeeId] = [
                    'employee_name' => $employeeName,
                    'leave_balances' => [],
                ];
            }

            // Add leave balance data to the employee's entry
            $groupedData[$employeeId]['leave_balances'][] = [
                'leave_type_id' => $leave->leave_type_id,
                'leave_type' => $leave->leave_type,
                'leave_balance' => $leave->leave_balance,
                'availed_balance' => $leave->availed_balance,
                'entitlement_balance' => $leave->entitlement_balance,
                'pending_leave_requests' => $leave->pending_leave_requests,
            ];
        }

// Convert associative array to indexed array
        $groupedData = array_values($groupedData);
        $data['leave_balance']=$groupedData;
        $data['leave_types']=Type::getTypeValues('leave-type');
       return $data;
    }

    public function leaveListing()
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 600);
        $this->authorizeAny([
            'leave_request_view',
            'manage_employee_portal',
        ]);

        $leaveList=EmployeeLeave::query()->with('leave_type')->orderBy('id','DESC')->get();
        foreach($leaveList as $key=> $leave){
            $leaveList[$key]['approval_request']=getNextApproval(15,auth()->user()->designation_id,$leave->id);
            $leaveList[$key]['approval_request_status']=checkApprovalRequestStatus(15,$leave->id);
        }
        $data['leaveList']=$leaveList->load('empDetail.department','empDetail.designation');
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    public function leaveListingReport(Request $request){
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 600);
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
        ]);
        $leaveList = EmployeeLeave::query()
                                    ->with('leave_type')
                                    ->whereDate('start_date', '>=', $request->start_date)
                                    ->whereDate('end_date', '<=', $request->end_date)
                                    ->orderBy('id', 'DESC')
                                    ->get();
        $data['leaveList']=$leaveList->load('empDetail.department','empDetail.designation');
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'leave_request_create',
            'manage_employee_portal',
        ]);

        try {
            DB::beginTransaction();

            $request->validate([
                'employee_name' => 'required|string',
                'employee_number' => 'required|integer',
                'designation_id' => 'required|integer',
                'leave_type' => 'required|integer',
                //'location' => 'required|string',
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d',
                'days' => 'required',
                'FYID' => 'required|integer',
                'reason' => 'required|string',
                'is_added_by' => 'required|integer',
            ]);

            $start_date = date('Y-m-d', strtotime($request->start_date));
            $end_date = date('Y-m-d', strtotime($request->end_date));
            $is_added_by = $request->is_added_by;
            if($is_added_by == 1){

                $user = User::where('employee_id', $request->employee_number)->first();

                if ($user) {
                    $this->input['created_by'] = $user->id;
                } else {
                    return resp('0', 'User is not created for this employee.', null, Response::HTTP_BAD_REQUEST);
                }


            }else{
                $this->input['created_by']=auth()->user()->id;
            }

            // Check for overlapping leave
            $overlapExists = EmployeeLeave::query()
                ->where('employee_number', $request->employee_number)
                ->where(function ($query) use ($start_date, $end_date) {
                    $query->whereBetween('start_date', [$start_date, $end_date])
                        ->orWhereBetween('end_date', [$start_date, $end_date])
                        ->orWhereRaw('? BETWEEN start_date AND end_date', [$start_date])
                        ->orWhereRaw('? BETWEEN start_date AND end_date', [$end_date]);
                })
                ->exists();

            if ($overlapExists) {
                return resp('0', 'Leave already exists for the selected date range.', null, Response::HTTP_BAD_REQUEST);
            }

            // Process file if provided
            $this->input['start_date'] = $start_date;
            $this->input['end_date'] = $end_date;
            $this->input['created_at'] = now();

            if ($request->hasFile('leave_file')) {
                $responce = $this->saveLeaveFile($request, 'leave_file');

                if ($responce) {
                    $this->input['leave_file'] = $responce;
                }
            } else {
                unset($this->input['leave_file']);
            }

            // Add the leave record

            $leave = EmployeeLeave::query()->insert($this->input);

            DB::commit();
            return resp('1', 'Leave added Successfully!', [], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    public function saveLeaveFile($request,$folder){

        $file = $request->file('leave_file');
        $path = 'uploads/media/' . $folder;
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (!file_exists('uploads/media')) {
            mkdir('uploads/media', 0777, true);
        }
        if (!file_exists('uploads/media/' . $folder)) {
            mkdir('uploads/media/' . $folder, 0777, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        $file_name = str_replace(' ', '_', $filename);
        $file->move($path, $file_name);
        return $path.'/'.$file_name;

    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeeLeave $leave)
    {
        $this->authorizeAny([
            'leave_request_view',
            'manage_employee_portal',
        ]);

        $data['leave']=$leave->load('leave_type','empDetail.designation','empDetail.department');
        $approval_process_id=($leave->days <= 3)? 15 : 1;
        $data['approval_request']=getNextApproval($approval_process_id,auth()->user()->designation_id,$leave->id);
        $data['approval_request_status']=checkApprovalRequestStatus($approval_process_id,$leave->id);

        $FYID=FinancialYear::query()->where('status',1)->with('financialYear')->first();
        $leaveData = DB::select('EXEC get_leave_balances ?, ? , ?', [$leave->employee_number, NULL,$FYID->id]);
        $groupedData = [];
        foreach ($leaveData as $leave) {
            $employeeId = $leave->id; // Assuming 'id' is the employee ID field
            $employeeName = $leave->employee_name;

            // Add leave balance data to the employee's entry
            $groupedData[$employeeId]['leave_balances'][] = [
                'leave_type_id' => $leave->leave_type_id,
                'leave_type' => $leave->leave_type,
                'leave_balance' => $leave->leave_balance,
                'availed_balance' => $leave->availed_balance,
                'entitlement_balance' => $leave->entitlement_balance,
                'pending_leave_requests' => $leave->pending_leave_requests,
                'employee_name' => $employeeName,
            ];
        }
        $groupedData = array_values($groupedData);
        $data['leave_balance']=$groupedData;
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeLeave $leave)
    {
        $this->authorizeAny([
            'leave_request_update',
            'manage_employee_portal',
        ]);

        try {
            DB::beginTransaction();

            $request->validate([
                'employee_name' => 'required|string',
                'employee_number' => 'required|date_format:Y',
                'designation_id' => 'required|integer',
                'leave_type' => 'required|integer',
                //'location' => 'required|string',
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d',
                'days' => 'required',
                'FYID' => 'required|integer',
                'reason' => 'required|string',
            ]);
            $this->input['start_date']=date('Y-m-d',strtotime($request->start_date));
            $this->input['end_date']=date('Y-m-d',strtotime($request->end_date));
            if($request->hasFile('leave_file')) {

                $responce = $this->saveLeaveFile($request, 'leave_file');

                if ($responce) {
                    $this->input['leave_file'] = $responce;
                }
            }else{
                unset($this->input['leave_file']);
            }
            EmployeeLeave::query()->findOrFail($leave->id)->update($this->input);
            DB::commit();
            $leave->refresh();
            return resp('1', 'Leave updated Successfully!', $leave->load('leave_type'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->authorizeAny([
            'leave_request_delete',
            'manage_employee_portal',
        ]);
        $leave = EmployeeLeave::query()->findOrFail($id);

        $item = $leave->delete();
        return resp('1', 'Leave deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function employeeLeaveDropDown()
    {

        $data['leave_types']=Type::getTypeValues('leave-type');
        $data['employeeDetail']=Employee::query()->where('id',auth()->user()->employee_id)->with(['designation','employeeTyp','gender'])->first();
        $data['allEmployeeList']=Employee::query()->with('designation','employeeTyp','gender')->whereNotIn('employee_type', [14, 16, 17, 18])->get();
        $data['departments']=Type::getTypeValues('department-names');
        $data['financial_year']=FinancialYear::query()->where('status',1)->with('financialYear')->first();
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }
    public function sendLeaveRequestForApproval(EmployeeLeave $leave)
    {

        $leave_process=($this->input['approval_send_to'] == 1)? 15 : 1;
        $approval_process_name=ApprovalProcessName::query()->where('id',$leave_process)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',$leave_process)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',$leave_process)->where('approval_request_status',1)->where('request_module_id',$leave->id)->count();
        if($approval_process->count() > 0  && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$leave->id,
                );
                $insert['created_by']=$leave->created_by;
                $insert['created_at']=now();
                $insert['updated_at']=now();
                $Approval=ApprovalProcessList::query()->insert($insert);
                sendNotification($approval['designation_id'],$approval_process_name->approval_process_name);

            }
            $update=array('approval_status'=>2);
            EmployeeLeave::query()->where('id',$leave->id)->update($update);
            deductLeave($leave->id);
            return resp(1,'Leave send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Leave approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function getLeaveBalance()
    {
        $this->authorizeAny([
            'leave_entitlement_view',
        ]);

        $employeeNumber= $this->input['employeeNumber'];
        $departmentId= $this->input['departmentId'];
        $FYID=FinancialYear::query()->where('status',1)->with('financialYear')->first();
        $leaveData = DB::select('EXEC get_leave_balances ?, ? , ?', [$employeeNumber, $departmentId,$FYID->id]);
        $groupedData = [];

        foreach ($leaveData as $leave) {
            $employeeId = $leave->id; // Assuming 'id' is the employee ID field
            $employeeName = $leave->employee_name;

            if (!isset($groupedData[$employeeId])) {
                // Initialize the employee entry if it doesn't exist
                $groupedData[$employeeId] = [
                    'employee_name' => $employeeName,
                    'leave_balances' => [],
                ];
            }

            // Add leave balance data to the employee's entry
            $groupedData[$employeeId]['leave_balances'][] = [
                'leave_type' => $leave->leave_type,
                'leave_balance' => $leave->leave_balance,
            ];
        }

// Convert associative array to indexed array
        $groupedData = array_values($groupedData);
        $data['leave_balance']=$groupedData;

        return resp(1,'Successfully!', $data,Response::HTTP_OK);
    }

    //leaveApprovalReport
    public function leaveApprovalReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate   = Carbon::parse($request->end_date)->endOfDay();

        $report = ApprovalProcessList::query()
                ->with('updated_by.employeeDetail.department')
                ->select(
                    'updated_by',
                    DB::raw('COUNT(*) as total_records'),
                    DB::raw('AVG(DATEDIFF(DAY, created_at, updated_at)) as avg_days')
                )
                ->whereIn('approval_process_id', [1, 15])
                ->whereNotNull('updated_by')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('updated_by')
                ->get();

        return resp(
            '1',
            'Leave Approval Report generated successfully!',
            $report,
            Response::HTTP_OK
        );
    }

}

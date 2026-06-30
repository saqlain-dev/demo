<?php

use App\Models\Admin\FinancialYear;
use App\Models\Admin\PurchaseRequestRfqDetail;
use App\Models\Admin\TenderDetail;
use App\Models\ApprovalProcessList;
use App\Models\HR\Attendance\EmployeeManuelAttendance;
use App\Models\HR\Leaves\LeaveBalanceDetail;
use App\Models\PurchaseRequestDetail;
use App\Models\HR\Leaves\EmployeeLeave;
use App\Models\HR\Leaves\LeaveBalance;
use Illuminate\Http\Response;
use App\Models\User;
use App\Jobs\SendEmailJob;
use App\Models\EmailTemplate;
use App\Notifications\AppNotification;
use Illuminate\Support\Facades\DB;

function resp($success = 1, $message = 'Successful!', $data = [], $status_code=200): \Illuminate\Http\JsonResponse
{
    $type = $success == 1 ? 'success' : 'error';
    return response()->json([
        'type' => $type, 'status' => $success,
        'message' => $message, 'data' => $data
    ], $status_code);
}

enum STATUS: int
{
    case APPROVED = 1;
    case PENDING = 2;
    case REJECTED = 3;
    case DRAFT = 4;
}

function getAnyTablefieldName($table, $id, $coulmnName)
{
    $res = \DB::table($table)->select($coulmnName)->where('id', $id)->first();
    return $res->$coulmnName;
}
function getAllNextApproval($app_process_id, $current_user_designation_id, $module_request_id = "")
{
    $records = [];

    // Get all pending approval records for the given process
    $pendingApprovals = \App\Models\ApprovalProcessList::query()
        ->where('approval_process_id', $app_process_id)
        ->where('approval_status', 2) // Pending
        ->where('approval_request_status', 1)
        ->get();

    foreach ($pendingApprovals as $approval) {
        // CASE 1: This step is assigned to a Line Manager (dynamic check)
        if ($approval->designation_id == 1000) {
            $requestingUser = \App\Models\User::find($approval->created_by);
            if (!$requestingUser) continue;

            $requestingEmployee = \App\Models\Employee::find($requestingUser->employee_id);
            if (!$requestingEmployee || !$requestingEmployee->reportTo) continue;

            $lineManager = $requestingEmployee->reportTo;
            if ($lineManager->id == auth()->user()->employee_id) {
                // Current user is the requestor's Line Manager — include this approval
                $records[] = $approval;
            }
        }

        // CASE 2: Normal designation-based step
        else if ($approval->designation_id == $current_user_designation_id) {
            // Check previous step (process_order - 1) must be approved
            $previousStep = \App\Models\ApprovalProcessList::query()
                ->where('approval_process_id', $app_process_id)
                ->where('process_order', $approval->process_order - 1)
                ->where('request_module_id', $approval->request_module_id)
                ->where('approval_request_status', 1)
                ->first();

            if ($previousStep && $previousStep->approval_status != 1) {
                // Previous step exists but is not approved yet
                continue;
            }

            // Either first step or previous step is approved — it's your turn
            $records[] = $approval;
        }
    }

    return $records;
}

/*function getAllNextApproval($app_process_id,$desg_id,$module_request_id="")
{

    $lineManagerApprovalRecordList=\App\Models\ApprovalProcessList::query()->where('approval_process_id',$app_process_id)->where('designation_id',1000)->where('approval_request_status',1)->where('approval_status',2)->get();

    $records=array();
    if($lineManagerApprovalRecordList) {
        foreach($lineManagerApprovalRecordList as $lineManagerApprovalRecord) {
            if ($lineManagerApprovalRecord->approval_status == 3) {
                return null;
            } else {
                if ($lineManagerApprovalRecord->approval_status == 2) {

                    $dbQuery = match ($app_process_id) {
                        2 => \App\Models\ApprovalProcessList::query(),
                        default => \App\Models\ApprovalProcessList::query(),
                    };

                    $resObject = $dbQuery->where('id', $lineManagerApprovalRecord->id)->first();

                    $createdUserDesignation = \App\Models\User::query()->find($resObject->created_by);
                    $loginEmployee = auth()->user()->employee_id;
                    if ($createdUserDesignation->designation_id) {
                        $ReportToEmployee = \App\Models\Employee::query()->where('id', $createdUserDesignation->employee_id)->first();
                        $reportToEmployeeDetail = $ReportToEmployee->reportTo;
                        //$reportTodesignation=$createdUserDesignation->userdesignation()->first()->reportTo;
                        $reportTodesignation = $reportToEmployeeDetail->designation;

                        $lineManagerApprovalRecord->designation_id = $reportTodesignation->id;
                        $lineManagerApprovalRecord->designation = $reportTodesignation;


                        if ($loginEmployee == $reportToEmployeeDetail->id) {
                            // return $lineManagerApprovalRecord;
                            $records[] = $lineManagerApprovalRecord;
                        }
                    } else {
                        //return null;
                    }


                } else {
                    //return null;
                }
            }
        }

    }
    $approvalRecordList=\App\Models\ApprovalProcessList::query()->where('approval_process_id',$app_process_id)->where('designation_id',$desg_id)->where('approval_status',2)->where('approval_request_status',1)->get();


        if($approvalRecordList){

            foreach($approvalRecordList as $approvalRecord) {
                $CheckPreviousApprovalRecord = \App\Models\ApprovalProcessList::query()->where('approval_process_id', $app_process_id)->where('designation_id',$desg_id)->where('approval_request_status', 1)->where('process_order', $approvalRecord->process_order - 1)->first();

                if ($CheckPreviousApprovalRecord && $CheckPreviousApprovalRecord->approval_status == 3) {
                    //return null;
                } elseif ($CheckPreviousApprovalRecord && $CheckPreviousApprovalRecord->approval_status == 2) {
                    //return null;
                } else {
                    if ($approvalRecord->approval_status == 2) {

                        $records[]= $approvalRecord;
                    } else {
                        //return null;
                    }

                }
            }

            //return $records;
        }else{
            //return $records;
        }

    return $records;


}*/

function getNextApproval($app_process_id,$desg_id,$module_request_id)
{

    $lineManagerApprovalRecord=\App\Models\ApprovalProcessList::query()->where('approval_process_id',$app_process_id)->where('designation_id',1000)->where('approval_request_status',1)->where('request_module_id',$module_request_id)->where('approval_status',2)->first();


    if($lineManagerApprovalRecord){

        if($lineManagerApprovalRecord->approval_status == 3){
            return null;
        }else{
            if($lineManagerApprovalRecord->approval_status == 2){

                $dbQuery = match ($app_process_id) {
                    2 => \App\Models\ApprovalProcessList::query(),
                    default => \App\Models\ApprovalProcessList::query(),
                };

                $resObject=$dbQuery->where('request_module_id',$module_request_id)->where('approval_process_id',$app_process_id)->first();

                $createdUserDesignation=\App\Models\User::query()->find($resObject->created_by);
                $loginEmployee=auth()->user()->employee_id;
                if($createdUserDesignation->designation_id){
                    $ReportToEmployee=\App\Models\Employee::query()->where('id',$createdUserDesignation->employee_id)->first();
                    $reportToEmployeeDetail=$ReportToEmployee->reportTo;
                    //$reportTodesignation=$createdUserDesignation->userdesignation()->first()->reportTo;
                    $reportTodesignation=$reportToEmployeeDetail->designation;

                    $lineManagerApprovalRecord->designation_id=$reportTodesignation->id;
                    $lineManagerApprovalRecord->designation=$reportTodesignation;

                    /*if($desg_id == $reportTodesignation->id){
                        return $lineManagerApprovalRecord;
                    }*/
                    if($loginEmployee == $reportToEmployeeDetail->id){
                        return $lineManagerApprovalRecord;
                    }
                }else{
                    return null;
                }


            }else{
                return null;
            }
        }

    }else{

        $approvalRecord=\App\Models\ApprovalProcessList::query()->where('approval_process_id',$app_process_id)->where('designation_id',$desg_id)->where('approval_status',2)->where('approval_request_status',1)->where('request_module_id',$module_request_id)->first();

        if($approvalRecord){

            $CheckPreviousApprovalRecord=\App\Models\ApprovalProcessList::query()->where('approval_process_id',$app_process_id)->where('approval_request_status',1)->where('request_module_id',$module_request_id)->where('process_order',$approvalRecord->process_order - 1 )->first();

            if($CheckPreviousApprovalRecord && $CheckPreviousApprovalRecord->approval_status == 3){
                return null;
            }elseif ($CheckPreviousApprovalRecord && $CheckPreviousApprovalRecord->approval_status == 2){
                return null;
            }else{
                if($approvalRecord->approval_status == 2){
                    return $approvalRecord;
                }else{
                    return null;
                }

            }
        }else{
            return null;
        }
    }


}

function getUserIdsByDesignation(int $designationId): array
{
    $item = \App\Models\User::query()->where('designation_id', $designationId)->get();
    return $item->pluck('id')->toArray();
}
function autoApprovalForSender($desg_id,$request_module_id,$app_process_id)
{
    $approvalRecord=\App\Models\ApprovalProcessList::query()->where('approval_process_id',$app_process_id)->where('designation_id',$desg_id)->where('approval_status',2)->where('approval_request_status',1)->where('request_module_id',$request_module_id)->where('process_order',1)->first();
    if($approvalRecord){
        $approvalRecord->approval_status=1;
        $approvalRecord->save();
    }
}
function getRemainingPr($approvedPr)
{

    foreach($approvedPr as $key => $pr){
        $remItem=0;
        $purchase_request_details = PurchaseRequestDetail::query()->with('items.itemUnit','purchase_request')->where('purchase_request_id', $pr->id)->get();

        $purchase_request_details = $purchase_request_details->filter(function ($item)  use (&$remItem){

            $rfq_quantity_used = PurchaseRequestRfqDetail::where('purchase_request_detail_id', $item->id)->sum('required_quantity');

            $tender_quantity_used = TenderDetail::where('purchase_request_detail_id', $item->id)->sum('required_quantity');
            $remaining_quantity = $item->required_quantity - $rfq_quantity_used - $tender_quantity_used;

            $item->required_quantity = $remaining_quantity;
            if( $remaining_quantity > 0){
                $remItem=1;

            }
            // Keep items with remaining_quantity greater than 0
            return $remaining_quantity > 0;
        });

        if($remItem == 0){

            unset($approvedPr[$key]);
        }


    }

    if($approvedPr){
        $approvedPr=array_values($approvedPr->toArray());
    }

    return $approvedPr;

}

function checkApprovalRequestStatus($process_id,$module_id)
{
    $loginEmployee=auth()->user()->employee_id;
    //$request_status=ApprovalProcessList::query()->where('approval_process_id',$process_id)->where('request_module_id',$module_id)->where('approval_request_status',1)->with('designation','updated_by')->orderBy('process_order','ASC')->get();
    $request_status=ApprovalProcessList::query()->where('approval_process_id',$process_id)->where('request_module_id',$module_id)->with('designation','updated_by')->orderBy('id','ASC')->get();
    foreach($request_status as $key => $request){

        if($request['designation_id'] == 1000){
            unset($request_status[$key]['designation']);
            $dbQuery = match ($process_id) {
                2 => \App\Models\ApprovalProcessList::query(),
                default => \App\Models\ApprovalProcessList::query(),
            };

            $resObject=$dbQuery->where('request_module_id',$module_id)->where('approval_process_id',$process_id)->first();

            $createdUserDesignation=\App\Models\User::query()->find($resObject->created_by);

            if($createdUserDesignation->designation_id){
                $ReportToEmployee=\App\Models\Employee::query()->where('id',$createdUserDesignation->employee_id)->first();
                $reportToEmployeeDetail=$ReportToEmployee->reportTo;
                $reportTodesignation=$reportToEmployeeDetail->designation;
                //$reportTodesignation=$createdUserDesignation->userdesignation()->first()->reportTo;
                $request_status[$key]['designation']=$reportTodesignation->toArray();
                $request_status[$key]['designation_id']=$reportTodesignation->id;


            }else{
                return null;
            }

        }
    }
    return $request_status;

}
function encode($value) {
    if (!$value) {
        return false;
    }
    $key = sha1('EnCRypT10nK#Y!RiSRNn');
    $strLen = strlen($value);
    $keyLen = strlen($key);
    $j = 0;
    $crypttext = '';
    for ($i = 0; $i < $strLen; $i++) {
        $ordStr = ord(substr($value, $i, 1));
        if ($j == $keyLen) {
            $j = 0;
        }
        $ordKey = ord(substr($key, $j, 1));
        $j++;
        $crypttext .= strrev(base_convert(dechex($ordStr + $ordKey), 16, 36));
    }
    return $crypttext;
}
function decode($value) {
    if (!$value) {
        return false;
    }
    $key = sha1('EnCRypT10nK#Y!RiSRNn');
    $strLen = strlen($value);
    $keyLen = strlen($key);
    $j = 0;
    $decrypttext = '';
    for ($i = 0; $i < $strLen; $i += 2) {
        $ordStr = hexdec(base_convert(strrev(substr($value, $i, 2)), 36, 16));
        if ($j == $keyLen) {
            $j = 0;
        }
        $ordKey = ord(substr($key, $j, 1));
        $j++;
        $decrypttext .= chr($ordStr - $ordKey);
    }
    return $decrypttext;
}
 function getCategoryDescription($category)
{
    // Map category to description based on your logic
    return ($category == 1) ? 1 : 2;
}


 function getCalculatedByDescription($calculatedBy): string
{

    if($calculatedBy == 1){
        return 'Percentage';
    }elseif ($calculatedBy == 2)
    {
        return 'Fixed Amount';

    }
    elseif ($calculatedBy == 3)
    {
        return 'Per Liter';
    }else{
        return '';
    }


}

function deductLeave($leaveID)
{
   $employeeLeave= EmployeeLeave::query()->where('id',$leaveID)->first();

    $leaveBalance = LeaveBalance::query()
        ->where('EmployeeID', $employeeLeave->employee_number)
        ->where('FYID', $employeeLeave->FYID)
        ->where('LeaveTypeID', $employeeLeave->leave_type)
        ->first();
    if (is_null($leaveBalance->Availed)) {
        $leaveBalance->Availed = 0;
    }
    $leaveBalance->Availed += $employeeLeave->days;
    $leaveBalance->save();
   LeaveBalance::query()->where('EmployeeID',$employeeLeave->employee_number)->where('FYID',$employeeLeave->FYID)->where('LeaveTypeID',$employeeLeave->leave_type)->decrement('Balance',$employeeLeave->days);
}
function reverseDeductLeave($leaveID)
{
   $employeeLeave= EmployeeLeave::query()->where('id',$leaveID)->first();

    $leaveBalance = LeaveBalance::query()
        ->where('EmployeeID', $employeeLeave->employee_number)
        ->where('FYID', $employeeLeave->FYID)
        ->where('LeaveTypeID', $employeeLeave->leave_type)
        ->first();
    /*if (is_null($leaveBalance->Availed)) {
        $leaveBalance->Availed = 0;
    }*/
    $leaveBalance->Availed -= $employeeLeave->days;
    //$leaveBalance->Balance += $employeeLeave->days;
    $leaveBalance->save();
   LeaveBalance::query()->where('EmployeeID',$employeeLeave->employee_number)->where('FYID',$employeeLeave->FYID)->where('LeaveTypeID',$employeeLeave->leave_type)->increment('Balance',$employeeLeave->days);
}
function addAttendance($att_id)
{
   $manual_attendance= EmployeeManuelAttendance::query()->where('id',$att_id)->first();
   $employee_detail= \App\Models\Employee::query()->where('id',$manual_attendance->userid)->first();
   $employee_shift=
   $FYID=FinancialYear::query()->where('status',1)->with('financialYear')->first();
   if($manual_attendance){

       $update=array(
           'USERID'=>$manual_attendance->userid,
           'VERIFYCODE'=>0,
           'SENSORID'=>29,
           'att_date'=>$manual_attendance->att_date,
           'att_timeIn'=>$manual_attendance->att_timeIn,
           'att_TimeOut'=>$manual_attendance->att_timeOut,
           'SensorIDOut'=>29,
           'IsUpdated'=>1,
           'IsManual'=>1,
           'attendance_remarks'=>$manual_attendance->remarks,
       );
      /* $affected = DB::table('ams_check_in_outs')
           ->where('USERID', $manual_attendance->userid)
           ->where('att_date', $manual_attendance->att_date)
           ->insert($update);*/
       $checkAttendance = DB::table('ams_check_in_outs')
           ->where('USERID', $manual_attendance->userid)
           ->where('att_date', $manual_attendance->att_date)
           ->first();
       if($checkAttendance){
           $affected = DB::table('ams_check_in_outs')
               ->where('USERID', $manual_attendance->userid)
               ->where('att_date', $manual_attendance->att_date)
               ->update($update);
       }else{
           $affected = DB::table('ams_check_in_outs')
               ->where('USERID', $manual_attendance->userid)
               ->where('att_date', $manual_attendance->att_date)
               ->insert($update);
       }


       if($manual_attendance->isHoliday ==  1){

           $insert=array(
               'FYID' => $FYID->id,
               'type' => 1,
               'leave_type_id' => 480, // 430 id for local server and 480 live las
               'EmployeeID' => $manual_attendance->userid,
               'NoOfDays' => 1,
               'remarks' => 'Off day compensation',
           );

           $leaveAdd = DB::table('leave_add_deducts')
               ->insert($insert);
           if($leaveAdd){
               $empBalance=LeaveBalance::query()->where('FYID',$FYID->id)->where('EmployeeID',$manual_attendance->userid)->where('LeaveTypeID',480)->first();
               $empBalance->Balance+=1;
               $empBalance->save();
           }
       }
   }

}

function updateEmployeeYearlyLeave($employeeId)
{

    $financialYear= FinancialYear::query()->where('status', 1)->first();
    if($financialYear) {
        $leave_balance_details = LeaveBalanceDetail::query()->where('FYID', $financialYear->id)->get();
        $end_date = date('Y-m-d');
        //$months = DB::select("select DATEDIFF(MONTH, '".$financialYear->start_date."', '".$end_date."') as month");
        $months = DB::select("
    SELECT
        CASE
            WHEN DAY('$end_date') > 15 THEN
                DATEDIFF(month, '" . $financialYear->start_date . "', DATEADD(month, -1, '$end_date'))
            ELSE
                DATEDIFF(month, '" . $financialYear->start_date . "', '$end_date')
        END AS month"
        );


        if ($leave_balance_details) {
            foreach ($leave_balance_details as $leave_balance) {

                $checkLeave = LeaveBalance::query()->where('FYID', $leave_balance['FYID'])->where('LeaveTypeID', $leave_balance['LeaveTypeID'])->where('EmployeeID', $employeeId)->first();
                if (empty($checkLeave)) {
                    $month = @$months[0]->month;
                    $monthlyBalnce = ($leave_balance['LeaveBalance'] / 12) * (12 - intval($month));

                    $insert = array(
                        'EmployeeID' => $employeeId,
                        'LeaveTypeID' => $leave_balance['LeaveTypeID'],
                        'Balance' => round($monthlyBalnce),
                        'FYID' => $leave_balance['FYID'],
                        'totalBalance' => round($monthlyBalnce),
                        'adjusted' => 1,
                    );
                    LeaveBalance::query()->insert($insert);
                }
            }
        }
    }
}


function sendFinalRejectionNotification( $designation_id,$apprval_send_by, $title = "")

{
    $title = $title ?? "Rejection Notification";
    $message = "A new ".$title."  request requires your attention. Please review and take action as per your authorization level.";

    // Find all users with the given designation
    $users = User::where('designation_id', $designation_id)->get();



    if ($users->isEmpty()) {
        return resp(0, 'No users found with this designation.', [], Response::HTTP_NOT_FOUND);
    }

    // Notification data
    $data = [
        'title' => $title,
        'message' => $message,
        'url' =>  null,
    ];


    // Send notification to each user
    try {
        foreach ($users as $user) {
            sendEmail($user,$data);
        }

        return resp(1, 'Notification sent successfully to all users.', [], Response::HTTP_OK);
    } catch (\Exception $e) {
        return resp(0, 'Failed to send notification.', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
    }
}

function sendFinalApprovalNotification( $designation_id,$apprval_send_by, $title = "")
{
    $title = $title ?? "Approval Notification";
    $message = "A new ".$title."  request requires your attention. Please review and take action as per your authorization level.";

    // Find all users with the given designation
    $users = User::where('designation_id', $designation_id)->get();



    if ($users->isEmpty()) {
        return resp(0, 'No users found with this designation.', [], Response::HTTP_NOT_FOUND);
    }

    // Notification data
    $data = [
        'title' => $title,
        'message' => $message,
        'url' =>  null,
    ];


    // Send notification to each user
    try {
        foreach ($users as $user) {
            sendEmail($user,$data);
        }

        return resp(1, 'Notification sent successfully to all users.', [], Response::HTTP_OK);
    } catch (\Exception $e) {
        return resp(0, 'Failed to send notification.', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
    }
}
function sendNotification($designation_id, $title = "", $message = "")
{
    $title = $title ?? "Approval Notification";
    $message = "A new ".$title."  request requires your attention. Please review and take action as per your authorization level.";

    // Find all users with the given designation
    $users = User::where('designation_id', $designation_id)->get();



    if ($users->isEmpty()) {
        return resp(0, 'No users found with this designation.', [], Response::HTTP_NOT_FOUND);
    }

    // Notification data
    $data = [
        'title' => $title,
        'message' => $message,
        'url' =>  null,
    ];


    // Send notification to each user
    try {
        foreach ($users as $user) {
            $user->notify(new AppNotification($data));
            sendEmail($user,$data);
        }

        return resp(1, 'Notification sent successfully to all users.', [], Response::HTTP_OK);
    } catch (\Exception $e) {
        return resp(0, 'Failed to send notification.', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
    }
}

function sendEmail($user,$data)
{
    // Validate the request

    $email_template=EmailTemplate::query()->where('template_name','Approval Email Notification')->first();
   if($email_template) {
       $emailBody=$email_template->template_body;
       $emailBody = str_replace('{{EmployeeName}}', $user->name, $emailBody);
       $emailData = [
           'to' => $user->email,
           'subject' => $data['title'] ?? $email_template->template_subject, //$email_template->template_subject
           'body' => $emailBody,
       ];

       try {
           // Send the email using raw text or HTML body

           SendEmailJob::dispatch($emailData);
           return resp(1, 'Email sent successfully.!', [], Response::HTTP_OK);

       } catch (\Exception $e) {
           return resp(0, 'Failed to send email.', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
       }
   }
}

function sendVendorNotification($user_id, $title = "", $message = "", $url = ""){
    $title = $title ?? "Vendor Quotation";
    $message = $message ?? "A new ".$title."  request requires your attention. Please review and take action as per your authorization level.";

    // Find all users with the given designation
    $user = User::where('id', $user_id)->first();

    if (!$user) {
        return resp(0, 'No users found.', [], Response::HTTP_NOT_FOUND);
    }

    $users = User::where('designation_id', $user->designation_id)->get();

    if ($users->isEmpty()) {
        return resp(0, 'No users found with this designation.', [], Response::HTTP_NOT_FOUND);
    }

    // Notification data
    $data = [
        'title' => $title,
        'message' => $message,
        'url' =>  $url,
    ];


    // Send notification to each user
    try {
        foreach ($users as $user) {
            $user->notify(new AppNotification($data));
           // sendEmail($user,$data);
        }

        return resp(1, 'Notification sent successfully to all users.', [], Response::HTTP_OK);
    } catch (\Exception $e) {
        return resp(0, 'Failed to send notification.', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
    }

}


function generalAPPNotification($user_id, $title, $message, $url){
    $title = $title ?? "New Notification";
    $message = $message ?? "";

    // Find all users with the given designation
    $user = User::where('id', $user_id)->first();

    if (!$user) {
        return resp(0, 'No users found.', [], Response::HTTP_NOT_FOUND);
    }

    // Notification data
    $data = [
        'title' => $title,
        'message' => $message,
        'url' =>  $url,
    ];

    $user->notify(new AppNotification($data));

    return resp(1, 'Notification sent successfully.', [], Response::HTTP_OK);

}


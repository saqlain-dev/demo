<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Employee;
use App\Models\MeetingBooking;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MeetingBookingController extends Controller
{
    //index
    public function index(Request $request)
    {
        // Fetch all meeting bookings
        $meetings = MeetingBooking::with(['employee', 'employee.branchOffice', 'employee.designation','employee.department', 'meetingRoom', 'createdBy', 'updatedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return resp('1', 'Record Fetched Successfully!', $meetings, Response::HTTP_OK);
    }

    //store
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'meeting_title' => 'required|string|max:255',
            'meeting_room_id' => 'required|exists:type_values,id',
            'meeting_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'attendees_count' => 'required|integer|min:0',
        ]);

        $exists = MeetingBooking::where('meeting_room_id', $request->meeting_room_id)
                                ->where('status', 1)
                                ->where('meeting_date', $request->meeting_date)
                                ->where(function ($query) use ($request) {
                                    $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                                        ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                                        ->orWhere(function ($query) use ($request) {
                                            $query->where('start_time', '<=', $request->start_time)
                                                    ->where('end_time', '>=', $request->end_time);
                                        });
                                })
                                ->exists();

        if ($exists) {
            return resp('0', 'This room is already booked for this date and time.', null, Response::HTTP_CONFLICT);
        }

        // Create a new meeting booking
        $meeting = MeetingBooking::create($request->all());
        $meeting->load(['employee', 'employee.designation','employee.department', 'meetingRoom', 'createdBy', 'updatedBy']);
        return resp('1', 'Record Created Successfully!', $meeting, Response::HTTP_CREATED);
    }

    // Show
    public function show($id)
    {

        $meeting = MeetingBooking::with(['employee', 'employee.branchOffice', 'employee.designation','employee.department', 'meetingRoom', 'createdBy', 'updatedBy'])->find($id);

        if (!$meeting) {
            return resp('0', 'Record Not Found.', null, Response::HTTP_NOT_FOUND);
        }
        $data['meeting']=$meeting;
        $data['approval_request']=getNextApproval(73,auth()->user()->designation_id,$id);
        $data['approval_request_status']=checkApprovalRequestStatus(73,$id);

        return resp('1', 'Record Fetched Successfully!', $data, Response::HTTP_OK);
    }

    // Update
    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'meeting_title' => 'required|string|max:255',
            'meeting_room_id' => 'required|exists:type_values,id',
            'meeting_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'attendees_count' => 'required|integer|min:0',
        ]);

        $meeting = MeetingBooking::find($id);

        if (!$meeting) {
            return resp('0', 'Record Not Found.', null, Response::HTTP_NOT_FOUND);
        }

        $exists = MeetingBooking::where('meeting_room_id', $request->meeting_room_id)
            ->where('meeting_date', $request->meeting_date)
            ->where('status', 1)
            ->where('id', '!=', $id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($query) use ($request) {
                        $query->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                    });
            })
            ->exists();

        if ($exists) {
            return resp('0', 'This room is already booked for this date and time.', null, Response::HTTP_CONFLICT);
        }

        $meeting->update($request->all());
        $meeting->load(['employee', 'employee.designation','employee.department', 'meetingRoom', 'createdBy', 'updatedBy']);
        return resp('1', 'Record Updated Successfully!', $meeting, Response::HTTP_OK);
    }

    // Delete
    public function destroy($id)
    {
        $meeting = MeetingBooking::find($id);

        if (!$meeting) {
            return resp('0', 'Record Not Found.', null, Response::HTTP_NOT_FOUND);
        }

        $meeting->delete();

        return resp('1', 'Record Deleted Successfully!', null, Response::HTTP_OK);
    }

    public function sendMeetingBookingForApproval(MeetingBooking $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',73)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',73)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',73)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            MeetingBooking::query()->where('id',$item->id)->update($update);
            return resp(1,'Meeting Booking Request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Meeting Booking Request approval already sent.', [],Response::HTTP_OK);
            }
        }
    }


    public function getMeetingBookingsDropdown(){
        $data['employees'] = Employee::with(['designation', 'department'])
                            ->whereNotIn('employee_type', [14, 16, 17, 18])
                            ->get();
        $data['meeting_rooms']= Type::getTypeValues('meeting-rooms');
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }


}

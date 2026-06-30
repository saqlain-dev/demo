<?php

namespace App\Http\Controllers\Api\V1\Communication;

use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Type;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Communication\AssignCommunicationEventTask;
use App\Models\Communication\EventCategory;
use App\Models\Communication\CommunicationEvent;
use App\Models\Communication\CommunicationEventDetail;
use App\Models\Communication\CommunicationEventHistory;

class CommunicationEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny(['task_management_view', 'event_management_view', 'manage_employee_portal']);

        $data = CommunicationEvent::query()->with(['department','eventDetails' => ['department','subCategory','category']])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny(['task_management_create', 'event_management_create', 'manage_employee_portal']);

        $request->validate([
            'event_name' => 'required|string|max:255',
            'description' => 'required|string',
            'requester_response' => 'nullable',
            'requester_comment' => 'nullable',
            'team_response' => 'nullable',
            'team_comment' => 'nullable',
        ]);
        try {
            DB::beginTransaction();

            $item = CommunicationEvent::query()->create($request->all());

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CommunicationEvent $communicationEvent)
    {
        $this->authorizeAny(['task_management_view', 'event_management_view', 'manage_employee_portal']);

        $data['item'] = $communicationEvent->load(['comments.createdBy','department','eventHistory','eventDetails' => ['department','subCategory','category']]);
        $data['approval_request']=getNextApproval(44,auth()->user()->designation_id,$communicationEvent->id);
        $data['approval_request_status']=checkApprovalRequestStatus(44,$communicationEvent->id);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CommunicationEvent $communicationEvent)
    {
        $this->authorizeAny(['task_management_update', 'event_management_update', 'manage_employee_portal']);

        $request->validate([
            'event_name' => 'required|string|max:255',
            'description' => 'required|string',
            'requester_response' => 'nullable',
            'requester_comment' => 'nullable',
            'team_response' => 'nullable',
            'team_comment' => 'nullable',
        ]);
        try {
            DB::beginTransaction();

            $parent = $communicationEvent->update($request->all());

            DB::commit();
            return resp(1, 'Successful!', $communicationEvent, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CommunicationEvent $communicationEvent)
    {
        $this->authorizeAny(['task_management_delete', 'event_management_delete', 'manage_employee_portal']);

        $communicationEvent->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    public function getDropdowns()
    {

        $data['categories'] =  EventCategory::with('subCategories')->get();
        $data['departments'] =  Type::getTypeValues('department-names');
        $data['employees'] =  Employee::select('id','name')->whereNotIn('employee_type', [14, 16, 17, 18])->get();

        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function eventStats()
    {
        $this->authorizeAny([
            'dashboard-communication',
        ]);

        $data['eventStatusCounts'] = $this->eventStatusCount();

        $data['taskStatusCounts'] = $this->taskStatusCount();

        $data['allEventsWithTasksCount'] = CommunicationEvent::select('id', 'event_name')
        ->withCount('eventDetails')
        ->with(['assignedTasks.assignedTo' => function ($query) {
            $query->select('id', 'name');
        }])
        ->withCount('assignedTasks')
        ->get();


        $response = CommunicationEventHistory::with([
            'communicationEvent' => function ($query) {
                $query->select('id', 'event_name');
            },
            'communicationEventDetail' => function ($query) {
                $query->select('id', 'event_name');
            }
        ])
        ->orderBy('id')
        ->get(['id', 'status', 'communication_event_id', 'communication_event_detail_id', 'created_at']);

        $data['eventTaskHistory'] = $response->groupBy(function ($item) {
            return $item->communication_event_id . '-' . $item->communication_event_detail_id;
        })->map(function ($group) {
            $formattedGroup = [
                'communication_event_id' => $group->first()->communication_event_id,
                'communication_event_detail_id' => $group->first()->communication_event_detail_id,
                'communication_event' => $group->first()->communicationEvent ? [
                    'id' => $group->first()->communicationEvent->id,
                    'event_name' => $group->first()->communicationEvent->event_name
                ] : null,
                'communication_event_detail' => $group->first()->communicationEventDetail ? [
                    'id' => $group->first()->communicationEventDetail->id,
                    'event_name' => $group->first()->communicationEventDetail->detail_name
                ] : null,
                'statuses' => []
            ];

            foreach ($group as $index => $item) {
                $formattedGroup['statuses'][] = [
                    'status' => $item->status,
                    // 'status' . ($index + 1) => $item->status,
                    'date' => $item->created_at->format('Y-m-d')
                ];
            }

            return $formattedGroup;
        })->values();

        $data['categoryWithTasksCount'] = EventCategory::select('id', 'name')->withCount('eventDetails')->get();

        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    private function eventStatusCount()
    {

        $data['totalEvents'] = CommunicationEvent::count();

        $data['Inprogress'] = CommunicationEvent::where('requester_response', 1)->count();

        $data['Delivered'] = CommunicationEvent::where('requester_response', 2)->count();

        $data['Rejected'] = CommunicationEvent::where('requester_response', 3)->count();

        $data['Completed'] = CommunicationEvent::where('requester_response', 4)->count();

        $data['pending'] = CommunicationEvent::where('requester_response', 0)->count();

        return $data;
    }

    private function taskStatusCount()
    {

        $data['totalTasks'] = CommunicationEventDetail::count();

        $data['Assigned'] = AssignCommunicationEventTask::count();

        $data['Inprogress'] = CommunicationEventDetail::where('status', 1)->count();

        $data['Delivered'] = CommunicationEventDetail::where('status', 2)->count();

        $data['Rejected'] = CommunicationEventDetail::where('status', 3)->count();

        $data['Completed'] = CommunicationEventDetail::where('status', 4)->count();

        $data['pending'] = CommunicationEventDetail::where('status', 0)->count();

        return $data;
    }

    public function sendCommunicationEventForApproval(CommunicationEvent $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',44)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',44)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',44)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            CommunicationEvent::query()->where('id',$item->id)->update($update);
            return resp(1,'Communication Event request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Communication Event approval already sent.', [],Response::HTTP_OK);
            }
        }
    }


}

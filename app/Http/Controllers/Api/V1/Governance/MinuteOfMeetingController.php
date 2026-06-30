<?php

namespace App\Http\Controllers\Api\V1\Governance;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Governance\BoardMeeting;
use App\Models\Governance\BoardMeetingApplicant;
use App\Models\Governance\MinuteOfMeeting;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MinuteOfMeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'board_meeting_view',
            'finance_minutes_meeting_view',
        ]);

        // $data['minuteOfMeeting_list']=MinuteOfMeeting::query()->with('boardMeetingDetail.agendaDetail')->get();
        $data['minuteOfMeeting_list'] = MinuteOfMeeting::query()->with([
            'boardMeetingDetail.agendaDetail',
            'boardMeetingApplicants' => function ($query) {
                $query->where('IsBoardMember', 1)->with('applicantDetail.designation');
            }
        ])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'board_meeting_create',
            'finance_minutes_meeting_create',
        ]);

        try {

            DB::beginTransaction();
            $request->validate([
                'board_meeting_id' => 'required',
                'mom_detail' => 'required',
            ]);

            if($request->file('mom_document')){
                $responce=$this->saveMoMFile($request,'MoMDocuments');
                $this->input['mom_document']=$responce;
            }
            $mom= MinuteOfMeeting::query()->create($this->input);
            DB::commit();
            return resp('1', 'MoM added Successfully!', $mom, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function saveMoMFile($request,$folder){

        $file = $request->file('mom_document');
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
    public function show(MinuteOfMeeting $minuteOfMeeting)
    {
        $this->authorizeAny([
            'board_meeting_view',
            'finance_minutes_meeting_view',
        ]);

        $data['approval_request']=getNextApproval(28,auth()->user()->designation_id,$minuteOfMeeting->id);
        $data['approval_request_status']=checkApprovalRequestStatus(28,$minuteOfMeeting->id);
        //$data['minuteOfMeeting']=$minuteOfMeeting->load('boardMeetingDetail.agendaDetail','boardMeetingApplicants.applicantDetail');

        $data['minuteOfMeeting'] = $minuteOfMeeting->load([
            'boardMeetingDetail.agendaDetail',
            'boardMeetingApplicants' => function ($query) {
                $query->where('IsBoardMember', 1)->with('applicantDetail.designation');
            }
        ]);

        return resp(1, 'Successful!',$data , Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MinuteOfMeeting $minuteOfMeeting)
    {
        $this->authorizeAny([
            'board_meeting_update',
            'finance_minutes_meeting_update',
        ]);

        try {
            DB::beginTransaction();
            $request->validate([
                'board_meeting_id' => 'required',
                'mom_detail' => 'required',
            ]);

            if($request->file('mom_document')){
                $responce=$this->saveMoMFile($request,'MoMDocuments');
                $this->input['mom_document']=$responce;
            }
            MinuteOfMeeting::query()->find($minuteOfMeeting->id)->update($this->input);
            $minuteOfMeeting->refresh();
            DB::commit();
            return resp('1', 'MoM updated Successfully!', $minuteOfMeeting, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MinuteOfMeeting $minuteOfMeeting)
    {
        $this->authorizeAny([
            'board_meeting_delete',
            'finance_minutes_meeting_delete',
        ]);

        $minuteOfMeeting->delete();
        return resp(1, 'MoM deleted successfully.', [], Response::HTTP_OK);
    }
    public function momDropDown()
    {
        $this->authorize('board_meeting_view');

        $data['board_meetings_list']=BoardMeeting::query()->with(['agendaDetail','BoardMeetingMom'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
    public function sendMinutesOfMeetingForApproval(MinuteOfMeeting $item)
    {
        $this->authorize('board_meeting_update');

        $update=array('approval_status'=>0);
        MinuteOfMeeting::query()->where('id',$item->id)->update($update);
        return resp(1,'Minutes of meeting send for Approval.', [],Response::HTTP_OK);

        /*$approval_process=ApprovalProcess::query()->where('approval_process_id',28)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',28)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            MinuteOfMeeting::query()->where('id',$item->id)->update($update);
            return resp(1,'Minutes of meeting send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Minutes of meeting approval already sent.', [],Response::HTTP_OK);
            }
        }*/
    }

    public function approveMinutesOfMeeting(Request $request,MinuteOfMeeting $item)
    {
        $this->authorizeAny([
            'board_meeting_update',
            'finance_minutes_meeting_update',
        ]);

        $request->validate([
            'status' => 'required',
            'board_member_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

           // $this->input['added_date']=date('Y-m-d h:i:s');
            $updateCommittee= BoardMeetingApplicant::query()->where('board_member_id',$request->board_member_id)->where('board_meeting_id',$item->board_meeting_id)->update($this->input);
            if($updateCommittee){
                $totalRecords = BoardMeetingApplicant::query()->where('board_meeting_id',$item->board_meeting_id)->where('IsBoardMember',1)->count();

                $onethirPercent=ceil($totalRecords/3);

                if($onethirPercent <= 4){
                    $approvedRecords = BoardMeetingApplicant::query()->where('status', 1)->where('board_meeting_id',$item->board_meeting_id)->where('IsBoardMember',1)->count();
                    $rejectRecords = BoardMeetingApplicant::query()->where('status', 2)->where('board_meeting_id',$item->board_meeting_id)->where('IsBoardMember',1)->count();
                    $totalAppReject=$approvedRecords + $rejectRecords;

                    $percentageApproved=0;
                    $percentageReject=0;
                    if ($totalRecords > 0) {
                        //$percentageApproved = ($approvedRecords / $totalRecords) * 100;
                        $percentageApproved = $approvedRecords;
                        //$percentageReject = ($rejectRecords / $totalRecords) * 100;
                        $percentageReject = $rejectRecords;
                    } else {
                        $percentageApproved = 0; // Handle division by zero
                        $percentageReject = 0; // Handle division by zero
                    }

                    if( $percentageApproved >= $onethirPercent){
                        MinuteOfMeeting::query()->where('id',$item->id)->update(array('approval_status'=>1));
                    }else {
                        if( $percentageReject >= $onethirPercent && $totalAppReject == $totalRecords){
                            MinuteOfMeeting::query()->where('id',$item->id)->update(array('approval_status'=>2));
                        }
                    }
                }else{
                    $approvedRecords = BoardMeetingApplicant::query()->where('status', 1)->where('board_meeting_id',$item->board_meeting_id)->where('IsBoardMember',1)->count();
                    $rejectRecords = BoardMeetingApplicant::query()->where('status', 2)->where('board_meeting_id',$item->board_meeting_id)->where('IsBoardMember',1)->count();
                    $totalAppReject=$approvedRecords + $rejectRecords;

                    if($totalAppReject == $totalRecords){

                        if($approvedRecords >= $rejectRecords){
                            MinuteOfMeeting::query()->where('id',$item->id)->update(array('approval_status'=>1));
                        }else{
                            MinuteOfMeeting::query()->where('id',$item->id)->update(array('approval_status'=>2));
                        }
                    }

                }

            }
            $item->refresh();
            DB::commit();
            return resp('1', 'Data updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
}

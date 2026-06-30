<?php

namespace App\Http\Controllers\Api\V1\Governance;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Employee;
use App\Models\Governance\ArticleOfAssociation;
use App\Models\Governance\BoardResolutionApprovalCommittee;
use App\Models\Governance\BoardResolutionPassed;
use App\Models\Governance\Memorandum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BoardResolutionPassedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'board_resolution_view',
            'finance_board_resolution_view',
        ]);

        $data['list']=BoardResolutionPassed::with('BoardMeetingId')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'board_resolution_create',
            'finance_board_resolution_create',
        ]);

        $request->validate([
            //'board_meeting_id' => 'required',
            'name' => 'required',
            'date' => 'required',
            'particular' => 'required',
            'remarks' => 'required',
            //'attachment' => 'required',
        ]);
        if($request->file('attachment')){
            $responce=$this->saveFile($request,'BoardResolutionPassed');
            $this->input['attachment']=$responce;
        }
        try {
            DB::beginTransaction();
            $this->input['status']=4;
            $mom= BoardResolutionPassed::query()->create($this->input);
            if($mom){
                $board_member=Employee::query()->where('IsBoardMember',1)->where('employee_type',13)->get();

                if($board_member){
                    foreach ($board_member as $member){
                        $insert=array(
                            'resolution_id'=>$mom->id,
                            'board_member_id'=>$member->id,

                        );
                        BoardResolutionApprovalCommittee::query()->create( $insert);
                    }

                }
            }
            DB::commit();
            return resp('1', 'Article of Association added Successfully!', $mom, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage() .' on line :: '.$e->getLine() , null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BoardResolutionPassed $boardResolutionPassed): JsonResponse
    {
        $this->authorizeAny([
            'board_resolution_view',
            'finance_board_resolution_view',
        ]);

        $data['boardResolutionPassed'] = $boardResolutionPassed->load('BoardMeetingId');
        $data['boardResolutionCommittee'] = BoardResolutionApprovalCommittee::query()->with('employeeDetail')->where('resolution_id',$boardResolutionPassed->id)->get();
        $data['approval_request']=getNextApproval(25,auth()->user()->designation_id,$boardResolutionPassed->id);
        $data['approval_request_status']=checkApprovalRequestStatus(25,$boardResolutionPassed->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BoardResolutionPassed $boardResolutionPassed)
    {
        $this->authorizeAny([
            'board_resolution_update',
            'finance_board_resolution_update',
        ]);

        $request->validate([
            //'board_meeting_id' => 'required',
            'name' => 'required',
            'date' => 'required',
            'particular' => 'required',
            'remarks' => 'required',
            //'attachment' => 'required',
        ]);
        if($request->file('attachment')){
            $responce=$this->saveFile($request,'BoardResolutionPassed');
            $this->input['attachment']=$responce;
        }
        try {
            DB::beginTransaction();
            BoardResolutionPassed::query()->find($boardResolutionPassed->id)->update($this->input);
            $boardResolutionPassed->refresh();
            DB::commit();
            return resp('1', 'Data updated Successfully!', $boardResolutionPassed, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveFile($request,$folder)
    {
        $file = $request->file('attachment');
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
     * Remove the specified resource from storage.
     */
    public function destroy(BoardResolutionPassed $boardResolutionPassed): JsonResponse
    {
        $this->authorizeAny([
            'board_resolution_delete',
            'finance_board_resolution_delete',
        ]);

        $item = $boardResolutionPassed->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    /*public function sendBoardResolutionForApproval(BoardResolutionPassed $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',25)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',25)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            BoardResolutionPassed::query()->where('id',$item->id)->update($update);
            return resp(1,'Board resolution send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Board resolution approval already sent.', [],Response::HTTP_OK);
            }
        }
    }*/
    public function sendBoardResolutionForApproval(BoardResolutionPassed $item)
    {
        $this->authorize('board_resolution_update');

        $update=array('status'=>0);
        BoardResolutionPassed::query()->where('id',$item->id)->update($update);
        return resp(1,'Board resolution send for Approval.', [],Response::HTTP_OK);
    }

    public function approveResolution(Request $request,BoardResolutionApprovalCommittee $item)
    {
        $this->authorize('board_resolution_update');


        $request->validate([
            'status' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $this->input['added_date']=date('Y-m-d h:i:s');
           $updateCommittee= BoardResolutionApprovalCommittee::query()->find($item->id)->update($this->input);
            if($updateCommittee){
                $totalRecords = BoardResolutionApprovalCommittee::query()->where('resolution_id',$item->resolution_id)->count();
                $onethirPercent=ceil($totalRecords/3);
                if($onethirPercent <= 4){
                    $approvedRecords = BoardResolutionApprovalCommittee::query()->where('status', 1)->where('resolution_id',$item->resolution_id)->count();
                    $rejectRecords = BoardResolutionApprovalCommittee::query()->where('status', 2)->where('resolution_id',$item->resolution_id)->count();
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
                        BoardResolutionPassed::query()->where('id',$item->resolution_id)->update(array('status'=>1));
                    }else {
                        if( $percentageReject >= $onethirPercent && $totalAppReject == $totalRecords){
                            BoardResolutionPassed::query()->where('id',$item->resolution_id)->update(array('status'=>2));
                        }
                    }
                }else{
                    $approvedRecords = BoardResolutionApprovalCommittee::query()->where('status', 1)->where('resolution_id',$item->resolution_id)->count();
                    $rejectRecords = BoardResolutionApprovalCommittee::query()->where('status', 2)->where('resolution_id',$item->resolution_id)->count();
                    $totalAppReject=$approvedRecords + $rejectRecords;

                    if($totalAppReject == $totalRecords){
                        if($approvedRecords >= $rejectRecords){
                            BoardResolutionPassed::query()->where('id',$item->resolution_id)->update(array('status'=>1));
                        }else{
                            BoardResolutionPassed::query()->where('id',$item->resolution_id)->update(array('status'=>2));
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

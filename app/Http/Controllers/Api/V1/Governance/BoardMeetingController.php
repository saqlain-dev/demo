<?php

namespace App\Http\Controllers\Api\V1\Governance;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Employee;
use App\Models\Finance\Budget\AnnualBudget;
use App\Models\Finance\Budget\ProjectBudget;
use App\Models\Governance\BoardMeeting;
use App\Models\Governance\BoardMeetingAgenda;
use App\Models\Governance\BoardMeetingApplicant;
use App\Models\Governance\BoardResolutionPassed;
use App\Models\Governance\MinuteOfMeeting;
use App\Models\HR\Policy;
use App\Models\Type;
use App\Models\TypeValue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BoardMeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'board_meeting_view',
            'finance_board_meeting_view',
        ]);

        $boardMeeting=BoardMeeting::query()->with('boardMeetingApplicant.applicantDetail','created_by.employeeDetail')->get();
        foreach ($boardMeeting as $key => $meeting)
        {
            if($meeting['topic_category']){
                $list=$this->getCategoryDetail($meeting['topic_category']);
                $boardMeeting[$key]['topic_category']=$list;
            }
        }
        $data['board_meeting_listing']=$boardMeeting;
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'board_meeting_create',
            'finance_board_meeting_create',
        ]);

        try {

            DB::beginTransaction();
            $request->validate([
                'board_meeting_title' => 'required',
                'topic_category' => 'required|array|min:1',
                'board_member_id' => 'required|array|min:1',
            ]);
            $this->input['board_meeting_date']=date('Y-m-d',strtotime($request->board_meeting_date));
            $this->input['topic_category']=implode(',',$this->input['topic_category']);
            $board_member=$this->input['board_member_id'];

            // Check for duplication based on date and time
            $existingMeeting = BoardMeeting::where('board_meeting_date', $this->input['board_meeting_date'])
                //->where('board_meeting_time', $this->input['board_meeting_time'])
                ->first();

            if ($existingMeeting) {
                // If a meeting with the same date and time exists, rollback and return an error response
                DB::rollBack();
                return resp('0', 'A board meeting at this date already exists.', null, Response::HTTP_CONFLICT);
            }
            if ($request->hasFile('meeting_file')) {
                $responses = $this->saveBoardMeetingFile($request, 'BoardMeeting');
                $this->input['meeting_file'] = $responses;
            }
            $boardMeeting= BoardMeeting::query()->create($this->input);
            if($boardMeeting){
                foreach ($board_member as $member){
                    $employee=Employee::query()->where('id',$member)->first();
                    $insert=array(
                        'board_meeting_id'=>$boardMeeting->id,
                        'board_member_id'=>$member,
                        'IsBoardMember'=>$employee['IsBoardMember'],
                    );
                    BoardMeetingApplicant::query()->create( $insert);
               }

            }

            DB::commit();
            return resp('1', 'Board meeting added Successfully!', $boardMeeting, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage() .' on line :: '.$e->getLine() , null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function saveBoardMeetingFile($request, $folder)
    {
        $images = $request->file('meeting_file');
        // Ensure $images is an array
        if (!is_array($images)) {
            $images = [$images];
        }
        foreach ($images as $image) {
            $path = 'uploads/media/' . $folder;

            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }
            if (!file_exists('uploads/media')) {
                mkdir('uploads/media', 0777, true);
            }
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $filename = time() . '_' . $image->getClientOriginalName();
            $file_name = str_replace(' ', '_', $filename);
            $image->move($path, $file_name);

            // Save the path to the array
            $paths = $path . '/' . $file_name;
        }

        return json_encode($paths);
    }

    /**
     * Display the specified resource.
     */
    public function show(BoardMeeting $boardMeeting)
    {
        $this->authorizeAny([
            'board_meeting_view',
            'finance_board_meeting_view',
        ]);

        $boardMeeting->load('boardMeetingApplicant.applicantDetail.designation','created_by.employeeDetail');
        if ($boardMeeting && $boardMeeting['topic_category'] != "")
        {

            $list=$this->getCategoryDetail($boardMeeting['topic_category']);
            $boardMeeting['topic_category']=$list;


        }

        $data['board_meeting_detail']=$boardMeeting;
        $data['approval_request']=getNextApproval(26,auth()->user()->designation_id,$boardMeeting->id);
        $data['approval_request_status']=checkApprovalRequestStatus(26,$boardMeeting->id);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BoardMeeting $boardMeeting)
    {
        $this->authorizeAny([
            'board_meeting_update',
            'finance_board_meeting_update',
        ]);

        try {

            DB::beginTransaction();
            $request->validate([
                'board_meeting_title' => 'required',
                'topic_category' => 'required|array|min:1'
            ]);
            $this->input['board_meeting_date']=date('Y-m-d',strtotime($request->board_meeting_date));
            $this->input['topic_category']=implode(',',$this->input['topic_category']);
            $board_member=$this->input['board_member_id'];
            if ($request->hasFile('meeting_file')) {
                $responses = $this->saveBoardMeetingFile($request, 'BoardMeeting');
                $this->input['meeting_file'] = $responses;
            }
             $responce=BoardMeeting::query()->find($boardMeeting->id)->update($this->input);
            if($responce){
                BoardMeetingApplicant::query()->where('board_meeting_id',$boardMeeting->id)->delete();
                foreach ($board_member as $member){
                    $employee=Employee::query()->where('id',$member)->first();
                    $insert=array(
                        'board_meeting_id'=>$boardMeeting->id,
                        'board_member_id'=>$member,
                        'IsBoardMember'=>$employee['IsBoardMember'],
                    );
                    BoardMeetingApplicant::query()->create( $insert);
                }

            }
             $boardMeeting->refresh();
             DB::commit();
            return resp('1', 'Board meeting updated Successfully!', $boardMeeting, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage() .' on line :: '.$e->getLine() , null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BoardMeeting $boardMeeting)
    {
        $this->authorizeAny([
            'board_meeting_delete',
            'finance_board_meeting_delete',
        ]);

        $MoM=MinuteOfMeeting::query()->where('board_meeting_id',$boardMeeting->id)->get();

        if($MoM){
            $boardMeeting->boardMeetingApplicant()->delete();
            $boardMeeting->delete();
            return resp(1, 'Board meeting deleted successfully.', [], Response::HTTP_OK);
        }else{
            $boardMeeting->delete();
            return resp(0, 'Board meeting deleted successfully.', [], Response::HTTP_OK);
        }

    }
    public function boardMeetingDropDown()
    {

       /* $agenda_listing=BoardMeetingAgenda::all()->toArray();
        foreach ($agenda_listing as $key => $agenda)
        {

            $list=$this->getAgendaCategoryDetail($agenda['agenda_category']);
            $agenda_listing[$key]['agenda_category']=$list;

        }
        $data['agenda_listing']=$agenda_listing;
        $data['board_members']=Employee::query()->where('IsBoardMember',1)->get();*/
        $data['board_members']=Employee::all();
        $data['governance_category_list']=Type::getTypeValues('governance-category');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
    public function getCategoryDetail($category)
    {

        $categories=explode(',',$category);

        $categories=TypeValue::query()->whereIn('id',$categories)->get();

        return $categories ? $categories->toArray():[];

    }

    public function sendBoardMeetingForApproval(BoardMeeting $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',26)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',26)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            BoardMeeting::query()->where('id',$item->id)->update($update);
            return resp(1,'Board meeting send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Board meeting approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function governanceReport(Request $request)
    {
        try {

            DB::beginTransaction();
            $annual_budgets = AnnualBudget::with('BudgetDetail')->get();

            $totalBudget = 0;
            $totalApprovedBudget = 0;
            $totalRejectedBudget = 0;

            foreach ($annual_budgets as $annualBudget) {

                $totalBudget += $annualBudget->BudgetDetail->sum('budget_amount');


                if ($annualBudget->approval_status == 1) {
                    $totalApprovedBudget += $annualBudget->BudgetDetail->sum('budget_amount');
                } elseif ($annualBudget->approval_status == 3) {
                    $totalRejectedBudget += $annualBudget->BudgetDetail->sum('budget_amount');
                }
            }
            $data['total_budget'] = $totalBudget;
            $data['total_approved_budget'] = $totalApprovedBudget;
            $data['total_rejected_budget'] = $totalRejectedBudget;

            $totalProjectBudget = 0;
            $project_budgets = ProjectBudget::with('BudgetDetail')->get();

            foreach ($project_budgets as $projectBudget) {

                if ($projectBudget->BudgetDetail->isNotEmpty()) {
                    $totalProjectBudget += $projectBudget->BudgetDetail->sum('amount');
                }

            }
            $data['total_project_budget'] = $totalProjectBudget;
            $data['bod_meetings'] = BoardMeeting::query()->orderByDesc('id')->get();
            $data['total_resolutions'] = BoardResolutionPassed::query()->count();
            $data['approved_resolutions'] = BoardResolutionPassed::where('approval_status', 1)->count();
            $data['rejected_resolutions'] = BoardResolutionPassed::where('approval_status', 3)->count();
            $data['pending_resolutions'] = BoardResolutionPassed::where('approval_status', 2)->count();

            $data['total_policies'] = Policy::query()->count();
            $data['approved_policies'] = Policy::where('approval_status', 1)->count();
            $data['rejected_policies'] = Policy::where('approval_status', 3)->count();
            $data['pending_policies'] = Policy::where('approval_status', 2)->count();

            DB::commit();
            return resp('1', 'Governance Report', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage() .' on line :: '.$e->getLine() , null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

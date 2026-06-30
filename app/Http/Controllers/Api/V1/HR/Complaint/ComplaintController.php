<?php

namespace App\Http\Controllers\Api\V1\HR\Complaint;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ComplaintCommittee;
use App\Models\Configuration\GeneralTemplates;
use App\Models\Employee;
use App\Models\HR\Complaint\Complaint;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'complaint_view',
            'manage_employee_portal',
        ]);

        $data['complaint_list']=Complaint::query()->whereIn('complaint_status',[0, 1, 2, 3])->with('complainFrom','complainAgainst','department','natureOfComplaint')->get();
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'complaint_create',
            'manage_employee_portal',
        ]);

        try {
            DB::beginTransaction();

            $request->validate([
                'complaint_date' => 'required|date_format:Y-m-d',
                'complain_from_emp' => 'required|integer',
                'department' => 'required|integer',
                'position_title' => 'required|string',
                'complain_against_emp' => 'required|string',
                'nature_of_complaint' => 'required|string',
                'complaint_detail' => 'required|string',
            ]);
            $statement = DB::select("SELECT IDENT_CURRENT('complaints') as nextID");
            $cNO='C#/'.sprintf('%04d', $statement[0]->nextID);
            $this->input['complaint_no']=$cNO;
            $this->input['complaint_date']=date('Y-m-d',strtotime($request->complaint_date));

            if($request->hasFile('complaint_file')) {

                $responce = $this->saveComplaintFile($request, 'complaint_file');

                if ($responce) {
                    $this->input['complaint_file'] = $responce;
                }
            }else{
                unset($this->input['complaint_file']);
            }

            $complaint=Complaint::query()->create($this->input);
            DB::commit();
            return resp('1', 'Complaint added Successfully!', $complaint->load('department','natureOfComplaint'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function saveComplaintFile($request,$folder){

        $file = $request->file('complaint_file');
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
    public function show(Complaint $complaint)
    {
        $this->authorizeAny([
            'complaint_view',
            'manage_employee_portal',
        ]);

        $data['view_complaint']=$complaint->load('complainFrom','department','natureOfComplaint','committeeMembers.memeberDetail','committeeMembers.complaintLetter');
        $data['view_complaint']->complain_against = $complaint->complain_against_or_department;
        if ($complaint->complain_type == 0){ // against employee
            $history = Employee::query()->with(['complainAgainsts' => ['complainFrom','department','complainAgainst']])->find($complaint->complain_against_emp);
            $data['view_complaint']->complain_against_emp_history = $history->complainAgainsts;
        }
        $history = Employee::query()->with(['complainFromEmployees' => ['complainFrom','department','complainAgainst']])->find($complaint->complain_from_emp);
        $data['view_complaint']->complain_from_emp_history = $history->complainFromEmployees;
        
        $data['approval_request']=getNextApproval(20,auth()->user()->designation_id,$complaint->id);
        $data['approval_request_status']=checkApprovalRequestStatus(20,$complaint->id);
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    public function getEmployeeComplaints($emplId)
    {
        $this->authorizeAny([
            'manage_employee_portal',
        ]);

        $data['view_complaint']= Complaint::query()->with(['complainFrom','complainAgainst','department','natureOfComplaint'])->where('complain_from_emp',$emplId)
            ->orWhere(function ($query) use ($emplId) {
                $query->where('complain_against_emp', $emplId)
                    ->where('complain_type', 0); // get employee complaints only
            })->get();

        // Add 'complain_against_or_department' to each complaint in the collection.
        /*$data['view_complaint']->each(function ($complaint) {
            $complaint->complain_against = $complaint->complain_against_or_department;
        });*/
        
       // $complaint
        //$data['approval_request']=getNextApproval(20,auth()->user()->designation_id,$complaint->id);
        //$data['approval_request_status']=checkApprovalRequestStatus(20,$complaint->id);
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Complaint $complaint)
    {
        $this->authorizeAny([
            'complaint_update',
            'manage_employee_portal',
        ]);

        try {
            DB::beginTransaction();

            $request->validate([
                'complaint_date' => 'required|date_format:Y-m-d',
                'complain_from_emp' => 'required|integer',
                'department' => 'required|integer',
                'position_title' => 'required|string',
                //'contact_detail' => 'required|string',
                'complain_against_emp' => 'required|string',
                'nature_of_complaint' => 'required|integer',
                'complaint_detail' => 'required|string',
            ]);
            $this->input['complaint_date']=date('Y-m-d',strtotime($request->complaint_date));

            if($request->hasFile('complaint_file')) {

                $responce = $this->saveComplaintFile($request, 'complaint_file');

                if ($responce) {
                    $this->input['complaint_file'] = $responce;
                }
            }else{
                unset($this->input['complaint_file']);
            }

            Complaint::query()->where('id',$complaint->id)->update($this->input);
            $complaint->refresh();
            DB::commit();
            return resp('1', 'Complaint updated Successfully!', $complaint->load('complainFrom','complainAgainst','department','natureOfComplaint'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Complaint $complaint)
    {
        $this->authorizeAny([
            'complaint_delete',
            'manage_employee_portal',
        ]);

        $complaint->delete();
        return resp('1', 'Complaint deleted Successfully!', [], Response::HTTP_OK);
    }

    public function complaintDropDown()
    {
        $data['admin_nature_of_complaints']=Type::getTypeValues('nature-of-complaint');
        $data['hr_nature_of_complaints']=Type::getTypeValues('hr-nature-of-complaint');
        $data['departments']=Type::getTypeValues('department-names');
        $data['employee_list']=Employee::query()->with('department','designation')->get();
        $data['letter_templates'] = GeneralTemplates::all();
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }
    public function sendComplaintRequestForApproval(Complaint $item)
    {
        $approval_process=ApprovalProcess::query()->where('approval_process_id',20)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',20)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0  && $checkProcess == 0){
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
            Complaint::query()->where('id',$item->id)->update($update);
            return resp(1,'Complaint request send for Approval.', $Approval,Response::HTTP_OK);
        }else{
            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Complaint approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function complaintSendToHr(Request $request,Complaint $item)
    {
        $this->authorizeAny([
            'manage_employee_portal',
        ]);

        $item->complaint_status=$request->complaint_status;
        $item->save();
        $item->refresh();
        return resp('1', 'Successfully!', $item, Response::HTTP_OK);
    }

    public function complaintAction(Request $request,Complaint $item)
    {

        try {
            DB::beginTransaction();
            $request->validate([
                'complaint_action' => 'required',
            ]);

            if($request->complaint_action ==  2){
                Complaint::query()->where('id',$item->id)->update($this->input);

                Complaint::query()->where('id',$item->id)->update(array('complaint_status' => 1));
            }else{
                Complaint::query()->where('id',$item->id)->update($this->input);
            }

            $item->refresh();
            DB::commit();
            return resp('1', 'Complaint updated Successfully!', $item->load('complainFrom','complainAgainst','department','natureOfComplaint'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function createCommittee(Request $request,Complaint $item)
    {

        try {
            DB::beginTransaction();
            $request->validate([
                'committee_member' => 'required|array|min:1',
                'committee_member.*' => 'required',
                'focal_person_id' => 'required',
                'nda_letter' => 'required|integer',
            ]);

            $committeeMembers=$request->committee_member;

            foreach($committeeMembers as $member){
                $insert=array(
                    'member_id'=>$member,
                    'complaint_id'=>$item->id,
                    'nda_letter'=>$request->nda_letter,
                );
                ComplaintCommittee::query()->create($insert);
            }
            $focal=array(
                'member_id'=>$request->focal_person_id,
                'is_focal_person'=>1,
                'complaint_id'=>$item->id,
                'nda_letter'=>$request->nda_letter,
            );
           $committeemember= ComplaintCommittee::query()->create($focal);

            DB::commit();
            return resp('1', 'Committee created Successfully!', $committeemember, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    public function updateCommittee(Request $request,Complaint $item)
    {

        try {
            DB::beginTransaction();
            $request->validate([
                'committee_member' => 'required|array|min:1',
                'committee_member.*' => 'required',
                'focal_person_id' => 'required',
            ]);
            ComplaintCommittee::query()->where('complaint_id',$item->id)->delete();

            $committeeMembers=$request->committee_member;

            foreach($committeeMembers as $member){
                $insert=array(
                    'member_id'=>$member,
                    'complaint_id'=>$item->id,
                );
                ComplaintCommittee::query()->create($insert);
            }
            $focal=array(
                'member_id'=>$request->focal_person_id,
                'is_focal_person'=>1,
                'complaint_id'=>$item->id,
            );
           $committeemember= ComplaintCommittee::query()->create($focal);

            DB::commit();
            return resp('1', 'Committee updated Successfully!', $committeemember, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function submitComplaintFeedback(Request $request,Complaint $item)
    {

        try {
            DB::beginTransaction();
            $request->validate([
                'comments' => 'required',
                'member_id' => 'required',
            ]);

            ComplaintCommittee::query()->where('complaint_id',$item->id)->where('member_id',$request->member_id)->update(array('comments'=>$request->comments));

            DB::commit();
            return resp('1', 'Committee feedback updated Successfully!', $item->load('committeeMembers.memeberDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    public function getCommitteeMembers(Complaint $item)
    {

        return resp('1', 'Successfully!', $item->load('committeeMembers.memeberDetail'), Response::HTTP_OK);

    }
    public function getAssignedComplaints(Complaint $item)
    {

        $complaints = Complaint::query()
            ->whereHas('committeeMembers', function ($query) {
                $query->where('member_id', auth()->user()->employee_id);
            })
            ->with([
                'committeeMembers' => function ($query) {
                    $query->where('member_id', auth()->user()->employee_id);
                }
            ],'committeeMembers.memberDetail')->get();
        return resp('1', 'Successfully!',$complaints, Response::HTTP_OK);

    }

    public function actionReport(Request $request,Complaint $item)
    {

        try {
            DB::beginTransaction();
            $request->validate([
                'action_report_title' => 'required',
                'action_report' => 'required',
            ]);

            if($request->hasFile('action_report_file')) {

                $responce = $this->saveActionReportFile($request, 'actionReportFile');

                if ($responce) {
                    $this->input['action_report_file'] = $responce;
                }
            }

            $complaintaction=Complaint::query()->where('id',$item->id)->update($this->input);

            $item->refresh();
            DB::commit();
            return resp('1', 'Complain action report added Successfully!', $item->load('committeeMembers.memeberDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function saveActionReportFile($request,$folder){

        $file = $request->file('action_report_file');
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

    public function saveNdaAgreement(Request $request)
    {
        $request->validate([
            'committee_members_id' => 'required|integer|exists:complaint_committees,id',
            'nda_agreement' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            $item = ComplaintCommittee::query()->find($request->committee_members_id)->update(['nda_agreement' => $request->nda_agreement]);
           
            DB::commit();
            return resp('1', 'Successfully!', $item, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}

<?php

namespace App\Http\Controllers\Api\V1\Lead;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Division\Division;
use App\Models\ErpActivity\ErpActivity;
use App\Models\Inquiry\Inquiry;
use App\Models\Lead;
use App\Models\LeadAttachment;
use App\Models\SalesTeam\SalesTeamEmployee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'lead_view',
        ]);
        $data['lead_listing']=Lead::query()->with('leadStatus','leadType','leadRequestType','leadOwner','organization','comments.createdBy','attachments.createdBy','tasks','qualificationStatus','qualifiedBy','inquiry','leadQualification.qualificationStatus','leadQualification.qualifiedBy','leadQualification.employeeRef')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'lead_create',
        ]);
        $request->validate([
            //'lead_series' => 'required',
            'job_title' => 'required',
            'lead_owner' => 'required',
            //'salutation' => 'required',
            //'gender' => 'required',
            'lead_status' => 'required',
            //'first_name' => 'required',
            'lead_type' => 'required',
            'lead_request_type' => 'required',
           // 'qualification_type' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $statement = DB::select("SELECT IDENT_CURRENT('leads') as nextID");
            $lead_series_number='LEAD-'.date('Y').'-'.sprintf('%04d', $statement[0]->nextID);
            $this->input['lead_series']=$lead_series_number;
            $lead=Lead::query()->create($this->input);

            $leadOwner=auth()->user()->employee_id;

            $divisionHeads = Division::whereNotNull('division_head_id')->pluck('division_head_id')->unique()->toArray();
            $divisionHeadsArray = is_array($divisionHeads) ? $divisionHeads : [$divisionHeads];
            $leadOwnerArray = is_array($leadOwner) ? $leadOwner : [$leadOwner];
            $divisionHeads = array_unique(array_merge($divisionHeadsArray, $leadOwnerArray));
            $title="New Lead Alert!";
            $message="A new lead has been created.";

            sendWebNotification($divisionHeads,$title,$message,'lead-creation');

            DB::commit();
            return resp(1, 'Successful!', $lead->load('leadStatus','leadType','leadRequestType','leadOwner','organization','inquiry'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        $this->authorizeAny([
            'lead_view',
        ]);
        $lead=$lead->load(['gender','leadActivity'=>['performedBy','activityState','activityType','activityAttachments','activityable'],'salutation','leadStatus','leadType','leadRequestType','leadOwner.divisionEmployee.division','qualificationType','organization','comments.createdBy','attachments.createdBy','tasks'=>['assignTo','taskStatus','taskPriority'],'qualificationStatus','qualifiedBy','inquiry','activities','leadQualification'=>['assignTo','qualificationStatus','qualifiedBy','employeeRef']]);
        $leadQualificationId = null;
        if ($lead->leadQualification) {
            $leadQualificationId = $lead->leadQualification->id;
        }
        if($leadQualificationId != "") {
            $data['approval_request'] = getNextApproval(61, auth()->user()->designation_id, $leadQualificationId);
            $data['approval_request_status'] = checkApprovalRequestStatus(61, $leadQualificationId);
        }else{
            $data['approval_request'] = [];
            $data['approval_request_status'] =[];
        }

        $data['lead']=$lead;
        return resp(1, 'Successful!',$data , Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $this->authorizeAny([
            'lead_update',
        ]);
        $request->validate([
            //lead_series' => 'required',
            'job_title' => 'required',
            'lead_owner' => 'required',
            //'salutation' => 'required',
            //'gender' => 'required',
            'lead_status' => 'required',
            //'first_name' => 'required',
            'lead_type' => 'required',
            'lead_request_type' => 'required',
            //'qualification_type' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $this->input = array_map(function ($value) {
                return $value === "null" ? null : $value;
            }, $this->input);
            $lead->update($this->input);
            $lead->refresh();

            DB::commit();
            return resp(1, 'Successful!', $lead->load('leadStatus','leadType','leadRequestType','leadOwner','organization','inquiry'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead)
    {
        $this->authorizeAny([
            'lead_delete',
        ]);
        $lead->leadQualification()->delete();
        $lead->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }

    public function saveLeadAttachment(Request $request)
    {
        $request->validate([
            'lead_id' => 'required',
            'lead_attachment' => 'required',
        ]);

        try {
            DB::beginTransaction();
            if ($request->hasFile('lead_attachment')) {
                $responses = $this->saveAttachmentFile($request, 'LeadAttachment');

                $this->input['lead_attachment'] = $responses;
            }
            $LeadAttachment=LeadAttachment::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $LeadAttachment, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function deleteLeadAttachment(Request $request)
    {
        $request->validate([
            'attachment_id' => 'required',
        ]);

        try {

            $LeadAttachment = LeadAttachment::query()->find($request->attachment_id);

            if ($LeadAttachment) {
                $LeadAttachment->delete();
            } else {
                // Handle the case when the attachment is not found
                return resp(0, 'Attachment not found!', [], Response::HTTP_OK);
            }


            return resp(1, 'Successful!', $LeadAttachment, Response::HTTP_OK);
        } catch (\Exception $e) {

            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveAttachmentFile($request, $folder)
    {
        $image = $request->file('lead_attachment');

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

        $path = $path . '/' . $file_name;

        return $path;
    }

    public function getLeadDropdowns()
    {
        $data['inquiry_listing']=Inquiry::query()->with('inquiryType')->get();
        $data['activity_listing']=ErpActivity::query()->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}

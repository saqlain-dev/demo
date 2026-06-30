<?php

namespace App\Http\Controllers\Api\V1\ErpActivity;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\ErpActivity\ErpActivity;
use App\Models\ErpActivity\ErpActivityAttachment;
use App\Models\Lead;
use App\Models\Opportunity\Opportunity;
use App\Models\Prospect;
use App\Models\SalesTeam\SalesTeam;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ErpActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'crm_activity_view',
        ]);
        $data['activity_list']=ErpActivity::query()->with('activityAttachments','activityable','performedBy','activityState','activityType')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'crm_activity_create',
        ]);
        $request->validate([
            'activityable_id' => 'required|integer',
            'activityable_type' => 'required|string',
            'performed_by' => 'required|integer',
            'activity_state' => 'required|integer',
            'activity_type' => 'required|integer',
            'actual_start_date' => 'required|date_format:Y-m-d',
            //'actual_end_date' => 'required|date_format:Y-m-d',
        ]);

        try {
            DB::beginTransaction();
            $this->input['activityable_type']= $request->activityable_type === 'lead' ? Lead::class : ($request->activityable_type === 'prospect' ? Prospect::class : Customer::class);
            $activity=ErpActivity::query()->create($this->input);

            //if ($request->hasFile('attachment_file')) {
                $activityAttachment = $this->addActivityAttachmentList($request, $activity);
           // }

            DB::commit();
            return resp(1, 'Successful!', $activity->load('activityAttachments','activityable','performedBy','activityState','activityType'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function addActivityAttachmentList(Request $request, ErpActivity $erp_activity)
    {
        try {
            DB::beginTransaction();

            $input = [
                'erp_activity_id' => $erp_activity->id, // Attach to created activity
            ];

            if ($request->hasFile('attachment_file')) {
                $response = $this->saveAttachment($request, 'erp_activity_attachment');
                if ($response) {
                    $input['attachment_file'] = $response;
                }
            }

            // Save attachment record
            $input['attachment_type']=$request->attachment_type;
            $input['attachment_name']=$request->attachment_name;
            $input['support_required']=$request->support_required;
            $input['next_meeting_remarks']=$request->next_meeting_remarks;
            $input['description']=$request->attachment_description;
            $input['opportunity_qualified']=$request->opportunity_qualified;


            $activity_attachment = ErpActivityAttachment::query()->create($input);

            DB::commit();
            return $activity_attachment->load('attachmentType'); // Return attachment details

        } catch (\Exception $e) {
            DB::rollBack();
            return null; // Return null if attachment fails
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ErpActivity $erp_activity)
    {
        $this->authorizeAny([
            'crm_activity_view',
        ]);
        return resp(1, 'Successful!', $erp_activity->load('activityAttachments','activityable','performedBy','activityState','activityType'), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ErpActivity $erp_activity)
    {
        $this->authorizeAny([
            'crm_activity_update',
        ]);
        $request->validate([
            'activityable_id' => 'required|integer',
            'activityable_type' => 'required|string',
            'performed_by' => 'required|integer',
            'activity_state' => 'required|integer',
            'activity_type' => 'required|integer',
            'actual_start_date' => 'required|date_format:Y-m-d',
           // 'actual_end_date' => 'required|date_format:Y-m-d',
        ]);

        try {
            DB::beginTransaction();
            $this->input['activityable_type']= $request->activityable_type === 'lead' ? Lead::class : ($request->activityable_type === 'prospect' ? Prospect::class : Customer::class);
            $erp_activity->update($this->input);
            $erp_activity->refresh();

            // Delete old attachments before inserting new ones


            //if ($request->hasFile('attachment_file')) {

                ErpActivityAttachment::query()->where('erp_activity_id', $erp_activity->id)->delete();
                $activityAttachment = $this->addActivityAttachmentList($request, $erp_activity);
            //}

            DB::commit();
            return resp(1, 'Successful!', $erp_activity->load('activityAttachments','activityable','performedBy','activityState','activityType'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ErpActivity $erp_activity)
    {
        $this->authorizeAny([
            'crm_activity_delete',
        ]);
        $erp_activity->activityAttachments()->delete();
        $erp_activity->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }
    function getEmployeeHierarchy($employee_id) {
        // Fetch the main employee details along with sales team details
        $employee = Employee::with('salesTeamEmployee')->find($employee_id);

        if (!$employee) {
            return null; // Return null if employee not found
        }

        // Fetch subordinates and recursively build their hierarchy
        $employee->subordinates = Employee::whereHas('salesTeamEmployee')
            ->where('report_to_id', $employee_id)
            ->get()
            ->map(function ($subordinate) {
                $subordinate->subordinates = $this->getEmployeeHierarchy($subordinate->id);
                return $subordinate;
            });

        return $employee;
    }
    function getAllEmployeesAtSameLevel($employee, &$result = []) {
        if (!$employee || !is_object($employee)) {
            return;
        }

        // Store the current employee in result
        $result[] = $employee;

        // Check if subordinates exist before looping
        if (!empty($employee->subordinates)) {
            foreach ($employee->subordinates as $subordinate) {
                $this->getAllEmployeesAtSameLevel($subordinate, $result);
            }
        }

        return $result;
    }



    public function getActivityDropDown()
    {
        //$data['employees']=Employee::query()->whereHas('salesTeamEmployee')->with('salesTeamEmployee')->get();
        $employeeHierarchy=$this->getEmployeeHierarchy(auth()->user()->employee_id);
        $allSameLevelEmployees = $this->getAllEmployeesAtSameLevel($employeeHierarchy);
        $data['employees']=$allSameLevelEmployees;
        $data['activity_against']=array('Prospect','Lead','Customer');
        $data['leads']=Lead::query()->with('organization')->get();
        $data['prospect']=Prospect::query()->with('company')->get();
        $data['customer']=Customer::query()->get();
        $data['opportunities']=Opportunity::query()->get();
        $data['activity_state']=Type::getTypeValues('activity-state');
        $data['activity_type']=Type::getTypeValues('activity-type');
        $data['attachment_type']=Type::getTypeValues('attachment-type');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function addActivityAttachment(Request $request)
    {
        $request->validate([
            //'attachment_type' => 'required|integer',
            //'attachment_name' => 'required|string',
            'erp_activity_id' => 'required|integer',
        ]);

        try {

            $erp_activity=ErpActivity::query()->find($request->erp_activity_id);
            if($erp_activity){
                DB::beginTransaction();
                if($request->hasFile('attachment_file')) {

                    $responce = $this->saveAttachment($request, 'erp_activity_attachment');

                    if ($responce) {
                        $this->input['attachment_file'] = $responce;
                    }
                }
                $activity_attachment=ErpActivityAttachment::query()->create($this->input);
                DB::commit();
                return resp(1, 'Successful!', $activity_attachment->load('attachmentType'), Response::HTTP_CREATED);
            }else{
                return resp(0, 'No record Found!', [], Response::HTTP_EXPECTATION_FAILED);
            }




        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveAttachment($request,$folder){

        $file = $request->file('attachment_file');
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

    public function deleteErpActivityAttachment($id)
    {
        $activityAttachment=ErpActivityAttachment::query()->find($id);
        if($activityAttachment){

            $activityAttachment->delete();
            return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);

        }else{
            return resp(0, 'No record Found!', [], Response::HTTP_EXPECTATION_FAILED);
        }
    }



}


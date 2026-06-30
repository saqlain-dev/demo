<?php

namespace App\Http\Controllers\Api\V1\Complaint;

use App\Http\Controllers\Controller;
use App\Models\Complaint\LasComplaint;
use App\Models\District;
use App\Models\Employee;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class LasComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $data['las_complaint']=LasComplaint::query()->with(['forwardTo'=>['department','designation'],'priority','gender','district','complainantCategory','feedbackCategory'])->get();
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'complainant_name' => 'required|string',
            ]);

            $insert=array(
                "complainant_name"=>$request->complainant_name ?? NULL,
                "complaint_date"=>date('Y-m-d h:i:s',strtotime($request->complaint_date)) ?? NULL,
                "relation_with_applicant"=>$request->relation_with_applicant ?? NULL,
                "gender"=>$request->gender ?? NULL,
                "district"=>$request->district ?? NULL,
                "address"=>$request->address ?? NULL,
                "contact"=>$request->contact ?? NULL,
                "mode_of_feedback"=>$request->mode_of_feedback ?? NULL,
                "feedback_received_by"=>$request->feedback_received_by ?? NULL,
                "relation_with_complainant"=>$request->relation_with_complainant ?? NULL,
                "complainant_category"=>$request->complainant_category ?? NULL,
                "type_of_call"=>$request->type_of_call ?? NULL,
                "another_request"=>$request->another_request ?? NULL
            );
            $lasComplaint=LasComplaint::query()->create($insert);

            DB::commit();

            return resp('1', 'Successfully!', $lasComplaint, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LasComplaint $las_complaint)
    {
        $data['las_complaint']=$las_complaint->load(['forwardTo'=>['department','designation'],'priority','gender','district','complainantCategory','feedbackCategory'])->get();
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LasComplaint $las_complaint)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'complainant_name' => 'required|string',
                'complaint_id' => 'required',
            ]);

            $insert=array(
                "complainant_name"=>$request->complainant_name ?? NULL,
                "complaint_date"=>date('Y-m-d h:i:s',strtotime($request->complaint_date)) ?? NULL,
                "relation_with_applicant"=>$request->relation_with_applicant ?? NULL,
                "gender"=>$request->gender ?? NULL,
                "district"=>$request->district ?? NULL,
                "address"=>$request->address ?? NULL,
                "contact"=>$request->contact ?? NULL,
                "mode_of_feedback"=>$request->mode_of_feedback ?? NULL,
                "feedback_received_by"=>$request->feedback_received_by ?? NULL,
                "relation_with_complainant"=>$request->relation_with_complainant ?? NULL,
                "complainant_category"=>$request->complainant_category ?? NULL,
                "type_of_call"=>$request->type_of_call ?? NULL,
                "another_request"=>$request->another_request ?? NULL
            );
            LasComplaint::query()->where('id',$request->complaint_id)->update($insert);
            $las_complaint->refresh();

            DB::commit();

            return resp('1', 'Successfully!', $las_complaint, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LasComplaint $las_complaint)
    {
        $las_complaint->delete();
        return resp(1,'Record Deleted Successfully!', $las_complaint,Response::HTTP_OK);
    }

    public function getLasComplaintDropdowns()
    {
        $data['gender']=Type::getTypeValues('employee-gender');
        $data['employees']=Employee::query()->with('designation','department')->whereNotIn('employee_type', [14, 16, 17, 18])->get();
        $data['districts']=District::all();
        $data['complainant_category']=Type::getTypeValues('complainant-category');
        $data['feedback_category']=Type::getTypeValues('feedback-category');
        $data['priority']=Type::getTypeValues('priority');
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    public function assignComplaint(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'complaint_id' => 'required',
            ]);

            $insert=array(
                "feedback_category"=>$request->feedback_category ?? NULL,
                "forwarding_date"=>date('Y-m-d h:i:s',strtotime($request->forwarding_date)) ?? NULL,
                "detail_of_feedback"=>$request->detail_of_feedback ?? NULL,
                "priority"=>$request->priority ?? NULL,
                "program"=>$request->program ?? NULL,
                "forwarding_mode"=>$request->forwarding_mode ?? NULL,
                "forwarded_to"=>$request->forwarded_to ?? NULL,
                "other_specify"=>$request->other_specify ?? NULL,
            );
            LasComplaint::query()->where('id',$request->complaint_id)->update($insert);
            $las_complaint=LasComplaint::query()->where('id',$request->complaint_id)->get();

            DB::commit();

            return resp('1', 'Successfully!', $las_complaint, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function complaintResponse(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'complaint_id' => 'required',
            ]);

            $insert=array(
                "reminder_date_1"=>date('Y-m-d',strtotime($request->reminder_date_1)) ?? NULL,
                "reminder_date_2"=>date('Y-m-d',strtotime($request->reminder_date_2)) ?? NULL,
                "reminder_date_3"=>date('Y-m-d',strtotime($request->reminder_date_3)) ?? NULL,
                "response_received_date"=>date('Y-m-d',strtotime($request->response_received_date)) ?? NULL,
                "res_forwarded_complainant_date"=>date('Y-m-d',strtotime($request->res_forwarded_complainant_date)) ?? NULL,
                "response_detail"=>$request->response_detail ?? NULL,
                "response_mode"=>$request->response_mode ?? NULL,
                "response_of_feedback_giver"=>$request->response_of_feedback_giver ?? NULL,
                "complaint_status"=>$request->complaint_status ?? NULL,
                "closing_date"=>date('Y-m-d',strtotime($request->closing_date)) ?? NULL,
                "remarks"=>$request->remarks ?? NULL,
                "no_days_feedback_closed"=>$request->no_days_feedback_closed ?? NULL,
            );
            LasComplaint::query()->where('id',$request->complaint_id)->update($insert);
            $las_complaint=LasComplaint::query()->where('id',$request->complaint_id)->get();

            DB::commit();

            return resp('1', 'Successfully!', $las_complaint, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

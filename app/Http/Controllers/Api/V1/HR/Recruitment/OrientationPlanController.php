<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\HR\Recruitment\ManageJob;
use App\Models\HR\Recruitment\OrientationPlan;
use App\Models\OrientationParticipant;
use App\Models\OrientationPlanActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class OrientationPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'orientation_plan_view',
            'manage_audit_recruitment',
        ]);

        $OrientationPlan = OrientationPlan::with(['orientationActivity.orientationParticipants.employeeDetail','created_by','updated_by'])->get();

        if($OrientationPlan ){
                foreach($OrientationPlan as $key => $plan){
                    $OrientationPlan[$key]['executedByDetail']=Employee::query()->whereIn('id',explode(',',$plan['executed_by']))->get();
                }
        }
        $data['OrientationPlan']=$OrientationPlan;
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('orientation_plan_create');

        $request->validate([
            'name' => 'required',
            'dated' => 'required',
            //'executed_by' => 'required|array|min:1',
            //'executed_by.*' => 'required',
        ]);
        /*$request->validate([
            'employee_id' => 'required',
            'executed_by' => 'required',
            'dated' => 'required',
            'time' => 'required',
            'name' => 'required',
            'venue' => 'required',
            'main_activity' => 'required',
            'sub_activity' => 'required',
        ]);*/
        try {
            DB::beginTransaction();
            //$this->input['executed_by']=implode(',',$this->input['executed_by']);
            $item = OrientationPlan::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(OrientationPlan $orientationPlan): JsonResponse
    {
        $this->authorizeAny([
            'orientation_plan_view',
            'manage_audit_recruitment',
        ]);

        $orientationPlan = $orientationPlan->load(['orientationActivity'=>['ExecutedBy','orientationParticipants.employeeDetail'],'created_by','updated_by']);
        if($orientationPlan && $orientationPlan->executed_by != ""){
            $orientationPlan->executedByDetail=Employee::query()->whereIn('id',explode(',',$orientationPlan->executed_by))->get();
        }
        return resp('1', 'Successful!', $orientationPlan, Response::HTTP_OK);
    }

    public function getEmployeeOrientationplan($empId): JsonResponse
    {
        $this->authorizeAny([
            'manage_employee_portal',
            'orientation_plan_view',
        ]);

        $data['items'] = OrientationPlan::query()->with(['EmployeeId','created_by','updated_by'])->where('created_by', $empId)->get();
        $data['orientation_plan_activity'] =OrientationPlan::query()
            ->with(['orientationActivity.orientationParticipants.employeeDetail','orientationActivity.ExecutedBy', 'created_by', 'updated_by'])
            ->whereHas('orientationActivity.orientationParticipants', function ($query) use ($empId) {
                $query->where('orientation_participants_id', $empId); // Assuming 'employee_id' is the column in OrientationPlanParticipants table
            })
            ->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrientationPlan $orientationPlan)
    {
        $this->authorize('orientation_plan_update');

        $request->validate([
            'name' => 'required',
            'dated' => 'required',
            //'executed_by' => 'required|array|min:1',
            //'executed_by.*' => 'required',
        ]);
        /*$request->validate([
            'employee_id' => 'required',
            'time' => 'required',
            'venue' => 'required',
            'main_activity' => 'required',
            'sub_activity' => 'required',
        ]);*/
        try {
            DB::beginTransaction();
            //$this->input['executed_by']=implode(',',$this->input['executed_by']);
            $item = $orientationPlan->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrientationPlan $orientationPlan): JsonResponse
    {
        $this->authorize('orientation_plan_delete');

        $item = $orientationPlan->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function addOrientationActivity(Request $request,OrientationPlan $item)
    {
        $this->authorize('orientation_plan_create');

        $request->validate([
            'venue' => 'required',
            'main_activity' => 'required',
            'sub_activity' => 'required',
            // 'time' => 'required|date_format:H:i',
            'employee_id' => 'required|array|min:1',
            'employee_id.*' => 'required',
            'activity_date' => 'required',
            //'end_time' => 'required',
            'executed_by' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $this->input['orientation_plan_id']=$item->id;
            $OrientationPlanActivity = OrientationPlanActivity::query()->create($this->input);
            if($OrientationPlanActivity){
                foreach ($request->employee_id as $employees){
                    $participants=array(
                        'orientation_plan_activity_id'=>$OrientationPlanActivity->id,
                        'orientation_participants_id'=>$employees,
                    );
                    OrientationParticipant::query()->create($participants);
                }
            }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function updateOrientationActivity(Request $request,OrientationPlan $item)
    {
        $this->authorize('orientation_plan_update');

        $request->validate([
            'venue' => 'required',
            'main_activity' => 'required',
            'sub_activity' => 'required',
            'activity_id' => 'required',
            // 'time' => 'required|date_format:H:i',
            'employee_id' => 'required|array|min:1',
            'employee_id.*' => 'required',
            'activity_date' => 'required',
            //'end_time' => 'required',
            'executed_by' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $this->input['orientation_plan_id']=$item->id;
            $employee_ids=$request->employee_id;
            $activity_id=$request->activity_id;
            unset( $this->input['employee_id']);
            unset( $this->input['activity_id']);

            $OrientationPlanActivity = OrientationPlanActivity::query()->where('id',$activity_id)->update($this->input);

            if($OrientationPlanActivity){
                OrientationParticipant::query()->where('orientation_plan_activity_id',$activity_id)->delete();
                foreach ($employee_ids as $employees){
                    $participants=array(
                        'orientation_plan_activity_id'=>$activity_id,
                        'orientation_participants_id'=>$employees,
                    );
                    OrientationParticipant::query()->create($participants);
                }
            }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function deleteOrientationActivity(OrientationPlanActivity $item)
    {
        $this->authorize('orientation_plan_delete');

        OrientationParticipant::query()->where('orientation_plan_activity_id',$item->id)->delete();
        $item->delete();
        return resp('1', 'Record deleted Successfully!', $item, Response::HTTP_CREATED);
    }
    public function deleteOrientationParticipant(OrientationParticipant $item)
    {
        $this->authorize('orientation_plan_delete');

        $item->delete();
        return resp('1', 'Record deleted Successfully!', $item, Response::HTTP_CREATED);
    }
}

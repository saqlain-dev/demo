<?php

namespace App\Http\Controllers\Api\V1\Progress;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Finance\Budget\ProjectBudgetDetail;
use App\Models\Progress\ProgressWorkplanGoals;
use App\Models\Progress\ProgressWorkplanOutcome;
use App\Models\Progress\ProgressWorkplanOutput;
use App\Models\Progress\WorkplanActivities;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkplanActivitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = WorkplanActivities::all();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'type_id' => 'required',
            'activity_cat' => 'required',
            'name' => 'required'
        ]);
        if ($this->input['type'] == 3){
            $item = ProgressWorkplanOutput::query()->findOrFail($this->input['type_id']);
            if ($item){
                    $activity = new Activity();
                    $activity->activity_cat = $this->input['activity_cat'];
                    $activity->name = $this->input['name'];
                    $activity->activityable()->associate($item);
                    $activity->save();
            }
        }

        //$item = Activity::query()->create($this->input);

        return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkplanActivities $workplanActivities): JsonResponse
    {
        return resp('1', 'Successful!', $workplanActivities, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {


        $activitity=Activity::query()->findOrFail($id);
        $request->validate([
            'name' => 'required'
        ]);
        $activitity->name=$request->name;
        if($request->activity_cat){
            $activitity->activity_cat=$request->activity_cat;
        }
        $activitity->save();
        return resp('1', 'Record Updated Successfully!', $activitity, Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {

        $workplanActivities = Activity::query()->findOrFail($id);
        $budget_details = ProjectBudgetDetail::where('activity_id',$id)->first();
        if ($budget_details) {
            return resp('0', 'Cannot delete: Activity is used in budget.', null, Response::HTTP_FORBIDDEN);
        }
        $item = $workplanActivities->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function getActivitiesBytypeId($TypeId)
    {
        $activities = WorkplanActivities::query()->where('activity_type', $TypeId)->get();
        return resp('1', 'Successful!', $activities, Response::HTTP_OK);
    }
}

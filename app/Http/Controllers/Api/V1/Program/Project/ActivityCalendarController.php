<?php

namespace App\Http\Controllers\Api\V1\Program\Project;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Program\Project\ActivityCalendar;
use App\Models\Program\Project\MnE\ProjectMnePlan;
use App\Models\Program\Project\ProjectProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ActivityCalendarController extends Controller
{
    public function index($project)
    {
        $this->authorizeAny([
            'field_calendar_view',
            'manage_audit_program_mne',
            'manage_audit_program_reports',
        ]);

        $data = ProjectProfile::query()->with('activityCalendars')->findOrFail($project);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ProjectProfile $project)
    {
        $this->authorize('field_calendar_create');

        $request->validate([
            'activity_id' => 'required|max:255',
            'audience' => 'required|max:255',
            'facilitator' => 'required|max:255',
            'venue' => 'required|max:255',
            'focal_person_id' => 'required',
            'district_id' => 'required',
            //'pickup_details' => 'required',
            'vehicle_required' => 'required',
            'time' => 'required',
            'date' => 'required',
            //'drop_off_details' => 'required',
            //'other_details' => 'required',
        ]);
        $item = $project->activityCalendars()->create($request->all());
        return resp('1', 'Successful!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show($item)
    {
        $this->authorize('field_calendar_view');

        $item = ActivityCalendar::query()->findOrFail($item);
        return resp('1', 'Successful!', $item, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $item)
    {
        $this->authorize('field_calendar_update');

        $item = ActivityCalendar::query()->findOrFail($item);

        $request->validate([
            'project_id' => 'required',
            'activity_id' => 'required|max:255',
            'audience' => 'required|max:255',
            'facilitator' => 'required|max:255',
            'venue' => 'required|max:255',
            'date' => 'required',
            'time' => 'required',
            'focal_person_id' => 'required',
            'district_id' => 'required',
            //'pickup_details' => 'required',
            'vehicle_required' => 'required',
            //'drop_off_details' => 'required',
            //'other_details' => 'required',
        ]);

        $item->update($request->all());
        return resp('1', 'Successful!', $item, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ActivityCalendar $activityCalendar)
    {
        $this->authorize('field_calendar_delete');

        $activityCalendar->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }

    public function getDropdowns($project_id)
    {
        $this->authorize('field_calendar_view');

        $data['activities'] = ProjectProfileController::getActivities($project_id);
        $data['users'] = User::all();
        $data['districts'] = District::all();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);

    }

    public function getProjectActivityCalender(string $project_id)
    {
        $this->authorize('field_calendar_view');

        $item = ProjectProfile::query()->with('activityCalendars')->findOrFail($project_id);
        return resp('1', 'Successful!', $item, Response::HTTP_OK);

    }

}

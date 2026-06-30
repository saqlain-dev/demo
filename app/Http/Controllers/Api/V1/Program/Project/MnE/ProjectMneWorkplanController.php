<?php

namespace App\Http\Controllers\Api\V1\Program\Project\MnE;

use App\Http\Controllers\Api\V1\Program\Project\ProjectProfileController;
use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Employee;
use App\Models\Program\Project\MnE\ProjectMnePlan;
use App\Models\Program\Project\MnE\ProjectMneWorkplan;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Questionnaire\Questionnaire;
use App\Models\Questionnaire\QuestionnaireForm;
use App\Models\Type;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProjectMneWorkplanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($project)
    {
        $this->authorizeAny([
            'internal_calendar_view',
            'manage_audit_program_mne',
            'manage_audit_program_reports',
        ]);

        $data = ProjectProfile::query()->with(['mneWorkplans' => ['focalPerson','responsiblePerson','created_by', 'updated_by', 'district', 'activity','mneKpi']])->findOrFail($project);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ProjectProfile $project)
    {
        $this->authorize('internal_calendar_create');

        //$project = ProjectProfile::query()->findOrFail($project);
        $request->validate([
            'project_id' => ['required'],
            'year' => ['required'],
            'quarter' => ['required'],
            'week' => ['required'],
            'date' => ['required'],
            'district_id' => ['required'],
            'venue_of_activity' => ['required'],
            'project_focal_person' => ['required'],
            'mne_responsible_person' => ['required'],
            'mne_requirement' => ['required'],
        ]);

        $item = $project->mneWorkplans()->create($this->input);

        return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectMneWorkplan $mneWorkplan)
    {
        $this->authorize('internal_calendar_view');

        $data = $mneWorkplan->load(['created_by', 'updated_by', 'district', 'activity','mneKpi']);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectMneWorkplan $mneWorkplan)
    {
        $this->authorize('internal_calendar_update');

        $request->validate([
            'project_id' => ['required'],
            'year' => ['required'],
            'quarter' => ['required'],
            'week' => ['required'],
            'date' => ['required'],
            'district_id' => ['required'],
            'venue_of_activity' => ['required'],
            'project_focal_person' => ['required'],
            'mne_responsible_person' => ['required'],
            'mne_requirement' => ['required'],
        ]);
        $item = $mneWorkplan->update($this->input);

        return resp('1', 'Record Updated Successfully!', $mneWorkplan->refresh(), Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectMneWorkplan $mneWorkplan)
    {
        $this->authorize('internal_calendar_delete');

        $item = $mneWorkplan->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function getDropdowns($project)
    {
        $data['activity_categories'] = Type::getTypeValues('activity-categories');
        $data['mne_kpis'] = Type::getTypeValues('mne-kpis');
        $data['users'] = User::all();

        $employeeTypes = ['Confirmed', 'Probationary', 'Trainee'];
        $data['employees'] = Employee::whereHas('employeeTyp', function ($query) use ($employeeTypes) {
            $query->whereIn('name', $employeeTypes);
        })->get();
        //$data['employees'] = Employee::query()->where()->get();
        $data['activities'] = ProjectProfileController::getActivities($project);
        $data['districts'] = District::all();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);

    }

    public function fillForm($item, QuestionnaireForm $form, Request $request)
    {
        $request->validate([
            'location' => ['required'],
            'answers' => ['required']

        ]);
        $item = ProjectMnePlan::query()->findOrFail($item);

        try {
            DB::beginTransaction();
            $formRecord = new Questionnaire();
            $formRecord->form_id = $form->id;
            $formRecord->location = $request->location;
            $formRecord->questionnaireable()->associate($item);
            $formRecord->save();

            $formRecord->answers()->createMany($request->answers);

            DB::commit();

            return resp(1, 'Successful!', [], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return resp(0, 'Failed to save form!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    public function getFormResponses($mnePlanId, $formId)
    {
        $item = ProjectMnePlan::query()
            ->with(['questionnaires' => function ($query) use ($formId) {
                $query->where('form_id', $formId);
                $query->with('answers.question');
            }])
            ->findOrFail($mnePlanId);
        $item->form = QuestionnaireForm::query()->findOrFail($formId);

        return resp('1', 'Successful!', $item, Response::HTTP_OK);
    }

    public function approvedProgressWorkplanProjects()
    {
        $this->authorizeAny([
            'manage_audit_program_mne',
            'manage_audit_program_reports',
            'mne_calender_report',
            'mne_field_calender_report',
        ]);

        $data = ProjectProfile::query()->whereRelation('progressWorkplans','status','=',1)
            ->with('progressWorkplans')
            ->where('approval_status',\STATUS::APPROVED)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }


}

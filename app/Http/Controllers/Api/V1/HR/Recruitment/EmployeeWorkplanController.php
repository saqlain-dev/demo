<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\EmployeeWorkplanActivity;
use App\Models\EmployeeWorkplanParticipant;
use App\Models\HR\Recruitment\EmployeeWorkplan;
use App\Models\HR\Recruitment\OrientationPlan;
use App\Models\Program\Project\MnE\MneObservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmployeeWorkplanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'employee_workplan_view',
            'consultant_workplan_view',
            'manage_audit_recruitment',
            'manage_audit_consultant_management',
            'manage_employee_portal'
        ]);

        $data = EmployeeWorkplan::with(['employeeWorkplanActivity' => ['KpiId'], 'EmployeeId.employeeTyp', 'ProjectId', 'created_by', 'updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'employee_workplan_create',
            'consultant_workplan_create',
            'manage_employee_portal'
        ]);

        $request->validate([
            'week_no' => 'required',
            'date_form' => 'required',
            'date_to' => 'required',
            'workplan_title' => 'required',
            'employee_id' => 'required|integer|exists:employees,id',
            //            'appriasal_kpi_id' => 'required|integer|exists:appriasal_kpis,id',
//            'section_questions' => 'required|array',
//            'section_questions.*' => 'integer|exists:section_questions,id',
        ]);
        /*
        $request->validate([
            'employee_id' => 'required',
            'week_no' => 'required',
            'date_form' => 'required',
            'date_to' => 'required',
            'activity' => 'required',
            'sub_activity' => 'required',
            'description' => 'required',
            'area' => 'required',
            'task' => 'required',
        ]);*/
        $this->input['date_form'] = date('Y-m-d', strtotime($request->date_form));
        $this->input['date_to'] = date('Y-m-d', strtotime($request->date_to));
        try {
            DB::beginTransaction();

            //            $section_questions = $this->input['section_questions'];
//            unset($this->input['section_questions']);

            $item = EmployeeWorkplan::query()->create($this->input);
            //            $item->sectionQuestions()->sync($section_questions);

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
    public function show(EmployeeWorkplan $employeeWorkplan): JsonResponse
    {
        $this->authorizeAny([
            'employee_workplan_view',
            'consultant_workplan_view',
            'manage_audit_recruitment',
            'manage_audit_consultant_management',
            'manage_employee_portal'
        ]);

        $employeeWorkplan = $employeeWorkplan->load(['employeeWorkplanActivity' => ['KpiId.domain', 'sectionQuestions'], 'EmployeeId', 'ProjectId', 'created_by', 'updated_by', 'sectionQuestions', 'domain']);
        return resp('1', 'Successful!', $employeeWorkplan, Response::HTTP_OK);
    }
    public function getEmployeeWorkplan($empId): JsonResponse
    {
        $this->authorizeAny([
            'employee_workplan_view',
            'consultant_workplan_view',
            'manage_employee_portal',
        ]);

        $data['items'] = EmployeeWorkplan::query()
            ->with([
                'employeeWorkplanActivity' => ['KpiId'],
                'created_by',
                'updated_by',
                'sectionQuestions',
                'EmployeeId',
            ])
            ->whereIn('employee_id', function ($query) use ($empId) {
                $query->select('id')
                    ->from('employees')
                    ->where('id', $empId)
                    ->orWhere('report_to_id', $empId);
            })
            ->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeWorkplan $employeeWorkplan)
    {
        $this->authorizeAny([
            'employee_workplan_update',
            'consultant_workplan_update',
            'manage_employee_portal'
        ]);

        $request->validate([
            'week_no' => 'required',
            'date_form' => 'required',
            'date_to' => 'required',
            'workplan_title' => 'required',
            'employee_id' => 'required|integer|exists:employees,id',
            //            'appriasal_kpi_id' => 'required|integer|exists:appriasal_kpis,id',
//            'section_questions' => 'required|array',
//            'section_questions.*' => 'integer|exists:section_questions,id',
        ]);
        try {
            DB::beginTransaction();

            //            $section_questions = $this->input['section_questions'];
//            unset($this->input['section_questions']);

            $item = $employeeWorkplan->update($this->input);
            //            $employeeWorkplan->sectionQuestions()->sync($section_questions);

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
    public function destroy(EmployeeWorkplan $employeeWorkplan): JsonResponse
    {
        $this->authorizeAny([
            'employee_workplan_delete',
            'consultant_workplan_delete',

        ]);

        $item = $employeeWorkplan->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
    public function addWorkplanActivity(Request $request)
    {
        $this->authorizeAny([
            'employee_workplan_create',
            'consultant_workplan_create',
            'manage_employee_portal'
        ]);

        $request->validate([
            'description' => 'required',
            //'kpi_id' => 'required',
            'activity' => 'required',
            'deadline' => 'required',
            //'sub_activity' => 'required',
            //'area' => 'required',
            //'task' => 'required',
            'employee_workplan_id' => 'required|integer|exists:employee_workplans,id',
            //'section_questions' => 'required|array',
            //'section_questions.*' => 'integer|exists:section_questions,id',
        ]);
        if ($request->hasFile('mov_attachment')) {
            $responce = $this->saveImages($request, 'mov_attachment');
            if ($responce) {
                $this->input['mov_attachment'] = $responce;
            }
        }
        try {
            DB::beginTransaction();
            $employeeWorkplan = EmployeeWorkplan::query()->findOrFail($request->employee_workplan_id);
            $section_questions = $this->input['section_questions'];
            unset($this->input['section_questions']);

            $item = EmployeeWorkplanActivity::query()->create($this->input);

            // Prepare the sync data with the additional employee_workplan_activity_id
            $syncData = [];
            foreach ($section_questions as $section_question_id) {
                $syncData[$section_question_id] = ['employee_workplan_activity_id' => $item->id];
            }
            $employeeWorkplan->sectionQuestions()->attach($syncData);

            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveImages($request, $folder)
    {

        $file = $request->file('mov_attachment');
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
        return $path . '/' . $file_name;
    }

    public function updateWorkplanActivity(Request $request, EmployeeWorkplanActivity $item)
    {
        $this->authorizeAny([
            'employee_workplan_update',
            'consultant_workplan_update',
            'manage_employee_portal'
        ]);

        $request->validate([
            'description' => 'required',
            //'kpi_id' => 'required',
            'activity' => 'required',
            'sub_activity' => 'required',
            'deadline' => 'required',
            //'area' => 'required',
            //'task' => 'required',
            'employee_workplan_id' => 'required|integer|exists:employee_workplans,id',
            //'section_questions' => 'required|array',
            //'section_questions.*' => 'integer|exists:section_questions,id',
        ]);

        if ($request->hasFile('mov_attachment')) {
            $responce = $this->saveImages($request, 'mov_attachment');
            if ($responce) {
                $this->input['mov_attachment'] = $responce;
            }
        }

        try {
            DB::beginTransaction();

            $employeeWorkplan = EmployeeWorkplan::query()->findOrFail($request->employee_workplan_id);
            $section_questions = $this->input['section_questions'];
            unset($this->input['section_questions']);

            $item->update($this->input);

            // Detach all records with the specific employee_workplan_activity_id
            $employeeWorkplan->sectionQuestions()
                ->wherePivot('employee_workplan_activity_id', $item->id)
                ->detach();

            // Prepare the sync data with the additional employee_workplan_activity_id
            $syncData = [];
            foreach ($section_questions as $section_question_id) {
                $syncData[$section_question_id] = ['employee_workplan_activity_id' => $item->id];
            }
            $employeeWorkplan->sectionQuestions()->attach($syncData);

            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }


    public function deleteWorkPlanActivity(EmployeeWorkplanActivity $item)
    {
        $this->authorizeAny([
            'employee_workplan_delete',
            'consultant_workplan_delete',
        ]);
        EmployeeWorkplanParticipant::query()->where('employee_workplan_activity_id', $item->id)->delete();
        $item->delete();
        return resp('1', 'Record deleted Successfully!', $item, Response::HTTP_CREATED);
    }
    public function deleteWorkplanParticipant(EmployeeWorkplanParticipant $item)
    {
        $this->authorizeAny([
            'employee_workplan_delete',
            'consultant_workplan_delete',
        ]);

        $item->delete();
        return resp('1', 'Record deleted Successfully!', $item, Response::HTTP_CREATED);
    }
}

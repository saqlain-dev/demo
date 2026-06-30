<?php

namespace App\Http\Controllers\Api\V1\HR\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\HR\Appraisal\AppriasalKpi;
use App\Models\HR\Appraisal\DepartmentalObjective;
use App\Models\HR\Appraisal\KeyResponsibility;
use App\Models\HR\Appraisal\PerformanceFactor;
use App\Models\HR\Appraisal\PerformancePlanning;
use App\Models\HR\Appraisal\SectionQuestion;
use App\Models\HR\Insurance\EmployeeRelative;
use App\Models\HR\Recruitment\EmployeeWorkplan;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PerformancePlanningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'appraisal_view',
            'manage_audit_employee_kpis_appraisal',
        ]);

        $data = PerformancePlanning::query()->with(['employee' => ['designation', 'department','shift'], 'supervisorImmediate', 'supervisorExtended', 'developmentGoals', 'keyResponsibilities', 'performanceFactors' => ['questionSection.Kpis.DesignationId']])->get();

        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'appraisal_create',
        ]);

        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'employee_workplan_id' => 'required|integer|exists:employee_workplans,id',
            'supervisor_immediate' => 'required|integer|exists:employees,id',
            'supervisor_extended' => 'required|integer|exists:employees,id',
            'period_from' => 'required|date|date_format:Y-m-d',
            'period_to' => 'required|date|date_format:Y-m-d|after_or_equal:period_from',
        ]);
        try {
            DB::beginTransaction();

            $parent = PerformancePlanning::query()->create($request->all());

            $employeeWorkplan = EmployeeWorkplan::query()->findOrFail($parent->employee_workplan_id);
            $questions = $employeeWorkplan->sectionQuestions;

            // Create performance factors
            $performanceFactors = $questions->map(function ($question) use ($parent) {
                return [
                    'performance_planning_id' => $parent->id,
                    'question_id' => $question->id,
                    'section_id' => $question->type_value_id,
                ];
            });
            PerformanceFactor::insert($performanceFactors->toArray());

            // Create key responsibilities
            $keyResponsibilities = $questions->map(function ($question) use ($parent) {
                return [
                    'performance_planning_id' => $parent->id,
                    'key_responsibility' => $question->question,
                    'question_id' => $question->id,
                    'section_id' => $question->type_value_id,
                ];
            });
            KeyResponsibility::insert($keyResponsibilities->toArray());

            DB::commit();
            return resp(1, 'Successful!', $parent, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PerformancePlanning $PerformancePlanning)
    {
        $this->authorizeAny([
            'appraisal_view',
            'manage_audit_employee_kpis_appraisal',
        ]);

        $item = $PerformancePlanning->load(['employee' => ['designation', 'department', 'shift'], 'supervisorImmediate', 'supervisorExtended', 'keyResponsibilities' => ['questionSection.Kpis.DesignationId'], 'developmentGoals', 'performanceFactors' => ['questionSection.Kpis.DesignationId']]);
        $groupedPerformanceFactors = $item->performanceFactors->groupBy(function ($factor) {
            return $factor->questionSection?->Kpis?->kpis;
        });
        $groupedKeyResponsibilities = $item->keyResponsibilities->groupBy(function ($factor) {
            return $factor->questionSection?->Kpis?->kpis;
        });

        // Calculate kr_rating
        $keyResponsibilities = $item->keyResponsibilities;
        $totalKrRating = $keyResponsibilities->sum('supervisor_rating');
        $krCount = $keyResponsibilities->count();
        $item->kr_rating = $krCount > 0 ? $totalKrRating / $krCount : 0;


        $data['item'] = $item;
        $data['groupedPerformanceFactors'] = $groupedPerformanceFactors;
        $data['groupedKeyResponsibilities'] = $groupedKeyResponsibilities;
        $data['approval_request'] = getNextApproval(32, auth()->user()->designation_id, $PerformancePlanning->id);
        $data['approval_request_status'] = checkApprovalRequestStatus(32, $PerformancePlanning->id);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PerformancePlanning $PerformancePlanning)
    {
        $this->authorizeAny([
            'appraisal_update',
        ]);

        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'supervisor_immediate' => 'required|integer|exists:employees,id',
            'supervisor_extended' => 'required|integer|exists:employees,id',
            'period_from' => 'required|date|date_format:Y-m-d',
            'period_to' => 'required|date|date_format:Y-m-d|after_or_equal:period_from',
        ]);
        try {
            DB::beginTransaction();

            $parent = $PerformancePlanning->update($request->all());

            DB::commit();
            return resp(1, 'Successful!', $PerformancePlanning, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PerformancePlanning $PerformancePlanning)
    {
        $this->authorizeAny([
            'appraisal_delete',
        ]);

        $PerformancePlanning->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    public function getDropdowns()
    {
        $data['employees'] = Employee::all();
        $data['designations'] = Designation::all();
        $data['departmental_objectives'] = DepartmentalObjective::query()->with(['department'])->get();
        $data['departments'] = Type::getTypeValues('department-names');
        //$question_sections = Type::getTypeValues('question-sections');
        $question_sections = AppriasalKpi::all();
        $question_sections->each(function ($record) {
            $record->questions = SectionQuestion::query()->where('type_value_id', $record->id)->get();
        });
        $data['question_sections'] = $question_sections;
        $data['kpis'] = AppriasalKpi::query()->with(['domain','DesignationId','KpiIndicators', 'kpiIndicatorsMapping' => ['designations','indicator']])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function sendPerformaceForApproval(PerformancePlanning $item)
    {
        $approval_process = ApprovalProcess::query()->where('approval_process_id', 32)->get();
        $checkProcess = ApprovalProcessList::query()->where('approval_process_id', 32)->where('approval_request_status', 1)->where('request_module_id', $item->id)->count();
        if ($approval_process->count() > 0 && $checkProcess == 0) {

            foreach ($approval_process as $approval) {
                $insert = array(
                    'approval_process_id' => $approval['approval_process_id'],
                    'designation_id' => $approval['designation_id'],
                    'process_order' => $approval['process_order'],
                    'request_module_id' => $item->id,
                );
                $Approval = ApprovalProcessList::query()->create($insert);

            }
            $update = array('approval_status' => 2);
            PerformancePlanning::query()->where('id', $item->id)->update($update);
            return resp(1, 'Performance send for Approval.', $Approval, Response::HTTP_OK);
        } else {


            if ($checkProcess == 0) {
                return resp(0, 'Approval process not available', [], Response::HTTP_OK);
            } else {
                return resp(0, 'Performance approval already sent.', [], Response::HTTP_OK);
            }
        }
    }

    public function addEmployeeKpis(Request $request)
    {
        $request->validate([
            'performance_planing_id' => 'required|integer',
            'questionsIds' => 'required|array',
        ]);


        $questions = SectionQuestion::query()->whereIn('id', $request->questionsIds)->get();
        $parent_id = $request->performance_planing_id;
        PerformanceFactor::query()->where('performance_planning_id', $parent_id)->delete();

        $performanceFactors = $questions->map(function ($question) use ($parent_id) {
            return [
                'performance_planning_id' => $parent_id,
                'question_id' => $question->id,
                'section_id' => $question->type_value_id,
            ];
        });
        PerformanceFactor::insert($performanceFactors->toArray());
        $data['list'] = PerformanceFactor::query()->where('performance_planning_id', $parent_id)->get();

        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}

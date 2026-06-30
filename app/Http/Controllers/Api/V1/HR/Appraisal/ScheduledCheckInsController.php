<?php

namespace App\Http\Controllers\Api\V1\HR\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\HR\Appraisal\PerformanceFactor;
use App\Models\HR\Appraisal\ScheduledCheckIn;
use App\Models\HR\Recruitment\EmployeeWorkplan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ScheduledCheckInsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['item'] = ScheduledCheckIn::query()->with('performanceCheckIn', 'employeeWorkplan')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'performance_check_in_id' => 'required|integer|exists:performance_check_ins,id',
            'employee_workplan_id' => 'required|integer|exists:employee_workplans,id',
            'check_in_date' => 'required|date|date_format:Y-m-d',
            'check_in_title' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();

            $parent = ScheduledCheckIn::query()->create($request->all());

            $employeeWorkplan = EmployeeWorkplan::query()->findOrFail($parent->employee_workplan_id);
            $questions = $employeeWorkplan->sectionQuestions;

            // Create performance factors
            $performanceFactors = $questions->map(function ($question) use ($parent) {
                return [
                    'scheduled_check_in_id' => $parent->id,
                    'question_id' => $question->id,
                    'section_id' => $question->type_value_id,
                ];
            });
            PerformanceFactor::insert($performanceFactors->toArray());

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
    public function show(ScheduledCheckIn $scheduledCheckIn)
    {
        $data['item'] = $scheduledCheckIn->load('performanceCheckIn', 'employeeWorkplan', 'performanceFactors.questionSection', 'performanceFactors.appriasalKpi');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ScheduledCheckIn $scheduledCheckIns)
    {
        $request->validate([
            'performance_check_in_id' => 'required|integer|exists:performance_check_ins,id',
            'employee_workplan_id' => 'required|integer|exists:employee_workplans,id',
            'check_in_date' => 'required|date|date_format:Y-m-d',
            'check_in_title' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $scheduledCheckIns->update($request->all());

            DB::commit();
            return resp(1, 'Successful!', $scheduledCheckIns, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ScheduledCheckIn $scheduledCheckIn)
    {
        $scheduledCheckIn->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }
}

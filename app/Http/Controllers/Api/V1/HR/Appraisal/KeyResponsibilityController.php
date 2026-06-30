<?php

namespace App\Http\Controllers\Api\V1\HR\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\HR\Appraisal\KeyResponsibility;
use App\Models\HR\Appraisal\PerformanceFactor;
use App\Models\HR\Appraisal\PerformancePlanning;
use App\Models\HR\Insurance\EmployeeRelative;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class KeyResponsibilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = KeyResponsibility::query()->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            '*.performance_planning_id' => 'required|integer|exists:performance_plannings,id',
            '*.question_id' => 'required|integer|exists:section_questions,id',
            '*.section_id' => 'required|integer',
            '*.priority' => 'required',
            '*.awarded_marks' => 'required',
            '*.supervisor_rating' => 'required',
            '*.remarks' => 'required',
            '*.obtained_percentage' => 'required',
            '*.obtained_rating' => 'required',
        ]);
        try {
            DB::beginTransaction();

            $records = $request->all();
            $performancePlanningId = $records[0]['performance_planning_id'];
            KeyResponsibility::query()->where('performance_planning_id', $performancePlanningId)?->delete();

            foreach ($records as $reviewData) {
                KeyResponsibility::create([
                    'performance_planning_id' => $reviewData['performance_planning_id'],
                    'section_id' => $reviewData['section_id'],
                    'question_id' => $reviewData['question_id'],
                    'priority' => $reviewData['priority'],
                    'awarded_marks' => $reviewData['awarded_marks'],
                    'supervisor_rating' => $reviewData['supervisor_rating'],
                    'remarks' => $reviewData['remarks'],
                    'obtained_percentage' => $reviewData['obtained_percentage'],
                    'obtained_rating' => $reviewData['obtained_rating'],
                ]);
            }

            $this->calculateAggregates($performancePlanningId);

            DB::commit();
            return resp(1, 'Successful!', [], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(KeyResponsibility $keyResponsibility)
    {
        $data['item'] = $keyResponsibility;
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KeyResponsibility $keyResponsibility)
    {
        $request->validate([
            //'performance_planning_id' => 'required|integer|exists:performance_plannings,id',
            //'date_modified' => 'required|date|date_format:Y-m-d',
            //'key_responsibility' => 'required',
            'priority' => 'required',
            'awarded_marks' => 'required',
            'supervisor_rating' => 'required',
            'remarks' => 'required',
            'obtained_percentage' => 'required',
            'obtained_rating' => 'required',
        ]);
        try {
            DB::beginTransaction();

            $parent = $keyResponsibility->update($request->all());
            $this->calculateAggregates($keyResponsibility->performance_planning_id);

            DB::commit();
            return resp(1, 'Successful!', $keyResponsibility, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KeyResponsibility $keyResponsibility)
    {
        $keyResponsibility->delete();
        $this->calculateAggregates($keyResponsibility->performance_planning_id);
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    private function calculateAggregates($performance_planning_id)
    {
        $totalMarks = KeyResponsibility::query()->where('performance_planning_id', $performance_planning_id)->sum('total_marks');
        $obtainedMarks = KeyResponsibility::query()->where('performance_planning_id', $performance_planning_id)->sum('awarded_marks');
        // Initialize percentageObtained to zero or handle it differently if needed
        $percentageObtained = 0;

        if ($totalMarks > 0) {
            $percentageObtained = ($obtainedMarks / $totalMarks) * 100;
        }

        $parent = PerformancePlanning::query()->findOrFail($performance_planning_id);
        $parent->update([
            'total_marks' => $totalMarks,
            'obtained_marks' => $obtainedMarks,
            'percentage_obtained' => $percentageObtained,
        ]);
    }

}

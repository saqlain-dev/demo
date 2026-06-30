<?php

namespace App\Http\Controllers\Api\V1\HR\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\HR\Appraisal\PerformanceFactor;
use App\Models\HR\Appraisal\PerformancePlanning;
use App\Models\HR\Appraisal\ScheduledCheckIn;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PerformanceFactorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = PerformanceFactor::query()->with('performanceFactorValue','performancePlanning')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /*$request->validate([
            '*.performance_planning_id' => 'nullable|integer|exists:performance_plannings,id',
            '*.scheduled_check_in_id' => 'nullable|integer|exists:scheduled_check_ins,id',
            '*.annual_check_in_id' => 'nullable|integer',
            '*.question_id' => 'required|integer|exists:section_questions,id',
            '*.section_id' => 'required|integer',
            '*.awarded_points' => 'required|numeric|min:1|max:10',
            '*.comments' => 'nullable|string|max:255',
        ]);*/
        try {
            DB::beginTransaction();

            //$item = PerformanceFactor::query()->create($request->all());
            $records = $request->all();
            $records = $records['data'];
            info("Performance factor payload", $records);
            $questionSectionId = $records[0]['section_id'];

            $performancePlanningId = $records[0]['performance_planning_id'] ?? null;
            if ($performancePlanningId){
                PerformanceFactor::query()->where('performance_planning_id', $performancePlanningId)->delete();
                foreach ($records as $key => $reviewData) {
                    if($request->hasFile('mov_attachment')) {
                        $responce = $this->saveAttachment($request, 'performance_planning_mov_attachment',$key);
                        if ($responce) {
                            $reviewData['mov_attachment'] = $responce;
                        }
                    }

                    PerformanceFactor::create([
                        'performance_planning_id' => $reviewData['performance_planning_id'],
                        'section_id' => $reviewData['section_id'],
                        'question_id' => $reviewData['question_id'],
                        'awarded_points' => $reviewData['awarded_points'],
                        'comments' => $reviewData['comments'],
                        'mov_attachment' => $reviewData['mov_attachment'] ?? null,
                    ]);
                }

                $this->calculateKpiAggregates($performancePlanningId);
            }

            $scheduled_check_in_id = $records[0]['scheduled_check_in_id'] ?? null;
            if ($scheduled_check_in_id){
                PerformanceFactor::query()->where('scheduled_check_in_id', $scheduled_check_in_id)->delete();

                foreach ($records as $key => $reviewData) {

                    if($request->hasFile('mov_attachment')) {

                        $responce = $this->saveAttachment($request, 'scheduled_checkin_mov_attachment',$key);

                        if ($responce) {
                            $reviewData['mov_attachment'] = $responce;
                        }
                    }

                    PerformanceFactor::create([
                        'scheduled_check_in_id' => $reviewData['scheduled_check_in_id'],
                        'section_id' => $reviewData['section_id'],
                        'question_id' => $reviewData['question_id'],
                        'awarded_points' => $reviewData['awarded_points'],
                        'comments' => $reviewData['comments'],
                        'mov_attachment' => $reviewData['mov_attachment'] ?? null,
                    ]);
                }

                $this->calculateScheduledKpiAggregates($scheduled_check_in_id);
            }

            $annual_check_in_id = $records[0]['annual_check_in_id'] ?? null;
            if ($annual_check_in_id){
                PerformanceFactor::query()->where('annual_check_in_id', $annual_check_in_id)->delete();

                foreach ($records as $reviewData) {
                    PerformanceFactor::create([
                        'annual_check_in_id' => $reviewData['annual_check_in_id'],
                        'section_id' => $reviewData['section_id'],
                        'question_id' => $reviewData['question_id'],
                        'awarded_points' => $reviewData['awarded_points'],
                        'comments' => $reviewData['comments'],
                    ]);
                }

                $this->calculateAnnualKpiAggregates($annual_check_in_id);
            }

            DB::commit();
            return resp(1, 'Successful!', [], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PerformanceFactor $performanceFactor)
    {
        $data['item'] = $performanceFactor->load('performanceFactorValue','performancePlanning');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PerformanceFactor $performanceFactor)
    {
        $request->validate([
            'performance_planning_id' => 'required|integer|exists:performance_plannings,id',
            'section_id' => 'required|integer',
            'question_id' => 'required|integer|exists:section_questions,id',
            'awarded_points' => 'required',
            'comments' => 'nullable',
        ]);
        try {
            DB::beginTransaction();

            $parent = $performanceFactor->update($request->all());
            $this->calculateKpiAggregates($request->performance_planning_id);

            DB::commit();
            return resp(1, 'Successful!', $performanceFactor, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PerformanceFactor $performanceFactor)
    {
        $performanceFactor->delete();
        $this->calculateKpiAggregates($performanceFactor->performance_planning_id);
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    private function calculateKpiAggregates($performance_planning_id)
    {
        $totalKpiMarks = PerformanceFactor::query()->where('performance_planning_id', $performance_planning_id)->sum('total_points');
        $obtainedKpiMarks = PerformanceFactor::query()->where('performance_planning_id', $performance_planning_id)->sum('awarded_points');

        $kpiPercentageObtained = 0;

        if ($totalKpiMarks > 0) {
            $kpiPercentageObtained = ($obtainedKpiMarks / $totalKpiMarks) * 100;
        }

        $parent = PerformancePlanning::query()->find($performance_planning_id);
        $parent?->update([
            'total_kpi_marks' => $totalKpiMarks,
            'obtained_kpi_marks' => $obtainedKpiMarks,
            'kpi_percentage_obtained' => $kpiPercentageObtained,
        ]);
    }

    private function calculateScheduledKpiAggregates($scheduled_check_in_id)
    {
        $totalKpiMarks = PerformanceFactor::query()->where('scheduled_check_in_id', $scheduled_check_in_id)->sum('total_points');
        $obtainedKpiMarks = PerformanceFactor::query()->where('scheduled_check_in_id', $scheduled_check_in_id)->sum('awarded_points');

        $kpiPercentageObtained = 0;

        if ($totalKpiMarks > 0) {
            $kpiPercentageObtained = ($obtainedKpiMarks / $totalKpiMarks) * 100;
        }

        $parent = ScheduledCheckIn::query()->find($scheduled_check_in_id);
        $parent?->update([
            'total_kpi_marks' => $totalKpiMarks,
            'obtained_kpi_marks' => $obtainedKpiMarks,
            'kpi_percentage_obtained' => $kpiPercentageObtained,
        ]);
    }

    private function calculateAnnualKpiAggregates($annual_check_in_id)
    {
        $totalKpiMarks = PerformanceFactor::query()->where('annual_check_in_id', $annual_check_in_id)->sum('total_points');
        $obtainedKpiMarks = PerformanceFactor::query()->where('annual_check_in_id', $annual_check_in_id)->sum('awarded_points');

        $kpiPercentageObtained = 0;

        if ($totalKpiMarks > 0) {
            $kpiPercentageObtained = ($obtainedKpiMarks / $totalKpiMarks) * 100;
        }

        /*$parent = PerformancePlanning::query()->find($annual_check_in_id);
        $parent?->update([
            'total_kpi_marks' => $totalKpiMarks,
            'obtained_kpi_marks' => $obtainedKpiMarks,
            'kpi_percentage_obtained' => $kpiPercentageObtained,
        ]);*/
    }

    /*public function saveAttachment($request,$folder,$key)
    {
        $file = $request->file('mov_attachment')[$key];
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
    }*/

    public function saveAttachment($request, $folder, $key)
    {
        // Retrieve the file at the specified index
        $attachments = $request->file('mov_attachment');

        if (is_array($attachments) && isset($attachments[$key])) {
            $file = $request->file('mov_attachment')[$key];
            // Validate the file
            if (is_array($attachments) && isset($attachments[$key]) && $file && $file->isValid()) {
                // Define the upload path
                $path = 'uploads/media/' . $folder;


                // Create directories if they don't exist
                if (!file_exists('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                if (!file_exists('uploads/media')) {
                    mkdir('uploads/media', 0777, true);
                }
                if (!file_exists('uploads/media/' . $folder)) {
                    mkdir('uploads/media/' . $folder, 0777, true);
                }

                // Generate a unique filename
                $filename = time() . '_' . $file->getClientOriginalName();
                $file_name = str_replace(' ', '_', $filename);

                // Move the file to the target directory
                $file->move($path, $file_name);

                // Return the file path
                return $path . '/' . $file_name;
            }

            // Handle invalid file case
            return null;
        }
    }

}

<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\HR\Recruitment\InterviewCommittee;
use App\Models\HR\Recruitment\ScheduleInterview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ScheduleInterviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = ScheduleInterview::with(['ApplyJobId','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'apply_job_id' => 'required',
            'interview_date' => 'required',
            'interview_time' => 'required',
            'interview_mode' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = ScheduleInterview::query()->create($this->input);
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
    public function show(ScheduleInterview $scheduleInterview): JsonResponse
    {
        $scheduleInterview = $scheduleInterview->load(['ApplyJobId','created_by','updated_by']);
        return resp('1', 'Successful!', $scheduleInterview, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ScheduleInterview $scheduleInterview): JsonResponse
    {
        $request->validate([
            'apply_job_id' => 'required',
            'interview_date' => 'required',
            'interview_time' => 'required',
            'interview_mode' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $scheduleInterview->update($this->input);
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
    public function destroy(ScheduleInterview $scheduleInterview): JsonResponse
    {
        $item = $scheduleInterview->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}

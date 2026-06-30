<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;

use App\Models\HR\Recruitment\InterviewResult;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InterviewResultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = InterviewResult::query()->with(['employee','manageJob','applyJob','createdBy'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'apply_job_id' => 'required|integer|exists:apply_jobs,id',
            'manage_job_id' => 'required|integer|exists:manage_jobs,id',
            'interview_id' => 'required',
            'recommendation' => 'boolean',
            'feedback' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $item = InterviewResult::query()->create($request->all());

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InterviewResult $interviewResult)
    {
        $interviewResult->load(['employee','manageJob','applyJob','createdBy']);
        return resp('1', 'Successful!', $interviewResult, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InterviewResult $interviewResult)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'apply_job_id' => 'required|integer|exists:apply_jobs,id',
            'manage_job_id' => 'required|integer|exists:manage_jobs,id',
            'interview_id' => 'required',
            'recommendation' => 'boolean',
            'feedback' => 'required',
        ]);
        try {
            DB::beginTransaction();

            $interviewResult->update($request->all());

            DB::commit();
            return resp(1, 'Successful!', $interviewResult->refresh(), Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InterviewResult $interviewResult)
    {
        $interviewResult->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\LogBook;
use App\Models\HR\Recruitment\InterviewCommittee;
use App\Models\HR\Recruitment\InterviewQuestionnaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InterviewQuestionnaireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = InterviewQuestionnaire::with(['ApplyJobId','EmployeeId','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'apply_job_id' => 'required',
            'employee_id' => 'required',
            'text' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = InterviewQuestionnaire::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InterviewQuestionnaire $interviewQuestionnaire): JsonResponse
    {
        $logBook = $interviewQuestionnaire->load(['ApplyJobId','EmployeeId','created_by','updated_by']);
        return resp('1', 'Successful!', $logBook, Response::HTTP_OK);
    }

    public function getFeedbackByInterviewId($interview_id)
    {
        $feedback = InterviewQuestionnaire::query()->with(['EmployeeId','ApplyJobId','InterviewId'])->where('interview_id',$interview_id)->get();
        return resp('1', 'Successful!', $feedback, Response::HTTP_OK);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InterviewQuestionnaire $interviewQuestionnaire)
    {
        $request->validate([
            'apply_job_id' => 'required',
            'employee_id' => 'required',
            'text' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $interviewQuestionnaire->update($this->input);
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
    public function destroy(InterviewQuestionnaire $interviewQuestionnaire): JsonResponse
    {
        $item = $interviewQuestionnaire->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\HR\Recruitment\InterviewQuestion;
use App\Models\HR\Recruitment\InterviewQuestionnaire;
use App\Models\HR\Recruitment\QuestionAnswer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class QuestionAnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['answers'] = QuestionAnswer::with(['ApplyJobId','EmployeeId','InterviewQuestionId','QuestionOptionId','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'apply_job_id' => 'required',
            'job_id' => 'required',
            'employee_id' => 'required',
            'interview_id' => 'required',
            'answers' => 'required|array',
            'answers.*.interview_question_id' => 'required',
            'answers.*.question_option_id' => 'required',
            //'answers.*.remarks' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $createdItems = [];
            $commonData = $request->only(['apply_job_id', 'employee_id','job_id']);
            foreach ($request->input('answers') as $answer) {
                $data = array_merge($commonData, $answer);
                $createdItems[] = QuestionAnswer::query()->create($data);
            }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $createdItems, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(QuestionAnswer $questionAnswer): JsonResponse
    {
        $questionAnswer = $questionAnswer->load(['ApplyJobId','EmployeeId','InterviewQuestionId','QuestionOptionId','created_by','updated_by']);
        return resp('1', 'Successful!', $questionAnswer, Response::HTTP_OK);
    }

    public function getAnswersByApplyJobId($applyJobId)
    {
        $data['answers'] = QuestionAnswer::query()->with(['ApplyJobId','EmployeeId','InterviewQuestionId','QuestionOptionId'])->where('apply_job_id',$applyJobId)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);

    }

    public function getAnswersByJobId($JobId)
    {
        $data['answers'] = QuestionAnswer::query()->with(['ApplyJobId','EmployeeId','InterviewQuestionId','QuestionOptionId'])->where('job_id',$JobId)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);

    }

    /**
     * Update the specified resource in storage.
     */
//    public function update(Request $request, QuestionAnswer $questionAnswer)
//    {
//        $request->validate([
//            'apply_job_id' => 'required',
//            'employee_id' => 'required',
//            'interview_question_id' => 'required',
//            'question_option_id' => 'required',
//            'remarks' => 'required',
//        ]);
//        try {
//            DB::beginTransaction();
//            $item = $questionAnswer->update($this->input);
//            DB::commit();
//            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
//        } catch (\Exception $e) {
//            DB::rollBack();
//            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
//        }
//    }

    public function update(Request $request)
    {
        $request->validate([
            'apply_job_id' => 'required',
            'job_id' => 'required',
            'employee_id' => 'required',
            'interview_id' => 'required',
            'answers' => 'required|array',
            'answers.*.id' => 'required', // Assuming each answer has an 'id' field for update
            'answers.*.interview_question_id' => 'required',
            'answers.*.question_option_id' => 'required',
            //'answers.*.remarks' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $commonData = $request->only(['apply_job_id', 'employee_id','job_id']);
            $updatedItems = [];

            foreach ($request->input('answers') as $answer) {
                $data = array_merge($commonData, $answer);
                $questionAnswer = QuestionAnswer::find($answer['id']);
                if ($questionAnswer) {
                    $questionAnswer->update($data);
                    $updatedItems[] = $questionAnswer;
                }
            }
            DB::commit();
            return resp('1', 'Records Updated Successfully!', $updatedItems, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update records!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QuestionAnswer $questionAnswer): JsonResponse
    {
        $item = $questionAnswer->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}

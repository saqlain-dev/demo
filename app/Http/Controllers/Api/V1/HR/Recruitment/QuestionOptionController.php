<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\HR\Recruitment\InterviewQuestion;
use App\Models\HR\Recruitment\QuestionOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class QuestionOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = QuestionOption::with(['created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'interview_question_id' => 'required',
            'option' => 'required',
            'option_score' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = QuestionOption::query()->create($this->input);
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
    public function show(QuestionOption $questionOption): JsonResponse
    {
        $logBook = $questionOption->load(['created_by','updated_by']);
        return resp('1', 'Successful!', $logBook, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QuestionOption $questionOption)
    {
        $request->validate([
            'interview_question_id' => 'required',
            'option' => 'required',
            'option_score' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $questionOption->update($this->input);
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
    public function destroy(QuestionOption $questionOption): JsonResponse
    {
        $item = $questionOption->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}

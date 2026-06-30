<?php

namespace App\Http\Controllers\Api\V1\Questionnaire;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire\Question;
use App\Models\Questionnaire\QuestionnaireForm;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(QuestionnaireForm $form)
    {
        $data = $form->questions;
        //dd($data);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request,QuestionnaireForm $form)
    {
        $request->validate([
            'form_id' => ['required'],
            'title' => ['required'],
            'question_no' => ['required'],
            'order' => ['required'],
            'question_type' => ['required'],
            //'collection' => ['required_if:question_type,1,2,3'],
        ]);
        $item = $form->questions()->create($this->input);
        $this->calculateTotals($request->form_id);

        return resp('1','Record Created Successfully!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        return resp('1','Successful!', $question, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Question $question)
    {
        $request->validate([
            'form_id' => ['required'],
            'title' => ['required','max:255'],
            'question_no' => ['required'],
            'order' => ['required'],
            'question_type' => ['required'],
            //'collection' => ['required_if:question_type,1,2,3'],
        ]);
        $question->update($this->input);
        $this->calculateTotals($request->form_id);

        return resp('1','Record Updated Successfully!', $question, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        $item = $question->delete();
        return resp('1','Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function calculateTotals($formId)
    {
        $form = QuestionnaireForm::query()->find($formId);

        if (!$form) {
            return;
        }

        $totalScores = Question::query()->where('form_id', $formId)->sum('score');
        $totalQuestions = Question::query()->where('form_id', $formId)->count();

        $form->update([
            'total_scores' => $totalScores,
            'total_questions' => $totalQuestions,
        ]);
    }


}

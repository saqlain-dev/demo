<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\OffboardingAnswer;
use App\Models\OffboardingQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OffBoardingQuestionController extends Controller
{
    //index
    public function index(Request $request) {
        $id = $request->query('type_value_id');
        $query = OffboardingQuestion::with('typeValue');
        if ($id) {
            $query->where('type_value_id', $id);
        }
        $data = $query->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function show($id) {
        $data = OffboardingQuestion::with('typeValue')->findOrFail($id);
        $data->load('type');
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'type_value_id' => 'required|exists:type_values,id',
            'questions' => 'required|array',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|in:text,boolean',
        ]);

        try {
            $inserted = [];
            foreach ($data['questions'] as $q) {
                $question = OffboardingQuestion::create([
                    'type_value_id' => $data['type_value_id'],
                    'question' => $q['question'],
                    'type' => $q['type'],
                ]);
                $question->load('typeValue');
                $inserted[] = $question;
            }

            return resp('1', 'Successful!', $inserted, Response::HTTP_OK);
        } catch (\Throwable $th) {
            return resp('0', 'Failed!', $th->getMessage());
        }
    }


    public function update(Request $request, $id) {
        $validated = $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:text,boolean',
        ]);

        try {
            $question = OffboardingQuestion::findOrFail($id);
            $question->update($validated);
            $question->load('typeValue');
            return resp('1', 'Successful!', $question, Response::HTTP_OK);
        } catch (\Throwable $th) {
            return resp('0', 'Failed!', $th->getMessage());
        }
    }

    public function destroy($id) {
        try {
            $question = OffboardingQuestion::findOrFail($id);
            $question->delete();
            return resp('1', 'Deleted Successfully!', null, Response::HTTP_OK);
        } catch (\Throwable $th) {
            return resp('0', 'Failed!', $th->getMessage());
        }
    }

    public function storeEmployeeAnswers(Request $request){
        $data = $request->validate([
            'employee_offboard_id' => 'required',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:offboarding_questions,id',
            'answers.*.answer' => 'required'
        ]);

        try {
            foreach ($data['answers'] as $a) {
                OffboardingAnswer::updateOrCreate(
                    [
                        'employee_offboard_id' => $data['employee_offboard_id'],
                        'offboarding_question_id' => $a['question_id'],
                    ],
                    [
                        'answer' => $a['answer'],
                    ]
                );
            }
            return resp('1', 'Answers submitted successfully.');
        } catch (\Throwable $th) {
            return resp('0', 'Failed to submit answers', $th->getMessage());
        }
    }

    public function responses($employee_offboard_id)
    {

        $questions = OffboardingQuestion::with(['answers' => function ($q) use ($employee_offboard_id) {
            $q->where('employee_offboard_id', $employee_offboard_id);
            $q->with(['createdBy', 'updatedBy']);
        }])->get();

        return resp('1', 'fetched successfully', $questions, Response::HTTP_OK);
    }

}

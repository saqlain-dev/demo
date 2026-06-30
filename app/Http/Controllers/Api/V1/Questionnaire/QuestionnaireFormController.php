<?php

namespace App\Http\Controllers\Api\V1\Questionnaire;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire\QuestionnaireAnswer;
use App\Models\Questionnaire\QuestionnaireForm;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class QuestionnaireFormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'configuration-hr',
            'form_view',
            'manage_audit_program_progress',
            'manage_audit_program_mne',
            'manage_m&e',
        ]);

        $data = QuestionnaireForm::all();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'configuration-hr',
            'form_create',
            'manage_m&e',
        ]);

        //return $request->all();
        $request->validate([
            'name' => 'required',
            'questions' => 'nullable|array',
            'form_category' => 'required',
            'description' => 'nullable',
            'instruction' => 'nullable',
        ]);

        try {
            DB::beginTransaction();

            $item = QuestionnaireForm::query()->create($request->only(['name','form_category','description','instruction']));
            if ($request->questions){
                $item->questions()->createMany($request->questions);
            }

            DB::commit();

            return resp(1, 'Successful!', $item->load('questions'), Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return resp(0, 'Failed to save form!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->authorizeAny([
            'configuration-hr',
            'form_view',
            'manage_audit_program_progress',
            'manage_audit_program_mne',
            'manage_m&e',
        ]);

        $questionnaireForm = QuestionnaireForm::query()->with('questions')->findOrFail($id);
        return resp('1','Successful!', $questionnaireForm, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QuestionnaireForm $questionnaireForm)
    {
        $this->authorizeAny([
            'configuration-hr',
            'form_update',
            'manage_m&e',
        ]);

        $request->validate([
            'name' => 'required',
            'form_category' => 'required',
        ]);
        $item = $questionnaireForm->update($this->input);

        return resp('1','Record Updated Successfully!', $questionnaireForm, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QuestionnaireForm $questionnaireForm)
    {
        $this->authorizeAny([
            'form_delete',
            'configuration-hr',
        ]);

        if ($questionnaireForm->questionnaires()->count() > 0){
            return resp('0','Tool is being used somewhere, cannot be deleted', [], Response::HTTP_OK);
        }

        $item = $questionnaireForm->delete();
        return resp('1','Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function getDropdowns()
    {
        $data['form_categories'] = Type::getTypeValues('form-categories');
        return resp('1','Record Deleted Successfully!', $data, Response::HTTP_OK);
    }

    public function submitTestAssessment(Request $request)
    {
        $request->validate([
            'answers' => 'required|array',
            'answers.*.answer_id' => 'required|integer|exists:questionnaire_answers,id',
            'answers.*.obtained_score' => 'required|numeric',
            'answers.*.obtained_percentage' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->answers as $answer){
                QuestionnaireAnswer::query()->find($answer['answer_id'])
                    ->update([
                        'obtained_score' => $answer['obtained_score'],
                        'obtained_percentage' => $answer['obtained_percentage'],
                    ]);
            }

            DB::commit();

            return resp(1, 'Successful!', [], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return resp(0, 'Failed to save test assessment!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

}

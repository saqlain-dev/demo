<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Admin\Library\Book;
use App\Models\Employee;
use App\Models\HR\Appraisal\AppriasalKpi;
use App\Models\HR\Recruitment\ApplyJob;
use App\Models\HR\Recruitment\EmployeeRequisition;
use App\Models\HR\Recruitment\InterviewCommittee;
use App\Models\HR\Recruitment\CandidateOnlineTest;
use App\Models\Progress\IndicatorProgress;
use App\Models\Questionnaire\Questionnaire;
use App\Models\Questionnaire\QuestionnaireForm;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CandidateOnlineTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = CandidateOnlineTest::with(['applyJob', 'questionnaireForm'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'questionnaire_form_id' => 'required|integer|exists:questionnaire_forms,id',
            'apply_job_id' => 'required|integer|exists:apply_jobs,id',
            'test_duration' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $this->input['uuid'] = Str::uuid();
            $item = CandidateOnlineTest::query()->create($this->input);
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
    public function show(CandidateOnlineTest $CandidateOnlineTest): JsonResponse
    {
        $CandidateOnlineTest = $CandidateOnlineTest->load(['applyJob', 'questionnaireForm']);
        return resp('1', 'Successful!', $CandidateOnlineTest, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CandidateOnlineTest $CandidateOnlineTest)
    {
        $request->validate([
            'questionnaire_form_id' => 'required',
            'apply_job_id' => 'required',
            'test_duration' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $CandidateOnlineTest->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CandidateOnlineTest $CandidateOnlineTest): JsonResponse
    {
        $item = $CandidateOnlineTest->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function getDropdowns()
    {
        $data['questionaire_forms'] =  QuestionnaireForm::query()->with('questions')->where('form_category',3)->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function getOnlineTest($uuid)
    {
        $data['item'] = CandidateOnlineTest::query()->with('questionnaireForm.questions', 'applyJob')->where('uuid', $uuid)->firstOrFail();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function submitOnlineTest(Request $request)
    {
        $request->validate([
            'answers' => 'required|array',
            'uuid' => 'required|uuid',
        ]);
        $item = CandidateOnlineTest::query()->with('questionnaireForm', 'applyJob')->where('uuid', $request->uuid)->firstOrFail();
        $formId = $item->questionnaire_form_id;
        $candidate = $item->applyJob;

        // Check if a form has already been submitted for this form_id and candidate
        $existingFormRecord = Questionnaire::where('form_id', $formId)
            ->whereHas('questionnaireable', function($query) use ($candidate) {
                $query->where('id', $candidate->id);
            })->first();

        if ($existingFormRecord) {
            return resp(0, 'Test already submitted!', [], Response::HTTP_CONFLICT);
        }

        $item->update(['is_submitted' => 1, 'test_submitted_at' => now()]);

        try {
            DB::beginTransaction();

            $formRecord = new Questionnaire();
            $formRecord->form_id = $formId;
            $formRecord->questionnaireable()->associate($candidate);
            $formRecord->save();


            $formRecord->answers()->createMany($request->answers);

            DB::commit();
            return resp(1, 'Successful!', [], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to save form!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function updateTestStartedAt(Request $request)
    {
        $request->validate([
            'uuid' => 'required|uuid',
        ]);
        $item = CandidateOnlineTest::query()->where('uuid', $request->uuid)->firstOrFail();

        if (filled($item->test_started_at)){
            return resp(0, "Test Already Started at {$item->test_started_at}", [], Response::HTTP_OK);
        }
        $item->update(['test_started_at' => now()]);
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    public function getTestAnswers($uuid)
    {
        $data['item'] = CandidateOnlineTest::query()->with('questionnaireForm', 'applyJob.questionnaires.answers.question')->where('uuid', $uuid)->firstOrFail();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}

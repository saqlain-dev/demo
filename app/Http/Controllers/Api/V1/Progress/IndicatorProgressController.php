<?php

namespace App\Http\Controllers\Api\V1\Progress;

use App\Enum\FormCategory;
use App\Http\Controllers\Controller;
use App\Http\Resources\IndicatorProgressResource;
use App\Models\Program\Project\MnE\ProjectMnePlan;
use App\Models\Program\Project\MnE\ProjectMneWorkplan;
use App\Models\Progress\IndicatorProgress;
use App\Models\Progress\IndicatorProgressMovs;
use App\Models\Questionnaire\Questionnaire;
use App\Models\Questionnaire\QuestionnaireForm;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class IndicatorProgressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('progress_indicator');

        $data = IndicatorProgressResource::collection(IndicatorProgress::with(['IndicatorMovs','questionnaires.answers','ProgressWorkplanId','ProgressStatus'])->get());
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('progress_indicator');

        $request->validate([
            'progress_workplan_id' => 'required',
            'type_of_indicator' => 'required',
            'type_id' => 'required',
            'indicator_id' => 'required',
            'progress_status' => 'required',
            'kpi' => 'required',
            'reporting_level' => 'required',
            'form_id' => 'required',
            'progress' => 'required',
            'budget_spent' => 'required',
        ]);
        $checkIndicatorExist=IndicatorProgress::query()->where('type_of_indicator',$request->type_of_indicator)->where('type_id',$request->type_id)->where('indicator_id',$request->indicator_id)->first();
        if(empty($checkIndicatorExist)){
            $item = IndicatorProgress::query()->create($this->input);
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        }else{
            return resp('0', 'Record already exist', [], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    public function saveIndicatorMovs(Request $request)
    {
        $this->authorize('progress_indicator');

        $request->validate([
            'indicator_progress_id' => 'required',
            'movs_id' => 'required',
            'mov_file' => 'required',
        ]);
        if($request->hasFile('mov_file')) {
            $responce = $this->saveMovFile($request, 'mov_files');
            if ($responce) {
                $this->input['mov_file'] = $responce;
            }
        }
        $item=IndicatorProgressMovs::query()->create($this->input);
        return resp(1,'Successful!', $item,Response::HTTP_CREATED);
    }

    public function fillForm($item, QuestionnaireForm $form, Request $request)
    {
        $this->authorize('progress_indicator');

        $request->validate([
            //'location' => ['required'],
            'answers' => ['required']
        ]);
        $item = IndicatorProgress::query()->findOrFail($item);
        try {
            DB::beginTransaction();
            $formRecord = new Questionnaire();
            $formRecord->form_id = $form->id;
            //$formRecord->location = $request->location;
            $formRecord->questionnaireable()->associate($item);
            $formRecord->save();
            $formRecord->answers()->createMany($request->answers);
            DB::commit();
            return resp(1, 'Successful!', [], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to save form!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveMovFile($request,$folder){

        $file = $request->file('mov_file');
        $path = 'uploads/media/'. $folder;
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
        return $file_name;

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->authorize('progress_indicator');

        $indicatorProgress = new IndicatorProgressResource(IndicatorProgress::with(['IndicatorMovs','questionnaires.answers','ProgressWorkplanId','ProgressStatus'])->findOrFail($id));
        return resp('1', 'Successful!', $indicatorProgress, Response::HTTP_OK);
    }
    public function getIndicatorProgressWkpid($wkpid)
    {
        $this->authorizeAny([
            'progress_indicator',
            'manage_audit_program_progress',
        ]);

        $indicatorProgress = IndicatorProgressResource::collection(IndicatorProgress::with(['IndicatorMovs','ProgressWorkplanId','ProgressStatus'])->where('progress_workplan_id',$wkpid)->get());
        return resp('1', 'Successful!', $indicatorProgress, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IndicatorProgress $indicatorProgress)
    {
        $this->authorize('progress_indicator');

        $request->validate([
            'progress_workplan_id' => 'required',
            'type_of_indicator' => 'required',
            'type_id' => 'required',
            'indicator_id' => 'required',
            'progress_status' => 'required',
            'kpi' => 'required',
            'reporting_level' => 'required',
            'form_id' => 'required',
            'progress' => 'required',
            'budget_spent' => 'required',
        ]);
        $item = IndicatorProgress::query()->update($this->input);
        return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IndicatorProgress $indicatorProgress)
    {
        $this->authorize('progress_indicator');

        $item = $indicatorProgress->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function getDropDowns(): JsonResponse
    {
        $this->authorize('progress_indicator');

        $data['workplan-statuses'] = Type::getTypeValues('project-workplan-status');
        $data['project-movs'] = Type::getTypeValues('project-movs');
        $data['reporting-level'] = Type::getTypeValues('reporting-level');
        $data['forms'] = QuestionnaireForm::query()->where('form_category',FormCategory::IndicatorProgress)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function getFormResponses($indicatorProgressId, $formId)
    {
        $this->authorize('progress_indicator');

        $indicatorProgress = new IndicatorProgressResource(IndicatorProgress::with(['IndicatorMovs','ProgressWorkplanId','ProgressStatus','questionnaires' => function ($query) use ($formId){
            $query->where('form_id', $formId);
            $query->with('answers.question');
        },'ProgressWorkplanId','ProgressStatus'])->findOrFail($indicatorProgressId));
        $indicatorProgress->form = QuestionnaireForm::query()->findOrFail($formId);
        return resp('1', 'Successful!', $indicatorProgress, Response::HTTP_OK);
    }
}

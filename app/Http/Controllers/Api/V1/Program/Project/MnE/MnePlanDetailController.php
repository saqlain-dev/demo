<?php

namespace App\Http\Controllers\Api\V1\Program\Project\MnE;

use App\Http\Controllers\Controller;
use App\Http\Resources\MnePlanDetailResource;
use App\Models\Program\Project\MnE\MnePlanDetail;
use App\Models\Program\Project\MnE\MnePlanDetailMov;
use App\Models\Program\Project\MnE\MnePlanGoal;
use App\Models\Program\Project\MnE\ObservationSheet;
use App\Models\Program\Project\MnE\ProjectMnePlan;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Progress\IndicatorProgressMovs;
use App\Models\StrategicPlan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MnePlanDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = MnePlanDetailResource::collection(MnePlanDetail::all());
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'plan_id' => 'required',
            'indicator_type' => 'required',
            'indicator_parent_id' => 'required',
            'indicator_id' => 'required',
            'indicator_definition' => 'required',
            'indicator_methodology' => 'required',
            'data_collection_methodology' => 'required',
            'disaggregates' => 'required|array',
            'mne_tools' => 'required',
            'data_collection_freq' => 'required',
            'data_reporting_freq' => 'required',
            'required_movs' => 'required',
            'responsibility' => 'required',
            'unit_of_measure' => 'required',
            //'expected_goal' => 'required',
        ]);
        $item = MnePlanDetail::query()->create($request->all());
        return resp('1', 'Successful!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resourc0e.
     */
    public function show($id)
    {
        $item = MnePlanDetail::query()->findOrFail($id);
        $resource = new MnePlanDetailResource($item);
        return resp('1', 'Successful!', $resource, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MnePlanDetail $mnePlanDetail)
    {
        $request->validate([
            'plan_id' => 'required',
            'indicator_type' => 'required',
            'indicator_parent_id' => 'required',
            'indicator_id' => 'required',
            'indicator_definition' => 'required',
            'indicator_methodology' => 'required',
            'data_collection_methodology' => 'required',
            'disaggregates' => 'required|array',
            'mne_tools' => 'required',
            'data_collection_freq' => 'required',
            'data_reporting_freq' => 'required',
            'required_movs' => 'required',
            'responsibility' => 'required',
            'unit_of_measure' => 'required',
            //'expected_goal' => 'required',
        ]);
        $mnePlanDetail->update($request->all());
        return resp('1', 'Successful!', $mnePlanDetail, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MnePlanDetail $mnePlanDetail)
    {
        $mnePlanDetail->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }

    public function getPlanAllDetails(ProjectMnePlan $plan)
    {
        $this->authorizeAny([
            'manage_audit_program_mne',
            'manage_audit_program_reports',
            'm&e_plans',
            'm_e_progress',
        ]);

        $planId = $plan->id;
        $plan=$plan->load('comments.createdBy');
        $data = $plan->toArray();
        $mnePlanDetails = MnePlanDetailResource::collection($plan->mnePlanDetails);
        $data['mnePlanDetails'] = $mnePlanDetails;
        $data['mneObservationSheet'] = ObservationSheet::with(['TypeOfActivity', 'MneOfficerId', 'DistrictId', 'created_by', 'updated_by', 'MneObservations.ProgrammaticResponses'])->where('mne_plan_id', $planId)->get();
        $data['approval_request'] = getNextApproval(5, auth()->user()->designation_id, $plan->id);
        $data['approval_request_status'] = checkApprovalRequestStatus(5, $plan->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function saveMnePlanDetailMovs(Request $request)
    {
        $request->validate([
            'mne_plan_detail_id' => 'required',
            'movs_id' => 'required',
            'mov_file' => 'required',
        ]);
        if ($request->hasFile('mov_file')) {
            $responce = $this->saveMovFile($request, 'mov_files');
            if ($responce) {
                $this->input['mov_file'] = $responce;
            }
        }
        $item = MnePlanDetailMov::query()->create($this->input);
        return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
    }

    public function saveMovFile($request, $folder)
    {

        $file = $request->file('mov_file');
        $path = 'uploads/media/' . $folder;
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
}

<?php

namespace App\Http\Controllers\Api\V1\Program\Project\MnE;

use App\Http\Controllers\Controller;
use App\Models\Program\Project\MnE\MnePlanOutput;
use App\Models\Program\Project\MnE\ProjectMnePlan;
use App\Models\Program\Project\ProjectProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MnePlanOutputController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($plan)
    {
        $data = ProjectMnePlan::query()->with('planOutputs')->findOrFail($plan);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ProjectMnePlan $mnePlan)
    {
        //dd($request->all());
        $request->validate([
            'project_output_id' => 'required',
            'indicator_definition' => 'required',
            'indicator_methodology' => 'required',
            'data_collection_methodology' => 'required',
            'disaggregates' => 'required|array',
            'mne_tools' => 'required',
            'data_collection_freq' => 'required',
            'data_reporting_freq' => 'required',
            'required_movs' => 'required',
            'responsibility' => 'required',
        ]);
        $item = $mnePlan->planOutputs()->create($request->all());
        return resp('1', 'Successful!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(MnePlanOutput $mnePlanOutput)
    {
        return resp('1', 'Successful!', $mnePlanOutput, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MnePlanOutput $mnePlanOutput)
    {
        if ($mnePlanOutput->plan_id != $request->plan_id)
            return resp('0', 'Unsuccessful!', ['error' => 'Record Not Found!'], Response::HTTP_OK);

        $request->validate([
            'plan_id' => 'required',
            'project_output_id' => 'required',
            'indicator_definition' => 'required',
            'indicator_methodology' => 'required',
            'data_collection_methodology' => 'required',
            'disaggregates' => 'required',
            'mne_tools' => 'required',
            'data_collection_freq' => 'required',
            'data_reporting_freq' => 'required',
            'required_movs' => 'required',
            'responsibility' => 'required',
        ]);
        $item = $mnePlanOutput->update($request->all());
        return resp('1', 'Successful!', $item, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MnePlanOutput $mnePlanOutput)
    {
        $mnePlanOutput->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }
}

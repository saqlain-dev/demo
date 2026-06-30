<?php

namespace App\Http\Controllers\Api\V1\Program\Project;

use App\Http\Controllers\Controller;
use App\Models\Program\Project\ProjectKpiMapping;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProjectKpiMappingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = ProjectKpiMapping::with(['GoalIndicatorDetail','OutcomeIndicatorDetail','OutputIndicatorDetail'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'selected_type_id' => 'required',
            'type_of_indicator' => 'required',
            'indicator_number' => 'required',
            'indicator_type' => 'required',
            'measuring_unit' => 'required',
            'kpi' => 'required',
            'reporting_level' => 'required',
        ]);
        $item = ProjectKpiMapping::query()->create($this->input);

        return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectKpiMapping $projectKpiMapping): JsonResponse
    {
        $projectKpiMapping = $projectKpiMapping->load(['GoalIndicatorDetail','OutcomeIndicatorDetail','OutputIndicatorDetail']);
        return resp('1', 'Successful!', $projectKpiMapping, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectKpiMapping $projectKpiMapping)
    {
        $request->validate([
            'project_id' => 'required',
            'selected_type_id' => 'required',
            'type_of_indicator' => 'required',
            'indicator_number' => 'required',
            'indicator_type' => 'required',
            'measuring_unit' => 'required',
            'kpi' => 'required',
            'reporting_level' => 'required',
        ]);
        $item = $projectKpiMapping->update($this->input);
        return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectKpiMapping $projectKpiMapping)
    {
        $item = $projectKpiMapping->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}

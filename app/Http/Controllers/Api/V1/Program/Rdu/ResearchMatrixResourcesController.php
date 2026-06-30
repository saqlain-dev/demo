<?php

namespace App\Http\Controllers\Api\V1\Program\Rdu;

use App\Http\Controllers\Controller;
use App\Models\Program\Rdu\ResearchMatrixResearchOutput;
use App\Models\Program\Rdu\ResearchMatrixResources;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ResearchMatrixResourcesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = ResearchMatrixResources::with(['AllocatedProgramResources','ResourcesAvailability'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }
    public function getRMResourcesByRmid($rmid)
    {
        $data = ResearchMatrixResources::query()->with(['AllocatedProgramResources','ResourcesAvailability'])->where('research_matrix_id',$rmid)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'research_matrix_id' => 'required',
            'allocated_program_resources' => 'required',
            'number_of_resources' => 'required',
            'resources_availability' => 'required',
        ]);
        $item = ResearchMatrixResources::query()->create($this->input);
        return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);

    }

    /**
     * Display the specified resource.
     */
    public function show(ResearchMatrixResources $researchMatrixResources): JsonResponse
    {
        $researchMatrixResources = $researchMatrixResources->load(['AllocatedProgramResources','ResourcesAvailability']);
        return resp('1', 'Successful!', $researchMatrixResources, Response::HTTP_OK);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $researchMatrixResources = ResearchMatrixResources::query()->findOrFail($id);
        $request->validate([
            'research_matrix_id' => 'required',
            'allocated_program_resources' => 'required',
            'number_of_resources' => 'required',
            'resources_availability' => 'required',
        ]);

        $item = $researchMatrixResources->update($this->input);

        return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $researchMatrixResources = ResearchMatrixResources::query()->findOrFail($id);
        $researchMatrixResources->delete();
        return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
    }
}

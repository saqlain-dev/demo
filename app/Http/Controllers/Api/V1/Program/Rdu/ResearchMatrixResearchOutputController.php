<?php

namespace App\Http\Controllers\Api\V1\Program\Rdu;

use App\Http\Controllers\Controller;
use App\Models\Program\Rdu\ResearchMatrixDataSources;
use App\Models\Program\Rdu\ResearchMatrixResearchOutput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ResearchMatrixResearchOutputController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = ResearchMatrixResearchOutput::with('ResearchOutputId','ResearchOutputPlaceId')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }
    public function getRMResearchOutByRmid($rmid)
    {
        $data = ResearchMatrixResearchOutput::query()->with('ResearchOutputId','ResearchOutputPlaceId')->where('research_matrix_id',$rmid)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'research_matrix_id' => 'required',
            'research_output_id' => 'required',
            'research_output_start_date' => 'required',
            'research_output_end_date' => 'required',
            'research_output_place_id' => 'required',
        ]);
        $item = ResearchMatrixResearchOutput::query()->create($this->input);
        return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);

    }

    /**
     * Display the specified resource.
     */
    public function show(ResearchMatrixResearchOutput $researchMatrixResearchOutput): JsonResponse
    {
        $researchMatrixResearchOutput = $researchMatrixResearchOutput->load('ResearchOutputId','ResearchOutputPlaceId');
        return resp('1', 'Successful!', $researchMatrixResearchOutput, Response::HTTP_OK);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $researchMatrixResearchOutput = ResearchMatrixResearchOutput::query()->findOrFail($id);
        $request->validate([
            'research_matrix_id' => 'required',
            'research_output_id' => 'required',
            'research_output_start_date' => 'required',
            'research_output_end_date' => 'required',
            'research_output_place_id' => 'required',
        ]);
        $item = $researchMatrixResearchOutput->update($this->input);
        return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $researchMatrixResearchOutput = ResearchMatrixResearchOutput::query()->findOrFail($id);
        $researchMatrixResearchOutput->delete();
        return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
    }
}

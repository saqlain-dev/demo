<?php

namespace App\Http\Controllers\Api\V1\Program\Rdu;

use App\Http\Controllers\Controller;
use App\Models\Program\Rdu\ResearchMatrix;
use App\Models\Program\Rdu\ResearchMatrixDataSources;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ResearchMatrixDataSourcesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = ResearchMatrixDataSources::with('DataSourceId','DataAvailability')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }
    public function getRMDataSourceByRmid($rmid)
    {
        $data = ResearchMatrixDataSources::query()->with('DataSourceId','DataAvailability')->where('research_matrix_id',$rmid)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'research_matrix_id' => 'required',
            'data_source_id' => 'required',
            'data_availability' => 'required',
        ]);
        $item = ResearchMatrixDataSources::query()->create($this->input);
        return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);

    }

    /**
     * Display the specified resource.
     */
    public function show(ResearchMatrixDataSources $researchMatrixDataSources)
    {
        $researchMatrixDataSources = $researchMatrixDataSources->load('DataSourceId','DataAvailability');
        return resp('1', 'Successful!', $researchMatrixDataSources, Response::HTTP_OK);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $researchMatrixDataSources = ResearchMatrixDataSources::query()->findOrFail($id);
        $request->validate([
            'research_matrix_id' => 'required',
            'data_source_id' => 'required',
            'data_availability' => 'required',
        ]);
        $item = $researchMatrixDataSources->update($this->input);
        return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $researchMatrixDataSources = ResearchMatrixDataSources::query()->findOrFail($id);
        $researchMatrixDataSources->delete();
        return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Program\Project\MnE;

use App\Http\Controllers\Controller;
use App\Models\Program\Project\MnE\ObservationProgrammaticResponse;
use App\Models\Program\Project\MnE\ObservationSheet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ObservationProgrammaticResponseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {


        $data = ObservationProgrammaticResponse::with(['ObservationId','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'observation_id' => 'required',
            'reason' => 'required',
            'future_mitigation_strategy' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $item = ObservationProgrammaticResponse::query()->create($this->input);
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
    public function show(ObservationProgrammaticResponse $observationProgrammaticResponse): JsonResponse
    {
        $observationProgrammaticResponse = $observationProgrammaticResponse->load(['ObservationId','created_by','updated_by']);
        return resp('1', 'Successful!', $observationProgrammaticResponse, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id )
    {
        $observationProgrammaticResponse = ObservationProgrammaticResponse::query()->findOrFail($id);
        $request->validate([
            'observation_id' => 'required',
            'reason' => 'required',
            'future_mitigation_strategy' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $observationProgrammaticResponse->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ObservationProgrammaticResponse $observationProgrammaticResponse): JsonResponse
    {
        $item = $observationProgrammaticResponse->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}

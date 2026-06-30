<?php

namespace App\Http\Controllers\Api\V1\Program\Project\MnE;

use App\Http\Controllers\Controller;
use App\Models\Program\Project\MnE\MneObservation;
use App\Models\Program\Project\MnE\ObservationSheet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MneObservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = MneObservation::with(['ObservationSheetId','TypeOfRedFlag','Priority','created_by','updated_by','ProgrammaticResponses'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'observation_sheet_id' => 'required',
            'observations' => 'required',
            'mitigation_on_spot' => 'required',
            'type_of_red_flag' => 'required',
            'priority' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $item = MneObservation::query()->create($this->input);
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
    public function show(MneObservation $mneObservation): JsonResponse
    {
        $mneObservation = $mneObservation->load(['ObservationSheetId','TypeOfRedFlag','Priority','created_by','updated_by','ProgrammaticResponses']);
        return resp('1', 'Successful!', $mneObservation, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MneObservation $mneObservation)
    {
        $request->validate([
            'observation_sheet_id' => 'required',
            'observations' => 'required',
            'mitigation_on_spot' => 'required',
            'type_of_red_flag' => 'required',
            'priority' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $mneObservation->update($this->input);
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
    public function destroy(MneObservation $mneObservation): JsonResponse
    {
        $mneObservation->ProgrammaticResponses()->delete();
        $item = $mneObservation->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}

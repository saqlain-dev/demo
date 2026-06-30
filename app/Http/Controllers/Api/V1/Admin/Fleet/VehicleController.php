<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\Vehicle;
use App\Models\Program\Project\ProjectRrfOutcomeIndicator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'vehicle_registration_view'
        ]);

        $data = Vehicle::with(['ProjectId','VehicleType'])->where(['status'=>1])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'vehicle_registration_create'
        ]);

        $request->validate([
            'project_id' => 'required',
            'region_name' => 'required',
            //'vehicle_number' => 'required',
            'registration_no' => 'required',
            'vehicle_type' => 'required',
            'vehicle_modal' => 'required',
            'vehicle_make' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = Vehicle::query()->create($this->input);
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
    public function show(Vehicle $vehicle): JsonResponse
    {
        $this->authorizeAny([
            'vehicle_registration_view'
        ]);

        $vehicle = $vehicle->load(['ProjectId','VehicleType']);
        return resp('1', 'Successful!', $vehicle, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorizeAny([
            'vehicle_registration_update'
        ]);

        $request->validate([
            'project_id' => 'required',
            'region_name' => 'required',
            //'vehicle_number' => 'required',
            'registration_no' => 'required',
            'vehicle_type' => 'required',
            'vehicle_modal' => 'required',
            'vehicle_make' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $vehicle->update($this->input);
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

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $this->authorizeAny([
            'vehicle_registration_delete'
        ]);

        $item = $vehicle->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}

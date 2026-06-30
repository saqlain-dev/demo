<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use Illuminate\Http\Request;
use App\Models\Admin\Fleet\Vehicle;
use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\AssignVehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class AssignVehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'assign_vehicle_view'
        ]);

        $data['assignedVehicle'] = AssignVehicle::with(['DriverId','VehicleId.VehicleType'])->whereNull('deleted_at')->get();
        $data['unAssignedVehicle'] =  Vehicle::whereDoesntHave('assignments')->get();

        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'assign_vehicle_create'
        ]);

        try {
            DB::beginTransaction();
            $request->validate([
                'driver_id' => 'required',
                'vehicle_id' => 'required',
                'assigned_date' => 'required',
            ]);
            $item = AssignVehicle::query()->create($this->input);
            DB::commit();
            return resp('1', 'Assigned Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to assign vehicle. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $this->authorizeAny([
            'assign_vehicle_view'
        ]);

        $assignedVehicle = AssignVehicle::query()->findOrFail($id)->with('VehicleId','DriverId')->get();
        return resp('1', 'Successful!', $assignedVehicle, Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAny([
            'assign_vehicle_update'
        ]);

        try {
            DB::beginTransaction();
            $request->validate([
                'driver_id' => 'required',
                'vehicle_id' => 'required',
                'assigned_date' => 'required',
            ]);
            $assignedVehicle = AssignVehicle::query()->findOrFail($id);
            $item = $assignedVehicle->update($this->input);
            DB::commit();
            return resp('1', 'Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to update vehicle. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->authorizeAny([
            'assign_vehicle_delete'
        ]);
        $assignedVehicle = AssignVehicle::query()->findOrFail($id);
        $item = $assignedVehicle->delete();
        return resp('1', 'Unassigned Successfully!', $item, Response::HTTP_OK);
    }
}

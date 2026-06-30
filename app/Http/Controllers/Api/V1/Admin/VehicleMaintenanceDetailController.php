<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\VehicleMaintenanceDetail;
use App\Models\PurchaseRequestItems;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VehicleMaintenanceDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $parent = VehicleMaintenanceDetail::all();
        return resp(1, 'Successful!', $parent, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|integer|exists:vehicle_maintenance_forms,id',
            'nature_of_work' => 'nullable',
            'previous_meter_reading' => 'nullable',
            'previous_meter_reading_date' => 'nullable|date|date_format:Y-m-d',
            'present_meter_reading' => 'nullable',
            'present_meter_reading_date' => 'nullable|date|date_format:Y-m-d',
            'difference' => 'nullable',
            'last_work_date' => 'nullable|date|date_format:Y-m-d',
            'is_detected' => 'nullable|boolean',
            'estimated_expenditure' => 'nullable',
            'shop_owner' => 'nullable',
            'repair_date' => 'nullable|date|date_format:Y-m-d',
        ]);
        $parent = VehicleMaintenanceDetail::query()->create($this->input);
        return resp(1, 'Successful!', $parent, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show($parent_id)
    {
        $parent = VehicleMaintenanceDetail::query()->findOrFail($parent_id);
        return resp(1, 'Successful!', $parent, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'parent_id' => 'required|integer|exists:vehicle_maintenance_forms,id',
            'nature_of_work' => 'nullable',
            'previous_meter_reading' => 'nullable',
            'previous_meter_reading_date' => 'nullable|date',
            'present_meter_reading' => 'nullable',
            'present_meter_reading_date' => 'nullable|date',
            'difference' => 'nullable',
            'last_work_date' => 'nullable|date',
        ]);

        VehicleMaintenanceDetail::query()->findOrFail($id)->update($this->input);

        $data = VehicleMaintenanceDetail::query()->findOrFail($id);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($item_id)
    {
        $item = VehicleMaintenanceDetail::query()->findOrFail($item_id);
        $item->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

}

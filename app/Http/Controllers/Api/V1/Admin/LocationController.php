<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Location;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'warehouse_view',
            'manage_audit_inventory_warehouse',
        ]);

        $data = Location::getLocations();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'warehouse_create'
        ]);

        $request->validate([
            'parent_id' => 'nullable|integer',
            'name' => 'required|max:255',
            'description' => 'required',
        ]);

        $item = Location::query()->create($request->all());
        return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show($itemId)
    {
        $this->authorizeAny([
            'warehouse_view',
            'manage_audit_inventory_warehouse',
        ]);

        $item = Location::query()->with(['subLocations.racks.aisles','inventory.item.itemUnit'])->findOrFail($itemId);
        return resp('1', 'Successful!', $item, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        $this->authorizeAny([
            'warehouse_update'
        ]);

        $request->validate([
            'parent_id' => 'nullable|integer',
            'name' => 'required|max:255',
            'description' => 'required',
        ]);

        $location->update($request->all());
        return resp('1', 'Record Created Successfully!', $location->refresh(), Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        $this->authorizeAny([
            'warehouse_delete'
        ]);

        $location->subLocations()->delete();
        $location->delete();
        return resp('1', 'Record Deleted Successfully!', [], Response::HTTP_OK);
    }

    public function getDistricts()
    {
        $item = Province::query()->with('districts')->get();
        return resp('1', 'Successful!', $item, Response::HTTP_OK);
    }
}

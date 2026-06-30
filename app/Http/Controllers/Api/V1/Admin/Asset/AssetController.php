<?php

namespace App\Http\Controllers\Api\V1\Admin\Asset;

use App\Http\Controllers\Controller;
use App\Models\Admin\Asset\Asset;
use App\Models\Employee;
use App\Models\ItemSubCategory;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Program\Rdu\RmPlanDataSource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Asset::query()->with('assetCategory','projectId','handoverTo')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'asset_type' => 'required|integer',
            'gl_code' => 'required|string|max:255',
            'description' => 'required|string',
            'serial_no' => 'required|string|max:255',
            'inventory_no' => 'required|string|max:255',
            'new_inventory_no' => 'required|string|max:255',
            'asset_category' => 'required|exists:item_sub_categories,id',
            'asset_location' => 'required|string|max:255',
            'handover_to' => 'required|exists:employees,id',
            'date_of_purchase' => 'required|date',
            'cost_of_items' => 'required|numeric',
            'voucher_no' => 'required|string|max:255',
            'voucher_date' => 'required|date',
            'vendor_name' => 'required|string|max:255',
            'project_id' => 'required|exists:project_profiles,id',
            'depreciation_rate' => 'required|string|max:255',
            'acc_dep_start_date' => 'required|date',
            'acc_dep_end_date' => 'required|date|after_or_equal:acc_dep_start_date',
            'number_of_months' => 'required|string|max:255',
            'net_book_value' => 'required|numeric',
            'item_sold' => 'required|string|max:255',
            'physical_verification_date' => 'required|date',
            'remarks' => 'required|string',
        ]);
        $item = Asset::query()->create($request->all());
        return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);

    }

    /**
     * Display the specified resource.
     */
    public function show($assetId)
    {
        $asset = Asset::query()->with('assetCategory','projectId','handoverTo')->findOrFail($assetId);
        return resp('1', 'Successful!', $asset, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asset $asset)
    {
        $request->validate([
            'asset_type' => 'required|integer',
            'gl_code' => 'required|string|max:255',
            'description' => 'required|string',
            'serial_no' => 'required|string|max:255',
            'inventory_no' => 'required|string|max:255',
            'new_inventory_no' => 'required|string|max:255',
            'asset_category' => 'required|exists:item_sub_categories,id',
            'asset_location' => 'required|string|max:255',
            'handover_to' => 'required|exists:employees,id',
            'date_of_purchase' => 'required|date',
            'cost_of_items' => 'required|numeric',
            'voucher_no' => 'required|string|max:255',
            'voucher_date' => 'required|date',
            'vendor_name' => 'required|string|max:255',
            'project_id' => 'required|exists:project_profiles,id',
            'depreciation_rate' => 'required|string|max:255',
            'acc_dep_start_date' => 'required|date',
            'acc_dep_end_date' => 'required|date|after_or_equal:acc_dep_start_date',
            'number_of_months' => 'required|string|max:255',
            'net_book_value' => 'required|numeric',
            'item_sold' => 'required|string|max:255',
            'physical_verification_date' => 'required|date',
            'remarks' => 'required|string',
        ]);
        $asset->update($request->all());
        return resp('1', 'Record Created Successfully!', $asset->refresh(), Response::HTTP_OK);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset)
    {
        $asset->delete();
        return resp('1', 'Record Deleted Successfully!', [], Response::HTTP_OK);
    }

    public function getDropdowns()
    {
        $data['employees'] = Employee::query()->whereNotIn('employee_type', [14, 16, 17, 18])->get();
        $data['projects'] = ProjectProfile::all();
        $data['categories'] = ItemSubCategory::all();
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }
}

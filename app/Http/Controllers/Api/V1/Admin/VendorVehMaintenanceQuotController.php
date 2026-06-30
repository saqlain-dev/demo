<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\VendorVehMaintenanceQuot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class VendorVehMaintenanceQuotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_maintenance_id' => 'required',
            'qty' => 'required',
            'estimated_cost' => 'required',
        ]);
        try {

            DB::beginTransaction();

            $vmQuotation=VendorVehMaintenanceQuot::query()->create($this->input);
            DB::commit();

            return resp('1', 'Quotation added Successfully!', $vmQuotation, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);

        }
    }

    public function getVMQuotationList($vm)
    {
        $data['vm_quotation_list'] = VendorVehMaintenanceQuot::query()->where('vehicle_maintenance_id',$vm)->get();

        return resp('1', 'Successfully!', $data, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(VendorVehMaintenanceQuot $vendorVehMaintenanceQuot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VendorVehMaintenanceQuot $vendorVehMaintenanceQuot)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VendorVehMaintenanceQuot $vm_quotation)
    {
        $vm_quotation->delete();

        return resp('1', 'Successfully!', $vm_quotation, Response::HTTP_CREATED);
    }
}

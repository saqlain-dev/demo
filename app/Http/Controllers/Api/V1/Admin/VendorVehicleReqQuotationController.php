<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\VendorVehicleReqQuotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class VendorVehicleReqQuotationController extends Controller
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
            'vehicle_req_id' => 'required',
            'per_day_rate' => 'required'
        ]);
        try {

            DB::beginTransaction();

            $vrQuotation=VendorVehicleReqQuotation::query()->create($this->input);
            DB::commit();

            return resp('1', 'Quotation added Successfully!', $vrQuotation, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);

        }
    }

    public function getVRQuotationList($vr)
    {
        $data['vr_quotation_list'] = VendorVehicleReqQuotation::query()->where('vehicle_req_id',$vr)->get();

        return resp('1', 'Successfully!', $data, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(VendorVehicleReqQuotation $vr_quotation)
    {

        return resp('1', 'Successfully!', $vr_quotation, Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VendorVehicleReqQuotation $vr_quotation)
    {
        $request->validate([
            'vehicle_req_id' => 'required',
            'per_day_rate' => 'required'
        ]);
        try {
            

            DB::beginTransaction();

            $vr_quotation->update($this->input);
            DB::commit();

            return resp('1', 'Quotation updated Successfully!', $vr_quotation, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VendorVehicleReqQuotation $vr_quotation)
    {
        $vr_quotation->delete();

        return resp('1', 'Successfully!', $vr_quotation, Response::HTTP_CREATED);
    }
}

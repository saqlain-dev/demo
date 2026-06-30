<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\VehicleRequest;
use App\Models\Admin\Fleet\VehicleRequestDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class VehicleRequestDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = VehicleRequestDetail::with(['VehicleRequestId','VehicleId','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_request_id' => 'required',
            'pool_type' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = VehicleRequestDetail::query()->create($this->input);
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
    public function show(VehicleRequestDetail $vehicleRequestDetail): JsonResponse
    {
        $data['vehicleRequestDetail']=$vehicleRequestDetail = $vehicleRequestDetail->load(['VehicleRequestId','VehicleId','created_by','updated_by']);
       // $data['approval_request']=getNextApproval(17,auth()->user()->designation_id,$vehicleRequest->id);
        //$data['approval_request_status']=checkApprovalRequestStatus(17,$vehicleRequest->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleRequestDetail $vehicleRequestDetail)
    {
        $request->validate([
            'vehicle_request_id' => 'required',
            'pool_type' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $vehicleRequestDetail->update($this->input);
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
    public function destroy(VehicleRequestDetail $vehicleRequestDetail): JsonResponse
    {
        $item = $vehicleRequestDetail->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}

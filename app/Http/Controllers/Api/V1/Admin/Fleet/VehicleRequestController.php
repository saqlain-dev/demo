<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\VehicleRequest;
use App\Models\Admin\VehicleRequestVendor;
use App\Models\Admin\VendorVehicleReqQuotation;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class VehicleRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'vehicle_requisition_view',
            'admin_vehicle_requisition_view',
            'manage_audit_procurement',
        ]);

        $data = VehicleRequest::with(['created_by','updated_by','VehicleRequestDetail.VehicleId.VehicleType'])->get();

        if($data ){
            foreach($data as $key => $emp){
                //dd($emp['commuters']);
                $data[$key]['commuter_detail']=Employee::query()->whereIn('id',explode(',',$emp['commuters']))->get();
            }
        }
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'vehicle_requisition_create',
            'admin_vehicle_requisition_create',
        ]);

        $request->validate([
            //'vehicle_id' => 'required',
            'movement_from' => 'required',
            'movement_to' => 'required',
            'total_km' => 'required',
            //'commuters' => 'required',
            'commuters' => 'required|array|min:1',
            'commuters.*' => 'required',
            'expected_date_from' => 'required',
            'expected_date_to' => 'required',
            'purpose' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $this->input['commuters']=implode(',',$this->input['commuters']);
            $item = VehicleRequest::query()->create($this->input);
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
    public function show(VehicleRequest $vehicleRequest): JsonResponse
    {
        $this->authorizeAny([
            'vehicle_requisition_view',
            'admin_vehicle_requisition_view',
            'manage_audit_procurement',
            'manage_vendor_portal',
        ]);

        $data['vehicleRequest']=$vehicleRequest = $vehicleRequest->load(['created_by','updated_by','feedBack.question','feedBack.employee.designation','VehicleRequestDetail.VehicleId.VehicleType','vehicleReqQuotations.vendorDetail','vehicleReqQuotations.VehicleRequestDetail','vehicleReqVendor.vendorDetail','vrInvoice']);
        if($vehicleRequest ){

            $vehicleRequest['commuter_detail']=Employee::query()->whereIn('id',explode(',',$vehicleRequest['commuters']))->get();

        }
        $data['vehicleRequest']=$vehicleRequest;

        $data['approval_request']=getNextApproval(17,auth()->user()->designation_id,$vehicleRequest->id);
        $data['approval_request_status']=checkApprovalRequestStatus(17,$vehicleRequest->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleRequest $vehicleRequest)
    {
        $this->authorizeAny([
            'vehicle_requisition_update',
            'admin_vehicle_requisition_update',
        ]);

        $request->validate([
            //'vehicle_id' => 'required',
            'movement_from' => 'required',
            'movement_to' => 'required',
            'total_km' => 'required',
            'commuters' => 'required|array|min:1',
            'commuters.*' => 'required',
            'expected_date_from' => 'required',
            'expected_date_to' => 'required',
            'purpose' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $this->input['commuters']=implode(',',$this->input['commuters']);
            $item = $vehicleRequest->update($this->input);
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
    public function destroy(VehicleRequest $vehicleRequest): JsonResponse
    {
        $this->authorizeAny([
            'vehicle_requisition_delete',
            'admin_vehicle_requisition_delete',
        ]);

        $vehicleRequest->delete();
        return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
    }

    public function sendVehicleRequestForApproval(VehicleRequest $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',17)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',17)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                $Approval=ApprovalProcessList::query()->create($insert);

            }
            $update=array('approval_status'=>2);
            VehicleRequest::query()->where('id',$item->id)->update($update);
            return resp(1,'Vehicle request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Vehicle request approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function attachVRVendor(Request $request, VehicleRequest $vr)
    {

        if($vr->approval_status == 1){

            $request->validate([
                'vendors' => 'required|array|min:1',
                'vendors.*' => 'required',
            ]);

            try {
                DB::beginTransaction();
                $vendors=$request->vendors;
                unset($this->input['vendors']);
                $this->input['float_vr']=1;
                $atrUpdate=VehicleRequest::query()->where('id',$vr->id)->update($this->input);
                if($atrUpdate){
                    foreach($vendors as $vendor_id){
                        VehicleRequestVendor::query()->create(['vehicle_req_id' => $vr->id, 'vendor_id' => $vendor_id]);
                    }
                }

                DB::commit();
                $vr=VehicleRequest::query()->findOrFail($vr->id);
                return resp(1,'Successful!', $vr,Response::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();

                return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
            }
        }else{
            return resp(0,'VR not approved yet.', [],Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function acceptVRQuotation(Request $request, VendorVehicleReqQuotation $vr)
    {

        if($vr){

            $request->validate([
                'quotation_status' => 'required',
            ]);

            try {

                VendorVehicleReqQuotation::query()->where('vehicle_req_id',$vr->vehicle_req_id)->where('vendor_id',$vr->vendor_id)->update($this->input);

                DB::commit();

                $vr=VehicleRequest::query()->findOrFail($vr->vehicle_req_id);
                return resp(1,'Successful!', $vr,Response::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();

                return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
            }
        }else{
            return resp(0,'ATR not found.', [],Response::HTTP_EXPECTATION_FAILED);
        }
    }
}

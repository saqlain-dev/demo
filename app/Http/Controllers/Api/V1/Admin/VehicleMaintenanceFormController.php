<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AirTravelRequest;
use App\Models\Admin\AirTravelRequestDetail;
use App\Models\Admin\VehicleMaintenanceDetail;
use App\Models\Admin\VehicleMaintenanceForm;
use App\Models\Admin\VehicleMaintenanceVendor;
use App\Models\Admin\VendorVehMaintenanceQuot;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItems;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class VehicleMaintenanceFormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'vehicle_maintenance_view',
            'vehicle_maintenance_fleet_view',
            'manage_audit_procurement',
        ]);

        $data = VehicleMaintenanceForm::query()->with('items','vehicle','project','department','procurement','procurementDetail.item')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'vehicle_maintenance_create',
            'vehicle_maintenance_fleet_create',
        ]);

        $request->validate([
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            // 'nature_of_work' => 'nullable|integer|exists:items,id',
            //'project_id' => 'required|integer',
            //'department_id' => 'required|integer',
            'current_odo' => 'nullable|string',
            'procurement_id' => 'nullable|integer|exists:procurements,id',
            'procurement_detail_id' => 'nullable|integer|exists:procurement_details,id',
        ]);
        $parent = VehicleMaintenanceForm::query()->create($this->input);
        if ($parent) {
            return resp(1, 'Successful!', $parent->load('items'), Response::HTTP_CREATED);
        }
        return resp(0, 'Unsuccessful!', ['errors' => 'Failed to save data!'], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function show($parent_id)
    {
        $this->authorizeAny([
            'vehicle_maintenance_view',
            'vehicle_maintenance_fleet_view',
            'manage_audit_procurement',
            'manage_vendor_portal',
            'manage_employee_portal',
        ]);

        $data['parent']=$parent = VehicleMaintenanceForm::query()->with([
            'invoices.invoiceVehicleMaintenanceDetail',
            'items.natureOfWork',
            'vehicle' => function ($q) {
                            $q->with([
                                'LogBooks' => function ($query) {
                                    $query->latest()->limit(1);
                                }
                            ]);
                        },
            'project',
            'department',
            'vehicleMaintenanceVendor.vendorDetail',
            'vehicleMaintenanceQuotations.vendorDetail',
            'vehicleMaintenanceQuotations.VehicleMaintenanceDetail',
            'vmInvoice',
            'procurement',
            'procurementDetail.item'])->findOrFail($parent_id);
        $data['approval_request']=getNextApproval(16,auth()->user()->designation_id,$parent_id);
        $data['approval_request_status']=checkApprovalRequestStatus(16,$parent_id);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $parent_id)
    {
        $this->authorizeAny([
            'vehicle_maintenance_update',
            'vehicle_maintenance_fleet_update',
        ]);

        $request->validate([
            'vehicle_id' => 'required|integer|exists:vehicles,id',
        //    'nature_of_work' => 'required|integer|exists:items,id',
            //'project_id' => 'required|integer',
            //'department_id' => 'required|integer',
            'procurement_id' => 'nullable|integer|exists:procurements,id',  
            'procurement_detail_id' => 'nullable|integer|exists:procurement_details,id',
            'current_odo' => 'required|string',
        ]);

        VehicleMaintenanceForm::query()->findOrFail($parent_id)->update($this->input);

        $data = VehicleMaintenanceForm::query()->with(['items'])->findOrFail($parent_id);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($parent_id)
    {
        $this->authorizeAny([
            'vehicle_maintenance_delete',
            'vehicle_maintenance_fleet_delete',
        ]);

        $parent = VehicleMaintenanceForm::query()->with(['items'])->findOrFail($parent_id);
        $parent->items()->delete();
        $parent->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }
    public function sendMaintenanceRequestForApproval(VehicleMaintenanceForm $item)
    {

        $item = VehicleMaintenanceForm::withSum('items', 'estimated_expenditure')->find($item->id);
        $claimAmount = $item->items_sum_estimated_expenditure ?? 0;

        $approval_process_name=ApprovalProcessName::query()->where('id',16)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',16)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',16)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                if($approval_process_name->isFinancialApproval == 1){
                    if($approval->financialAmount < $claimAmount ){
                        $insert['approval_status']=0;
                        $Approval=ApprovalProcessList::query()->create($insert);
                    }else{
                        $Approval=ApprovalProcessList::query()->create($insert);
                    }
                }else{
                    $Approval=ApprovalProcessList::query()->create($insert);
                }

                sendNotification($approval['designation_id'],$approval_process_name->approval_process_name);

            }
            $update=array('approval_status'=>2);
            VehicleMaintenanceForm::query()->where('id',$item->id)->update($update);
            return resp(1,'Vehicle Maintenance request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Vehicle Maintenance approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function attachVMVendor(Request $request, VehicleMaintenanceForm $vm)
    {

        if($vm->approval_status == 1){

            $request->validate([
                'vendors' => 'required|array|min:1',
                'vendors.*' => 'required',
            ]);

            try {
                DB::beginTransaction();
                $vendors=$request->vendors;
                unset($this->input['vendors']);
                $this->input['float_vm']=1;
                $atrUpdate=VehicleMaintenanceForm::query()->where('id',$vm->id)->update($this->input);
                if($atrUpdate){
                    foreach($vendors as $vendor_id){
                        VehicleMaintenanceVendor::query()->create(['vehicle_maintenance_id' => $vm->id, 'vendor_id' => $vendor_id]);
                    }
                }

                DB::commit();
                $vm=VehicleMaintenanceForm::query()->findOrFail($vm->id);
                return resp(1,'Successful!', $vm,Response::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();

                return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
            }
        }else{
            return resp(0,'VR not approved yet.', [],Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function acceptVMQuotation(Request $request, VendorVehMaintenanceQuot $vm)
    {

        if($vm){

            $request->validate([
                'quotation_status' => 'required',
            ]);

            try {

                VendorVehMaintenanceQuot::query()->where('vehicle_maintenance_id',$vm->vehicle_maintenance_id)->where('vendor_id',$vm->vendor_id)->update($this->input);

                DB::commit();

                $vm=VehicleMaintenanceForm::query()->findOrFail($vm->vehicle_maintenance_id);
                return resp(1,'Successful!', $vm,Response::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();

                return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
            }
        }else{
            return resp(0,'ATR not found.', [],Response::HTTP_EXPECTATION_FAILED);
        }
    }

}

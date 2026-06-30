<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AirTravelRequest;
use App\Models\Admin\AirTravelRequestDetail;
use App\Models\Admin\AtrVendor;
use App\Models\Admin\Fleet\Vehicle;
use App\Models\Admin\VendorAtrQuotation;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Program\Project\ProjectProfile;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItems;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AirTravelRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'atr_view',
            'manage_audit_procurement',
            'manage_employee_portal',
        ]);

        $data = AirTravelRequest::query()->with(['items.department',
                                                'department','project',
                                                'accommodation',
                                                'externalVisitor',
                                                'airlineCategory',
                                                'procurement',
                                                'procurementDetail.item',
                                                'airTravelReqVendor.vendorDetail',
                                                'vendorAtrQuotation.vendorDetail',
                                                'vendorAtrQuotation.airline',
                                                'vendorAtrQuotation.airlineCategory',
                                                'invoices.invoiceAtrDetail',
                                                'atrInvoice'
                                                ])->get();

        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'atr_create',
            'manage_employee_portal',
        ]);

        $request->validate([
            'traveler_name' => 'required|max:255',
            'purpose_of_visit' => 'required',
            'accommodation_id' => 'required|integer',
            'project_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'estimated_amount' => 'required',
            'date_time' => 'required',
//            'items' => 'required|array',
//            'items.*.date' => 'required',
//            'items.*.seat_name' => 'required',
//            'items.*.cnic' => 'required',
//            'items.*.traveller_name' => 'required',
//            //'items.*.department_id' => 'required',
//            'items.*.act_code' => 'required',
//            'items.*.donor_code' => 'required',
//            'items.*.purpose' => 'required',
//            'items.*.estimated_amount' => 'required',
            'procurement_id' => 'nullable|integer|exists:procurements,id',
            'procurement_detail_id' => 'nullable|integer|exists:procurement_details,id',
            'is_external_visitor' => 'required|boolean',
            'external_visitor_id' => 'nullable|required_if:is_external_visitor,1',
            'arrival_at' => 'required|string|max:255',
            'departure_from' => 'required|string|max:255',
        ]);
        try {
            DB::beginTransaction();
            $this->input['date_time']=date('Y-m-d h:i:s',strtotime($this->input['date_time']));
            $parent = AirTravelRequest::query()->create($this->input);
            if ($parent) {
                $insertTraveler=array(
                    'parent_id'=>$parent->id,
                    'date'=>date('Y-m-d',strtotime($parent->date_time)),
                    'traveller_name'=>$parent->traveler_name,
                    'department_id'=>$parent->department_id,
                    'purpose'=>$parent->purpose_of_visit,
                    'cnic'=>$parent->cnic,
                    'estimated_amount'=>$parent->estimated_amount,
                );
                AirTravelRequestDetail::query()->create($insertTraveler);
            }

            $totalAmount = AirTravelRequestDetail::query()->where('parent_id', $parent->id)->sum('estimated_amount');
            AirTravelRequest::query()->find($parent->id)?->update(['total_amount' => $totalAmount]);

            DB::commit();
            return resp(1, 'Successful!', $parent->load('items'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($parent_id)
    {
        $this->authorizeAny([
            'manage_audit_procurement',
            'atr_view',
            'manage_vendor_portal',
            'manage_employee_portal',
        ]);

        $data['parent'] =$parent= AirTravelRequest::query()->with(['invoices.invoiceAtrDetail','items.department','department','project','accommodation','externalVisitor','airlineCategory','airTravelReqVendor.vendorDetail','vendorAtrQuotation.vendorDetail','vendorAtrQuotation.airline','vendorAtrQuotation.airlineCategory','atrInvoice','procurement','procurementDetail.item'])->findOrFail($parent_id);

        $data['approval_request']=getNextApproval(18,auth()->user()->designation_id,$parent_id);
        $data['approval_request_status']=checkApprovalRequestStatus(18,$parent_id);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $parent_id)
    {
        $this->authorizeAny([
            'atr_update',
            'manage_employee_portal',
        ]);

        $request->validate([
            'project_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'traveler_name' => 'required|max:255',
            'purpose_of_visit' => 'required',
            'accommodation_id' => 'required|integer',
            'estimated_amount' => 'required',
            'date_time' => 'required',
            'procurement_id' => 'nullable|integer|exists:procurements,id',
            'procurement_detail_id' => 'nullable|integer|exists:procurement_details,id',

//            'items' => 'required|array',
//            'items.*.id' => 'required',
//            'items.*.date' => 'required',
//            'items.*.seat_name' => 'required',
//            'items.*.cnic' => 'required',
//            'items.*.traveller_name' => 'required',
//            //'items.*.department_id' => 'required',
//            'items.*.act_code' => 'required',
//            'items.*.donor_code' => 'required',
//            'items.*.purpose' => 'required',
//            'items.*.estimated_amount' => 'required',
            'is_external_visitor' => 'required|boolean',
            'external_visitor_id' => 'nullable|required_if:is_external_visitor,1',
            'arrival_at' => 'required|string|max:255',
            'departure_from' => 'required|string|max:255',
        ]);
        try {
            DB::beginTransaction();

//            $itemsList = $this->input['items'];
//            unset($this->input['items']);
            $this->input['date_time']=date('Y-m-d h:i:s',strtotime($this->input['date_time']));
            AirTravelRequest::query()->findOrFail($parent_id)->update($this->input);

            $totalAmount = AirTravelRequestDetail::query()->where('parent_id', $parent_id)->sum('estimated_amount');
            AirTravelRequest::query()->find($parent_id)?->update(['total_amount' => $totalAmount]);

            /*foreach ($itemsList as $key => $item) {
                $item['parent_id'] = $parent_id;
                AirTravelRequestDetail::query()->updateOrCreate(['id' => $item['id']], $item);
            }

            $totalAmount = AirTravelRequestDetail::query()->where('parent_id', $parent_id)->sum('estimated_amount');
            AirTravelRequest::query()->find($parent_id)->update(['total_amount' => $totalAmount]);
            */

            $airTravelRequest = AirTravelRequest::query()->with(['items'])->findOrFail($parent_id);

            DB::commit();
            return resp(1, 'Successful!', $airTravelRequest, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($parent_id)
    {
        $this->authorizeAny([
            'atr_delete'
        ]);

        $parent = AirTravelRequest::query()->with(['items'])->findOrFail($parent_id);
        $parent->items()->delete();
        $parent->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }


    public function getDropdowns()
    {
        $data['external_visitors'] = Type::getTypeValues('external-visitors');
        $data['airline_categories'] = Type::getTypeValues('airline-categories');
        $data['accommodations'] = Type::getTypeValues('accommodation');
        $data['departments'] = Type::getTypeValues('department-names');
        $data['vehicle_types'] = Type::getTypeValues('vehicle-types');
        $data['vehicles'] = Vehicle::with(['VehicleType', 'LogBooks' => function ($query) {
                $query->latest()->limit(1);
            }])->get();
        $data['projects'] = ProjectProfile::approvedProjects();
        $data['drivers'] = Employee::query()->where(['designation_id'=>28])->whereNotIn('employee_type', [14, 16, 17, 18])->get(['id','name']);
        $data['employees'] = Employee::query()->whereNotIn('employee_type', [14, 16, 17, 18])->get(['id','name','cnic', 'phone_no', 'department_id' ,'designation_id']);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
    public function sendAirTravelRequestForApproval(AirTravelRequest $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',18)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',18)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',18)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                if($approval_process_name->isFinancialApproval == 1){
                    if($approval->financialAmount < $item->total_amount  ){
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
            AirTravelRequest::query()->where('id',$item->id)->update($update);
            return resp(1,'Air Travel request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Air Travel approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function attachATRVendor(Request $request, AirTravelRequest $atr)
    {

        if($atr->approval_status == 1){

            $request->validate([
                'vendors' => 'required|array|min:1',
                'vendors.*' => 'required',
            ]);

            try {
                DB::beginTransaction();
                $vendors=$request->vendors;
                unset($this->input['vendors']);
                $this->input['float_atr']=1;
                $atrUpdate=AirTravelRequest::query()->where('id',$atr->id)->update($this->input);
                if($atrUpdate){
                    foreach($vendors as $vendor_id){
                        AtrVendor::query()->create(['atr_id' => $atr->id, 'vendor_id' => $vendor_id]);
                    }
                }

                DB::commit();
                $atr=AirTravelRequest::query()->findOrFail($atr->id);
                return resp(1,'Successful!', $atr,Response::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();

                return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
            }
        }else{
            return resp(0,'ATR not approved yet.', [],Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function acceptQuotation(Request $request, VendorAtrQuotation $atr)
    {
        if($atr){

            $request->validate([
                'quotation_status' => 'required',
            ]);

            try {

                VendorAtrQuotation::query()->where('id',$atr->id)->update($this->input);

                DB::commit();

                $atr=AirTravelRequest::query()->findOrFail($atr->atr_id);
                return resp(1,'Successful!', $atr->load('vendorAtrQuotation'),Response::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();

                return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
            }
        }else{
            return resp(0,'ATR not found.', [],Response::HTTP_EXPECTATION_FAILED);
        }
    }
}

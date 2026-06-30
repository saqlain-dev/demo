<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TenderResource;
use App\Models\Admin\AirTravelRequest;
use App\Models\Admin\AtrVendor;
use App\Models\Admin\Fleet\VehicleRequest;
use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\Tender;
use App\Models\Admin\VehicleMaintenanceForm;
use App\Models\Admin\VehicleMaintenanceInvoiceDocument;
use App\Models\Admin\VehicleMaintenanceVendor;
use App\Models\Admin\VehicleRequestInvoiceDocument;
use App\Models\Admin\VehicleRequestVendor;
use App\Models\AtrVendorDocument;
use App\Models\Documents;
use App\Models\Employee;
use App\Models\Type;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorQuotationDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'log_book_view'
        ]);

        $data['vendor_list']= Vendor::with(['Supplier','serviceProvider'])->get();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required',
            'email_address' =>'required|email|unique:users,email',
        ]);
        //dd($request->all());
       $vendor= Vendor::query()->create($this->input);
       /*if($vendor){
           $this->input['name']=$request->company_name;
           $this->input['email']=$request->email_address;
           $this->input['password']=bcrypt($request->password);
           $this->input['status']=1;
           $this->input['vendor_id']=$vendor->id;
           $this->input['user_type']=2;
           $user=User::query()->create( $this->input);
       }*/
        return resp(1,'Successful!', $vendor,Response::HTTP_CREATED);
    }

    public function saveContactPerson(Request $request,Vendor $vendor)
    {

        $request->validate([
            'contact_person_1' => 'required',
            'telephone_1' => 'required',
            'cell_phone_1' => 'required',
        ]);
        try {

            DB::beginTransaction();
            Vendor::query()->where('id',$vendor->id)->update($this->input);
            DB::commit();
            $vendor=Vendor::query()->findOrFail($vendor->id);
            return resp('1', 'Contact person detail updated Successfully!', $vendor, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function saveVendorAddress(Request $request,Vendor $vendor)
    {

        $request->validate([
            'address_1' => 'required',
        ]);
        try {

            DB::beginTransaction();
            Vendor::query()->where('id',$vendor->id)->update($this->input);
            DB::commit();
            $vendor=Vendor::query()->findOrFail($vendor->id);
            return resp('1', 'Address updated Successfully!', $vendor, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendor $vendor)
    {
        $this->authorizeAny([
            'vendors_view',
            'manage_vendor_portal',
        ]);

        $vendor= Vendor::with(['Supplier','serviceProvider'])->findOrFail($vendor->id);
        return resp(1,'Successful!', $vendor,Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vendor $vendor)
    {
        $this->authorizeAny([
            'vendors_udpate',
            'manage_vendor_portal',
        ]);

        Vendor::query()->where('id', $vendor->id)->update($this->input);

        $updateVendor=Vendor::query()->findOrFail($vendor->id);
        return resp(1,'Successful!', $updateVendor,Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        $this->authorizeAny([
            'vendors_delete'
        ]);

        $vendor->user()->delete();
        $vendor->delete();
        $message="Vendor Deleted Successfully";
        return resp(1,'Successful!', $message,Response::HTTP_CREATED);
    }

    public function getVendorDropDown(){
        $data['supplier_list']= Type::getTypeValues('suppliers-list');
        $data['service_provider']= Type::getTypeValues('service-providers');
        $data['airline_category']= Type::getTypeValues('airline-categories');
        $data['airlin_list']= Type::getTypeValues('airline-names');
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    public function rfqDetailByID($id)
    {
        $data = Vendor::query()
            ->with([
                'rfqs' => function ($query) use ($id){
                    $query->where('expiry_date', '>=', now()->format('Y-m-d h:i:s'))
                        ->where('purchase_request_rfqs.id', $id)
                        ->where('float_rfq', 1);
                },
                'rfqs.items.itemDetail',
                'rfqs.purchase_request',
                'rfqs.rfType',
                'rfqs.quotationType',
                'rfqs.vendor_quotations' => function ($query) {

                    $query->where('vendor_id', auth()->user()->vendor_id);

                },
                'rfqs.vendor_quotations.quotationItems',
                'rfqs.rfqBiddingDocuments' => function ($query) {

                    $query->where('vendor_id', auth()->user()->vendor_id);
                },
                'rfqs.rfqBiddingDocuments.documentDetail'
            ])
            ->find(auth()->user()->vendor_id);

        if($data?->rfqs){
            foreach($data->rfqs as  &$rfq){
                $documetsDetails=Documents::query()->whereIn('id',$rfq['documents_ids'])->get();
                $rfq['documentDetails'] = $documetsDetails;
            }

        }


        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }
    public function vendorRfqList()
    {
        $this->authorizeAny([
            'manage_vendor_portal',
        ]);
        
        $data = Vendor::query()
            ->with([
                'rfqs' => function ($query) {
                    $query->where('expiry_date', '>=', now()->format('Y-m-d h:i:s'))
                        ->where('float_rfq', 1);
                },
                'rfqs.items.itemDetail',
                'rfqs.items.disposeRequestDetail.itemVariant',
                'rfqs.purchase_request',
                'rfqs.disposeRequest.disposeItems',
                'rfqs.rfType',
                'rfqs.quotationType',
                'rfqs.vendor_quotations' => function ($query) {

                    $query->where('vendor_id', auth()->user()->vendor_id);

                },
                'rfqs.vendor_quotations.quotationItems',
                'rfqs.rfqBiddingDocuments' => function ($query) {

                    $query->where('vendor_id', auth()->user()->vendor_id);
                },
                'rfqs.rfqBiddingDocuments.documentDetail'
            ])
            ->find(auth()->user()->vendor_id);

        if($data?->rfqs){
            foreach($data->rfqs as  &$rfq){
                $documetsDetails=Documents::query()->whereIn('id',$rfq['documents_ids'])->get();
                $rfq['documentDetails'] = $documetsDetails;
            }

        }
        $data['procurement_manager_list'] = Employee::query()
            ->with('designation')
            ->orWhereHas('designation', function ($query) {
                $query->whereIn('name', array('Procurement Officer','Manager Procurement'));
            })
            ->get();

        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }
    public function updateVProfilePicture(Request $request, Vendor $vendor)
    {

        $request->validate([
            'profile_pic' => 'required|file',
        ]);
        try {

            DB::beginTransaction();
            if($request->file('profile_pic')){
                $responce=$this->uploadProfile($request,'VendorProfile','profile_pic');
                $this->input['profile_pic']=$responce;
            }
            Vendor::query()->where('id', $vendor->id)->update( $this->input);
            DB::commit();
            $vendor=Vendor::query()->findOrFail($vendor->id);
            return resp(1,'Successful!', $vendor,Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }



    }
    public function uploadProfile($request,$folder,$name){

        $file = $request->file($name);
        $path = 'uploads/media/' . $folder;
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (!file_exists('uploads/media')) {
            mkdir('uploads/media', 0777, true);
        }
        if (!file_exists('uploads/media/' . $folder)) {
            mkdir('uploads/media/' . $folder, 0777, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        $file_name = str_replace(' ', '_', $filename);
        $file->move($path, $file_name);
        return $path.'/'.$file_name;

    }
    public function tenderList()
    {
        $this->authorizeAny([
            'manage_vendor_portal',
        ]);
        
        $data=Tender::query()
            ->whereHas('vendors', function ($query) {
                $query->where('vendor_id', auth()->user()->vendor_id);
            })->with('vendors','vendor_quotations','purchaseRequest','tenderNature','createdBy', 'updatedBy', 'tenderDetails.itemDetail')->where('float_tender',1)->where('approval_status',1)->where('expiry_date ','>=', now()->format('Y-m-d h:i:s'))->get();

        $data['tenderList'] =  TenderResource::collection($data);


        $data['procurement_manager_list'] = Employee::query()
            ->with('designation')
            ->orWhereHas('designation', function ($query) {
                $query->whereIn('name', array('Procurement Officer','Manager Procurement'));
            })
            ->get();
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }
    public function expireTenderList()
    {
        $data['tenderList'] = Tender::query()
            ->with('tenderDetails')->where('approval_status',1)->where('closing_date ','<', now()->format('Y-m-d h:i:s'))->get();


        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    public function viewTender(Tender $tender)
    {
        $data['tender_detail']=Tender::query()->with('tenderDetails')->findOrFail($tender->id);
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }
    public function expireRfqList()
    {
        $data = Vendor::query()
            ->with(['rfqs' => function ($query) {
                $query->where('expiry_date ','<', now()->format('Y-m-d h:i:s'));
                $query->where('float_rfq ', 1);

            }, 'rfqs.items','rfqs.rfType','rfqs.quotationType'])
            ->find(auth()->user()->vendor_id);



        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }
    public function appliedRfqList()
    {
        $this->authorizeAny([
            'manage_vendor_portal',
        ]);
     
        $rfqDetail= PurchaseRequestRfq::query()
            ->with(['items.itemDetail','purchase_request','disposeRequest', 'vendor_quotations' => function ($query) {
                $query->where('vendor_id', auth()->user()->vendor_id);
                $query->where('apply_status', 1);
            }, 'vendor_quotations.quotationItems.item','vendor_quotations.awardQuotation.awardPo','vendor_quotations.awardQuotation.awardWo','rfType',
                'quotationType'])
            ->get();

        foreach($rfqDetail as $key => $detail){

            if($detail->vendor_quotations->count() == 0){
                unset($rfqDetail[$key]);
            }else{

                $rfqDetail[$key]->vendor_quotations[0]->total_quotation_amount=decode(@$detail->vendor_quotations[0]->total_quotation_amount);
                $documetsDetails=VendorQuotationDocument::query()->with(['documentDetail'])->where('rfq_id',$detail->id)->whereIn('document_id',$detail['documents_ids'])->get();
                $rfqDetail[$key]['documentDetails'] = $documetsDetails;

            }

        }
        $data['rfqDetail']=array_values($rfqDetail->toArray());
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }
    public function appliedTenderList()
    {
        $this->authorizeAny([
            'manage_vendor_portal',
        ]);
        
        $tenderDetail= Tender::query()
            ->with(['tenderDetails.itemDetail','purchaseRequest', 'vendor_quotations' => function ($query) {
                $query->where('vendor_id', auth()->user()->vendor_id);
                $query->where('apply_status', 1);
            }, 'vendor_quotations.quotationItems.item','vendor_quotations.awardQuotation.awardPo','vendor_quotations.awardQuotation.awardWo','vendor_quotations.awardQuotation.awardCc'])
            ->get();
        foreach($tenderDetail as $key => $detail){

            if($detail->vendor_quotations->count() == 0){
                unset($tenderDetail[$key]);
            }else{

                $tenderDetail[$key]->vendor_quotations[0]->total_quotation_amount=decode(@$detail->vendor_quotations[0]->total_quotation_amount);

            }

        }
        $data['tender_list']=array_values($tenderDetail->toArray());
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    public function updateVendorStatus(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required',
            'profile_status' =>'required',
        ]);
        $vendor=Vendor::query()->where('id',$request->vendor_id)->update(array('profile_status'=>$request->profile_status));
        return resp(1,'Successful!', $vendor,Response::HTTP_OK);
    }

    public function vendorATRList()
    {
        $this->authorizeAny([
            'manage_vendor_portal',
        ]);

        $data['atr_list'] = AirTravelRequest::query()
            ->with([
                'airTravelReqVendor' => function ($query) {
                    $query->where('vendor_id', auth()->user()->vendor_id);
                },
                'project'
            ])
            ->where('float_atr', 1)
            ->get()
            ->filter(function ($atr) {
                return $atr->airTravelReqVendor->isNotEmpty();
            })->values();


        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    public function appliedAtr(Request $request)
    {
        $request->validate([
            'atr_id' => 'required',
            'vendor_id' => 'required',
        ]);

        try {


            $atrVendor=AtrVendor::query()->where('atr_id',$request->atr_id)->where('vendor_id',$request->vendor_id)->update(array('isApplied'=>1));

            DB::commit();
            return resp(1,'Successful!', $atrVendor,Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function sendAirTicketInvoice(Request $request)
    {
        $request->validate([
            'atr_id' => 'required',
            'vendor_id' => 'required',
            'date' => 'required'
        ]);

        try {

           $atrInvoice= AtrVendorDocument::query()->where('atr_id',$request->atr_id)->where('vendor_id',$request->vendor_id)->first();

            if(!$atrInvoice){

                if($request->hasFile('invoice')) {

                    $responce = $this->saveInvoiceFile($request, 'ATRDocument','invoice');

                    if ($responce) {
                        $this->input['invoice'] = $responce;
                    }
                }else{
                    unset($this->input['invoice']);
                }
                if($request->hasFile('air_document')) {

                    $responce = $this->saveInvoiceFile($request, 'ATRDocument','air_document');

                    if ($responce) {
                        $this->input['air_document'] = $responce;
                    }
                }else{
                    unset($this->input['air_document']);
                }
                $this->input['date']=date('Y-m-d',strtotime($request->date));
                $atrVendorDocument=AtrVendorDocument::query()->create($this->input);

                DB::commit();
                return resp(1,'Successful!', $atrVendorDocument,Response::HTTP_OK);
            }else{

                if($request->hasFile('invoice')) {

                    $responce = $this->saveInvoiceFile($request, 'ATRDocument','invoice');

                    if ($responce) {
                        $this->input['invoice'] = $responce;
                    }
                }else{
                    unset($this->input['invoice']);
                }
                if($request->hasFile('air_document')) {

                    $responce = $this->saveInvoiceFile($request, 'ATRDocument','air_document');

                    if ($responce) {
                        $this->input['air_document'] = $responce;
                    }
                }else{
                    unset($this->input['air_document']);
                }
                $this->input['date']=date('Y-m-d',strtotime($request->date));
                $atrVendorDocument=AtrVendorDocument::query()->where('id',$atrInvoice->id)->update($this->input);
                $atrInvoice->refresh();

                DB::commit();
                return resp(1,'Updated Successful!', $atrInvoice,Response::HTTP_OK);
            }


        } catch (\Exception $e) {
            DB::rollBack();

            return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function sendVRTicketInvoice(Request $request)
    {
        $request->validate([
            'vehicle_req_id' => 'required',
            'vendor_id' => 'required',
            'date' => 'required'
        ]);

        try {

           $vrInvoice= VehicleRequestInvoiceDocument::query()->where('vehicle_req_id',$request->vehicle_req_id)->where('vendor_id',$request->vendor_id)->first();

            if(!$vrInvoice){

                if($request->hasFile('invoice')) {

                    $responce = $this->saveInvoiceFile($request, 'VRDocument','invoice');

                    if ($responce) {
                        $this->input['invoice'] = $responce;
                    }
                }else{
                    unset($this->input['invoice']);
                }
                if($request->hasFile('vr_document')) {

                    $responce = $this->saveInvoiceFile($request, 'VRDocument','vr_document');

                    if ($responce) {
                        $this->input['vr_document'] = $responce;
                    }
                }else{
                    unset($this->input['vr_document']);
                }
                $this->input['date']=date('Y-m-d',strtotime($request->date));
                $atrVendorDocument=VehicleRequestInvoiceDocument::query()->create($this->input);

                DB::commit();
                return resp(1,'Successful!', $atrVendorDocument,Response::HTTP_OK);
            }else{

                if($request->hasFile('invoice')) {

                    $responce = $this->saveInvoiceFile($request, 'VRDocument','invoice');

                    if ($responce) {
                        $this->input['invoice'] = $responce;
                    }
                }else{
                    unset($this->input['invoice']);
                }
                if($request->hasFile('vr_document')) {

                    $responce = $this->saveInvoiceFile($request, 'VRDocument','vr_document');

                    if ($responce) {
                        $this->input['vr_document'] = $responce;
                    }
                }else{
                    unset($this->input['vr_document']);
                }
                $this->input['date']=date('Y-m-d',strtotime($request->date));

                $vrVendorDocument=VehicleRequestInvoiceDocument::query()->where('id',$vrInvoice->id)->update($this->input);
                $vrInvoice->refresh();

                DB::commit();
                return resp(1,'Updated Successful!', $vrVendorDocument,Response::HTTP_OK);
            }


        } catch (\Exception $e) {
            DB::rollBack();

            return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function sendVMInvoice(Request $request)
    {
        $request->validate([
            'vehicle_maintenance_id' => 'required',
            'vendor_id' => 'required',
            'date' => 'required'
        ]);

        try {

           $vmInvoice= VehicleMaintenanceInvoiceDocument::query()->where('vehicle_maintenance_id',$request->vehicle_maintenance_id)->where('vendor_id',$request->vendor_id)->first();

            if(!$vmInvoice){

                if($request->hasFile('invoice')) {

                    $responce = $this->saveInvoiceFile($request, 'VMDocument','invoice');

                    if ($responce) {
                        $this->input['invoice'] = $responce;
                    }
                }else{
                    unset($this->input['invoice']);
                }
                if($request->hasFile('vm_document')) {

                    $responce = $this->saveInvoiceFile($request, 'VMDocument','vm_document');

                    if ($responce) {
                        $this->input['vm_document'] = $responce;
                    }
                }else{
                    unset($this->input['vm_document']);
                }
                $this->input['date']=date('Y-m-d',strtotime($request->date));
                $atrVendorDocument=VehicleMaintenanceInvoiceDocument::query()->create($this->input);

                DB::commit();
                return resp(1,'Successful!', $atrVendorDocument,Response::HTTP_OK);
            }else{

                if($request->hasFile('invoice')) {

                    $responce = $this->saveInvoiceFile($request, 'VMDocument','invoice');

                    if ($responce) {
                        $this->input['invoice'] = $responce;
                    }
                }else{
                    unset($this->input['invoice']);
                }
                if($request->hasFile('vm_document')) {

                    $responce = $this->saveInvoiceFile($request, 'VMDocument','vm_document');

                    if ($responce) {
                        $this->input['vm_document'] = $responce;
                    }
                }else{
                    unset($this->input['vm_document']);
                }
                $this->input['date']=date('Y-m-d',strtotime($request->date));

                $vmVendorDocument=VehicleMaintenanceInvoiceDocument::query()->where('id',$vmInvoice->id)->update($this->input);
                $vmInvoice->refresh();

                DB::commit();
                return resp(1,'Updated Successful!', $vmVendorDocument,Response::HTTP_OK);
            }


        } catch (\Exception $e) {
            DB::rollBack();

            return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function saveInvoiceFile($request,$folder,$name){

        $file = $request->file($name);

        $path = 'uploads/media/' . $folder;
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (!file_exists('uploads/media')) {
            mkdir('uploads/media', 0777, true);
        }
        if (!file_exists('uploads/media/' . $folder)) {
            mkdir('uploads/media/' . $folder, 0777, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        $file_name = str_replace(' ', '_', $filename);
        $file->move($path, $file_name);
        return $path.'/'.$file_name;

    }

    public function vendorVRList()
    {
        $this->authorizeAny([
            'manage_vendor_portal',
        ]);
        
        $data['vr_list'] = VehicleRequest::query()
            ->with([
                'vehicleReqVendor' => function ($query) {
                    $query->where('vendor_id', auth()->user()->vendor_id);
                },
                'VehicleId'
            ])
            ->where('float_vr', 1)
            ->get()
            ->filter(function ($vr) {
                return $vr->vehicleReqVendor->isNotEmpty();
            })->values();


        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    public function vendorVMList()
    {
        $this->authorizeAny([
            'manage_vendor_portal',
        ]);
        
        $data['vm_list'] = VehicleMaintenanceForm::query()
            ->with([
                'vehicleMaintenanceVendor' => function ($query) {
                    $query->where('vendor_id', auth()->user()->vendor_id);
                },
                'vehicle'
            ])
            ->where('float_vm', 1)
            ->get()
            ->filter(function ($vm) {
                return $vm->vehicleMaintenanceVendor->isNotEmpty();
            })->values();


        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    public function appliedVR(Request $request)
    {
        $request->validate([
            'vehicle_req_id' => 'required',
            'vendor_id' => 'required',
        ]);

        try {


            $vrVendor=VehicleRequestVendor::query()->where('vehicle_req_id',$request->vehicle_req_id)->where('vendor_id',$request->vendor_id)->update(array('isApplied'=>1));

            DB::commit();
            return resp(1,'Successful!', $vrVendor,Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function appliedVM(Request $request)
    {
        $request->validate([
            'vehicle_maintenance_id' => 'required',
            'vendor_id' => 'required',
        ]);

        try {


            $vmVendor=VehicleMaintenanceVendor::query()->where('vehicle_maintenance_id',$request->vehicle_maintenance_id)->where('vendor_id',$request->vendor_id)->update(array('isApplied'=>1));

            DB::commit();
            return resp(1,'Successful!', $vmVendor,Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
        }
    }
}



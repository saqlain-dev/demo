<?php

namespace App\Http\Controllers\Api\V1\Admin\PurchaseRequest;

use App\Http\Controllers\Controller;
use App\Models\Admin\PrRfqVendor;
use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\PurchaseRequestRfqDetail;
use App\Models\Admin\RfqType;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\DisposeRequest;
use App\Models\Documents;
use App\Models\PurchaseRequest;
use App\Models\Type;
use App\Models\TypeValue;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PurchaseRequestRfqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'rfq_view',
             'auction_rfq_view',
             'purchase-request-rfqs',
            'manage_audit_procurement',
        ]);

        $data['purchase_request_Rfq'] =$purchase_request_Rfq= PurchaseRequestRfq::with(['rfqVendors','items.itemDetail','quotationType','rfType','purchase_request', 'disposeRequest','createdBy'])->orderByDesc('id')->get();
        $data['draft']=$purchase_request_Rfq->where('status',4)->count();
        $data['pending']=$purchase_request_Rfq->where('status',2)->count();
        $data['approved']=$purchase_request_Rfq->where('status',1)->count();
        $data['reject']=$purchase_request_Rfq->where('status',3)->count();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
    public function rfqDropdown()
    {
        $data['qTypes']=RfqType::all();
        $data['rfTypes']=Type::getTypeValues('rf-types');
        $data['purchase_request']=getRemainingPr(PurchaseRequest::getApprovedPRs());
        $data['vendors']= Vendor::all();
        $data['documents'] = Documents::getTenderDocs();
        $data['dispose_requests'] = DisposeRequest::all();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function remainingPr()
    {

        $data['approved_purchase_request']=getRemainingPr(PurchaseRequest::getApprovedPRs());
       // $data['purchase_request']=PurchaseRequest::getApprovedPRs();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function attachRFQVendor(Request $request, PurchaseRequestRfq $purchaseRequestRfq)
    {

        if($purchaseRequestRfq->status == 1){

            $request->validate([
                'vendors' => 'required|array',
                'expiry_date' => 'required|date|after_or_equal:today',
            ]);

            try {
                DB::beginTransaction();
                $vendors=$request->vendors;
                unset($this->input['vendors']);
                $this->input['expiry_date']=date('Y-m-d H:i:s',strtotime($request->expiry_date));
                $this->input['float_rfq']=1;
                $this->input['opening_date']= date('Y-m-d');

                $qrfUpdate=PurchaseRequestRfq::query()->where('id',$purchaseRequestRfq->id)->update($this->input);
                if($qrfUpdate){
                    foreach($vendors as $vendor_id){
                        PrRfqVendor::query()->create(['purchase_request_rfq_id' => $purchaseRequestRfq->id, 'vendor_id' => $vendor_id]);
                    }
                }

                DB::commit();
                $purchaseRequestRfq=PurchaseRequestRfq::query()->findOrFail($purchaseRequestRfq->id);
                return resp(1,'Successful!', $purchaseRequestRfq,Response::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();

                return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
            }
        }else{
            return resp(0,'RFQ not approved yet.', [],Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function store(Request $request)
    {
        $this->authorizeAny([
            'rfq_create',
            'auction_rfq_create'
        ]);

        $request->validate([
            'details' => 'required',
            'terms_conditions' => 'required',
            //'documents_ids' => 'required',
            'attachment' => 'nullable',
        ]);
        try {
            DB::beginTransaction();
            $path = null;
            if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
                $path= $this->saveRFQFile($request,'rfq');   
               $this->input['attachment']= $path;
            }
            $item = PurchaseRequestRfq::query()->create($this->input);   
            DB::commit();
            $data = $item->load(['vendors','items']);
            return resp(1,'Successful!', $data,Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
        }


    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->authorizeAny([
            'rfq_view',
            'auction_rfq_view',
            'purchase-request-rfqs',
            'manage_audit_procurement',
            'manage_employee_portal',
        ]);

        $data['item'] = PurchaseRequestRfq::query()->with(['rfqVendors','rfqMinutesOfMeeting','rfqWaiver.approvers','items.itemDetail', 'items.itemDetail.itemCategory', 'items.itemDetail.subCategory', 'items.itemDetail.itemUnit', 'items.itemDetail.itemType', 'quotationType','rfType','purchase_request.department','disposeRequest.department','createdBy'])->findOrFail($id);
        $data['approval_request']=getNextApproval(9,auth()->user()->designation_id,$id);
        $data['approval_request_status']=checkApprovalRequestStatus(9,$id);
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseRequestRfq $PurchaseRequestRfq)
    {
        $this->authorizeAny([
            'rfq_update',
            'auction_rfq_update',
        ]);
        
        $request->validate([
            'details' => 'required',
            'terms_conditions' => 'required',
            //'documents_ids' => 'required',
           // 'department_id' => 'required',
            'attachment' => 'nullable|file',
        ]);
        try {
            DB::beginTransaction();

            if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
                $path= $this->saveRFQFile($request,'rfq');  
                $this->input['attachment']= $path;
            }
            $PurchaseRequestRfq->update($this->input);

            DB::commit();
            $data = $PurchaseRequestRfq->load('vendors');
            return resp(1,'Successful!', $data,Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseRequestRfq $PurchaseRequestRfq)
    {
        $this->authorizeAny([
            'rfq_delete',
            'auction_rfq_delete',
        ]);

        $PurchaseRequestRfq->vendors()->delete();
        $PurchaseRequestRfq->items()->delete();
        $PurchaseRequestRfq->delete();
        return resp(1,'Successful!', ['meg' => 'Record deleted successfully!'],Response::HTTP_OK);
    }

    public function addPrItem(Request $request)
    {
        $request->validate([
        //            'purchase_request_rfq_id' => 'required|integer',
        //            'purchase_request_detail_id' => 'required|integer',
            'description' => 'required',
            'unit_of_measurement' => 'required',
            'required_quantity' => 'required',
            'unit_price' => 'required',
            'amount' => 'required',
            // 'version' => 'required',
            // 'pages' => 'required',
        ]);

        $data = PurchaseRequestRfqDetail::query()->create($request->all());
        if ($request->filled('purchase_request_rfq_id')){
            $totalAmount = PurchaseRequestRfqDetail::query()->where('purchase_request_rfq_id', $request->purchase_request_rfq_id)->sum('amount');
            PurchaseRequestRfq::query()->findOrFail($request->purchase_request_rfq_id)->update(['sub_total' => $totalAmount]);
        }

        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    //Add Multiple items to rfq
    public function addMultiplePrItem(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.description' => 'required',
            'items.*.unit_of_measurement' => 'required',
            'items.*.required_quantity' => 'nullable',
            'items.*.unit_price' => 'required',
            'items.*.amount' => 'required',
            'items.*.purchase_request_detail_id' => 'nullable|integer',
        ]);

        $createdItems = [];

        foreach ($request->items as $item) {
            $item['purchase_request_rfq_id'] = $request->purchase_request_rfq_id;
            $createdItems[] = PurchaseRequestRfqDetail::create($item);
        }

        $totalAmount = PurchaseRequestRfqDetail::where('purchase_request_rfq_id', $request->purchase_request_rfq_id)->sum('amount');
        PurchaseRequestRfq::findOrFail($request->purchase_request_rfq_id)->update(['sub_total' => $totalAmount]);

        return resp(1, 'Items added successfully!', $createdItems, Response::HTTP_CREATED);
    }

    public function updatePrItem(Request $request)
    {
        $request->validate([
            "id" => 'required|exists:purchase_request_rfq_details,id',
            //            'purchase_request_rfq_id' => 'required|integer',
            //            'purchase_request_detail_id' => 'required|integer',
            'description' => 'required',
            'unit_of_measurement' => 'required',
            'required_quantity' => 'required',
            'unit_price' => 'required',
            'amount' => 'required',
            //'version' => 'required',
            //'pages' => 'required',
        ]);

        $data = PurchaseRequestRfqDetail::query()->findOrFail($request->id)->update($request->all());
        if ($request->filled('purchase_request_rfq_id')) {
            $totalAmount = PurchaseRequestRfqDetail::query()->where('purchase_request_rfq_id', $request->purchase_request_rfq_id)->sum('amount');
            PurchaseRequestRfq::query()->findOrFail($request->purchase_request_rfq_id)->update(['sub_total' => $totalAmount]);
        }
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    public function deletePrItem($id)
    {
        $item = PurchaseRequestRfqDetail::query()->findOrFail($id)->delete();
        return resp(1,'Successful!', $item ,Response::HTTP_OK);

    }
    public function sendRFQForApproval(PurchaseRequestRfq $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',9)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',9)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',9)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                if($approval_process_name->isFinancialApproval == 1){
                    if($approval->financialAmount < $item->sub_total  ){
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
            $update=array('status'=>2);
            PurchaseRequestRfq::query()->where('id',$item->id)->update($update);
            return resp(1,'RFQ send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'RFQ approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function getRfqsItems($id)
    {
        $data['record'] = PurchaseRequestRfq::query()->with(['items'])->findOrFail($id);
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    public function updateRfqExpiry(Request $request,PurchaseRequestRfq $item)
    {


        try {

            DB::beginTransaction();
            $request->validate([
                'expiry_date' => 'required',
            ]);
            $this->input['expiry_date']=date('Y-m-d H:i:s',strtotime($request->expiry_date));

            PurchaseRequestRfq::query()->find($item->id)->update($this->input);

            $item->refresh();
            DB::commit();
            return resp('1', 'RFQ expiry updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage() .' on line :: '.$e->getLine() , null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    
    public function saveRFQFile($request,$folder){
        $file = $request->file('attachment');
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

}

<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TenderResource;
use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\PurchaseRequestRfqDetail;
use App\Models\Admin\Tender;
use App\Models\Admin\TenderDetail;
use App\Models\Admin\TenderVendor;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Documents;
use App\Models\HR\Policy;
use App\Models\PurchaseRequest;
use App\Models\Type;
use App\Models\TypeValue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TenderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'tender_view',
            'manage_audit_procurement',
        ]);

        $data = TenderResource::collection(Tender::query()->with('purchaseRequest','tenderNature','createdBy', 'updatedBy', 'tenderDetails.itemDetail')->get());
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'tender_create'
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'nature_id' => 'required|integer',
            'expiry_date' => 'nullable|date',
            'term_conditions' => 'nullable|string',
            'documents_ids' => 'nullable|string',
            'purchase_request_id' => 'required|exists:purchase_requests,id',
        ]);
        $item = Tender::query()->create($request->all());
        return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->authorizeAny([
            'tender_view',
            'manage_audit_procurement',
        ]);

        $data['tenderDetail'] = new TenderResource(Tender::query()->with('vendors.vendor','tenderMinutesOfMeeting','purchaseRequest','tenderNature','createdBy', 'updatedBy', 'tenderDetails.itemDetail')->findOrFail($id));
        $data['approval_request']=getNextApproval(11,auth()->user()->designation_id,$id);
        $data['approval_request_status']=checkApprovalRequestStatus(11,$id);

        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tender $tender)
    {
        $this->authorizeAny([
            'tender_update'
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'nature_id' => 'required|integer',
            'expiry_date' => 'nullable|date',
            'term_conditions' => 'nullable|string',
            'documents_ids' => 'nullable|string',
            'purchase_request_id' => 'required|exists:purchase_requests,id',
        ]);
        $item = Tender::query()->update($request->all());
        return resp(1, 'Successful!', $item, Response::HTTP_OK);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tender $tender)
    {
        $this->authorizeAny([
            'tender_delete'
        ]);

        $tender->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    public function getDropdowns()
    {
        // $data = getUserIdsByDesignation(1);
        $data['tender_nature_types'] = Type::getTypeValues('tender-nature');
        $data['tender_required_documents'] = Documents::getTenderDocs();
        $data['purchase_request']=getRemainingPr(PurchaseRequest::getApprovedPRs());
        return resp(1, 'Successful!', $data, Response::HTTP_OK);

    }

    public function addItem(Request $request)
    {
        $this->authorizeAny([
            'tender_create'
        ]);

        $request->validate([
            'tender_id' => 'required|integer|exists:tenders,id',
            'purchase_request_id' => 'required|integer',
            'purchase_request_detail_id' => 'required|integer',
            'description' => 'required',
            'unit_of_measurement' => 'required',
            'required_quantity' => 'required',
            'unit_price' => 'required',
            'amount' => 'required',
            //'version' => 'required',
            //'pages' => 'required',
            'item_id' => 'required|exists:items,id',
        ]);

        $data = TenderDetail::query()->create($request->all());
        $totalAmount = TenderDetail::query()->where('tender_id', $request->tender_id)->sum('amount');
        Tender::query()->findOrFail($request->tender_id)->update(['sub_total' => $totalAmount]);

        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    public function updateItem(Request $request)
    {
        $this->authorizeAny([
            'tender_update'
        ]);

        $request->validate([
            "id" => 'required',
            'tender_id' => 'required|integer|exists:tenders,id',
            'purchase_request_id' => 'required|integer',
            'purchase_request_detail_id' => 'required|integer',
            'description' => 'required',
            'unit_of_measurement' => 'required',
            'required_quantity' => 'required',
            'unit_price' => 'required',
            'amount' => 'required',
            'version' => 'required',
            'pages' => 'required',
            'item_id' => 'required|exists:items,id',
        ]);

        $totalAmount = TenderDetail::query()->where('tender_id', $request->tender_id)->sum('amount');
        Tender::query()->findOrFail($request->tender_id)->update(['sub_total' => $totalAmount]);

        $data = TenderDetail::query()->find($request->id)->update($request->all());
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    public function deleteItem($id)
    {
        $this->authorizeAny([
            'tender_delete'
        ]);

        $item = TenderDetail::query()->findOrFail($id)->delete();
        return resp(1,'Successful!', $item ,Response::HTTP_OK);

    }

    public function sendTenderForApproval(Tender $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',11)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',11)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            Tender::query()->where('id',$item->id)->update($update);
            return resp(1,'Tender send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Tender approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
    public function floatTender(Request $request,Tender $tender)
    {

        if($tender->approval_status == 1){
            $request->validate([
                'vendors' => 'required|array',
                'expiry_date' => 'nullable|date|after_or_equal:today',
            ]);

            try {
                DB::beginTransaction();
                $this->input['float_tender']=1;
                $vendors=$request->vendors;
                unset($this->input['vendors']);
                $tenderUpdate=Tender::query()->where('id',$tender->id)->update($this->input);
                if($tenderUpdate){
                    foreach($vendors as $vendor_id){
                        TenderVendor::query()->create(['tender_id' => $tender->id, 'vendor_id' => $vendor_id]);
                    }
                }
                DB::commit();
                $tender=Tender::query()->findOrFail($tender->id);
                return resp(1,'Successful!', $tender,Response::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();

                return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
            }
        }else{
            return resp(0,'Tender not approved yet.', [],Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function uploadAttachment(Request $request)
    {
        $request->validate([
            'tender_id' => 'required',
            'pack_document' => 'required|file',
        ]);
        $tender = Tender::query()->findOrFail($request->tender_id);

        $attachmentPath = $tender->pack_document;
        if ($attachmentPath) {
            Storage::disk('public')->delete($tender->pack_document);
        }

        if ($request->hasFile('pack_document')){
           // $extension = $request->file('pack_document')->getClientOriginalExtension();
           // $attachmentPath = $request->file('pack_document')->storeAs('images/tender', time() . '_pack_document.' . $extension, 'public');
            $responce=$this->uploadDocument($request,'Tender','pack_document');
            //$this->input['pack_document']=$responce;
        }

        $item = $tender->update([
            'pack_document' => $responce,
        ]);

        return resp(1, 'Successful!', $tender, Response::HTTP_OK);
    }
    public function uploadDocument($request,$folder,$name){

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

    public function updateTenderExpiry(Request $request,Tender $tender)
    {


        try {

            DB::beginTransaction();
            $request->validate([
                'expiry_date' => 'required',
            ]);
            $this->input['expiry_date']=date('Y-m-d H:i:s',strtotime($request->expiry_date));

            Tender::query()->find($tender->id)->update($this->input);

            $tender->refresh();
            DB::commit();
            return resp('1', 'Tender expiry updated Successfully!', $tender, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage() .' on line :: '.$e->getLine() , null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

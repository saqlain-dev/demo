<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\Tender;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Models\VendorQuotationDetail;
use App\Models\VendorQuotationDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class VendorQuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['rfqDetail'] = PurchaseRequestRfq::query()
            ->with(['items.itemDetail', 'vendor_quotations' => function ($query) {
                $query->where('vendor_id', auth()->user()->vendor_id);
            }, 'vendor_quotations.quotationItems.item'])
            ->get();

        return resp('1', 'Successfully!', $data, Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rfq_id' => 'required',
            'total_quotation_amount' => 'required',
            'items' => 'required|array',
            'items.*.bid_price' => 'required|numeric',
            'items.*.description' => 'nullable|string',
        ],[
            'items.*.bid_price.required' => 'All items bid price is required.', // Custom error message for the entire items array
            'items.*.bid_price.numeric' => 'The bid price must be numeric.', // Custom error message for the entire items array
        ]);
        try {

            DB::beginTransaction();

            $rfqid=$this->input['rfq_id'];
            unset($this->input['rfq_id']);
            $checkQuotation=PurchaseRequestRfq::query()
                ->with(['vendor_quotations' => function ($query) {
                    $query->where('vendor_id', auth()->user()->vendor_id);
                }])->where('id',$rfqid)
                ->first();

            if($checkQuotation->vendor_quotations->count() == 0){
                $rfqDet=PurchaseRequestRfq::query()->findOrFail($rfqid);
                $quotation=new VendorQuotation();
                $quotation->vendor_id = auth()->user()->vendor_id;
                $quotation->total_quotation_amount = encode($this->input['total_quotation_amount']);
                $quotation->projectable()->associate($rfqDet);
                $quotation->save();
                $quotation->quotationItems()->createMany($request->items);
                DB::commit();
            }else{
                return resp(0, 'Quotation already added against this RFQ.', [], Response::HTTP_EXPECTATION_FAILED);
            }



            return resp('1', 'Quotation added Successfully!', $quotation->load('quotationItems.item'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);

        }
    }

    /**
     * Display the specified resource.
     */
    public function show(VendorQuotation $vendorQuotation)
    {

    }
    public function ViewQuotation( $id)
    {

        $data['rfqDetail'] = PurchaseRequestRfq::query()
            ->with(['items.itemDetail', 'vendor_quotations' => function ($query) {
                $query->where('vendor_id', auth()->user()->vendor_id);
            }, 'vendor_quotations.quotationItems.item'])
            ->where('id', $id)
            ->get();

        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VendorQuotation $vendorQuotation)
    {

        $request->validate([
            'total_quotation_amount' => 'required',
            'items' => 'required|array',
            'items.*.bid_price' => 'required|numeric',
        ],[
            'items.*.bid_price.required' => 'All items bid price is required.', // Custom error message for the entire items array
            'items.*.bid_price.numeric' => 'The bid price must be numeric.', // Custom error message for the entire items array
        ]);
        try {

            DB::beginTransaction();

            $vendorQuotation->total_quotation_amount= encode($request->total_quotation_amount);
            $vendorQuotation->save();
            if( $vendorQuotation){
                foreach($request->items as $qitems){

                    $quotationItem=VendorQuotationDetail::query()->findOrFail($qitems['id']);
                    $quotationItem->bid_price=$qitems['bid_price'];
                    $quotationItem->description= $qitems['description'];
                    $quotationItem->save();

                }
            }
            DB::commit();

            return resp('1', 'Quotation update Successfully!', $vendorQuotation->load('quotationItems.item'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VendorQuotation $vendorQuotation)
    {
        //
    }

    public function uploadBiddingDocuments(Request $request,PurchaseRequestRfq $rfq)
    {
        $request->validate([
            'file' => 'required|file',
            'document_id' => 'required|integer',
        ]);
        try {

            DB::beginTransaction();
            $checkDocument=VendorQuotationDocument::query()->where('rfq_id',$rfq->id)->where('document_id',$this->input['document_id'])->where('created_by',auth()->user()->id)->first();

            $this->input['rfq_id']=$rfq->id;
            $this->input['vendor_id']=auth()->user()->vendor_id;
            if($request->file('file')){
                $responce=$this->uploadDocuments($request,'VendorBiddingDocuments','file');
                $this->input['file']=$responce;
            }
            if($this->input['file'] != ""){
                if(!$checkDocument){
                    $vendorQuoDoc=VendorQuotationDocument::query()->create( $this->input);
                }else{
                    $vendorQuoDoc=VendorQuotationDocument::query()->where('id',$checkDocument->id)->update( $this->input);
                    $vendorQuoDoc=VendorQuotationDocument::query()->findOrFail($checkDocument->id);
                }

            }

            DB::commit();
            return resp(1,'Successful!', $vendorQuoDoc,Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function uploadTenderBiddingDocuments(Request $request,Tender $tender)
    {
        $request->validate([
            'file' => 'required|file',
            'document_id' => 'required|integer',
        ]);
        try {

            DB::beginTransaction();
            $checkDocument=VendorQuotationDocument::query()->where('tender_id',$tender->id)->where('document_id',$this->input['document_id'])->where('created_by',auth()->user()->id)->first();

            $this->input['tender_id']=$tender->id;
            $this->input['vendor_id']=auth()->user()->vendor_id;
            if($request->file('file')){
                $responce=$this->uploadDocuments($request,'VendorBiddingDocuments','file');
                $this->input['file']=$responce;
            }
            if($this->input['file'] != ""){
                if(!$checkDocument){
                    $vendorQuoDoc=VendorQuotationDocument::query()->create( $this->input);
                }else{
                    $vendorQuoDoc=VendorQuotationDocument::query()->where('id',$checkDocument->id)->update( $this->input);
                    $vendorQuoDoc=VendorQuotationDocument::query()->findOrFail($checkDocument->id);
                }

            }

            DB::commit();
            return resp(1,'Successful!', $vendorQuoDoc,Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function applyProject(VendorQuotation $quotation)
    {
        $quotation->load('projectable');

        //dd(now()->format('Y-m-d H:i:s'));
        //dd($quotation->toArray());
        $currentdate=now()->format('Y-m-d H:i:s');
        $expiryDate=date('Y-m-d H:i:s',strtotime($quotation->projectable->expiry_date));
        try {
            $vendor = Vendor::find($quotation->vendor_id);
            if($expiryDate >= $currentdate){
                DB::beginTransaction();
                $quotation->apply_status=1;
                $quotation->save();

                if ($quotation->projectable_type === 'App\Models\Admin\PurchaseRequestRfq') {
                    $title = 'New RFQ Quotation by ' . $vendor->company_name;
                    $message = $vendor->company_name . ' has submitted RFQ quotation';
                    $url = 'quotation-list';
                } elseif ($quotation->projectable_type === 'App\Models\Admin\Tender') {
                    $title = 'New Tender Quotation by ' . $vendor->company_name;
                    $message = $vendor->company_name . ' has submitted Tender quotation';
                    $url = 'tender-quotations';
                }

                sendVendorNotification($quotation->projectable->created_by, $title, $message, $url);

                DB::commit();
                return resp(1,'Successfully applied!', $quotation,Response::HTTP_CREATED);
            }else{
                return resp(1,'Project time is expired', [],Response::HTTP_EXPECTATION_FAILED);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function uploadDocuments($request,$folder,$name){

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
    public function saveTenderQuotation(Request $request)
    {
        $request->validate([
            'tender_id' => 'required',
            'total_quotation_amount' => 'required',
            'items' => 'required|array',
            'items.*.bid_price' => 'required|numeric',
            'items.*.description' => 'nullable|string',
        ],[
            'items.*.bid_price.required' => 'All items bid price is required.', // Custom error message for the entire items array
            'items.*.bid_price.numeric' => 'The bid price must be numeric.', // Custom error message for the entire items array
        ]);
        try {

            DB::beginTransaction();
            $tnderid=$this->input['tender_id'];
            unset($this->input['tender_id']);

            $tenderDetail=Tender::query()->findOrFail($tnderid);
            $quotation = new VendorQuotation();
            $quotation->vendor_id = auth()->user()->vendor_id;
            $quotation->total_quotation_amount =encode($this->input['total_quotation_amount']);
            $quotation->projectable()->associate($tenderDetail);
            $quotation->save();
            $quotation->quotationItems()->createMany($request->items);
            DB::commit();

            return resp('1', 'Quotation added Successfully!', $quotation->load('quotationItems.item'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);

        }
    }
    public function updateTenderQuotation(Request $request, VendorQuotation $vendorQuotation)
    {

        $request->validate([
            'total_quotation_amount' => 'required',
            'items' => 'required|array',
            'items.*.bid_price' => 'required|numeric',
        ],[
            'items.*.bid_price.required' => 'All items bid price is required.', // Custom error message for the entire items array
            'items.*.bid_price.numeric' => 'The bid price must be numeric.', // Custom error message for the entire items array
        ]);
        try {

            DB::beginTransaction();

            $vendorQuotation->total_quotation_amount=$request->total_quotation_amount;
            $vendorQuotation->save();
            if( $vendorQuotation){
                foreach($request->items as $qitems){

                    $quotationItem=VendorQuotationDetail::query()->findOrFail($qitems['id']);
                    $quotationItem->bid_price=$qitems['bid_price'];
                    $quotationItem->save();

                }
            }
            DB::commit();

            return resp('1', 'Quotation update Successfully!', $vendorQuotation->load('quotationItems.item'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);

        }
    }
    public function ViewTenderQuotation( $id)
    {
        $this->authorizeAny([
            'manage_vendor_portal',
        ]);

        $data['tenderDetail'] =$tenderDetail= Tender::query()
            ->with(['tenderDetails.itemDetail', 'vendor_quotations' => function ($query) {
                $query->where('vendor_id', auth()->user()->vendor_id);
            },'tenderNature','purchaseRequest', 'vendor_quotations.quotationItems.item'])
            ->where('id', $id)
            ->get();


        if($tenderDetail){
            foreach($tenderDetail as $key=> $tender){
                $tenderDocuments=explode(',',$tender->documents_ids);
                $tenderDetail[$key]->tenderDocuments=@$this->getTenderDocuments($tenderDocuments,$tender->id);
            }

        }

        $data['tenderDetail']=$tenderDetail;
        return resp('1', 'Successfully!', $data, Response::HTTP_CREATED);
    }

    public function getTenderDocuments($tenderDocuments,$tenderID)
    {
       $documents= VendorQuotationDocument::query()->whereIn('document_id',$tenderDocuments)->where('tender_id',$tenderID)->where('vendor_id',auth()->user()->vendor_id)->with('documentDetail')->get();
       if($documents){
           return $documents->toArray();
       }else{
           return [];
       }

    }

    public function decodeAmount(Request $request)
    {

        $request->validate([
            'total_quotation_amount' => 'required',
        ]);
        $data['total_quotation_amount']=decode($request->total_quotation_amount);
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }
}

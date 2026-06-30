<?php

namespace App\Http\Controllers\Api\V1\Admin\PurchaseOrder;

use App\Http\Controllers\Controller;
use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\PurchaseRequestRfqDetail;
use App\Models\Admin\Tender;
use App\Models\Admin\TenderDetail;
use App\Models\ProjectAwarded;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\VendorQuotationDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'purchase_order_view',
            'manage_audit_procurement',
            'manage_vendor_portal',
        ]);

        $data['purchase_order_listing']=PurchaseOrder::query()->with('PoItems.poItmes')->get();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'purchase_order_create'
        ]);

        try {
            $awardedProject=ProjectAwarded::query()->findOrFail($request->awarded_project_id);
            if($awardedProject->po_status == 0 && $awardedProject->wo_status == 0){
            DB::beginTransaction();
            $request->validate([
                //'rfq_id' => 'required|integer',
                'purchase_order_date' => 'required|date',
                'purpose_of_po' => 'required',
                'awarded_project_id' => 'required',
                'date_of_delivery' => 'required|date',
                'time_of_delivery' => 'required|date_format:H:i',
            ]);
            $statement = DB::select("SELECT IDENT_CURRENT('purchase_orders') as nextID");
            $poNO='PO/'.sprintf('%04d', $statement[0]->nextID);
            $this->input['purchase_order_no']=$poNO;
            $this->input['purchase_order_date']=date('Y-m-d',strtotime($request->purchase_order_date));
            $this->input['project_award_id']=$request->awarded_project_id;
            $awardedProject=ProjectAwarded::query()->findOrFail($request->awarded_project_id);

            $porequest=PurchaseOrder::query()->create($this->input);
            if($porequest){
                $rfqItems=array();
                $rfqId = $this->input['rfq_id'] ?? null;
                $tenderId = $this->input['tender_id'] ?? null;
                if($rfqId != null){
                    $rfqItems=PurchaseRequestRfqDetail::query()->where('purchase_request_rfq_id',$this->input['rfq_id'])->get();
                }
                if($tenderId != null){
                    $rfqItems=TenderDetail::query()->where('tender_id',$this->input['tender_id'])->get();
                }
                //$rfqItems=PurchaseRequestRfqDetail::query()->where('purchase_request_rfq_id',$this->input['rfq_id'])->get();
                if($rfqItems) {
                    foreach ($rfqItems as $item) {
                        $bidAmount = $this->getItemBidAmount($awardedProject->quotation_id, $item['item_id']);

                        $insert = array(
                            'purchase_order_id' => $porequest->id,
                            'description' => $item['description'],
                            'unit_of_measurement' => $item['unit_of_measurement'],
                            'required_quantity' => $item['required_quantity'],
                            'unit_price' => $bidAmount->bid_price,
                            'item_id' => $item['item_id'],
                        );
                        PurchaseOrderDetail::query()->create($insert);

                    }
                    ProjectAwarded::query()->where('id', $this->input['awarded_project_id'])->update(array('po_status' => 1));
                }
                }
            DB::commit();
            return resp('1', 'Purchase Order added Successfully!', $porequest, Response::HTTP_CREATED);
        }else{
                return resp('1', 'Work Order / Purchase Order already Generated', [], Response::HTTP_OK);
        }
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return resp(1,'Successful!', $purchaseRequest,Response::HTTP_CREATED);
    }

    public function getItemBidAmount($quotationID,$itemId)
    {
        $itemQuotation=VendorQuotationDetail::query()->where('quotation_id',$quotationID)->where('item_id',$itemId)->first();
        return $itemQuotation;
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->authorizeAny([
            'purchase_order_view',
            'manage_audit_procurement',
            'manage_vendor_portal',
        ]);

        $data['view_purchase_order']=$purchaseOrder->load('PoItems.poItmes','rfqDetail.purchase_request');
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorizeAny([
            'purchase_order_update'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $this->authorizeAny([
            'purchase_order_delete'
        ]);
    }

    public function getAwardedProjects()
    {
        $this->authorizeAny([
            'awarded_projects',
            'manage_awarded_auctions',
            'manage_audit_procurement',
        ]);

        $projects = PurchaseRequestRfq::query()
            ->with([
                'items',
                'awardProject' => function ($query) {
                    $query->where('po_status', 0);
                    $query->where('wo_status', 0);
                },
                'awardProject.vendorDetail',
                'awardProject.awardQuotation',
                'purchase_request',
                'disposeRequest',
            ])
            ->get();
        $awardprojects=$projects->toArray();

        foreach($awardprojects as $key=> $projects){

           if(!empty($projects['award_project'])){
               $awardprojects[$key]['award_project'][0]['award_quotation']['total_quotation_amount']=decode(@$projects['award_project'][0]['award_quotation']['total_quotation_amount']);

           }else{
               unset($awardprojects[$key]);
           }

        }
        $data['awardedProjects']= array_values($awardprojects);
        return resp(1,'Successful!', $data,Response::HTTP_OK);

    }
    public function getAwardedTenderProjects()
    {
        $this->authorizeAny([
            'manage_awarded_tenders',
            'manage_audit_procurement',
        ]);

        $projects = Tender::query()
            ->with([
                'tenderDetails.itemDetail',
                'awardProject' => function ($query) {
                    $query->where('po_status', 0);
                    $query->where('wo_status', 0);
                    $query->where('cc_status', 0);
                },
                'awardProject.vendorDetail',
                'awardProject.awardQuotation',
                'purchase_request',
            ])
            ->get();

        $awardprojects=$projects->toArray();
        foreach($awardprojects as $key=> $projects){

           if(!empty($projects['award_project'])){
               $awardprojects[$key]['award_project'][0]['award_quotation']['total_quotation_amount']=decode(@$projects['award_project'][0]['award_quotation']['total_quotation_amount']);

           }else{
               unset($awardprojects[$key]);
           }

        }
        $data['awardedProjects']= array_values($awardprojects);
        return resp(1,'Successful!', $data,Response::HTTP_OK);

    }
    public function vendorAcknowledged(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|integer|exists:purchase_orders,id',
            'vendor_acknowledged' => 'required|boolean'
        ]);

        $purchase_order = PurchaseOrder::query()->findOrFail($request->purchase_order_id);
        $purchase_order->vendor_acknowledged = $request->vendor_acknowledged;
        $purchase_order->save();

        return resp('1', 'Successfully!', ['purchase_order' => $purchase_order], Response::HTTP_OK);
    }
}

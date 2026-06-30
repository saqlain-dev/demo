<?php

namespace App\Http\Controllers\Api\V1\Admin\WorkOrder;

use App\Http\Controllers\Controller;
use App\Models\Admin\PurchaseRequestRfqDetail;
use App\Models\Admin\TenderDetail;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\ProjectAwarded;
use App\Models\VendorQuotationDetail;
use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'work_order_view',
            'manage_audit_procurement',
            'manage_vendor_portal',
        ]);

        $data['work_order_listing']=$work_order_listing=WorkOrder::query()->with('WoItems.woItems','rfqDetail.items.itemDetail','rfqDetail.purchase_request','tenderDetail.tenderDetails.itemDetail','tenderDetail.purchaseRequest','invoices')->get();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'work_order_create'
        ]);

        try {
            $awardedProject=ProjectAwarded::query()->findOrFail($request->awarded_project_id);
            if($awardedProject->po_status == 0 && $awardedProject->wo_status == 0){


            DB::beginTransaction();
            $request->validate([
                'date' => 'required|date',
                'awarded_project_id' => 'required',
                'vendor_name' => 'required',
                'address' => 'required',
                'phone' => 'required',
            ]);
            $statement = DB::select("SELECT IDENT_CURRENT('work_orders') as nextID");
            $workNO='WO/'.sprintf('%04d', $statement[0]->nextID);
            $this->input['work_order_no']=$workNO;
            $this->input['date']=date('Y-m-d',strtotime($request->date));
            $this->input['project_award_id']=$request->awarded_project_id;

            $awardedProject=ProjectAwarded::query()->findOrFail($request->awarded_project_id);
            $this->input['vendor_id']=$awardedProject->vendor_id;
            $worequest=WorkOrder::query()->create($this->input);
            if($worequest){
                $items=array();
                $rfqId = $this->input['rfq_id'] ?? null;
                $tenderId = $this->input['tender_id'] ?? null;
                if($rfqId != null){
                    $items=PurchaseRequestRfqDetail::query()->where('purchase_request_rfq_id',$this->input['rfq_id'])->get();
                }
                if($tenderId != null){
                    $items=TenderDetail::query()->where('tender_id',$this->input['tender_id'])->get();
                }
                if($items){
                    foreach($items as $item){
                        $bidAmount=$this->getItemBidAmount($awardedProject->quotation_id,$item['item_id']);
                        if($bidAmount){
                            $insert=array(
                                'work_order_id'=>$worequest->id,
                                'description'=>$item['description'],
                                'unit_of_measurement'=>$item['unit_of_measurement'],
                                'required_quantity'=>$item['required_quantity'],
                                'unit_price'=>$bidAmount->bid_price,
                                'item_id'=>$item['item_id'],
                            );
                            WorkOrderDetail::query()->create($insert);
                        }

                    }
                    ProjectAwarded::query()->where('id',$this->input['awarded_project_id'])->update(array('wo_status'=>1));
                }

            }
            DB::commit();
            return resp('1', 'Work Order added Successfully!', $worequest, Response::HTTP_OK);
            }else{
                return resp('1', 'Work Order / Purchase Order already Generated', [], Response::HTTP_OK);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    public function getItemBidAmount($quotationID,$itemId)
    {
        $itemQuotation=VendorQuotationDetail::query()->where('quotation_id',$quotationID)->where('item_id',$itemId)->where('awarded_status', 1)->first();
        return $itemQuotation ?? null;
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkOrder $workOrder)
    {
        $this->authorizeAny([
            'work_order_view',
            'manage_audit_procurement',
            'manage_vendor_portal',
            'manage_vendor_portal',
            'manage_employee_portal',
        ]);

        $data['view_work_order']=$workOrder->load('WoItems.woItems','rfqDetail.items.itemDetail','rfqDetail.purchase_request','tenderDetail.tenderDetails.itemDetail','tenderDetail.purchaseRequest');
        $data['approval_request']=getNextApproval(67,auth()->user()->designation_id,$workOrder->id);
        $data['approval_request_status']=checkApprovalRequestStatus(67,$workOrder->id);
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkOrder $workOrder)
    {
        $this->authorizeAny([
            'work_order_update'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkOrder $workOrder)
    {
        $this->authorizeAny([
            'work_order_delete'
        ]);
    }

    public function vendorAcknowledged(Request $request)
    {
        $request->validate([
            'work_order_id' => 'required|integer|exists:work_orders,id',
            'vendor_acknowledged' => 'required',
            'award_id' => 'nullable'
        ]);

        $work_order = WorkOrder::query()->findOrFail($request->work_order_id);

        if ($request->vendor_acknowledged == 3 && isset($request->award_id)) {
            ProjectAwarded::query()
                ->where('id', $request->award_id)
                ->update(['vendor_status' => 3]);
        }
        $work_order->vendor_acknowledged = $request->vendor_acknowledged;
        $work_order->save();

        return resp('1', 'Successfully!', ['work_order' => $work_order], Response::HTTP_OK);
    }

    public function sendWorkOrderRequestForApproval(WorkOrder $item)
    {


        $approval_process_name=ApprovalProcessName::query()->where('id',67)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',67)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',67)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0  && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                $insert['created_by']=$item->created_by;
                $insert['created_at']=now();
                $insert['updated_at']=now();
                $Approval=ApprovalProcessList::query()->insert($insert);
                sendNotification($approval['designation_id'],$approval_process_name->approval_process_name);

            }
            $update=array('approval_status'=>2);
            WorkOrder::query()->where('id',$item->id)->update($update);

            return resp(1,'WorkOrder send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'WorkOrder approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function updateLastAcknowledged(Request $request)
    {

        $request->validate([
            'work_order_id' => 'required',
            'last_acknowledgement_date' => 'required|date',
        ]);

        $wo = WorkOrder::query()->find($request->work_order_id);
        if (!$wo) {
            return resp(0, 'Work Order not found.', null, Response::HTTP_NOT_FOUND);
        }

        try {
            $wo->last_acknowledgement_date = $request->last_acknowledgement_date;
            $wo->save();

            return resp(1, 'Last Acknowledgement Date updated successfully.', $wo, Response::HTTP_OK);
        } catch (\Exception $e) {
            return resp(0, 'Failed to update record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

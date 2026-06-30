<?php

namespace App\Http\Controllers\Api\V1\Admin\GRN;

use App\Http\Controllers\Controller;
use App\Models\Admin\Inventory;
use App\Models\Admin\ItemVariant;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\GRN;
use App\Models\GrnItem;
use App\Models\ProjectAwarded;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseRequestDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GRNController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'purchase_order_view',
            'manage_vendor_portal',
        ]);

        $vendor_id=auth()->user()->vendor_id ?? null;
        $data['award_po_list']=ProjectAwarded::query()->where('vendor_id',auth()->user()->vendor_id)->with('awardPo.PoItems')->get();
        $data['grn_list']=GRN::query()->where('vendor_id',$vendor_id)->with('vendorDetail','poDetails','checkedBy')->get();

        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    public function grnApprovalList()
    {
        $this->authorizeAny([
            'manage_good_receipt_notes',
            'manage_audit_procurement',
        ]);

        $statusValues = [1,2, 3];
        $data['grn_list']=GRN::query()->whereIn('approval_status',$statusValues)->with('vendorDetail','poDetails')->get();
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    public function approveGrn(Request $request)
    {
        try {
            DB::beginTransaction();

            $grn=GRN::query()->findOrFail($request->grn_id);
            $grnItems=$grn->load('grnItem','poDetails')->toArray();
            if($grn->approval_status == 1){

                foreach($grnItems['grn_item'] as $items){
                    $inventory = Inventory::query()->firstOrCreate(
                        ['item_id' => $items['item_id']],
                        [
                            "item_id"=>$items['item_id'],
                            "quantity"=>$items['required_quantity'],
                            "initial_quantity"=>$items['required_quantity'],
                            "purchase_date"=>date('Y-m-d',strtotime($grnItems['po_details']['purchase_order_date'])),
                            "po_id"=>$grnItems['po_details']['id'],
                        ]
                    );

                    $quantity = $inventory->quantity;

                    for ($i = 0; $i < $quantity; $i++) {
                        $statement = DB::select("SELECT IDENT_CURRENT('item_variants') as nextID");
                        $serialNo ='IV/'.sprintf('%04d', $statement[0]->nextID);

                        ItemVariant::query()->create([
                            'serial_no' => $serialNo,
                            'item_id' => $inventory->item_id,
                            'inventory_id' => $inventory->id,
                        ]);
                    }
                }
            }

            DB::commit();

            return resp('1', 'Successfully!', [], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'date' => 'required|date',
                'po_id' => 'required|integer',
                'po_item_details' => 'required|array',
                'po_item_details.*.remaining_quantity' => 'required',
                'po_item_details.*.unit_of_measurement' => 'required',
                'po_item_details.*.unit_price' => 'required',
                'po_item_details.*.item_id' => 'required',
            ]);
            $statement = DB::select("SELECT IDENT_CURRENT('g_r_n_s') as nextID");
            $GrnNO='GRN/'.sprintf('%04d', $statement[0]->nextID);
            $this->input['grn_no']=$GrnNO;
            $this->input['po_id']=$request->po_id;
            $this->input['vendor_id']=auth()->user()->vendor_id;
            $this->input['date']=date('Y-m-d',strtotime($request->date));

            $grn=GRN::query()->create($this->input);

            if($grn){
                info($request->po_item_details);
                //$poItemDetail=PurchaseOrderDetail::query()->where('purchase_order_id',$request->po_id)->get();
                foreach($request->po_item_details as $poitems){
                    info($poitems);
                    $itemInsert=array(
                        'required_quantity'=>$poitems['remaining_quantity'],
                        'unit_of_measurement'=>$poitems['unit_of_measurement'],
                        'unit_price'=>$poitems['unit_price'],
                        'item_id'=>$poitems['item_id'],
                        'grn_id'=>$grn->id,
                    );
                    GrnItem::query()->create($itemInsert);
                }

            }
            DB::commit();

            return resp('1', 'GRN added Successfully!', $grn->load('grnItem.itemDetail'), Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function addGrnItem(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'grn_id' => 'required|integer',
                'po_item_id' => 'required|integer',
            ]);
            $poItemDetail=PurchaseRequestDetail::query()->findOrFail($request->po_item_id);
            unset($this->input['po_item_id']);
            $this->input['required_quantity']=$poItemDetail->required_quantity;
            $this->input['unit_of_measurement']=$poItemDetail->unit_of_measurement;
            $this->input['unit_price']=$poItemDetail->unit_price;
            $this->input['item_id']=$poItemDetail->item_id;

            $grnitem=GrnItem::query()->create($this->input);
            DB::commit();
            return resp('1', 'GRN item added Successfully!', $grnitem, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function updateGrnItem(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'grn_item_id' => 'required|integer',
                'required_quantity' => 'required',
            ]);
            $update=array();
            $update['required_quantity']=$this->input['required_quantity'];
            if($this->input['grn_description'] != ""){
                $update['grn_description']=$this->input['grn_description'];
            }

            $grnitem=GrnItem::query()->where('id',$this->input['grn_item_id'])->update($update);
            DB::commit();
            return resp('1', 'GRN item updated Successfully!', $grnitem, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function sendGrnForApproval(GRN $item)
    {
        $item->status=2;
        $item->save();
        return resp(1,'Successful!', $item,Response::HTTP_OK);

    }

    public function viewGRN($id)
    {
        $data['view_grn']=GRN::query()->with('grnItem.itemDetail','vendorDetail','poDetails','checkedBy.userProfile','checkedBy.designation','checkedBy.department','checkedBy.branchOffice')->findOrFail($id);
        $data['approval_request']=getNextApproval(36,auth()->user()->designation_id,$id);
        $data['approval_request_status']=checkApprovalRequestStatus(36,$id);
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function show(GRN $gRN)
    {

        $data['view_grn']=$gRN->load('grnItem');
        $data['approval_request']=getNextApproval(36,auth()->user()->designation_id,$gRN->id);
        $data['approval_request_status']=checkApprovalRequestStatus(36,$gRN->id);
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GRN $gRN)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GRN $gRN)
    {
        //
    }

    public function getRemainingPoItems($poId)
    {
        // Retrieve all PurchaseOrderDetail items for the specified purchase order
        $poItems = PurchaseOrderDetail::where('purchase_order_id', $poId)->get();

        // Calculate received and remaining quantities for each PurchaseOrderDetail item
        $remainingPoItems = [];
        foreach ($poItems as $poItem) {
            $receivedQuantity = $this->calculateReceivedQuantityForPoItem($poItem->id, $poItem->item_id);
            $remainingQuantity = max(0, $poItem->required_quantity - $receivedQuantity);

            if ($remainingQuantity > 0) {
                $poItem->remaining_quantity = $remainingQuantity;
                $remainingPoItems[] = $poItem;
            }
        }

        return $remainingPoItems;
    }

    protected function calculateReceivedQuantityForPoItem($poItemId, $itemId)
    {
        // Calculate the total received quantity for the given PurchaseOrderDetail item and item_id
        $receivedQuantity = GrnItem::whereHas('grn', function ($query) use ($poItemId) {
            $query->where('po_id', function ($query) use ($poItemId) {
                $query->select('purchase_order_id')
                    ->from('purchase_order_details')
                    ->where('id', $poItemId);
            });
        })
            ->where('item_id', $itemId) // Filter by item_id
            ->sum('required_quantity');

        return $receivedQuantity;
    }
    public function sendGrnRequestForApproval(GRN $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',36)->first();
        $sp_approval_process=ApprovalProcess::query()->where('approval_process_id',36)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',36)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($sp_approval_process->count() > 0 && $checkProcess == 0){

            foreach ($sp_approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                $Approval=ApprovalProcessList::query()->create($insert);
                sendNotification($approval['designation_id'],$approval_process_name->approval_process_name);

            }
            $update=array('approval_status'=>2);
            GRN::query()->where('id',$item->id)->update($update);
            return resp(1,'GRN send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'GRN approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    //update check by
    public function updateCheckBy(Request $request){
        $request->validate([
            'grn_id' => 'required|integer',
            'check_by_id' => 'required|integer',
            'check_by_date' => 'required|date',
        ]);

        $updateData = [
            'check_by_id' => $request->check_by_id,
             'check_by_date' => \Carbon\Carbon::parse($request->check_by_date)->format('Y-m-d H:i:s'),
        ];

        $grn = GRN::query()->where('id', $request->grn_id)->update($updateData);
        return resp(1, 'Check By information updated successfully.', $grn, Response::HTTP_OK);
    }
}

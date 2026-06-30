<?php

namespace App\Http\Controllers\Api\V1\Admin\Item;

use App\Http\Controllers\Controller;
use App\Models\Admin\Inventory;
use App\Models\Admin\ItemVariant;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Item;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ItemVariantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['list']= ItemVariant::with(['item'])->get();
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer|exists:items,id',
            'inventory_id' => 'required|integer|exists:inventories,id',
            //'po_id' => 'required|integer|exists:purchase_orders,id',
            'purchase_date' => 'required|date|date_format:Y-m-d',
            'location_id' => 'required|integer|exists:locations,id',
            'store_id' => 'required|integer|exists:locations,id',
            //'assign_to_emp' => 'nullable|integer|exists:employees,id',
            //'assign_to_dept' => 'nullable|integer',
            'inventory_type' => 'nullable|integer',
            'gl_code' => 'nullable|string',
        ]);
        if (isset($this->input['parent_quantity'])){
            $parent_quantity =  $this->input['parent_quantity'];
            unset($this->input['parent_quantity']);
        }
        try {
            DB::beginTransaction();
            $statement = DB::select("SELECT IDENT_CURRENT('item_variants') as nextID");
            $serialNo ='IV/'.sprintf('%04d', $statement[0]->nextID);
            $this->input['serial_no']=$serialNo;
            $item = ItemVariant::query()->create($this->input);
            if (isset($this->input['parent_quantity'])){
                $item = Inventory::query()->findOrFail($this->input['inventory_id']);
                $item->update(['quantity' => $parent_quantity]);
            }
            DB::commit();
            return resp('1', 'Record create successfully!', $item, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(ItemVariant $itemVariant)
    {
        $data['item']= $itemVariant->load(['item'])->get();
        $data['approval_request']=getNextApproval(29,auth()->user()->designation_id,$itemVariant->id);
        $data['approval_request_status']=checkApprovalRequestStatus(29,$itemVariant->id);
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ItemVariant $itemVariant)
    {
        $request->validate([
            'item_id' => 'required|integer|exists:items,id',
            'inventory_id' => 'required|integer|exists:inventories,id',
            //'po_id' => 'required|integer|exists:purchase_orders,id',
            'purchase_date' => 'required|date|date_format:Y-m-d',
            'location_id' => 'required|integer|exists:locations,id',
            'store_id' => 'required|integer|exists:locations,id',
            //'assign_to_emp' => 'nullable|integer|exists:employees,id',
            //'assign_to_dept' => 'nullable|integer',
            'inventory_type' => 'nullable|integer',
            'gl_code' => 'required|string',
        ]);


        if (isset($this->input['parent_quantity'])){
            $parent_quantity =  $this->input['parent_quantity'];
            unset($this->input['parent_quantity']);
        }
        try {

            DB::beginTransaction();
            $itemVariant->update($request->except('serial_no'));
            if (isset($this->input['parent_quantity'])){
                $item = Inventory::query()->findOrFail($this->input['inventory_id']);
                $item->update(['quantity' => $parent_quantity]);
            }
            DB::commit();
            return resp('1', 'Record updated Successfully!', $itemVariant, Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemVariant $itemVariant)
    {
        $itemVariant->delete();
        Inventory::query()->where('id',$itemVariant->inventory_id)->decrement('quantity',1);
        return resp('1', 'Record deleted Successfully!', $itemVariant, Response::HTTP_OK);
    }

    public function addVariants(Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer',
            'item_id' => 'required|integer|exists:items,id',
            'inventory_id' => 'required|integer|exists:inventories,id',
            //'po_id' => 'required|integer|exists:purchase_orders,id',
            'purchase_date' => 'required|date|date_format:Y-m-d',
            'location_id' => 'required|integer|exists:locations,id',
            'store_id' => 'required|integer|exists:locations,id',
            'assign_to_emp' => 'nullable|integer|exists:employees,id',
            'assign_to_dept' => 'nullable|integer',
            'inventory_type' => 'nullable|integer',
            'gl_code' => 'required|string',
        ]);
        try {
            DB::beginTransaction();

            $nextId = DB::table('item_variants')->max('id') + 1;

            $variants = [];
            $quantity = $request->input('quantity');

            for ($i = 0; $i < $quantity; $i++) {
                $serialNo = 'IV/' . sprintf('%04d', $nextId);
                $nextId++;

                $this->input['serial_no'] = $serialNo;

                $variants[] = ItemVariant::query()->create($this->input);
            }
            Inventory::query()->where('id',$request->inventory_id)->increment('quantity',$quantity);

            DB::commit();

            return resp('1', 'Record create successfully!', $variants, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function issueVariant(Request $request)
    {
        $request->validate([
            'item_variant_id' => 'required|integer|exists:item_variants,id',
            'assign_to_emp' => 'nullable|integer|exists:employees,id',
            'assign_to_dept' => 'nullable|integer'
        ]);
        try {
            DB::beginTransaction();

            $variant = ItemVariant::query()->findOrFail($request->item_variant_id);
            $variant->update($this->input);

            DB::commit();

            return resp('1', 'Record updated successfully!', $variant, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function disposeVariant(Request $request)
    {
        $request->validate([
            'item_variant_id' => 'required|integer|exists:item_variants,id',
            'inventory_type' => 'required|integer',
        ]);
        try {
            DB::beginTransaction();

            $variant = ItemVariant::query()->findOrFail($request->item_variant_id);
            $variant->update(['inventory_type' => $request->inventory_type]);

            Inventory::query()->where('id',$variant->inventory_id)->decrement('quantity',1);
            DB::commit();

            return resp('1', 'Record updated successfully!', $variant, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function reclaimVariant(Request $request)
    {
        $request->validate([
            'item_variant_id' => 'required|integer|exists:item_variants,id',
        ]);
        try {
            DB::beginTransaction();

            $variant = ItemVariant::query()->findOrFail($request->item_variant_id);
            $variant->update([
                'assign_to_emp' => null,
                'assign_to_dept' => null,
            ]);

            DB::commit();

            return resp('1', 'Record updated successfully!', $variant, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function sendItemDisposeForApproval(ItemVariant $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',29)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',29)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            ItemVariant::query()->where('id',$item->id)->update($update);
            return resp(1,'Item Dispose send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Item Dispose approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function itemVariantPending()
    {
        $this->authorizeAny([
            'manage_dispose_requests',
            'manage_audit_inventory_warehouse',
        ]);

        $data['list']= ItemVariant::query()->where('approval_status',2)->with(['item','inventory','location','store','purchaseOrder'])->orderByDesc('id')->get();
        $data['list']->each( function ($item){
            $item->approval_request = getNextApproval(29,auth()->user()->designation_id,$item->id);
            $item->approval_request_status = checkApprovalRequestStatus(29,$item->id);
        });
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }
    public function itemVariantApproved()
    {
        $this->authorizeAny([
            'manage_dispose_items',
            'manage_audit_inventory_warehouse',
        ]);

        $data['list']= ItemVariant::query()->where('approval_status',1)->with(['item','inventory','location','store','purchaseOrder'])->orderByDesc('id')->get();
        $data['list']->each( function ($item){
            $item->approval_request = getNextApproval(29,auth()->user()->designation_id,$item->id);
            $item->approval_request_status = checkApprovalRequestStatus(29,$item->id);
        });
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }
    public function remainingAuctionItems()
    {
        $data['list']= ItemVariant::query()->where('approval_status',1)
            ->where('inventory_type', 1)
            ->with(['item','inventory','location','store','purchaseOrder'])
            ->orderByDesc('id')->get();

        $data['list']->each( function ($item){
            $item->approval_request = getNextApproval(29,auth()->user()->designation_id,$item->id);
            $item->approval_request_status = checkApprovalRequestStatus(29,$item->id);
        });
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }
}

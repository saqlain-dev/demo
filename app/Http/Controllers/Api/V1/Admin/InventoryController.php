<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enum\InventoryType;
use App\Http\Controllers\Controller;
use App\Models\Admin\DisposeItem;
use App\Models\Admin\Inventory;
use App\Models\Admin\Location;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Communication\CommunicationEvent;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Program\Project\ProjectProfile;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\StrategicPlan;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'general_inventory_view',
            'manage_audit_inventory_warehouse',
        ]);

        $data = Inventory::query()->with('poDetail', 'location','item.itemUnit')->get();
//        $data->each( function ($record){
//            $record->approval_request = getNextApproval(12,auth()->user()->designation_id,$record->id);
//            $record->approval_request_status=checkApprovalRequestStatus(12,$record->id);
//        });
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'general_inventory_create'
        ]);

        $request->validate([
            'item_id' => 'nullable|integer|exists:items,id',
            //'quantity' => 'required',
            //'initial_quantity' => '',
            'purchase_date' => 'nullable|date',
            'inventory_type' => 'nullable|integer',
            //'po_id' => 'nullable|integer|exists:purchase_orders,id',
            'location_id' => 'nullable|integer|exists:locations,id',
            'gl_code' => 'nullable|string',
            'description' => 'nullable|string',
            'serial_no' => 'nullable|integer',
            //'inventory_no' => 'nullable|integer',
            'physical_verification_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        //dd($request->all());
        $statement = DB::select("SELECT IDENT_CURRENT('inventories') as nextID");
        $inventoryNO='IN/'.sprintf('%04d', $statement[0]->nextID);
        $this->input['inventory_no']=$inventoryNO;
        $this->input['quantity']=0;
        $inventory = Inventory::query()->create($this->input);
        if(optional(optional($inventory->item)->item_type)->id!='144'){
            $inventory->update(['quantity' =>$this->input['initial_quantity']]);
            $inventory->refresh();
        }
        return resp(1, 'Successful!', $inventory, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show($inventoryId)
    {
        $this->authorizeAny([
            'general_inventory_view',
            'manage_audit_inventory_warehouse',
        ]);

//        $data['approval_request']=getNextApproval(12,auth()->user()->designation_id,$inventoryId);
//        $data['approval_request_status']=checkApprovalRequestStatus(12,$inventoryId);
        $data['inventory'] = Inventory::query()->with('poDetail', 'item.itemUnit','location','itemVariants.assignToEmploy','itemVariants.assignToEmploy.department','itemVariants.location','itemVariants.RackId','itemVariants.purchaseOrder','itemVariants.location','itemVariants.assignToDept','itemVariants.store','itemVariants.vendor', 'itemVariants.project')->findOrFail($inventoryId);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inventory $inventory)
    {
        $this->authorizeAny([
            'general_inventory_update'
        ]);

        $request->validate([
            'item_id' => 'nullable|integer|exists:items,id',
            'quantity' => 'nullable',
            'initial_quantity' => 'nullable',
            'purchase_date' => 'nullable|date',
            'inventory_type' => 'nullable|integer',
            //'po_id' => 'nullable|integer|exists:purchase_orders,id',
            'location_id' => 'nullable|integer|exists:locations,id',
            'gl_code' => 'nullable|string',
            'description' => 'nullable|string',
            'serial_no' => 'nullable|integer',
            //'inventory_no' => 'nullable|integer',
            'physical_verification_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);
        $inventory->update($this->input);
        return resp(1, 'Successful!', $inventory, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventory $inventory)
    {
        $this->authorizeAny([
            'general_inventory_delete'
        ]);

        $inventory->delete();
        $message = "Inventory Deleted Successfully";
        return resp(1, 'Successful!', $message, Response::HTTP_OK);
    }

    public function getDropdowns()
    {
        $data['employees'] = Employee::query()->whereNotIn('employee_type', [14, 16, 17, 18])->get();
        $data['items'] = Item::all();
        $data['categories'] = ItemCategory::query()->with('itemSubcategory')->get();
        $data['locations'] = Location::getLocations();
        $data['po_details'] = PurchaseOrder::all();
        $data['inventory_types'] = InventoryType::all();
        $data['inventory']= Inventory::with(['itemVariants','Reconciliation.InventoryReconciliationId.ReconciliationType'])->get();
        $data['projects'] = ProjectProfile::query()->select('id', 'project_name')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function sendForApproval($itemId, Request $request)
    {
        $request->validate(['quantity' => 'required']);
        $approval_process_name=ApprovalProcessName::query()->where('id',12)->first();
        $approval_process = ApprovalProcess::query()->where('approval_process_id', 12)->get();
        if ($approval_process->count() < 0) {
            return resp(0, 'Approval process not available', [], Response::HTTP_OK);
        }

        $item = Inventory::query()->findOrFail($itemId);
        $idle_items = $item->idle_items + $request->quantity;

        if ($idle_items > $item->initial_quantity){
            $remaining_items = $item->initial_quantity - $item->idle_items;
            return resp(0, 'Failed to dispose! quantity can be exceeded to '.$remaining_items.' items', [], Response::HTTP_EXPECTATION_FAILED);
        }
        try {
            DB::beginTransaction();
            $disposeItem = DisposeItem::query()->create([
                'inventory_id' => $item->id,
                'po_detail_id' => $item->po_detail_id,
                'item_id' => $item->item_id,
                'inventory_type' => $item->inventory_type,
                'dispose_quantity' => $idle_items,
                'approval_status' => 2,
            ]);
            foreach ($approval_process as $approval) {
                $insert = array(
                    'approval_process_id' => $approval['approval_process_id'],
                    'designation_id' => $approval['designation_id'],
                    'process_order' => $approval['process_order'],
                    'request_module_id' => $disposeItem->id,
                );
                $Approval = ApprovalProcessList::query()->create($insert);

                sendNotification($approval['designation_id'],$approval_process_name->approval_process_name);
            }

            $item->update(['idle_items' => $idle_items]);

            DB::commit();
            return resp(1, 'Inventory Idle send for approval.', $Approval, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to dispose!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    public function idleAction(Request $request)
    {
        $request->validate([
            'inventory_id' => 'required|exists:inventories,id',
            'idle_action' => 'required', // 1 for Auction, 2 for Donate, 3 for Dispose
            //'quantity' => 'required|integer',
        ]);
        $item = Inventory::query()->findOrFail($request->inventory_id);

        try {
            DB::beginTransaction();

            $item->update(['idle_action' => $request->idle_action]);

            $item->quantity = $item->quantity - $item->idle_items;
            if ($item->quantity < 0){
                DB::rollBack();
                return resp(0, 'Can not proceed request!', $item->refresh(), Response::HTTP_OK);
            }
            if ($item->idle_action == 1) {
                $item->auction_quantity = $item->auction_quantity + $item->idle_items;
            } elseif ($item->idle_action == 2) {
                $item->donate_quantity = $item->donate_quantity + $item->idle_items;
            } elseif ($item->idle_action == 3) {
                $item->dispose_quantity = $item->dispose_quantity + $item->idle_items;
            }
            $item->save();

            DB::commit();
            return resp(1, 'Successful!', $item->refresh(), Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    public function getAuction()
    {
        $data = Inventory::query()->with('poDetail', 'location')->where('idle_action',3)->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function getApprovedIdles()
    {
        $data = Inventory::query()->with('poDetail', 'location')->where('idle_approval_status',1)->get();
        $data->each( function ($record){
            $record->approval_request = getNextApproval(12,auth()->user()->designation_id,$record->id);
            $record->approval_request_status=checkApprovalRequestStatus(12,$record->id);
        });
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

}

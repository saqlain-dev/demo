<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\InventoryReconciliation;
use App\Models\Admin\Library\BookReconciliation;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InventoryReconciliationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'inventory_reconciliation_view',
            'manage_audit_inventory_warehouse',
        ]);

        $data['InventoryReconciliation'] = InventoryReconciliation::with(['ReconciliationType','created_by','updated_by','ReconciliationDetail.InventoryId'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'inventory_reconciliation_create'
        ]);

        $request->validate([
            'reconciliation_type' => 'required',
            'date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = InventoryReconciliation::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryReconciliation $inventoryReconciliation): JsonResponse
    {
        $this->authorizeAny([
            'inventory_reconciliation_view',
            'manage_audit_inventory_warehouse',
        ]);
        $data['book'] = $inventoryReconciliation->load(['ReconciliationType','created_by','updated_by','ReconciliationDetail.InventoryId']);
        $data['approval_request']=getNextApproval(35,auth()->user()->designation_id,$inventoryReconciliation->id);
        $data['approval_request_status']=checkApprovalRequestStatus(35,$inventoryReconciliation->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
        $this->authorizeAny([
            'inventory_reconciliation_update'
        ]);

        $inventoryReconciliation = InventoryReconciliation::query()->findOrFail($id);
        $request->validate([
            'reconciliation_type' => 'required',
            'date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $inventoryReconciliation->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $this->authorizeAny([
            'inventory_reconciliation_delete'
        ]);

        $inventoryReconciliation = InventoryReconciliation::query()->with('ReconciliationDetail')->findOrFail($id);
        $inventoryReconciliation->ReconciliationDetail()->delete();
        $item = $inventoryReconciliation->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
    public function sendInventoryReconciliationForApproval(InventoryReconciliation $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',35)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',35)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',35)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
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
            InventoryReconciliation::query()->where('id',$item->id)->update($update);
            return resp(1,'Inventory reconciliation send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Inventory reconciliation approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}

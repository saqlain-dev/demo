<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AuctionGatePass;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AuctionGatePassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'gate_pass_view',
            'manage_audit_procurement',
            'manage_vendor_portal',
        ]);

        $data['item'] = AuctionGatePass::query()->with('prRfq', 'vendor')->get();
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
            'gate_pass_create',
        ]);

        $request->validate([
            'pr_rfq_id' => 'required|integer|exists:purchase_request_rfqs,id',
            'vendor_id' => 'required|integer|exists:vendors,id',
            'gate_pass_date' => 'required',
            'reason' => 'required',
            'address' => 'required',
            'received_by' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = AuctionGatePass::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item , Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->authorizeAny([
            'gate_pass_view',
            'manage_audit_procurement',
            'manage_vendor_portal',
        ]);

//        $data['approval_request']=getNextApproval(12,auth()->user()->designation_id,$inventoryId);
//        $data['approval_request_status']=checkApprovalRequestStatus(12,$inventoryId);
        $data['item'] = AuctionGatePass::query()->with('prRfq', 'vendor')->findOrFail($id);
        $data['approval_request']=getNextApproval(47,auth()->user()->designation_id,$id);
        $data['approval_request_status']=checkApprovalRequestStatus(47,$id);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AuctionGatePass $auctionGatePass)
    {
        $this->authorizeAny([
            'gate_pass_update',
        ]);

        $request->validate([
            'pr_rfq_id' => 'required|integer|exists:purchase_request_rfqs,id',
            'vendor_id' => 'required|integer|exists:vendors,id',
            'gate_pass_date' => 'required',
            'reason' => 'required',
            'address' => 'required',
            'received_by' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $auctionGatePass->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $auctionGatePass, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AuctionGatePass $auctionGatePass)
    {
        $this->authorizeAny([
            'gate_pass_delete'
        ]);

        $auctionGatePass->delete();
        $message = "AuctionGatePass Deleted Successfully";
        return resp(1, 'Successful!', $message, Response::HTTP_OK);
    }
    public function sendGatePassForApproval(AuctionGatePass $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',47)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',47)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            AuctionGatePass::query()->where('id',$item->id)->update($update);
            return resp(1,'Gate Pass send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'GatePass approval already sent.', [],Response::HTTP_OK);
            }
        }
    }


}

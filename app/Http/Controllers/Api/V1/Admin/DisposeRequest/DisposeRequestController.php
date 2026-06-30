<?php

namespace App\Http\Controllers\Api\V1\Admin\DisposeRequest;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\DisposeRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DisposeRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'purchase_order_view',
            'auction_requests_view',
            'manage_audit_procurement',
        ]);

        $data['disposeRequests'] = DisposeRequest::query()->with( 'department', 'project', 'createdBy')->orderByDesc('id')->get();
        /*$data['draft']=$disposeRequest->where('pr_approval_status',4)->count();
        $data['pending']=$disposeRequest->where('pr_approval_status',2)->count();
        $data['approved']=$disposeRequest->where('pr_approval_status',1)->count();
        $data['reject']=$disposeRequest->where('pr_approval_status',3)->count();*/

        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'auction_requests_create'
        ]);

        $request->validate([
            'date' => 'required',
            'description' => 'required',
            'purpose' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $statement = DB::select("SELECT IDENT_CURRENT('dispose_requests') as nextID");
            $prNO = 'AR/' . sprintf('%04d', $statement[0]->nextID);
            $this->input['dispose_request_no'] = $prNO;
            $this->input['date'] = date('Y-m-d', strtotime($request->date));

            $prequest = DisposeRequest::query()->create($this->input);
            DB::commit();
            return resp('1', 'Dispose request added Successfully!', $prequest, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(DisposeRequest $disposeRequest)
    {
        $this->authorizeAny([
            'auction_requests_view',
            'manage_audit_procurement',
        ]);

        $data['dispose_request'] = DisposeRequest::query()->with(['disposeItems.itemVariant.item', 'createdBy'])->findOrFail($disposeRequest->id);
        $data['approval_request'] = getNextApproval(70, auth()->user()->designation_id, $disposeRequest->id);
        $data['approval_request_status'] = checkApprovalRequestStatus(70, $disposeRequest->id);
        return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DisposeRequest $disposeRequest)
    {
        $this->authorizeAny([
            'auction_requests_update'
        ]);

        $request->validate([
            'date' => 'required',
            'description' => 'required',
            'purpose' => 'required',
        ]);

        $this->input['date'] = date('Y-m-d', strtotime($request->date));
        DisposeRequest::query()->where('id', $disposeRequest->id)->update($this->input);

        return resp(1, 'Successful!', [], Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DisposeRequest $disposeRequest)
    {
        $this->authorizeAny([
            'auction_requests_delete'
        ]);

        $disposeRequest->delete();
        return resp(1, 'Dispose request deleted successfully.', [], Response::HTTP_OK);
    }

    public function sendDisposeRequestsForApproval(DisposeRequest $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',70)->first();
        $sp_approval_process=ApprovalProcess::query()->where('approval_process_id',70)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',70)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            DisposeRequest::query()->where('id',$item->id)->update($update);
            return resp(1,'Dispose request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Dispose request approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

}

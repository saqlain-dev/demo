<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\LogBook;
use App\Models\Admin\IssueStock;
use App\Models\Admin\StockRequest;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class StockRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'stock_request_view',
            'manage_audit_inventory_warehouse',
            'manage_employee_portal',
        ]);

        $data = StockRequest::query()->with(['DepartmentId','branchOffice','RequestedBy','StockRequestDetail','created_by','updated_by'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'stock_request_create',
            'manage_employee_portal',
        ]);

        $request->validate([
            'department_id' => 'required',
            'location_id' => 'required',
            'requested_by' => 'required',
            'remarks' => 'required',
            'request_date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = StockRequest::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockRequest $stockRequest): JsonResponse
    {
        $this->authorizeAny([
            'stock_request_view',
            'manage_audit_inventory_warehouse',
            'manage_employee_portal',
        ]);

        $data['stock_request'] = $stockRequest->load(['DepartmentId','branchOffice','RequestedBy','StockRequestDetail.ItemCategoryId','created_by','updated_by']);
        $issue_stock = IssueStock::query()->with(['StockRequestId','IssueStockDetail.ItemId','created_by','updated_by'])->where('stock_request_id',$stockRequest->id)->first();
        if($issue_stock){
            foreach ($issue_stock->IssueStockDetail as $key => $issueDetail) {
                $variants = $issueDetail->variantsDetail();
                $issue_stock->IssueStockDetail[$key]['variants']=$variants;
            }
        }

        $data['issue_stock']=$issue_stock;
        $data['approval_request']=getNextApproval(48,auth()->user()->designation_id,$stockRequest->id);
        $data['approval_request_status']=checkApprovalRequestStatus(48,$stockRequest->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockRequest $stockRequest)
    {
        $this->authorizeAny([
            'stock_request_update',
            'manage_employee_portal',
        ]);

        $request->validate([
            'department_id' => 'required',
            'location_id' => 'required',
            'requested_by' => 'required',
            'remarks' => 'required',
            'request_date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $stockRequest->update($this->input);
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
    public function destroy(StockRequest $stockRequest): JsonResponse
    {
        $this->authorizeAny([
            'stock_request_delete'
        ]);

        $stockRequest->StockRequestDetail()->delete();
        $item = $stockRequest->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
    public function sendStockRequestForApproval(StockRequest $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',48)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',48)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',48)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            StockRequest::query()->where('id',$item->id)->update($update);
            return resp(1,'Stock Request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Stock Request approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function filterStockRequest(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d',
            'user_id' => 'nullable|integer',
        ]);

        try {
            DB::beginTransaction();

            $query = StockRequest::with([
                'DepartmentId',
                'branchOffice',
                'RequestedBy',
                'StockRequestDetail',
                'created_by',
                'updated_by'
            ]);

            if ($request->filled('user_id')) {
                $query->where('requested_by', $request->user_id);
            }

            if ($request->filled('start_date')) {
                $query->whereDate('request_date', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('request_date', '<=', $request->end_date);
            }

            $reportData = $query->get();

            DB::commit();
            return resp(1, 'Successful!', $reportData, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

}

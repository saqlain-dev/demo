<?php

namespace App\Http\Controllers\Api\V1\Finance\SubGrants;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Finance\Grants\GrantFinancialReport;
use App\Models\Finance\SubGrants\SubGrantFinancialReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SubGrantFinancialReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = SubGrantFinancialReport::with(['SubGrantId','DraftBy','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'sub_grant_id' => 'required',
            'name' => 'required',
            //'draft_by' => 'required',
            'attachment' => 'required',
        ]);

        if ($request->hasFile('attachment')) {
            $responses = $this->saveImage($request, 'sub_grant_financial_report');
            $this->input['attachment'] = $responses;
        }
        try {
            DB::beginTransaction();
            $this->input['approval_status']=4;
            $item = SubGrantFinancialReport::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveImage($request,$folder){

        $file = $request->file('attachment');
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

    /**
     * Display the specified resource.
     */
    public function show(SubGrantFinancialReport $subGrantFinancialReport): JsonResponse
    {
        $data['grantFinancialReport']  = $subGrantFinancialReport->load(['SubGrantId','DraftBy','created_by','updated_by']);

        $data['approval_request']=getNextApproval(64,auth()->user()->designation_id,$subGrantFinancialReport->id);
        $data['approval_request_status']=checkApprovalRequestStatus(64,$subGrantFinancialReport->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubGrantFinancialReport $subGrantFinancialReport)
    {
        $request->validate([
            'sub_grant_id' => 'required',
            'name' => 'required',
            //'draft_by' => 'required',
            //'attachment' => 'required',
        ]);

        if ($request->hasFile('attachment')) {
            $responses = $this->saveImage($request, 'sub_grant_financial_report');
            $this->input['attachment'] = $responses;
        }
        try {
            DB::beginTransaction();
            $item = $subGrantFinancialReport->update($this->input);
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
    public function destroy(SubGrantFinancialReport $subGrantFinancialReport): JsonResponse
    {
        $item = $subGrantFinancialReport->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function sendSubGrantFinancialReportForApproval(SubGrantFinancialReport $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',64)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',64)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0  && $checkProcess == 0){

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
            SubGrantFinancialReport::query()->where('id',$item->id)->update($update);
            return resp(1,'Sub Grant Financial Report send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Sub Grant Financial Report approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}

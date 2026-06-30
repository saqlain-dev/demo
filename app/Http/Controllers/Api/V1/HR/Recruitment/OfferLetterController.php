<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Configuration\GeneralTemplates;
use App\Models\EmailTemplate;
use App\Models\HR\Recruitment\OfferLetter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class OfferLetterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['items'] = OfferLetter::with(['ApplyJobId','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'apply_job_id' => 'required',
            'offer_letter' => 'required',
            'description' => 'nullable',
        ]);
        try {
            DB::beginTransaction();
            $data['item'] = OfferLetter::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(OfferLetter $offerLetter): JsonResponse
    {

        $data['item'] = $offerLetter->load(['ApplyJobId','created_by','updated_by']);
        $data['approval_request']=getNextApproval(58,auth()->user()->designation_id,$offerLetter->id);
        $data['approval_request_status']=checkApprovalRequestStatus(58,$offerLetter->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $offerLetter = OfferLetter::query()->findOrFail($id);
        $request->validate([
            'apply_job_id' => 'required',
            'offer_letter' => 'required',
            'description' => 'nullable',
        ]);
        try {
            DB::beginTransaction();
            $data['item'] = $offerLetter->update($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OfferLetter $offerLetter): JsonResponse
    {
        $data['item'] = $offerLetter->delete();
        return resp('1', 'Record Deleted Successfully!', $data, Response::HTTP_OK);
    }

    public function sendOfferLetterForApproval(OfferLetter $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',58)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',58)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',58)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            OfferLetter::query()->where('id',$item->id)->update($update);
            return resp(1,'Offer letter send for Approval.', $Approval,Response::HTTP_OK);
        }else{
            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Offer letter approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}

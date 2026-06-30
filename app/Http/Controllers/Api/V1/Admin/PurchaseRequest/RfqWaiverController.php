<?php

namespace App\Http\Controllers\Api\V1\Admin\PurchaseRequest;

use App\Http\Controllers\Controller;
use App\Models\Admin\PurchaseRequestRfq;
use App\Models\RfqWaiver;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RfqWaiverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['rfq_waiver_list'] = RfqWaiver::with('approvers')->get();

        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            DB::beginTransaction();
            $request->validate([
                'purchase_rfq_id' => 'required',
                'rfq_waiver_detail' => 'required',
                'user_ids' => 'required|array|min:1',
            ]);

            if($request->hasFile('rfq_waiver_document')){
                $responce=$this->saveRfqWaiverFile($request,'RfqWaiverDocuments');
                $this->input['rfq_waiver_document']=$responce;
            }
            $waiver= RfqWaiver::query()->create($this->input);
            $waiver->approvers()->attach($request->user_ids);
            foreach($request->user_ids as $user_id){
                //send notification to approvers
                generalAPPNotification($user_id,'You have a new RFQ Waiver approval request.', 'You have a new RFQ Waiver approval request.', '/view-wavier');
            }
            DB::commit();
            return resp('1', 'RFQ Waiver added Successfully!', $waiver, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
 
    public function saveRfqWaiverFile($request,$folder){

        $file = $request->file('rfq_waiver_document');
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
    public function show(RfqWaiver $rfq_waiver)
    {
        $rfq_waiver->load('approvers');
        $data['rfq_waiver'] = $rfq_waiver;
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RfqWaiver $rfq_waiver)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'purchase_rfq_id' => 'required',
                'rfq_waiver_detail' => 'required',
                'user_ids' => 'required|array|min:1',
            ]);

            if($request->file('rfq_waiver_document')){
                $responce=$this->saveRfqWaiverFile($request,'RfqWaiverDocuments');
                $this->input['rfq_waiver_document']=$responce;
            }
            RfqWaiver::query()->find($rfq_waiver->id)->update($this->input);
            $rfq_waiver->approvers()->sync($request->user_ids);
            $rfq_waiver->refresh();
            foreach($request->user_ids as $user_id){
                //send notification to approvers
                generalAPPNotification($user_id,'You have a new RFQ Waiver approval request.', 'You have a new RFQ Waiver approval request.', '/view-wavier');
            }
            DB::commit();
            return resp('1', 'RFQ Waiver updated Successfully!', $rfq_waiver, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RfqWaiver $rfq_waiver)
    {
        $rfq_waiver->delete();
        return resp(1, 'RFQ Waiver deleted successfully.', [], Response::HTTP_OK);
    }

    //Approve RFQ
    public function ApproveRFQ(Request $request)
    {
        $request->validate([
            'rfq_waiver_id' => 'required|exists:rfq_waivers,id',
            'is_approved' => 'required|integer',
            'message' => 'nullable|string',
        ]);

        $userId = auth()->id(); // assumes auth middleware
        $waiver = RfqWaiver::find($request->rfq_waiver_id);

        if(!$waiver){
            return resp(0, 'Waiver not found.', null, Response::HTTP_NOT_FOUND);
        }

        $waiver->approvers()->updateExistingPivot($userId, [
            'is_approved' => $request->is_approved,
            'message' => $request->message,
            'updated_at' => now(),
        ]);

        return resp(1, 'Waiver approval updated successfully.', null, Response::HTTP_OK);
    }
}

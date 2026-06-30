<?php

namespace App\Http\Controllers\Api\V1\Admin\PurchaseRequest;

use App\Http\Controllers\Controller;
use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\RfqMinutesOfMeeting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RfqMinutesOfMeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['rfq_minuteOfMeeting_list']=RfqMinutesOfMeeting::all();
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
                'rfq_mom_detail' => 'required',
            ]);

            if($request->hasFile('rfq_mom_document')){
                $responce=$this->saveRfqMoMFile($request,'RfqMoMDocuments');
                $this->input['rfq_mom_document']=$responce;
            }
            $mom= RfqMinutesOfMeeting::query()->create($this->input);
            DB::commit();
            return resp('1', 'RFQ MoM added Successfully!', $mom, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function rfqMomDropDown()
    {
        $data['purchase_request_rfq']=PurchaseRequestRfq::query()->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function saveRfqMoMFile($request,$folder){

        $file = $request->file('rfq_mom_document');
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
    public function show(RfqMinutesOfMeeting $rfq_mom)
    {
        $data['rfq_minuteOfMeeting']=$rfq_mom;
        return resp(1, 'Successful!',$data , Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RfqMinutesOfMeeting $rfq_mom)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'purchase_rfq_id' => 'required',
                'rfq_mom_detail' => 'required',
            ]);

            if($request->file('rfq_mom_document')){
                $responce=$this->saveRfqMoMFile($request,'RfqMoMDocuments');
                $this->input['rfq_mom_document']=$responce;
            }
            RfqMinutesOfMeeting::query()->find($rfq_mom->id)->update($this->input);
            $rfq_mom->refresh();
            DB::commit();
            return resp('1', 'RFQ MoM updated Successfully!', $rfq_mom, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RfqMinutesOfMeeting $rfq_mom)
    {
        $rfq_mom->delete();
        return resp(1, 'RFQ MoM deleted successfully.', [], Response::HTTP_OK);
    }
}

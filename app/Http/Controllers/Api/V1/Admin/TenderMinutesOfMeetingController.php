<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Tender;
use App\Models\Admin\TenderMinutesOfMeeting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TenderMinutesOfMeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['tender_minuteOfMeeting_list']=TenderMinutesOfMeeting::all();
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
                'tender_id' => 'required',
                'tender_mom_detail' => 'required',
            ]);

            if($request->hasFile('tender_mom_document')){
                $responce=$this->saveTMoMFile($request,'TenderMoMDocuments');
                $this->input['tender_mom_document']=$responce;
            }
            $mom= TenderMinutesOfMeeting::query()->create($this->input);
            DB::commit();
            return resp('1', 'Tender MoM added Successfully!', $mom, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function saveTMoMFile($request,$folder){

        $file = $request->file('tender_mom_document');
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

    public function momDropDown()
    {
        $data['tender_list']=Tender::query()->where('float_tender',1)->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function show(TenderMinutesOfMeeting $tender_mom)
    {
        $data['tender_minuteOfMeeting']=$tender_mom;
        return resp(1, 'Successful!',$data , Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TenderMinutesOfMeeting $tender_mom)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'tender_id' => 'required',
                'tender_mom_detail' => 'required',
            ]);

            if($request->file('tender_mom_document')){
                $responce=$this->saveTMoMFile($request,'TenderMoMDocuments');
                $this->input['tender_mom_document']=$responce;
            }
            TenderMinutesOfMeeting::query()->find($tender_mom->id)->update($this->input);
            $tender_mom->refresh();
            DB::commit();
            return resp('1', 'Tender MoM updated Successfully!', $tender_mom, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TenderMinutesOfMeeting $tender_mom)
    {
        $tender_mom->delete();
        return resp(1, 'Tender MoM deleted successfully.', [], Response::HTTP_OK);
    }
}

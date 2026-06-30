<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AirTravelRequestDetailRequest;
use App\Models\Admin\AirTravelRequest;
use App\Models\Admin\AirTravelRequestDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AirTravelRequestDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = AirTravelRequestDetail::query()->with(['parent'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|integer|exists:air_travel_requests,id',
            'date' => 'required|date|date_format:Y-m-d',
            'cnic' => 'required',
            'seat_name' => 'required',
            'traveller_name' => 'required',
            'department_id' => 'required',
            'purpose' => 'required',
            'estimated_amount' => 'required',
            'revised_amount' => 'required',
            'traveller_contact_no' => 'required',
            'trip_type' => 'required',
        ]);

        try {
            DB::beginTransaction();
            if($this->input['return_date'] != ""){
                $this->input['return_date']=date('Y-m-d H:i:s',strtotime( $this->input['return_date']));
            }else{
                unset( $this->input['return_date']);
               // $this->input['return_date']=NULL;
            }

            $data = AirTravelRequestDetail::query()->create($this->input);
            $totalAmount = AirTravelRequestDetail::query()->where('parent_id', $request->parent_id)->sum('estimated_amount');
            AirTravelRequest::query()->find($request->parent_id)?->update(['total_amount' => $totalAmount]);

            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
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
        $parent = AirTravelRequestDetail::query()->with(['parent'])->findOrFail($id);
        return resp(1, 'Successful!', $parent, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AirTravelRequestDetail $atrItem)
    {
        $request->validate([
            'parent_id' => 'required|integer|exists:air_travel_requests,id',
            'date' => 'required|date|date_format:Y-m-d',
            'cnic' => 'required',
            'seat_name' => 'required',
            'traveller_name' => 'required',
            'department_id' => 'required',
            'purpose' => 'required',
            'estimated_amount' => 'required',
            'revised_amount' => 'required',
            'traveller_contact_no' => 'required',
            'trip_type' => 'required',
        ]);

        try {
            DB::beginTransaction();
            if($this->input['return_date'] != ""){
                $this->input['return_date']=date('Y-m-d H:i:s',strtotime( $this->input['return_date']));
            }else{
                unset( $this->input['return_date']);
                // $this->input['return_date']=NULL;
            }
            $atrItem->update($this->input);
            $totalAmount = AirTravelRequestDetail::query()->where('parent_id', $request->parent_id)->sum('estimated_amount');
            AirTravelRequest::query()->find($request->parent_id)?->update(['total_amount' => $totalAmount]);

            DB::commit();
            return resp(1, 'Successful!', $atrItem->refresh(), Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $parent = AirTravelRequestDetail::query()->findOrFail($id);
        $parent->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    public function addBoardingDocument(Request $request, AirTravelRequestDetail $atr)
    {

        try {
            DB::beginTransaction();
            if($request->file('boarding_document')){
                $responce=$this->saveBoardingPass($request,'ATRDocuments');
                $this->input['boarding_document']=$responce;
                $atr->update($this->input);
                DB::commit();
                return resp(1, 'Successful!', $atr->refresh(), Response::HTTP_OK);
            }else{
                return resp(0, 'File not found!',[], Response::HTTP_EXPECTATION_FAILED);
            }


        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveBoardingPass($request,$folder){

        $file = $request->file('boarding_document');
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

}

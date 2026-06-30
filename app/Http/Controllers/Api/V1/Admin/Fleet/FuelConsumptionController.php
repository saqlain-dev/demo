<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\FuelConsumption;
use App\Models\Admin\Fleet\FuelRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class FuelConsumptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = FuelConsumption::all();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fuel_request_id' => 'required',
            'description' => 'required',
            'qty' => 'required',
            //'tank_capacity' => 'required',
            'current_km' => 'required',
            'prev_km' => 'required',
            //'location' => 'required',
            'fuel_card' => 'required',
            'fuel_average' => 'required',
            'date' => 'required',
        ]);
        if($request->hasFile('pay_receipt')) {
            $responce = $this->saveFuelReceipt($request, 'fuel_consumption_receipts');
            if ($responce) {
                $this->input['pay_receipt'] = $responce;
            }
        }
        try {
            DB::beginTransaction();
            $item = FuelConsumption::query()->create($this->input);
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
    public function show(FuelConsumption $fuelConsumption)
    {
        return resp('1', 'Successful!', $fuelConsumption, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FuelConsumption $fuelConsumption)
    {
        $request->validate([
            'fuel_request_id' => 'required',
            'description' => 'required',
            'qty' => 'required',
           // 'tank_capacity' => 'required',
            'current_km' => 'required',
            'prev_km' => 'required',
            //'location' => 'required',
            'fuel_card' => 'required',
            'fuel_average' => 'required',
            'date' => 'required',
        ]);
        if($request->file('pay_receipt')){
            $responce=$this->saveFuelReceipt($request,'fuel_consumption_receipts');
            $this->input['pay_receipt']=$responce;
        }
        try {
            DB::beginTransaction();
            $item = $fuelConsumption->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function saveFuelReceipt($request,$folder){

        $file = $request->file('pay_receipt');
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
     * Remove the specified resource from storage.
     */
    public function destroy(FuelConsumption $fuelConsumption): JsonResponse
    {
        $item = $fuelConsumption->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\VendorAtrQuotation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class VendorAtrQuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'atr_id' => 'required',
            'date_time' => 'required',
            'check_in_time' => 'required',
            'airline' => 'required',
            'ticket_fare' => 'required',
            'airline_category' => 'required'
        ]);
        try {

            DB::beginTransaction();
            $this->input['date_time']=date('Y-m-d h:i:s',strtotime($request->date_time));
            $atrQuotation=VendorAtrQuotation::query()->create($this->input);
            DB::commit();

            return resp('1', 'Quotation added Successfully!', $atrQuotation->load('airline','airlineCategory'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);

        }
    }

    public function getAtrQuotationList($atr)
    {
        $data['atr_quotation_list'] = VendorAtrQuotation::query()->with('airline','airlineCategory')->where('atr_id',$atr)->get();

        return resp('1', 'Successfully!', $data, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(VendorAtrQuotation $atr_quotation)
    {
        return resp('1', 'Successfully!', $atr_quotation->load('airline',
        'airlineCategory','vendorDetail'), Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VendorAtrQuotation $vendorAtrQuotation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VendorAtrQuotation $atr_quotation)
    {
        $atr_quotation->delete();

        return resp('1', 'Successfully!', $atr_quotation, Response::HTTP_CREATED);
    }
}

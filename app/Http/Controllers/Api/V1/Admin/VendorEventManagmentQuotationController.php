<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorEventManagmentQuotation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class VendorEventManagmentQuotationController extends Controller
{
     /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'event_management_details_id' => 'required',
            'vendor_id' => 'required',
        ]);
        try {

            DB::beginTransaction();
            $eventQuotation=VendorEventManagmentQuotation::query()->create($this->input);
            DB::commit();

            return resp('1', 'Quotation added Successfully!', $eventQuotation->load('eventManagement','eventManagementDetails','vendorDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);

        }
    }

    public function getEventManagementQuotationList($eventManagementId)
    {
        $data['event_management_quotation_list'] = VendorEventManagmentQuotation::query()->with('eventManagement','eventManagementDetails','vendorDetail')->where('event_management_id',$eventManagementId)->get();

        return resp('1', 'Successfully!', $data, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(VendorEventManagmentQuotation $eventManagementQuotation)
    {
        return resp('1', 'Successfully!', $eventManagementQuotation->load('eventManagement','eventManagementDetails','vendorDetail'), Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VendorEventManagmentQuotation $vendorAtrQuotation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VendorEventManagmentQuotation $eventManagementQuotation)
    {
        $eventManagementQuotation->delete();

        return resp('1', 'Successfully!', $eventManagementQuotation, Response::HTTP_CREATED);
    }

    public function acceptQuotation(Request $request, $eventManagementQuotationId)
    {
        $eventManagementQuotation = VendorEventManagmentQuotation::query()->find($eventManagementQuotationId);
       
        if($eventManagementQuotation){

            $request->validate([
                'quotation_status' => 'required',
            ]);

            try {

                $eventManagementQuotation->update($request->all());

                DB::commit();

                $eventManagementQuotation=VendorEventManagmentQuotation::query()->findOrFail($eventManagementQuotation->id);
                return resp(1,'Successful!', $eventManagementQuotation->load('eventManagement','eventManagementDetails','vendorDetail'),Response::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();

                return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
            }
        }else{
            return resp(0,'Event Management Quotation not found.', [],Response::HTTP_EXPECTATION_FAILED);
        }
    }
}

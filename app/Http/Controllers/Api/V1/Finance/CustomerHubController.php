<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Api\V1\Donar\DonarProfileController;
use App\Http\Controllers\Controller;
use App\Models\Donar\DonarProfile;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\ChartOfAccount\HeadClass;
use App\Models\Finance\CustomerHub;
use App\Models\Program\ProjectImplementingPartner;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CustomerHubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'manage_finance_configuration'
        ]);

        $data['customers'] = CustomerHub::with('customerable','coaDetail','coaClassDetail')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'manage_finance_configuration'
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'customerable_id' => 'required|integer',
            'customerable_type' => 'required|string',
            'customer_coa' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $this->input['customerable_type']= $request->customerable_type === 'donor' ? DonarProfile::class : ($request->customerable_type === 'vendor' ? Vendor::class : ProjectImplementingPartner::class);
            $customer=CustomerHub::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $customer->load('customerable','coaDetail','coaClassDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerHub $customer)
    {
        $this->authorizeAny([
            'manage_finance_configuration'
        ]);

        return resp(1, 'Successful!', $customer->load('coaDetail','coaClassDetail'), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomerHub $customer)
    {
        $this->authorizeAny([
            'manage_finance_configuration'
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'customerable_id' => 'required|integer',
            'customerable_type' => 'required|string',
            'customer_coa' => 'required',
            //'customer_hub_id' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $this->input['customerable_type']= $request->customerable_type === 'donor' ? DonarProfile::class : ($request->customerable_type === 'vendor' ? Vendor::class : ProjectImplementingPartner::class);
            CustomerHub::query()->where('id',$customer->id)->update($this->input);
            $customer->refresh();
            DB::commit();
            return resp(1, 'Successful!', $customer->load('customerable','coaDetail','coaClassDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerHub $customerHub)
    {
        //
    }

    public function getCustomerList()
    {
        $donor_list = DonarProfile::all();
        $partner_list = ProjectImplementingPartner::all();
        $vendor_list = Vendor::all();

        $combined = $donor_list->map(function ($item) {
            $item->type = 'donor';
            return $item;
        })->merge(
            $partner_list->map(function ($item) {
                $item->type = 'partner';
                return $item;
            })
        )->merge(
            $vendor_list->map(function ($item) {
                $item->type = 'vendor';
                return $item;
            })
        );

        $data['customer_list'] = $combined;
        $data['coa']=ChartOfAccount::all();
        $data['head_class']=HeadClass::all();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}

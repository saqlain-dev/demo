<?php

namespace App\Http\Controllers\Api\V1\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Prospect;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProspectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'prospect_view',
        ]);
        $data['prospect_listing']=Prospect::query()->with('marketSegment','customerGroup','industry','territory','company','country')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'prospect_create',
        ]);
        $request->validate([
            'company_name' => 'required',
            'market_segment_id' => 'required',
            'prospect_owner' => 'required',
            'customer_group_id' => 'required',
            'industry_id' => 'required',
            'territory_id' => 'required',
            //'company_id' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $Prospect=Prospect::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $Prospect->load('marketSegment','customerGroup','industry','territory','company','country'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Prospect $prospect)
    {
        $this->authorizeAny([
            'prospect_view',
        ]);
        return resp(1, 'Successful!', $prospect->load('marketSegment','customerGroup','industry','territory','company','country'), Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prospect $prospect)
    {
        $this->authorizeAny([
            'prospect_update',
        ]);
        $request->validate([
            'company_name' => 'required',
            'market_segment_id' => 'required',
            'prospect_owner' => 'required',
            'customer_group_id' => 'required',
            'industry_id' => 'required',
            'territory_id' => 'required',
            //'company_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $prospect->update($this->input);
            $prospect->refresh();
            DB::commit();
            return resp(1, 'Successfully Update!', $prospect->load('marketSegment','customerGroup','industry','territory','company','country'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prospect $prospect)
    {

        $this->authorizeAny([
            'prospect_delete',
        ]);
        $prospect->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }

    public function getProspectDropdowns()
    {
        $data['market_segment']=Type::getTypeValues('market-segment');
        $data['customer_group']=Type::getTypeValues('customer-group');
        $data['industry']=Type::getTypeValues('industry-name');
        $data['company_listing']=Company::query()->with('currency','country')->get();
        $data['countries']=DB::table('countries')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}

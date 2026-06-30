<?php

namespace App\Http\Controllers\Api\V1\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign\Campaign;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'campaigns_view',
        ]);
        $data['campaign']=Campaign::query()->with('campaignStatus','campaignType','campaignDetail.template','emailCampaign')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'campaigns_create',
        ]);
        $request->validate([
            'campaign_name' => 'required',
            'campaign_type' => 'required',
            'campaign_status' => 'required',
            'campaign_desc' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $campaign=Campaign::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $campaign->load('campaignStatus','campaignType','campaignDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Campaign $campaign)
    {
        $this->authorizeAny([
            'campaigns_view',
        ]);
        return resp(1, 'Successful!', $campaign->load('campaignStatus','campaignType','campaignDetail.template','emailCampaign'), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Campaign $campaign)
    {
        $this->authorizeAny([
            'campaigns_update',
        ]);
        $request->validate([
            'campaign_name' => 'required',
            'campaign_type' => 'required',
            'campaign_status' => 'required',
            'campaign_desc' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $campaign->update($this->input);
            $campaign->refresh();

            DB::commit();
            return resp(1, 'Successful!', $campaign->load('campaignStatus','campaignType','campaignDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign)
    {
        $this->authorizeAny([
            'campaigns_delete',
        ]);
        $campaign->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }
}

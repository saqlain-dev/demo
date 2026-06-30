<?php

namespace App\Http\Controllers\Api\V1\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign\Campaign;
use App\Models\Campaign\CampaignDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CampaignDetailController extends Controller
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
            'campaign_id' => 'required',
            'email_template_id' => 'required',
            'send_after_days' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $CampaignDetail=CampaignDetail::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $CampaignDetail, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CampaignDetail $campaignDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CampaignDetail $campaign_detail)
    {
        $request->validate([
            'campaign_id' => 'required',
            'email_template_id' => 'required',
            'send_after_days' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $campaign_detail->update($this->input);
            $campaign_detail->refresh();

            DB::commit();
            return resp(1, 'Successful!', $campaign_detail, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CampaignDetail $campaign_detail)
    {
        $campaign_detail->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }
}

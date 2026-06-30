<?php

namespace App\Http\Controllers\Api\V1\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign\Campaign;
use App\Models\Campaign\EmailCampaign;
use App\Models\Configuration\GeneralTemplates;
use App\Models\Employee;
use App\Models\SalesTeam\SalesTeam;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmailCampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['email_campaign_list']=EmailCampaign::query()->with('campaign','emailCampaign','emailSender')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required',
            'email_campaign_for' => 'required',
            'recipient' => 'required',
            'sender' => 'required',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
        ]);

        try {
            DB::beginTransaction();
            $emailCampaign=EmailCampaign::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $emailCampaign->load('campaign','emailCampaign','emailSender'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmailCampaign $email_campaign)
    {
        return resp(1, 'Successful!', $email_campaign->load('campaign','emailCampaign','emailSender'), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmailCampaign $email_campaign)
    {
        $request->validate([
            'campaign_id' => 'required',
            'email_campaign_for' => 'required',
            'recipient' => 'required',
            'sender' => 'required',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
        ]);

        try {
            DB::beginTransaction();
            $emailCampaign=$email_campaign->update($this->input);
            $email_campaign->refresh();
            DB::commit();
            return resp(1, 'Successful!', $emailCampaign->load('campaign','emailCampaign','emailSender'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailCampaign $email_campaign)
    {
        $email_campaign->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }

    public function getEmailCampaignDropdowns()
    {
        $data['campaign']=Campaign::all();
        $data['email_campaign_for']=Type::getTypeValues('email-campaign-for');
        $data['email_sender']=Employee::query()->whereHas('salesTeamEmployee')->with('salesTeamEmployee')->get();
        $data['general_templates']=GeneralTemplates::query()->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}

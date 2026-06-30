<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Campaign\Campaign;
use App\Models\ErpPurchaseOrder\ErpPurchaseOrder;
use App\Models\Lead;
use App\Models\Opportunity\Opportunity;
use App\Models\Quotation\Quotation;
use Illuminate\Http\Response;

class ErpDashboardController extends Controller
{
    //
    public function erpDashboardStats()
    {
        $data['leads']=Lead::query()->count();
        $data['opportunities']=Lead::query()->where('lead_status','588')->count();
        $data['converted']=Lead::query()->where('lead_status','600')->count();
        $data['replied']=Lead::query()->where('lead_status','598')->count();
        $data['do_not_contact']=Lead::query()->where('lead_status','644')->count();
        $data['lead_owners']=$leadCounts = Lead::query()
            ->selectRaw('lead_owner, COUNT(*) as total_leads')
            ->groupBy('lead_owner')
            ->with(['leadOwner:id,name']) // Select only id and name from the leadOwner relation
            ->get()
            ->map(function ($lead) {
                return [
                    'lead_owner_name' => $lead->leadOwner->name ?? 'Unknown',
                    'total_leads' => $lead->total_leads,
                ];
            });




        $data['quotations'] = Quotation::query()
            ->withSum('quotationDetail', 'amount') // Sum of amount from quotationDetail
            ->get();


        $data['total_quotations'] = $data['quotations']->count();


        $data['quotations_amount'] = $data['quotations']->sum('quotation_detail_sum_amount');




        $data['purchase_order_list'] = ErpPurchaseOrder::query()
            ->withSum('purchaseOrderDetail', 'amount') // Sum amount from purchaseOrderDetail
            ->get();


        $data['total_orders'] = $data['purchase_order_list']->count();


        $data['total_purchase_order_amount'] = $data['purchase_order_list']->sum('purchase_order_detail_sum_amount');

        $data['Opportunities_listing'] = Opportunity::query()
            ->with([
                'opportunityable',
                'opportunityType',
                'opp_activities',
                'rfp' => [
                    'rfpDetail.division',
                    'quotation' => [
                        'quotationDetail',
                        'supplier',
                        'quotationStatus'
                    ]
                ]
            ])
            ->withCount('opp_activities')
            ->get();
        $total_margin_amount = 0;

// ✅ Loop through opportunities
        foreach ($data['Opportunities_listing'] as $key =>  $opportunity) {
            // ✅ Check if rfp and quotation exist
            if (!isset($opportunity->rfp) || !isset($opportunity->rfp->quotation)) {
                continue;
            }

            // ✅ Check if quotationDetail exists
            if (!isset($opportunity->rfp->quotation->quotationDetail)) {
                continue;
            }

            // ✅ Loop through quotation details and sum amounts
            foreach ($opportunity->rfp->quotation->quotationDetail as $quotationDetail) {
                $total_margin_amount += $quotationDetail->margin_rate ?? 0;

            }
            $data['Opportunities_listing'][$key]['total_margin_amount']=$total_margin_amount;
        }

        $data['campaign_listing']=Campaign::query()->with('campaignStatus','campaignType')->get();

        return resp('1', 'Successful!', $data, Response::HTTP_OK);

    }
}

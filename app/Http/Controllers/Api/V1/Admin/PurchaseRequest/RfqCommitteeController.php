<?php

namespace App\Http\Controllers\Api\V1\Admin\PurchaseRequest;

use App\Http\Controllers\Controller;
use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\RfqCommittee;
use App\Models\ProjectAwarded;
use App\Models\VendorQuotation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RfqCommitteeController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(RfqCommittee $rfqCommittee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RfqCommittee $rfqCommittee)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'pr_rfq_id' => 'required|exists:purchase_request_rfqs,id',
            //'comments' => 'required',
            'vendor_id' => 'required',
            'vendor_ids' => 'array',
            'vendor_ids.*' => 'exists:vendors,id',
            'status' => 'nullable',
        ]);
        try {
            DB::beginTransaction();
            $rfqCommittee->update($request->only(['user_id', 'status', 'pr_rfq_id', 'vendor_id', 'comments']));
            if ($rfqCommittee) {
                if ($request->filled('vendor_ids')) {
                    $rfqCommittee->vendors()->sync($request->vendor_ids);
                }

                $totalRecords = RfqCommittee::query()->where('pr_rfq_id', $request->pr_rfq_id)->count();
                $onethirPercent = ceil($totalRecords / 3);

                $approvedRecords = RfqCommittee::query()->where('pr_rfq_id', $request->pr_rfq_id)->where('vendor_id', $request->vendor_id)->count();
                $percentageApproved = 0;
                if ($totalRecords > 0) {
                    $percentageApproved = $approvedRecords;

                }
                if ($percentageApproved >= $onethirPercent) {

                    if ($request->filled('vendor_ids') && count($request->vendor_ids) > 1) {
                        foreach ($request->vendor_ids as $vendorId) {
                            $this->awardRfq($request->pr_rfq_id, $vendorId);
                        }
                    } else {
                        $this->awardRfq($request->pr_rfq_id, $request->vendor_id);
                    }
                }
            }
            DB::commit();
            return resp(1, 'Successful!', [], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function awardRfq($pr_rfq_id, $vendor_id)
    {


        DB::beginTransaction();
        $vendorQuotation = VendorQuotation::where('vendor_id', $vendor_id)
            ->whereHasMorph('projectable', [PurchaseRequestRfq::class], function ($query) use ($pr_rfq_id) {
                $query->where('id', $pr_rfq_id);
            })
            ->first();
        // if ($vendorQuotation) {
        //     $vendorQuotation->quotationItems()->update(['awarded_status' => 0]);
        // }
        $pr_rfq_detail = PurchaseRequestRfq::query()->withCount('awardProject')->findOrFail($pr_rfq_id);
        $alreadyAwarded = ProjectAwarded::where('awardable_type', PurchaseRequestRfq::class)
            ->where('awardable_id', $pr_rfq_id)
            ->where('vendor_id', $vendor_id)
            ->exists();
        if (!$alreadyAwarded) {
            $projectAwarded = new ProjectAwarded();
            $projectAwarded->vendor_id = $vendor_id;
            $projectAwarded->quotation_id = $vendorQuotation->id;
            $projectAwarded->awardable()->associate($pr_rfq_detail);
            $projectAwarded->save();
            DB::commit();
            //return resp('1', 'Project awarded Successfully!', [], Response::HTTP_OK);
        } else {
            DB::rollBack();

            //return resp('0', 'Project already awarded', [], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RfqCommittee $rfqCommittee)
    {
        //
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Tender;
use App\Models\Admin\TenderCommittee;
use App\Models\ProjectAwarded;
use App\Models\User;
use App\Models\VendorQuotation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TenderCommitteeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = TenderCommittee::query()->with('tender', 'member')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tender_id' => 'required|exists:tenders,id',
            'comments' => 'required'
        ]);
        $updateCommittee= TenderCommittee::query()->where($item->id)->update($this->input);
        if($updateCommittee){
            $totalRecords = BoardResolutionApprovalCommittee::query()->where('resolution_id',$item->resolution_id)->count();
            $onethirPercent=ceil($totalRecords/3);

            $approvedRecords = BoardResolutionApprovalCommittee::query()->where('status', 1)->where('resolution_id',$item->resolution_id)->count();
            $rejectRecords = BoardResolutionApprovalCommittee::query()->where('status', 2)->where('resolution_id',$item->resolution_id)->count();
            $totalAppReject=$approvedRecords + $rejectRecords;

            $percentageApproved=0;
            $percentageReject=0;
            if ($totalRecords > 0) {
                //$percentageApproved = ($approvedRecords / $totalRecords) * 100;
                $percentageApproved = $approvedRecords;
                //$percentageReject = ($rejectRecords / $totalRecords) * 100;
                $percentageReject = $rejectRecords;
            } else {
                $percentageApproved = 0; // Handle division by zero
                $percentageReject = 0; // Handle division by zero
            }
            if( $percentageApproved >= $onethirPercent){
                BoardResolutionPassed::query()->where('id',$item->resolution_id)->update(array('status'=>1));
            }else {
                if( $percentageReject >= $onethirPercent && $totalAppReject == $totalRecords){
                    BoardResolutionPassed::query()->where('id',$item->resolution_id)->update(array('status'=>2));
                }
            }
        }
        $item = TenderCommittee::query()->create($request->all());
        return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show($itemId)
    {
        $item = TenderCommittee::query()->with('tender', 'member')->findOrFail($itemId);
        return resp(1, 'Successful!', $item, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TenderCommittee $tenderCommittee)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tender_id' => 'required|exists:tenders,id',
            'comments' => 'required',
            'vendor_id' => 'required'
        ]);

        $tenderCommittee->update($request->all());
        if($tenderCommittee) {
            $totalRecords = TenderCommittee::query()->where('tender_id',$request->tender_id)->count();
            $onethirPercent=ceil($totalRecords/3);

            $approvedRecords = TenderCommittee::query()->where('tender_id',$request->tender_id)->where('vendor_id',$request->vendor_id)->count();
            $percentageApproved=0;
            if ($totalRecords > 0) {
                $percentageApproved = $approvedRecords;

            }
            if( $percentageApproved >= $onethirPercent){

                $this->awardTender($request->tender_id,$request->vendor_id);
            }
        }
        return resp(1, 'Successful!', $tenderCommittee->refresh(), Response::HTTP_CREATED);
    }

    public function awardTender($tender_id,$vendor_id)
    {


            DB::beginTransaction();
            $vendorQuotation = VendorQuotation::where('vendor_id', $vendor_id)
                ->whereHasMorph('projectable', [Tender::class], function ($query) use ($tender_id) {
                    $query->where('id', $tender_id);
                })
                ->first();
            $tenderDetail=Tender::query()->withCount('awardProject')->findOrFail($tender_id);

            if($tenderDetail->awardable_projects_count == 0){
                $projectAwarded=new ProjectAwarded();
                $projectAwarded->vendor_id =$vendor_id;
                $projectAwarded->quotation_id =$vendorQuotation->id;
                $projectAwarded->awardable()->associate($tenderDetail);
                $projectAwarded->save();
                DB::commit();
                //return resp('1', 'Project awarded Successfully!', [], Response::HTTP_OK);
            }else{
                //return resp('0', 'Project already awarded', [], Response::HTTP_EXPECTATION_FAILED);
            }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TenderCommittee $tenderCommittee)
    {
        $tenderCommittee->delete();
        $message = "Record Deleted Successfully";
        return resp(1, 'Successful!', $message, Response::HTTP_OK);
    }

    public function getDropdowns()
    {
        $data['users'] = User::all();
        $data['tenders'] = Tender::query()->with('vendor_quotations.vendor')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}

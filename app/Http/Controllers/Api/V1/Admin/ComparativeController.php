<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\RfqCommittee;
use App\Models\Admin\Tender;
use App\Models\Admin\TenderCommittee;
use App\Models\ProjectAwarded;
use App\Models\VendorQuotation;
use App\Models\VendorQuotationDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ComparativeController extends Controller
{
    public function index()
    {

    }

    public function rfqProjects()
    {
        
        $this->authorizeAny([
            'received_quotations',
            'manage_auction_received_quotations',
            'manage_audit_procurement',
            'manage_employee_portal',
        ]);

        $data['projectsList']=PurchaseRequestRfq::query()->with('items','rfType','quotationType','createdBy')->where('float_rfq',1)->orderByDesc('id')->get();
        return resp(1,'Successful!.', $data,Response::HTTP_OK);
    }
    public function rfqProjectsReport()
    {
        
        $this->authorizeAny([
            'received_quotations',
            'manage_auction_received_quotations',
            'manage_audit_procurement',
        ]);

        $data['projectsList']=PurchaseRequestRfq::query()->with('items.itemDetail.itemCategory','items.itemDetail.subCategory','rfType','quotationType','createdBy','vendor_quotations.vendor', 'vendor_quotations.quotationItems.item','vendor_quotations.awardQuotation')->where('float_rfq',1)->orderByDesc('id')->get();
        return resp(1,'Successful!.', $data,Response::HTTP_OK);
    }
    public function tenderProjects()
    {
        $this->authorizeAny([
            'manage_received_tenders_quotations',
            'manage_audit_procurement',
        ]);

        $data['projectsList']=Tender::query()->with('tenderDetails.itemDetail','tenderNature')->where('float_tender',1)->get();
        return resp(1,'Successful!.', $data,Response::HTTP_OK);
    }

    public function getProjectQuotation($id)
    {
        $projectsQuotation= PurchaseRequestRfq::query()
            ->with(['vendor_quotations' => function ($query) {
                $query->where('apply_status ', 1);

            },'purchaseRequestRFQLogs.user','items.itemDetail','vendor_quotations.vendor', 'vendor_quotations.quotationItems.item','purchase_request','disposeRequest'])
            ->find($id);

        foreach($projectsQuotation['vendor_quotations'] as $key => $quotation){
            if ($projectsQuotation->is_comp_generated == 1) {
                $projectsQuotation['vendor_quotations'][$key]['total_quotation_amount'] = decode($quotation->total_quotation_amount);
            }
            $documentResp=$this->getQuotationDocuments($projectsQuotation->id,$quotation->vendor_id);
            $projectsQuotation['vendor_quotations'][$key]['projects_documents']=$documentResp;
        }

        $data['projectsQuotation']=$projectsQuotation;
        return resp('1', 'Successfully!', $data, Response::HTTP_CREATED);
    }
    public function getTenderQuotation($id)
    {
        $tenderQuotation= Tender::query()
            ->with(['vendor_quotations' => function ($query) {
                $query->where('apply_status ', 1);

            }, 'tenderDetails.itemDetail','vendor_quotations.vendor', 'vendor_quotations.quotationItems.item','purchase_request'])
            ->find($id);

        foreach($tenderQuotation['vendor_quotations'] as $key => $quotation){
            if ($tenderQuotation->is_comp_generated == 1) {
                $tenderQuotation['vendor_quotations'][$key]['total_quotation_amount'] = decode($quotation->total_quotation_amount);
            }
            $documentResp=$this->getTenderQuotationDocuments($tenderQuotation->id,$quotation->vendor_id);
            $tenderQuotation['vendor_quotations'][$key]['projects_documents']=$documentResp;
        }

        $data['tenderQuotation']=$tenderQuotation;
        return resp('1', 'Successfully!', $data, Response::HTTP_CREATED);
    }

    public function getTenderQuotationDocuments($tender_id,$vendor_id)
    {

       $document=VendorQuotationDocument::query()->where('tender_id',$tender_id)->where('vendor_id',$vendor_id)->with('documentDetail')->get();
       return $document;
    }
    public function getQuotationDocuments($rfq_id,$vendor_id)
    {

       $document=VendorQuotationDocument::query()->where('rfq_id',$rfq_id)->where('vendor_id',$vendor_id)->with('documentDetail')->get();
       return $document;
    }

    public function shortListVendors(Request $request)
    {

        $disqualify_reason=$this->input['disqualify_reason'];
        $quotation_id=$this->input['quotation_id'];
        $status=$this->input['status'];

        if($status == 3){

            VendorQuotation::query()->where('id',$quotation_id)->update(array('is_qualified'=>3,'disqualify_reason'=>$disqualify_reason));
        }else{

            VendorQuotation::query()->where('id',$quotation_id)->update(array('is_qualified'=>1,'disqualify_reason'=>$disqualify_reason));
        }

        return resp('1', 'Successfully!', [], Response::HTTP_OK);

    }

    public function generateComparative(Request $request,PurchaseRequestRfq $item)
    {

        if (!empty($this->input['user_id']) && is_array($this->input['user_id'])) {
            foreach ($this->input['user_id'] as $userID) {
                $insert = [
                    'user_id' => $userID,
                    'pr_rfq_id' => $item->id,
                ];
                RfqCommittee::query()->create($insert);
            }
        }

        $item->is_comp_generated=1;
        $item->save();
        return resp('1', 'Successfully!', [], Response::HTTP_OK);
    }
    public function generateTenderComparative(Request $request,Tender $item)
    {
        $request->validate([
            'user_id' => 'array',
            'user_id.*' => 'required',
        ]);


        if($this->input['user_id']){
            foreach($this->input['user_id'] as $userID){
                $insert=array(
                    'user_id'=>$userID,
                    'tender_id'=>$item->id
                );
                TenderCommittee::query()->create($insert);
            }
        }
        $item->is_comp_generated=1;
        $item->save();
        return resp('1', 'Successfully!', [], Response::HTTP_OK);
    }
    public function getResponsiveQuotation($id)
    {
        $data['projectsQuotation']=$projectsQuotation = PurchaseRequestRfq::query()
            ->with(['vendorRecommendations.details.vendor','vendorRecommendations.details.item','items.itemDetail','rfqMinutesOfMeeting','committee.userDetail','committee.vendors','committee.vendorDetail','purchase_request','disposeRequest','vendor_quotations' => function ($query) {
                $query->where('apply_status ', 1);
                $query->where('is_qualified ', 1);

            }, 'vendor_quotations.vendor', 'vendor_quotations.quotationItems.item','vendor_quotations.awardQuotation','vendor_quotations.awardQuotation.awardPo.acknowledgementHistories.updatedBy','vendor_quotations.awardQuotation.awardWo.acknowledgementHistories.updatedBy','vendor_quotations.awardQuotation.awardCc.acknowledgementHistories.updatedBy'])
            ->find($id);

        $data['committee_user']=RfqCommittee::query()->where('pr_rfq_id',$id)->where('user_id',auth()->user()->id)->with('userDetail')->first();


        foreach($projectsQuotation->vendor_quotations as $key=> $quotation){

            $projectsQuotation->vendor_quotations[$key]->total_quotation_amount=decode(@$quotation->total_quotation_amount);
        }
        return resp('1', 'Successfully!', $data, Response::HTTP_CREATED);
    }
    public function getTenderResponsiveQuotation($id)
    {
        $data['projectsQuotation']=$projectsQuotation = Tender::query()
            ->with(['vendorRecommendations.details.vendor','vendorRecommendations.details.item','tenderLogs.createdBy','tenderMinutesOfMeeting','tenderDetails.itemDetail','purchase_request','vendor_quotations' => function ($query) {
                $query->where('apply_status ', 1);
                $query->where('is_qualified ', 1);

            }, 'vendor_quotations.vendor', 'vendor_quotations.quotationItems.item','vendor_quotations.awardQuotation','tendercommittee.userDetail','tendercommittee.vendors','tendercommittee.vendorDetail'])
            ->find($id);

        $data['committee_user']=TenderCommittee::query()->where('tender_id',$id)->where('user_id',auth()->user()->id)->with('userDetail')->first();

        if(isset($projectsQuotation->vendor_quotations)){
            foreach($projectsQuotation->vendor_quotations as $key=> $quotation){

                $projectsQuotation->vendor_quotations[$key]->total_quotation_amount=decode(@$quotation->total_quotation_amount);
                $documentResp=$this->getTenderQuotationDocuments($projectsQuotation->id,$quotation->vendor_id);
                $projectsQuotation['vendor_quotations'][$key]['projects_documents']=$documentResp;
            }
        }

        return resp('1', 'Successfully!', $data, Response::HTTP_CREATED);
    }

    public function projectAwarded(Request $request)
    {
        $request->validate([
            'rfq_id' => 'required',
            'quotation_id' => 'required',
            'vendor_id' => 'required',
        ]);
        try {

            DB::beginTransaction();
            $rfqid=$this->input['rfq_id'];
            unset($this->input['rfq_id']);

            $rfqDet=PurchaseRequestRfq::query()->withCount('awardProject')->findOrFail($rfqid);

            // if($rfqDet->award_project_count == 0){
                $projectAwarded=new ProjectAwarded();
                $projectAwarded->vendor_id =$this->input['vendor_id'];
                $projectAwarded->quotation_id =$this->input['quotation_id'];
                $projectAwarded->awardable()->associate($rfqDet);
                $projectAwarded->save();
                DB::commit();

                return resp('1', 'Project awarded Successfully!', [], Response::HTTP_OK);
            // }else{
            //     return resp('0', 'Project already awarded', [], Response::HTTP_EXPECTATION_FAILED);
            // }

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);

        }
    }
    public function tenderAwarded(Request $request)
    {
        $request->validate([
            'tender_id' => 'required',
            'quotation_id' => 'required',
            'vendor_id' => 'required',
        ]);
        try {

            DB::beginTransaction();
            $tender_id=$this->input['tender_id'];
            unset($this->input['tender_id']);
            $tenderDetail=Tender::query()->withCount('awardProject')->findOrFail($tender_id);
            // if($tenderDetail->awardable_projects_count == 0){
                $projectAwarded=new ProjectAwarded();
                $projectAwarded->vendor_id =$this->input['vendor_id'];
                $projectAwarded->quotation_id =$this->input['quotation_id'];
                $projectAwarded->awardable()->associate($tenderDetail);
                $projectAwarded->save();
                DB::commit();
                return resp('1', 'Project awarded Successfully!', [], Response::HTTP_OK);
            // }else{
            //     return resp('0', 'Project already awarded', [], Response::HTTP_EXPECTATION_FAILED);
            // }


        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);

        }
    }
}

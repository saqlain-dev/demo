<?php

namespace App\Http\Controllers\Api\V1\Admin\ConsultantContract;

use App\Http\Controllers\Controller;
use App\Models\Admin\ConsultantContract\ConsultantContract;
use App\Models\Admin\ConsultantContract\ConsultantContractDetail;
use App\Models\Admin\PurchaseRequestRfqDetail;
use App\Models\Admin\TenderDetail;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Configuration\GeneralTemplates;
use App\Models\Finance\LasConfiguration;
use App\Models\ProjectAwarded;
use App\Models\VendorQuotationDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConsultantContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'consultant_contracts_view',
            'manage_audit_procurement',
            'manage_vendor_portal'
        ]);

        $data['consultant_contract_listing'] = ConsultantContract::query()->with('invoices', 'CcItems.ccItem', 'rfqDetail.items.itemDetail', 'rfqDetail.purchase_request', 'tenderDetail.tenderDetails.itemDetail', 'tenderDetail.purchaseRequest')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'consultant_contracts_create'
        ]);

        $request->validate([
            'date' => 'required|date',
            'awarded_project_id' => 'required|integer|exists:project_awardeds,id',
            'tender_id' => 'nullable|integer|exists:tenders,id',
            'purchase_request_rfq_id' => 'nullable|integer|exists:purchase_request_rfqs,id',
            'contract' => 'required|string',
            'vendor_name' => 'required',
            'address' => 'required',
            'phone' => 'required',
            //'attachment' => 'nullable|file|max:5120',
        ]);
        try {
            $awardedProject = ProjectAwarded::query()->findOrFail($request->awarded_project_id);
            if ($awardedProject->po_status == 0 && $awardedProject->wo_status == 0 && $awardedProject->cc_status == 0) {
                DB::beginTransaction();
                // $this->input['contract'] = GeneralTemplates::query()->find($this->input['contract'])->template_data;
                $this->input['date'] = date('Y-m-d', strtotime($request->date));
                $this->input['project_award_id'] = $request->awarded_project_id;

               // $awardedProject = ProjectAwarded::query()->findOrFail($request->awarded_project_id);
                $this->input['vendor_id'] = $awardedProject->vendor_id;
                $consultantContract = ConsultantContract::query()->create($this->input);
                if ($consultantContract) {

                    if ($request->hasFile('attachment')){
                        $responce = $this->saveFile($request, 'consultant_contract');

                        if ($responce) {
                            $consultantContract->update([
                                'attachment' => $responce,
                            ]);
                        }
                    }

                    $items = array();
                    $rfqId = $this->input['rfq_id'] ?? null;
                    $tenderId = $this->input['tender_id'] ?? null;
                    if ($rfqId != null) {
                        $items = PurchaseRequestRfqDetail::query()->where('purchase_request_rfq_id', $this->input['rfq_id'])->get();
                    }
                    if ($tenderId != null) {
                        $items = TenderDetail::query()->where('tender_id', $this->input['tender_id'])->get();
                    }
                    if ($items) {
                        foreach ($items as $item) {
                            $bidAmount = $this->getItemBidAmount($awardedProject->quotation_id, $item['item_id']);

                            $insert = array(
                                'consultant_contract_id' => $consultantContract->id,
                                'description' => $item['description'],
                                'unit_of_measurement' => $item['unit_of_measurement'],
                                'required_quantity' => $item['required_quantity'],
                                'unit_price' => $bidAmount->bid_price,
                                'item_id' => $item['item_id'],
                            );
                            ConsultantContractDetail::query()->create($insert);

                        }
                        ProjectAwarded::query()->where('id', $this->input['awarded_project_id'])->update(array('cc_status' => 1));
                    }

                }
                DB::commit();
                return resp('1', 'Consultant Contract Added Successfully!', $consultantContract, Response::HTTP_OK);
            } else {
                return resp('1', 'Consultant Contract / Work Order / Purchase Order already Generated', [], Response::HTTP_OK);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    public function saveFile($request,$folder){

        $file = $request->file('attachment');
        $path = 'uploads/media/' . $folder;
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (!file_exists('uploads/media')) {
            mkdir('uploads/media', 0777, true);
        }
        if (!file_exists('uploads/media/' . $folder)) {
            mkdir('uploads/media/' . $folder, 0777, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        $file_name = str_replace(' ', '_', $filename);
        $file->move($path, $file_name);
        return $path.'/'.$file_name;

    }

    public function getItemBidAmount($quotationID, $itemId)
    {
        $itemQuotation = VendorQuotationDetail::query()->where('quotation_id', $quotationID)->where('item_id', $itemId)->first();
        return $itemQuotation;
    }

    /**
     * Display the specified resource.
     */
    public function show(ConsultantContract $consultantContract)
    {
        // $this->authorizeAny([
        //     'manage_audit_procurement',
        //     'consultant_contracts_view',
        // ]);
        $data['company_information'] = LasConfiguration::all();
        $data['view_consultant_contract'] = $consultantContract->load('CcItems.ccItem', 'vendor.vendorUser', 'rfqDetail.items.itemDetail', 'rfqDetail.purchase_request', 'tenderDetail.tenderDetails.itemDetail', 'tenderDetail.purchaseRequest');

        $data['approval_request'] = getNextApproval(69, auth()->user()->designation_id, $consultantContract->id);
        $data['approval_request_status'] = checkApprovalRequestStatus(69, $consultantContract->id);
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    //acknowledge consultant contract
    public function acknowledgmentConsultantcontract(Request $request)
    {
        $request->validate([
            'consultant_contract_id' => 'required|exists:consultant_contracts,id',
            'acknowledgment' => 'required',
            'award_id' => 'nullable',
        ]);

        $contract = ConsultantContract::find($request->consultant_contract_id);

        if(!$contract){
            return resp('0', 'Consultant Contract Not Found!', Response::HTTP_NOT_FOUND);
        }

        if ($request->acknowledgment == 3 && isset($request->award_id)) {
            ProjectAwarded::query()
                ->where('id', $request->award_id)
                ->update(['vendor_status' => 3]);
        }

        $updateData = [
            'acknowledgment' => $request->acknowledgment,
        ];

        if ($request->acknowledgment) {
            $updateData['acknowledgment_date'] = now();
        }

        $contract->update($updateData);

        return resp('1', 'Successfully acknowledged!', Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ConsultantContract $workOrder)
    {
        $this->authorizeAny([
            'consultant_contracts_update'
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConsultantContract $workOrder)
    {
        $this->authorizeAny([
            'consultant_contracts_delete'
        ]);

    }

    public function sendConsultantContractForApproval(ConsultantContract $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',69)->first();
        $sp_approval_process=ApprovalProcess::query()->where('approval_process_id',69)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',69)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($sp_approval_process->count() > 0 && $checkProcess == 0){

            foreach ($sp_approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                $Approval=ApprovalProcessList::query()->create($insert);
                sendNotification($approval['designation_id'],$approval_process_name->approval_process_name);

            }
            $update=array('approval_status'=>2);
            ConsultantContract::query()->where('id',$item->id)->update($update);
            return resp(1,'Consultant contract send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Consultant contract approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function updateLastAcknowledged(Request $request)
    {

        $request->validate([
            'consultant_contract_id' => 'required',
            'last_acknowledgement_date' => 'required|date',
        ]);

        $cc = ConsultantContract::query()->find($request->consultant_contract_id);
        if (!$cc) {
            return resp(0, 'Consultant Contract not found.', null, Response::HTTP_NOT_FOUND);
        }

        try {
            $cc->last_acknowledgement_date = $request->last_acknowledgement_date;
            $cc->save();

            return resp(1, 'Last Acknowledgement Date updated successfully.', $cc, Response::HTTP_OK);
        } catch (\Exception $e) {
            return resp(0, 'Failed to update record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

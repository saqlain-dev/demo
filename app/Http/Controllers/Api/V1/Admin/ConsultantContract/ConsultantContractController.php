<?php

namespace App\Http\Controllers\Api\V1\Admin\ConsultantContract;

use App\Http\Controllers\Controller;
use App\Models\Admin\ConsultantContract\ConsultantContract;
use App\Models\Admin\ConsultantContract\ConsultantContractDetail;
use App\Models\Admin\PurchaseRequestRfqDetail;
use App\Models\Admin\TenderDetail;
use App\Models\Configuration\GeneralTemplates;
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
        ]);

        $data['consultant_contract_listing'] = ConsultantContract::query()->with('CcItems.ccItem', 'rfqDetail.items.itemDetail', 'rfqDetail.purchase_request', 'tenderDetail.tenderDetails.itemDetail', 'tenderDetail.purchaseRequest')->get();
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
                        $extension = $request->file('attachment')->getClientOriginalExtension();
                        $attachmentPath = $request->file('attachment')->storeAs('images/consultant_contract', time() . '_attachment.' . $extension, 'public');

                        $consultantContract->update([
                            'attachment' => $attachmentPath,
                        ]);
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
        $this->authorizeAny([
            'manage_audit_procurement',
            'consultant_contracts_view',
        ]);

        $data['view_consultant_contract'] = $consultantContract->load('CcItems.ccItem', 'rfqDetail.items.itemDetail', 'rfqDetail.purchase_request', 'tenderDetail.tenderDetails.itemDetail', 'tenderDetail.purchaseRequest');
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
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
}

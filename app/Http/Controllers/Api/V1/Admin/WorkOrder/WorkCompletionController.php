<?php

namespace App\Http\Controllers\Api\V1\Admin\WorkOrder;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\WorkCompletion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkCompletionController extends Controller
{
    public function index(Request $request)
    {

        $query = WorkCompletion::with(['invoice', 'vendor', 'branchOffice']);

        if ($request->filled('vendor_id')) {
            $query->where('vender_id', $request->vendor_id);
        }

        if ($request->filled('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        if ($request->filled('invoice_date')) {
            $query->whereDate('invoice_date', $request->invoice_date);
        }

        return resp(1, 'Fetched successfully.', $query->get(), Response::HTTP_OK);
    }

    public function WorkCompletionByVendor(Request $request)
    {
        $query = WorkCompletion::with(['invoice', 'vendor']);

        if ($request->vendor_id) {
            $query->where('vender_id', $request->vendor_id);
        }

        return resp(1, 'Fetched successfully.', $query->get(), Response::HTTP_OK);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'vender_id' => 'required|exists:vendors,id',
            'invoice_date' => 'nullable|date',
            'branch_office_id' => 'nullable',
        ]);

        $data = WorkCompletion::create($validated);

        return resp(1, 'Created successfully.', $data, Response::HTTP_CREATED);
    }

    public function show($id)
    {

        $data['work_completion'] = WorkCompletion::with(['branchOffice', 'invoice' => [
            'eventManagement',
            'invoiceEventManagementDetail',
            'invoiceFuelRequest',
            'atr',
            'vr',
            'invoiceVRDetail',
            'invoiceAtrDetail',
            'vehicleMaintenanceForm',
            'invoiceVehicleMaintenanceDetail',
            'invoiceAudit.verifiedBy',
            'invoiceItems.itemDetail',
            'grn' => ['grnItem', 'poDetails' => ['PoItems', 'tenderDetails.purchaseRequest', 'rfqDetail' => ['vendor_quotations', 'purchase_request']]],
            'vendorDetail',
            'consultantContract.CcItems',
            'workOrder' => ['WoItems', 'tenderDetail.purchaseRequest', 'rfqDetail.purchase_request'],
            'rfq.disposeRequest',
            'ProjectId'
        ], 'vendor',])->findOrFail($id);
        $data['approval_request'] = getNextApproval(68, auth()->user()->designation_id, $id);
        $data['approval_request_status'] = checkApprovalRequestStatus(68, $id);

        return resp(1, 'Record fetched.', $data, Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {

        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'vender_id' => 'required|exists:vendors,id',
            'invoice_date' => 'nullable|date',
            'branch_office_id' => 'nullable',
        ]);

        $workCompletion = WorkCompletion::findOrFail($id);
        $workCompletion->update($validated);

        return resp(1, 'Updated successfully.', $workCompletion, Response::HTTP_OK);
    }

    public function destroy($id)
    {

        $workCompletion = WorkCompletion::findOrFail($id);
        $workCompletion->delete();

        return resp(1, 'Deleted successfully.', null, Response::HTTP_OK);
    }
    public function sendWorkCompletionForApproval(WorkCompletion $item)
    {

        $approval_process_name = ApprovalProcessName::query()->where('id', 68)->first();
        $approval_process = ApprovalProcess::query()->where('approval_process_id', 68)->get();
        $checkProcess = ApprovalProcessList::query()->where('approval_process_id', 68)->where('approval_request_status', 1)->where('request_module_id', $item->id)->count();
        if ($approval_process->count() > 0 && $checkProcess == 0) {

            foreach ($approval_process as $approval) {
                $insert = array(
                    'approval_process_id' => $approval['approval_process_id'],
                    'designation_id' => $approval['designation_id'],
                    'process_order' => $approval['process_order'],
                    'request_module_id' => $item->id,
                );
                if ($approval_process_name->isFinancialApproval == 1) {
                    if ($approval->financialAmount < $item->amount) {
                        $insert['approval_status'] = 0;
                        $Approval = ApprovalProcessList::query()->create($insert);
                    } else {
                        $Approval = ApprovalProcessList::query()->create($insert);
                    }
                } else {
                    $Approval = ApprovalProcessList::query()->create($insert);
                }

                sendNotification($approval['designation_id'], $approval_process_name->approval_process_name);
            }
            $update = array('approval_status' => 2);
            WorkCompletion::query()->where('id', $item->id)->update($update);
            return resp(1, 'Work Completion send for Approval.', $Approval, Response::HTTP_OK);
        } else {


            if ($checkProcess == 0) {
                return resp(0, 'Approval process not available', [], Response::HTTP_OK);
            } else {
                return resp(0, 'Work Completion approval already sent.', [], Response::HTTP_OK);
            }
        }
    }
}
